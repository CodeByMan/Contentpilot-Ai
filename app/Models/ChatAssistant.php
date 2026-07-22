<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
        return ['is_active' => 'boolean'];
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->avatar
            ? Storage::disk('public')->url($this->avatar)
            : asset('assets/images/avatar-placeholder.svg'));
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class, 'assistant_id');
    }
}
