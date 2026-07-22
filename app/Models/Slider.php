<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Slider extends Model
{
    protected $fillable = ['title', 'description', 'image', 'link'];

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->image
            ? Storage::disk('public')->url($this->image)
            : asset('frontend/images/gfx/feature/a.jpg'));
    }
}
