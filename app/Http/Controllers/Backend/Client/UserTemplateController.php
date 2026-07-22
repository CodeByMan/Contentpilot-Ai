<?php

namespace App\Http\Controllers\Backend\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\GenerateContentRequest;
use App\Http\Requests\Client\UpdateGeneratedContentRequest;
use App\Models\GeneratedContent;
use App\Models\Template;
use App\Models\User;
use App\Services\Ai\AiContentGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class UserTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->loadMissing('plan');
        $templates = $this->accessibleTemplates($user);

        return view('client.backend.template.all_template', compact('user', 'templates'));
    }

    public function show(Request $request, int $template): View
    {
        $template = Template::query()->with('inputFields')->findOrFail($template);
        $this->authorize('view', $template);
        $user = $request->user()->loadMissing('plan');

        return view('client.backend.template.details_template', compact('template', 'user'));
    }

    public function generate(
        GenerateContentRequest $request,
        int $template,
        AiContentGenerator $generator,
    ): JsonResponse {
        $template = Template::query()->with('inputFields')->findOrFail($template);
        $this->authorize('view', $template);
        $validated = $request->validated();
        $inputData = collect($validated)->except(['language', 'ai_model', 'result_length'])->all();

        $result = $generator->generate(
            $request->user(),
            $template,
            $inputData,
            $validated['ai_model'],
            (int) $validated['result_length'],
        );

        return response()->json(['success' => true] + $result);
    }

    public function documents(Request $request): View
    {
        $this->authorize('viewAny', GeneratedContent::class);
        $document = $request->user()->generatedContents()->with('template:id,title')->latest()->paginate(15);

        return view('client.backend.document.all_document', compact('document'));
    }

    public function editDocument(Request $request, int $document): View
    {
        $document = $this->ownedDocument($request, $document);
        $this->authorize('update', $document);

        return view('client.backend.document.edit_document', compact('document'));
    }

    public function updateDocument(UpdateGeneratedContentRequest $request, int $document): RedirectResponse
    {
        $document = $this->ownedDocument($request, $document);
        $this->authorize('update', $document);
        $document->update($request->validated());

        return redirect()->route('user.documents.index')->with([
            'message' => 'Document updated successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function destroyDocument(Request $request, int $document): RedirectResponse
    {
        $document = $this->ownedDocument($request, $document);
        $this->authorize('delete', $document);
        $document->delete();

        return back()->with([
            'message' => 'Document deleted successfully.',
            'alert-type' => 'success',
        ]);
    }

    /** @return Collection<int, Template> */
    private function accessibleTemplates(User $user): Collection
    {
        $limit = max(0, (int) ($user->plan?->templates ?? 0));

        return Template::query()
            ->with('inputFields')
            ->where('is_active', true)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    private function ownedDocument(Request $request, int $documentId): GeneratedContent
    {
        return $request->user()->generatedContents()->findOrFail($documentId);
    }
}
