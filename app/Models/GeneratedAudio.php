<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedAudio extends Model
{
    protected $table = 'generated_audio';

    protected $fillable = [
        'user_id',
        'prompt',
        'audio_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
