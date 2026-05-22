<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'icon',
        'prompt',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Backwards-compatible alias for older views/controllers. */
    public function ceratedBy(): BelongsTo
    {
        return $this->createdBy();
    }

    public function inputFields(): HasMany
    {
        return $this->hasMany(TemplateInputFields::class, 'template_id')->orderBy('order');
    }

    public function generatedContents(): HasMany
    {
        return $this->hasMany(GeneratedContent::class);
    }
}
