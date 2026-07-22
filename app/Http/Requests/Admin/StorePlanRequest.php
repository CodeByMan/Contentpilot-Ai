<?php

namespace App\Http\Requests\Admin;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        $planId = $this->route('plan') instanceof Plan
            ? $this->route('plan')->id
            : $this->route('plan');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('plans', 'name')->ignore($planId)],
            'monthly_word_limit' => ['required', 'integer', 'min:0', 'max:1000000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'templates' => ['required', 'integer', 'min:1', 'max:500'],
            'image_generation_limit' => ['required', 'integer', 'min:0', 'max:10000'],
            'audio_generation_limit' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
