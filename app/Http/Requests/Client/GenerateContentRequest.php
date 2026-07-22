<?php

namespace App\Http\Requests\Client;

use App\Models\Template;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GenerateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $routeTemplate = $this->route('template');
        $template = $routeTemplate instanceof Template
            ? $routeTemplate
            : Template::query()->find($routeTemplate);

        return $template !== null && $this->user()?->can('view', $template) === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'language' => 'English',
            'ai_model' => $this->input('ai_model', config('ai.default_content_model')),
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'language' => ['required', 'string', Rule::in(['English'])],
            'ai_model' => ['required', 'string', Rule::in(config('ai.content_models', []))],
            'result_length' => [
                'required',
                'integer',
                'min:50',
                'max:'.(int) config('ai.max_output_words', 800),
            ],
        ];

        $routeTemplate = $this->route('template');
        $template = $routeTemplate instanceof Template
            ? $routeTemplate->loadMissing('inputFields')
            : Template::query()->with('inputFields')->find($routeTemplate);

        foreach ($template?->inputFields ?? [] as $field) {
            $rules[$field->key] = [
                $field->is_required ? 'required' : 'nullable',
                'string',
                'max:'.(int) config('ai.max_input_field_characters', 1500),
            ];
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $characters = collect($this->except(['_token', 'language', 'ai_model', 'result_length']))
                ->sum(fn (mixed $value): int => mb_strlen((string) $value));

            if ($characters > (int) config('ai.max_prompt_characters', 6000)) {
                $validator->errors()->add('prompt', 'The combined prompt input is too long.');
            }
        }];
    }
}
