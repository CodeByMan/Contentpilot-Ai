<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'message' => [
                'required',
                'string',
                'max:'.(int) config('ai.chat_max_characters', 2000),
            ],
            'conversation_uuid' => ['nullable', 'uuid'],
        ];
    }
}
