<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['required', 'string', 'max:100'],
            'icon' => ['required', 'string', 'max:100'],
            'prompt' => ['required', 'string', 'max:5000'],
            'is_active' => ['required', 'boolean'],
            'input_fields' => ['required', 'array', 'min:1', 'max:5'],
            'input_fields.*.title' => ['required', 'string', 'max:255'],
            'input_fields.*.description' => ['nullable', 'string', 'max:1000'],
            'input_fields.*.type' => ['required', 'in:text,textarea'],
        ];
    }
}
