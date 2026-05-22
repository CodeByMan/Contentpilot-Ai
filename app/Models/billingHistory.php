<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class billingHistory extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'payment_date',
        'total',
        'bank_name',
        'account_holder',
        'account_number',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
