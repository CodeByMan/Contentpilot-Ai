<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'monthly_word_limit',
        'price',
        'templates',
        'image_generation_limit',
        'audio_generation_limit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'monthly_word_limit' => 'integer',
            'price' => 'decimal:2',
            'templates' => 'integer',
            'image_generation_limit' => 'integer',
            'audio_generation_limit' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function billingHistories(): HasMany
    {
        return $this->hasMany(BillingHistory::class);
    }
}
