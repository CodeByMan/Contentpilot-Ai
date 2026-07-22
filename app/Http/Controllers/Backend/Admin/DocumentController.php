<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateGeneratedContentRequest;
use App\Models\GeneratedContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(): View
    {
        $document = GeneratedContent::query()->with(['template', 'user'])->latest()->paginate(20);

        return view('admin.backend.document.all_document', compact('document'));
    }

    public function edit(GeneratedContent $document): View
    {
        $this->authorize('update', $document);

        return view('admin.backend.document.edit_document', compact('document'));
    }

    public function update(
        UpdateGeneratedContentRequest $request,
        GeneratedContent $document,
    ): RedirectResponse {
        $this->authorize('update', $document);
        $document->update($request->validated());

        return redirect()->route('admin.documents.index')->with([
            'message' => 'Document updated successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function destroy(GeneratedContent $document): RedirectResponse
    {
        $this->authorize('delete', $document);
        $document->delete();

        return back()->with([
            'message' => 'Document deleted successfully.',
            'alert-type' => 'success',
        ]);
    }
}
