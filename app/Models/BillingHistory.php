<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingHistory extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'plan_id',
        'submitted_at',
        'total',
        'bank_name',
        'account_holder',
        'payment_reference',
        'reference_fingerprint',
        'receipt_path',
        'status',
        'pending_key',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
    ];

    protected $hidden = [
        'reference_fingerprint',
        'receipt_path',
        'pending_key',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'plan_id' => 'integer',
            'reviewed_by' => 'integer',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'total' => 'decimal:2',
            'status' => PaymentStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
