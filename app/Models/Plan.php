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
    ];

    protected function casts(): array
    {
        return [
            'monthly_word_limit' => 'integer',
            'price' => 'decimal:2',
            'templates' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
