<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $fields = collect($this->input('input_fields', []))
            ->map(function (mixed $field): mixed {
                if (! is_array($field)) {
                    return $field;
                }

                $field['key'] = Str::of((string) ($field['key'] ?? $field['title'] ?? ''))
                    ->snake()
                    ->limit(100, '')
                    ->toString();
                $field['is_required'] = filter_var($field['is_required'] ?? false, FILTER_VALIDATE_BOOL);

                return $field;
            })->all();

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'input_fields' => $fields,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9 _-]+$/'],
            'prompt' => ['required', 'string', 'max:6000'],
            'is_active' => ['required', 'boolean'],
            'input_fields' => ['required', 'array', 'min:1', 'max:10'],
            'input_fields.*.key' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', 'distinct:ignore_case'],
            'input_fields.*.title' => ['required', 'string', 'max:160'],
            'input_fields.*.description' => ['nullable', 'string', 'max:1000'],
            'input_fields.*.type' => ['required', Rule::in(['text', 'textarea'])],
            'input_fields.*.is_required' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $prompt = (string) $this->input('prompt');
            $allowedPlaceholders = collect((array) $this->input('input_fields', []))
                ->pluck('key')
                ->filter(fn (mixed $key): bool => is_string($key) && $key !== '')
                ->push('result_length')
                ->unique()
                ->values();

            foreach ((array) $this->input('input_fields', []) as $index => $field) {
                $key = (string) ($field['key'] ?? '');

                if ($key !== '' && ! str_contains($prompt, '{'.$key.'}')) {
                    $validator->errors()->add(
                        "input_fields.{$index}.key",
                        "The prompt must include the {{$key}} placeholder.",
                    );
                }
            }

            preg_match_all('/\{([a-z][a-z0-9_]*)\}/i', $prompt, $matches);
            $unknownPlaceholders = collect($matches[1] ?? [])
                ->unique()
                ->reject(fn (string $placeholder): bool => $allowedPlaceholders->contains($placeholder))
                ->values();

            if ($unknownPlaceholders->isNotEmpty()) {
                $validator->errors()->add(
                    'prompt',
                    'The prompt contains undeclared placeholders: '.$unknownPlaceholders->map(
                        fn (string $placeholder): string => '{'.$placeholder.'}',
                    )->implode(', ').'.',
                );
            }
        }];
    }
}
