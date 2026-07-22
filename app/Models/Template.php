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
            'created_by' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function inputFields(): HasMany
    {
        return $this->hasMany(TemplateInputField::class, 'template_id')->orderBy('order');
    }

    public function generatedContents(): HasMany
    {
        return $this->hasMany(GeneratedContent::class);
    }
}
