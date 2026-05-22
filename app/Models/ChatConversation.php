<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatConversation extends Model
{
    protected $fillable = [
        'assistant_id',
        'user_id',
        'message',
        'response',
        'conversation_id',
    ];

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(ChatAssistant::class, 'assistant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }
}
