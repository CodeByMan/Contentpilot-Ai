<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class GenerateAudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active === true;
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'min:3', 'max:'.(int) config('ai.audio_max_characters', 2500)],
        ];
    }
}
