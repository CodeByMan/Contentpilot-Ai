<?php

namespace App\Services\Quota;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UsageCycleService
{
    public function resetDueUsers(): int
    {
        $reset = 0;

        User::query()
            ->whereNotNull('usage_reset_at')
            ->where('usage_reset_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$reset): void {
                foreach ($users as $user) {
                    DB::transaction(function () use ($user, &$reset): void {
                        $lockedUser = User::query()->lockForUpdate()->find($user->id);

                        if ($lockedUser) {
                            $plan = $lockedUser->plan_id
                                ? Plan::query()->lockForUpdate()->find($lockedUser->plan_id)
                                : null;
                            $lockedUser->setRelation('plan', $plan);
                        }

                        if ($lockedUser && $this->resetIfDue($lockedUser)) {
                            $reset++;
                        }
                    }, 3);
                }
            });

        return $reset;
    }

    public function resetIfDue(User $lockedUser): bool
    {
        if (! $lockedUser->usage_reset_at || $lockedUser->usage_reset_at->isFuture()) {
            return false;
        }

        $nextReset = $lockedUser->usage_reset_at->copy();

        while ($nextReset->lessThanOrEqualTo(now())) {
            $nextReset->addMonthNoOverflow();
        }

        $lockedUser->forceFill([
            'word_limit' => (int) ($lockedUser->plan?->monthly_word_limit ?? 0),
            'words_used' => 0,
            'image_generations_used' => 0,
            'audio_generations_used' => 0,
            // Active reservations remain counted until they commit or are released.
            'usage_reset_at' => $nextReset,
        ])->save();

        return true;
    }
}
