<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, MustVerifyEmailTrait, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'phone',
        'address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'plan_id' => 'integer',
            'word_limit' => 'integer',
            'words_used' => 'integer',
            'words_reserved' => 'integer',
            'image_generations_used' => 'integer',
            'image_generations_reserved' => 'integer',
            'audio_generations_used' => 'integer',
            'audio_generations_reserved' => 'integer',
            'is_active' => 'boolean',
            'plan_activated_at' => 'datetime',
            'usage_reset_at' => 'datetime',
        ];
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->photo
            ? Storage::disk('public')->url($this->photo)
            : asset('assets/images/avatar-placeholder.svg'));
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function billingHistories(): HasMany
    {
        return $this->hasMany(BillingHistory::class);
    }

    public function reviewedBillingHistories(): HasMany
    {
        return $this->hasMany(BillingHistory::class, 'reviewed_by');
    }

    public function generatedContents(): HasMany
    {
        return $this->hasMany(GeneratedContent::class);
    }

    public function generatedImages(): HasMany
    {
        return $this->hasMany(GeneratedImage::class);
    }

    public function generatedAudios(): HasMany
    {
        return $this->hasMany(GeneratedAudio::class);
    }

    public function chatConversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class);
    }

    public function aiUsageReservations(): HasMany
    {
        return $this->hasMany(AiUsageReservation::class);
    }
}
