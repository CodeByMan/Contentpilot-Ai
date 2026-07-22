<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateInputField extends Model
{
    protected $fillable = [
        'template_id',
        'key',
        'title',
        'description',
        'type',
        'is_required',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'template_id' => 'integer',
            'is_required' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
