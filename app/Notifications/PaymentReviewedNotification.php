<?php

namespace App\Notifications;

use App\Models\BillingHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly BillingHistory $payment) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        $status = $this->payment->status->value;
        $planName = $this->payment->plan?->name ?? 'requested plan';

        return [
            'type' => 'payment_reviewed',
            'invoice_number' => $this->payment->invoice_number,
            'status' => $status,
            'plan_name' => $planName,
            'message' => $status === 'approved'
                ? "Your payment was approved and the {$planName} plan is now active."
                : "Your payment for the {$planName} plan was rejected. Your existing plan was not changed.",
        ];
    }
}
