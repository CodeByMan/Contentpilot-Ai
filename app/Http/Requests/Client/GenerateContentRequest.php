<?php

namespace App\Http\Requests\Client;

use App\Models\Template;
use Illuminate\Foundation\Http\FormRequest;

class GenerateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['language' => 'English']);
    }

    public function rules(): array
    {
        $rules = [
            'language' => ['sometimes', 'string', 'in:English'],
            'ai_model' => ['required', 'string', 'in:gpt-4,gpt-3.5-turbo'],
            'result_length' => ['required', 'integer', 'min:50', 'max:1000'],
        ];

        $templateId = $this->route('id');
        if ($templateId) {
            $template = Template::with('inputFields')->find($templateId);
            if ($template) {
                foreach ($template->inputFields as $field) {
                    $fieldName = str_replace(' ', '_', $field->title);
                    $rules[$fieldName] = $field->is_required
                        ? ['required', 'string', 'max:3000']
                        : ['nullable', 'string', 'max:3000'];
                }
            }
        }

        return $rules;
    }
}
