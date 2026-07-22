<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatConversation extends Model
{
    protected $fillable = [
        'assistant_id',
        'user_id',
        'conversation_uuid',
        'message',
        'response',
        'model',
    ];

    protected function casts(): array
    {
        return [
            'assistant_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(ChatAssistant::class, 'assistant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
