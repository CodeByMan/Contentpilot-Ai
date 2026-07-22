<?php

namespace App\Models;

use App\Enums\AiFeature;
use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageReservation extends Model
{
    protected $fillable = [
        'token',
        'user_id',
        'feature',
        'reserved_units',
        'committed_units',
        'status',
        'expires_at',
        'released_at',
        'committed_at',
    ];

    protected $hidden = ['token'];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'feature' => AiFeature::class,
            'status' => ReservationStatus::class,
            'reserved_units' => 'integer',
            'committed_units' => 'integer',
            'expires_at' => 'datetime',
            'released_at' => 'datetime',
            'committed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
