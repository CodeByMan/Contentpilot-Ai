<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'monthly_word_limit' => ['required', 'integer', 'min:0', 'max:1000000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'templates' => ['required', 'integer', 'min:1', 'max:500'],
        ];
    }
}
