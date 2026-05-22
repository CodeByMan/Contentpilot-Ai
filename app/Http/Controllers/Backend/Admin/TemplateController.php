<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTemplateRequest;
use App\Http\Requests\Client\GenerateContentRequest;
use App\Models\Template;
use App\Models\TemplateInputFields;
use App\Services\Ai\AiContentGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    public function AllTemplate()
    {
        $templates = Template::with('createdBy')->latest()->get();

        return view('admin.backend.template.all_template', compact('templates'));
    }

    public function AddTemplate()
    {
        return view('admin.backend.template.add_template');
    }

    public function StoreTemplate(StoreTemplateRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $template = Template::create($request->safe()->except('input_fields') + [
                'created_by' => Auth::id(),
            ]);

            foreach ($request->validated('input_fields') as $index => $inputField) {
                TemplateInputFields::create([
                    'template_id' => $template->id,
                    'title' => $inputField['title'],
                    'description' => $inputField['description'] ?? null,
                    'type' => $inputField['type'],
                    'is_required' => true,
                    'order' => $index,
                ]);
            }
        });

        return redirect()->route('all.template')->with([
            'message' => 'Template created successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function EditTemplate($id)
    {
        $template = Template::with('inputFields')->findOrFail($id);

        return view('admin.backend.template.edit_template', compact('template'));
    }

    public function UpdateTemplate(StoreTemplateRequest $request, $id): RedirectResponse
    {
        DB::transaction(function () use ($request, $id): void {
            $template = Template::findOrFail($id);
            $template->update($request->safe()->except('input_fields'));

            $template->inputFields()->delete();
            foreach ($request->validated('input_fields') as $index => $inputField) {
                TemplateInputFields::create([
                    'template_id' => $template->id,
                    'title' => $inputField['title'],
                    'description' => $inputField['description'] ?? null,
                    'type' => $inputField['type'],
                    'is_required' => true,
                    'order' => $index,
                ]);
            }
        });

        return redirect()->route('all.template')->with([
            'message' => 'Template updated successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function DetailsTemplate($id)
    {
        $template = Template::with('inputFields')->findOrFail($id);
        $user = Auth::user();

        return view('admin.backend.template.details_template', compact('template', 'user'));
    }

    public function AdminContentGenerate(GenerateContentRequest $request, $id, AiContentGenerator $generator): JsonResponse
    {
        $template = Template::with('inputFields')->findOrFail($id);
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
}
