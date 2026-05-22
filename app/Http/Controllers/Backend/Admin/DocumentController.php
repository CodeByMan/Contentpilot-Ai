<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneratedContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function AdminDocument()
    {
        $document = GeneratedContent::with(['template', 'user'])->latest()->get();

        return view('admin.backend.document.all_document', compact('document'));
    }

    public function EditAdminDocument($id)
    {
        $document = GeneratedContent::findOrFail($id);

        return view('admin.backend.document.edit_document', compact('document'));
    }

    public function AdminUpdateDocument(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate(['output' => ['required', 'string']]);

        GeneratedContent::findOrFail($id)->update($validated);

        return redirect()->route('all.doucment')->with([
            'message' => 'Document updated successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function DeleteAdminDocument($id): RedirectResponse
    {
        GeneratedContent::findOrFail($id)->delete();

        return redirect()->back()->with([
            'message' => 'Document deleted successfully.',
            'alert-type' => 'success',
        ]);
    }
}
