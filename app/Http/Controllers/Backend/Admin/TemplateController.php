<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTemplateRequest;
use App\Http\Requests\Client\GenerateContentRequest;
use App\Models\Template;
use App\Services\Ai\AiContentGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function index(): View
    {
        $template = Template::query()
            ->with('createdBy')
            ->withCount(['inputFields', 'generatedContents'])
            ->latest()
            ->paginate(20);

        return view('admin.backend.template.all_template', compact('template'));
    }

    public function create(): View
    {
        return view('admin.backend.template.add_template');
    }

    public function store(StoreTemplateRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $template = Template::query()->create([
                ...$request->safe()->except('input_fields'),
                'created_by' => $request->user()->id,
            ]);

            $this->syncInputFields($template, $request->validated('input_fields'));
        }, 3);

        return redirect()->route('admin.templates.index')->with([
            'message' => 'Template created successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function edit(Template $template): View
    {
        $template->load('inputFields');

        return view('admin.backend.template.edit_template', compact('template'));
    }

    public function update(StoreTemplateRequest $request, Template $template): RedirectResponse
    {
        DB::transaction(function () use ($request, $template): void {
            $lockedTemplate = Template::query()->lockForUpdate()->findOrFail($template->id);

            $lockedTemplate->update($request->safe()->except('input_fields'));
            $lockedTemplate->inputFields()->delete();
            $this->syncInputFields($lockedTemplate, $request->validated('input_fields'));
        }, 3);

        return redirect()->route('admin.templates.index')->with([
            'message' => 'Template updated successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function destroy(Template $template): RedirectResponse
    {
        try {
            $deleted = DB::transaction(function () use ($template): bool {
                $lockedTemplate = Template::query()->lockForUpdate()->findOrFail($template->id);

                if ($lockedTemplate->generatedContents()->withTrashed()->exists()) {
                    return false;
                }

                return (bool) $lockedTemplate->delete();
            }, 3);
        } catch (QueryException) {
            return back()->withErrors([
                'template' => 'This template is referenced by generated documents and cannot be deleted. Deactivate it instead.',
            ]);
        }

        if (! $deleted) {
            return back()->withErrors([
                'template' => 'Templates with generated documents cannot be deleted. Deactivate the template instead.',
            ]);
        }

        return redirect()->route('admin.templates.index')->with([
            'message' => 'Template deleted successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function show(Template $template): View
    {
        $template->load('inputFields');
        $user = request()->user();

        return view('admin.backend.template.details_template', compact('template', 'user'));
    }

    public function generate(
        GenerateContentRequest $request,
        Template $template,
        AiContentGenerator $generator,
    ): JsonResponse {
        $template->load('inputFields');
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

    /**
     * @param  array<int, array<string, mixed>>  $fields
     */
    private function syncInputFields(Template $template, array $fields): void
    {
        foreach ($fields as $index => $field) {
            $template->inputFields()->create([
                'key' => $field['key'],
                'title' => $field['title'],
                'description' => $field['description'] ?? null,
                'type' => $field['type'],
                'is_required' => (bool) ($field['is_required'] ?? true),
                'order' => $index,
            ]);
        }
    }
}
