<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHeadingRequest;
use App\Models\Heading;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HeadingController extends Controller
{
    public function index(): View
    {
        return view('admin.backend.heading.all_heading', [
            'headings' => Heading::query()->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.backend.heading.add_heading', ['heading' => new Heading]);
    }

    public function store(StoreHeadingRequest $request): RedirectResponse
    {
        Heading::query()->create($request->validated());

        return $this->redirectWithSuccess('Heading created successfully.');
    }

    public function edit(Heading $heading): View
    {
        return view('admin.backend.heading.edit_heading', compact('heading'));
    }

    public function update(StoreHeadingRequest $request, Heading $heading): RedirectResponse
    {
        $heading->update($request->validated());

        return $this->redirectWithSuccess('Heading updated successfully.');
    }

    public function destroy(Heading $heading): RedirectResponse
    {
        $heading->delete();

        return $this->redirectWithSuccess('Heading deleted successfully.');
    }

    private function redirectWithSuccess(string $message): RedirectResponse
    {
        return redirect()->route('admin.cms.headings.index')->with([
            'message' => $message,
            'alert-type' => 'success',
        ]);
    }
}
