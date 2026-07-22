<?php

namespace App\Http\Requests\Client;

use App\Models\GeneratedContent;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneratedContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $routeDocument = $this->route('document');
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if ($routeDocument instanceof GeneratedContent) {
            return $user->can('update', $routeDocument);
        }

        $document = $user->isAdmin()
            ? GeneratedContent::query()->findOrFail($routeDocument)
            : $user->generatedContents()->findOrFail($routeDocument);

        return $user->can('update', $document);
    }

    public function rules(): array
    {
        return [
            'output' => ['required', 'string', 'max:100000'],
        ];
    }
}
