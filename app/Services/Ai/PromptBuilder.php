<?php

namespace App\Services\Ai;

use App\Models\Template;

class PromptBuilder
{
    /**
     * Build a final prompt from a saved template and user-provided fields.
     *
     * @param  array<string, mixed>  $inputData
     */
    public function build(Template $template, array $inputData, string $language, int $resultLength): string
    {
        $prompt = $template->prompt;

        foreach ($template->inputFields as $field) {
            $fieldName = str_replace(' ', '_', $field->title);
            $fieldValue = (string) ($inputData[$fieldName] ?? '');

            $prompt = str_replace('{'.$fieldName.'}', $fieldValue, $prompt);
            $prompt = str_replace('{'.$field->title.'}', $fieldValue, $prompt);
        }

        $prompt = str_replace('{result_length}', (string) $resultLength, $prompt);

        return "Write in English. {$prompt} Aim for approximately {$resultLength} words. Return polished, production-ready copy.";
    }
}
