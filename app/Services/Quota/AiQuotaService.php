<?php

namespace App\Services\Quota;

use App\Enums\AiFeature;
use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Exceptions\QuotaExceededException;
use App\Models\AiUsageReservation;
use App\Models\Plan;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;

class AiQuotaService
{
    public function __construct(private readonly UsageCycleService $cycles) {}

    public function reserve(User $user, AiFeature $feature, int $units): ?AiUsageReservation
    {
        if ($units < 1) {
            throw new LogicException('Reservation units must be positive.');
        }

        return DB::transaction(function () use ($user, $feature, $units): ?AiUsageReservation {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if (! $lockedUser->is_active) {
                throw new LogicException('Inactive accounts cannot reserve AI quota.');
            }

            if ($lockedUser->role === UserRole::Admin) {
                return null;
            }

            $lockedPlan = $lockedUser->plan_id
                ? Plan::query()->lockForUpdate()->find($lockedUser->plan_id)
                : null;

            if (! $lockedPlan?->is_active) {
                throw new QuotaExceededException('An active plan is required to use AI features.');
            }

            $lockedUser->setRelation('plan', $lockedPlan);
            $this->cycles->resetIfDue($lockedUser);

            [$limit, $usedColumn, $reservedColumn] = $this->quotaColumns($lockedUser, $feature);
            $used = (int) $lockedUser->{$usedColumn};
            $reserved = (int) $lockedUser->{$reservedColumn};

            if (($used + $reserved + $units) > $limit) {
                throw new QuotaExceededException($this->quotaMessage($feature));
            }

            $lockedUser->increment($reservedColumn, $units);

            return AiUsageReservation::query()->create([
                'token' => (string) Str::uuid(),
                'user_id' => $lockedUser->id,
                'feature' => $feature,
                'reserved_units' => $units,
                'status' => ReservationStatus::Reserved,
                'expires_at' => now()->addMinutes(max(1, (int) config('ai.reservation_ttl_minutes', 10))),
            ]);
        }, 3);
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $persist
     * @return T
     */
    public function commit(?AiUsageReservation $reservation, int $actualUnits, Closure $persist): mixed
    {
        if (! $reservation) {
            return DB::transaction($persist, 3);
        }

        return DB::transaction(function () use ($reservation, $actualUnits, $persist): mixed {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($reservation->user_id);
            $lockedReservation = AiUsageReservation::query()->lockForUpdate()->findOrFail($reservation->id);

            if (
                $lockedReservation->user_id !== $lockedUser->id
                || $lockedReservation->status !== ReservationStatus::Reserved
            ) {
                throw new LogicException('The AI quota reservation is no longer active.');
            }

            [$usedColumn, $reservedColumn] = $this->usageColumns($lockedReservation->feature);
            $committedUnits = min(max(1, $actualUnits), $lockedReservation->reserved_units);

            $result = $persist();

            $lockedUser->forceFill([
                $reservedColumn => max(0, (int) $lockedUser->{$reservedColumn} - $lockedReservation->reserved_units),
                $usedColumn => (int) $lockedUser->{$usedColumn} + $committedUnits,
            ])->save();

            $lockedReservation->update([
                'status' => ReservationStatus::Committed,
                'committed_units' => $committedUnits,
                'committed_at' => now(),
            ]);

            return $result;
        }, 3);
    }

    public function release(?AiUsageReservation $reservation): bool
    {
        if (! $reservation) {
            return false;
        }

        return DB::transaction(function () use ($reservation): bool {
            $lockedUser = User::query()->lockForUpdate()->find($reservation->user_id);
            $lockedReservation = AiUsageReservation::query()->lockForUpdate()->find($reservation->id);

            if (
                ! $lockedUser
                || ! $lockedReservation
                || $lockedReservation->user_id !== $lockedUser->id
                || $lockedReservation->status !== ReservationStatus::Reserved
            ) {
                return false;
            }

            [, $reservedColumn] = $this->usageColumns($lockedReservation->feature);

            $lockedUser->forceFill([
                $reservedColumn => max(0, (int) $lockedUser->{$reservedColumn} - $lockedReservation->reserved_units),
            ])->save();

            $lockedReservation->update([
                'status' => ReservationStatus::Released,
                'released_at' => now(),
            ]);

            return true;
        }, 3);
    }

    public function releaseExpiredReservations(): int
    {
        $count = 0;

        AiUsageReservation::query()
            ->where('status', ReservationStatus::Reserved->value)
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($reservations) use (&$count): void {
                foreach ($reservations as $reservation) {
                    if ($this->release($reservation)) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    /**
     * @return array{0: int, 1: string, 2: string}
     */
    private function quotaColumns(User $user, AiFeature $feature): array
    {
        return match ($feature) {
            AiFeature::Content, AiFeature::Chat => [
                (int) $user->word_limit,
                'words_used',
                'words_reserved',
            ],
            AiFeature::Image => [
                (int) ($user->plan?->image_generation_limit ?? 0),
                'image_generations_used',
                'image_generations_reserved',
            ],
            AiFeature::Audio => [
                (int) ($user->plan?->audio_generation_limit ?? 0),
                'audio_generations_used',
                'audio_generations_reserved',
            ],
        };
    }

    /** @return array{0: string, 1: string} */
    private function usageColumns(AiFeature $feature): array
    {
        return match ($feature) {
            AiFeature::Content, AiFeature::Chat => ['words_used', 'words_reserved'],
            AiFeature::Image => ['image_generations_used', 'image_generations_reserved'],
            AiFeature::Audio => ['audio_generations_used', 'audio_generations_reserved'],
        };
    }

    private function quotaMessage(AiFeature $feature): string
    {
        return match ($feature) {
            AiFeature::Content, AiFeature::Chat => 'Your monthly word quota is exhausted. Reduce the requested length or upgrade your plan.',
            AiFeature::Image => 'Your monthly image-generation quota is exhausted.',
            AiFeature::Audio => 'Your monthly audio-generation quota is exhausted.',
        };
    }
}
