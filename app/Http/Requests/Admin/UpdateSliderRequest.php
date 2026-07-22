<?php

namespace App\Http\Requests\Admin;

use App\Rules\SafeHttpOrRelativeUrl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSliderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:180'],
            'description' => ['sometimes', 'required', 'string', 'max:2000'],
            'link' => ['sometimes', 'nullable', 'string', 'max:2048', new SafeHttpOrRelativeUrl],
            'image' => [
                'sometimes',
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
                'dimensions:min_width=800,min_height=350,max_width=4096,max_height=4096',
            ],
        ];
    }
}
