<?php

namespace App\Http\Requests\Admin;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        $question = $this->route('question');
        $questionId = $question instanceof Question ? $question->id : $question;

        return [
            'title' => ['required', 'string', 'max:180', Rule::unique('questions', 'title')->ignore($questionId)],
            'description' => ['required', 'string', 'max:3000'],
        ];
    }
}
