<?php

namespace App\Services\Billing;

use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Models\BillingHistory;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\PaymentReviewedNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;

class OfflinePaymentService
{
    /**
     * @param  array{bank_name:string,account_holder:string,payment_reference:string,receipt_path?:string|null}  $data
     */
    public function submit(User $user, Plan $plan, array $data): BillingHistory
    {
        if (blank($data['receipt_path'] ?? null)) {
            throw new LogicException('A private payment receipt is required.');
        }

        $bankName = Str::squish((string) ($data['bank_name'] ?? ''));
        $accountHolder = Str::squish((string) ($data['account_holder'] ?? ''));
        $paymentReference = Str::upper(Str::squish((string) ($data['payment_reference'] ?? '')));

        if ($bankName === '' || $accountHolder === '' || $paymentReference === '') {
            throw new LogicException('Complete payment details are required.');
        }

        if (mb_strlen($bankName) > 120 || mb_strlen($accountHolder) > 120 || mb_strlen($paymentReference) > 120) {
            throw new LogicException('Payment details exceed the permitted length.');
        }

        $referenceFingerprint = hash('sha256', Str::lower($bankName).'|'.$paymentReference);

        try {
            return DB::transaction(function () use (
                $user,
                $plan,
                $data,
                $bankName,
                $accountHolder,
                $paymentReference,
                $referenceFingerprint,
            ): BillingHistory {
                $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
                $lockedPlan = Plan::query()->lockForUpdate()->findOrFail($plan->id);

                if (! $lockedUser->is_active) {
                    throw new LogicException('Inactive accounts cannot submit payment requests.');
                }

                if (! $lockedPlan->is_active || (float) $lockedPlan->price <= 0) {
                    throw new LogicException('Only active paid plans can receive payment requests.');
                }

                $hasPendingRequest = BillingHistory::query()
                    ->where('user_id', $lockedUser->id)
                    ->where('status', PaymentStatus::Pending->value)
                    ->lockForUpdate()
                    ->exists();

                if ($hasPendingRequest) {
                    throw new LogicException('A pending payment request already exists for this account.');
                }

                return BillingHistory::query()->create([
                    'invoice_number' => (string) Str::uuid(),
                    'user_id' => $lockedUser->id,
                    'plan_id' => $lockedPlan->id,
                    'submitted_at' => now(),
                    'total' => $lockedPlan->price,
                    'bank_name' => $bankName,
                    'account_holder' => $accountHolder,
                    'payment_reference' => $paymentReference,
                    'reference_fingerprint' => $referenceFingerprint,
                    'receipt_path' => $data['receipt_path'],
                    'status' => PaymentStatus::Pending,
                    'pending_key' => (string) $lockedUser->id,
                ]);
            }, 3);
        } catch (QueryException $exception) {
            if (! $this->isUniqueViolation($exception)) {
                throw $exception;
            }

            throw new LogicException(
                'A pending request or duplicate payment reference already exists.',
                previous: $exception,
            );
        }
    }

    public function approve(BillingHistory $payment, User $reviewer): BillingHistory
    {
        return DB::transaction(function () use ($payment, $reviewer): BillingHistory {
            [$user, $lockedReviewer] = $this->lockReviewUsers($payment, $reviewer);
            $plan = Plan::query()->lockForUpdate()->findOrFail($payment->plan_id);
            $lockedPayment = BillingHistory::query()->lockForUpdate()->findOrFail($payment->id);

            if (
                $lockedPayment->user_id !== $user->id
                || $lockedPayment->plan_id !== $plan->id
                || $lockedPayment->status !== PaymentStatus::Pending
            ) {
                throw new LogicException('Only the current pending payment can be approved.');
            }

            if (! $plan->is_active) {
                throw new LogicException('The selected plan is no longer active.');
            }

            if (! $user->is_active) {
                throw new LogicException('Payments for inactive accounts cannot be approved.');
            }

            $user->aiUsageReservations()
                ->where('status', ReservationStatus::Reserved->value)
                ->update([
                    'status' => ReservationStatus::Released->value,
                    'released_at' => now(),
                ]);

            $lockedPayment->update([
                'status' => PaymentStatus::Approved,
                'reviewed_at' => now(),
                'reviewed_by' => $lockedReviewer->id,
                'rejection_reason' => null,
                'pending_key' => null,
            ]);

            $user->forceFill([
                'plan_id' => $plan->id,
                'word_limit' => $plan->monthly_word_limit,
                'words_used' => 0,
                'words_reserved' => 0,
                'image_generations_used' => 0,
                'image_generations_reserved' => 0,
                'audio_generations_used' => 0,
                'audio_generations_reserved' => 0,
                'plan_activated_at' => now(),
                'usage_reset_at' => now()->addMonthNoOverflow(),
            ])->save();

            $reviewedPayment = $lockedPayment->refresh()->loadMissing('plan');
            $user->notify(new PaymentReviewedNotification($reviewedPayment));

            return $reviewedPayment;
        }, 3);
    }

    public function reject(BillingHistory $payment, User $reviewer, string $reason): BillingHistory
    {
        return DB::transaction(function () use ($payment, $reviewer, $reason): BillingHistory {
            [$user, $lockedReviewer] = $this->lockReviewUsers($payment, $reviewer);
            $lockedPayment = BillingHistory::query()->lockForUpdate()->findOrFail($payment->id);

            if (
                $lockedPayment->user_id !== $user->id
                || $lockedPayment->status !== PaymentStatus::Pending
            ) {
                throw new LogicException('Only the current pending payment can be rejected.');
            }

            $lockedPayment->update([
                'status' => PaymentStatus::Rejected,
                'reviewed_at' => now(),
                'reviewed_by' => $lockedReviewer->id,
                'rejection_reason' => $reason,
                'pending_key' => null,
            ]);

            $reviewedPayment = $lockedPayment->refresh()->loadMissing('plan');
            $user->notify(new PaymentReviewedNotification($reviewedPayment));

            return $reviewedPayment;
        }, 3);
    }

    /**
     * Lock account rows in a deterministic order before payment, plan, or quota rows.
     *
     * @return array{0: User, 1: User}
     */
    private function lockReviewUsers(BillingHistory $payment, User $reviewer): array
    {
        $userIds = array_values(array_unique([(int) $payment->user_id, (int) $reviewer->id]));
        sort($userIds);

        $users = User::query()
            ->whereKey($userIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $paymentUser = $users->get((int) $payment->user_id);
        $lockedReviewer = $users->get((int) $reviewer->id);

        if (! $paymentUser || ! $lockedReviewer) {
            throw new LogicException('The payment account or reviewer is unavailable.');
        }

        $this->ensureAdministrator($lockedReviewer);

        return [$paymentUser, $lockedReviewer];
    }

    private function ensureAdministrator(User $reviewer): void
    {
        if (! $reviewer->isAdmin() || ! $reviewer->is_active || ! $reviewer->hasVerifiedEmail()) {
            throw new LogicException('Only an active administrator can review payments.');
        }
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        return in_array((string) ($exception->errorInfo[0] ?? ''), ['23000', '23505'], true)
            || in_array((int) ($exception->errorInfo[1] ?? 0), [19, 1062], true);
    }
}
