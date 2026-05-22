<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaGenerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'prompt' => ['sometimes', 'required', 'string', 'max:1500'],
            'text' => ['sometimes', 'required', 'string', 'max:2500'],
        ];
    }
}
