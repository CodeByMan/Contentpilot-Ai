<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuestionsController extends Controller
{
    public function index(): View
    {
        return view('admin.backend.question.all_question', [
            'questions' => Question::query()->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.backend.question.add_question', ['question' => new Question]);
    }

    public function store(StoreQuestionRequest $request): RedirectResponse
    {
        Question::query()->create($request->validated());

        return $this->redirectWithSuccess('Question created successfully.');
    }

    public function edit(Question $question): View
    {
        return view('admin.backend.question.edit_question', compact('question'));
    }

    public function update(StoreQuestionRequest $request, Question $question): RedirectResponse
    {
        $question->update($request->validated());

        return $this->redirectWithSuccess('Question updated successfully.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return $this->redirectWithSuccess('Question deleted successfully.');
    }

    private function redirectWithSuccess(string $message): RedirectResponse
    {
        return redirect()->route('admin.cms.questions.index')->with([
            'message' => $message,
            'alert-type' => 'success',
        ]);
    }
}
