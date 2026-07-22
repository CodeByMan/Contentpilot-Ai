<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssistantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $assistant = $this->route('assistant');

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('chat_assistants', 'name')->ignore($assistant),
            ],
            'role_description' => ['required', 'string', 'max:255'],
            'welcome_message' => ['required', 'string', 'max:1000'],
            'instructions' => ['required', 'string', 'max:6000'],
            'category' => ['required', 'string', Rule::in(config('ai.assistant_categories', []))],
            'is_active' => ['required', 'boolean'],
            'avatar' => [
                $this->isMethod('post') ? 'nullable' : 'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000',
            ],
        ];
    }
}
