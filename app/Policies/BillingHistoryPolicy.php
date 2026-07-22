<?php

namespace App\Policies;

use App\Models\BillingHistory;
use App\Models\User;

class BillingHistoryPolicy
{
    public function view(User $user, BillingHistory $billingHistory): bool
    {
        return $billingHistory->user_id === $user->id;
    }
}
