<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedContent extends Model
{
    protected $fillable = [
        'user_id',
        'template_id',
        'input',
        'output',
        'word_count',
    ];

    protected function casts(): array
    {
        return [
            'input' => 'array',
            'word_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
