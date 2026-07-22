<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSliderRequest;
use App\Http\Requests\Client\StoreContactRequest;
use App\Models\Contact;
use App\Models\Heading;
use App\Models\Plan;
use App\Models\Question;
use App\Models\Slider;
use App\Models\Template;
use App\Services\Storage\SecureUploadService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home.index', $this->homeData());
    }

    public function useCases(): View
    {
        return view('home.page.use_case', ['templates' => $this->featuredTemplates()]);
    }

    public function features(): View
    {
        return view('home.page.features', ['templates' => $this->featuredTemplates()]);
    }

    public function pricing(): View
    {
        return view('home.page.pricing', [
            'plan' => Plan::query()->where('is_active', true)->orderBy('price')->get(),
            'questions' => Question::query()->oldest('id')->get(),
        ]);
    }

    public function contact(): View
    {
        return view('home.page.contact');
    }

    public function storeContact(StoreContactRequest $request): RedirectResponse
    {
        Contact::query()->create($request->validated());

        return back()->with([
            'message' => 'Your message was submitted successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function editSlider(): View
    {
        $slider = Slider::query()->firstOrCreate(['id' => 1], [
            'title' => 'Create better content with AI',
            'description' => 'Generate polished English content, images, and audio in one secure workspace.',
            'link' => route('register'),
        ]);

        return view('admin.backend.slider.get_slider', compact('slider'));
    }

    public function updateSlider(
        UpdateSliderRequest $request,
        Slider $slider,
        SecureUploadService $uploads,
    ): RedirectResponse {
        abort_unless($slider->id === 1, 404);

        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $newImage = $uploads->storeImage(
                $request->file('image'),
                'cms/sliders',
                1696,
                729,
                cover: true,
                maxBytes: 4_194_304,
            );
            $oldImage = $slider->image;
            $data['image'] = $newImage;

            try {
                $slider->update($data);
            } catch (\Throwable $exception) {
                $uploads->delete($newImage);

                throw $exception;
            }

            $uploads->delete($oldImage);
        } else {
            $slider->update($data);
        }

        return back()->with([
            'message' => 'Homepage slider updated successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function contacts(): View
    {
        return view('admin.backend.contact.all_contact', [
            'contacts' => Contact::query()->latest()->paginate(25),
        ]);
    }

    public function destroyContact(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return back()->with([
            'message' => 'Contact message deleted successfully.',
            'alert-type' => 'success',
        ]);
    }

    /** @return array<string, mixed> */
    private function homeData(): array
    {
        return [
            'slider' => Slider::query()->find(1),
            'headings' => Heading::query()->oldest('id')->limit(3)->get()->values()->mapWithKeys(
                fn (Heading $heading, int $index): array => [$index + 1 => $heading],
            ),
            'templates' => $this->featuredTemplates(),
            'plan' => Plan::query()->where('is_active', true)->orderBy('price')->get(),
            'questions' => Question::query()->oldest('id')->get(),
        ];
    }

    /** @return Collection<int, Template> */
    private function featuredTemplates(): Collection
    {
        return Template::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->limit(6)
            ->get();
    }
}
