<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatAssistant extends Model
{
    protected $fillable = [
        'name',
        'avatar',
        'role_description',
        'welcome_message',
        'instructions',
        'category',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class, 'assistant_id');
    }
}
