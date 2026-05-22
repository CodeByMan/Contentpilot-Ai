<?php

namespace App\Http\Controllers\Backend\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\GenerateContentRequest;
use App\Models\GeneratedContent;
use App\Models\Template;
use App\Services\Ai\AiContentGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserTemplateController extends Controller
{
    public function UserTemplate()
    {
        $user = Auth::user()->loadMissing('plan');
        $templateLimit = (int) ($user->plan?->templates ?? 3);
        $templates = Template::where('is_active', true)->latest()->limit($templateLimit)->get();

        return view('client.backend.template.all_template', compact('user', 'templates'));
    }

    public function UserDetailsTemplate($id)
    {
        $template = Template::with('inputFields')->where('is_active', true)->findOrFail($id);
        $user = Auth::user();

        return view('client.backend.template.details_template', compact('template', 'user'));
    }

    public function UserContentGenerate(GenerateContentRequest $request, $id, AiContentGenerator $generator): JsonResponse
    {
        $template = Template::with('inputFields')->where('is_active', true)->findOrFail($id);
        $inputData = $request->except(['_token', 'language', 'ai_model', 'result_length']);

        try {
            $result = $generator->generate(
                Auth::user(),
                $template,
                $inputData,
                $request->string('language')->toString(),
                $request->string('ai_model')->toString(),
                $request->integer('result_length')
            );

            return response()->json(['success' => true] + $result);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => $exception instanceof \RuntimeException
                    ? $exception->getMessage()
                    : 'AI generation failed. Please check your API key and try again.',
            ], $exception instanceof \RuntimeException ? 422 : 500);
        }
    }

    public function UserDocument()
    {
        $document = GeneratedContent::with(['template', 'user'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('client.backend.document.all_document', compact('document'));
    }

    public function EditUserDocument($id)
    {
        $document = GeneratedContent::where('user_id', Auth::id())->findOrFail($id);

        return view('client.backend.document.edit_document', compact('document'));
    }

    public function UserUpdateDocument(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate(['output' => ['required', 'string']]);

        GeneratedContent::where('user_id', Auth::id())->findOrFail($id)->update($validated);

        return redirect()->route('user.document')->with([
            'message' => 'Document updated successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function DeleteUserDocument($id): RedirectResponse
    {
        GeneratedContent::where('user_id', Auth::id())->findOrFail($id)->delete();

        return redirect()->back()->with([
            'message' => 'Document deleted successfully.',
            'alert-type' => 'success',
        ]);
    }
}
