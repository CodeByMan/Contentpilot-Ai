<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneratedAudio extends Model
{
    use SoftDeletes;

    protected $table = 'generated_audio';

    protected $fillable = [
        'user_id',
        'prompt',
        'audio_path',
        'model',
        'voice',
        'mime_type',
        'size_bytes',
    ];

    protected $hidden = ['audio_path'];

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
