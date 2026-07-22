<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneratedImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'prompt',
        'image_path',
        'model',
        'mime_type',
        'size_bytes',
    ];

    protected $hidden = ['image_path'];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'size_bytes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
