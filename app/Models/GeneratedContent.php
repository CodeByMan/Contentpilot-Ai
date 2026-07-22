<?php

namespace App\Models;

use App\Support\WordCounter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneratedContent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'template_id',
        'input',
        'output',
        'word_count',
        'model',
    ];

    protected static function booted(): void
    {
        static::saving(function (GeneratedContent $content): void {
            if ($content->isDirty('output')) {
                $content->word_count = WordCounter::count((string) $content->output);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'template_id' => 'integer',
            'input' => 'array',
            'word_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
