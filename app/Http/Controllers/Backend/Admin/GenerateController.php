<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\GenerateAudioRequest;
use App\Http\Requests\Client\GenerateImageRequest;
use App\Models\GeneratedAudio;
use App\Models\GeneratedImage;
use App\Services\Ai\AiMediaGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class GenerateController extends Controller
{
    public function adminImageCreate(): View
    {
        return view('admin.backend.generate.generate_image');
    }

    public function storeImage(
        GenerateImageRequest $request,
        AiMediaGenerator $generator,
    ): JsonResponse {
        $generatedImage = $generator->generateImage($request->user(), $request->validated('prompt'));

        return response()->json([
            'status' => 'success',
            'image_local_path' => route(
                $request->user()->isAdmin() ? 'admin.images.file' : 'user.images.file',
                $generatedImage,
            ),
            'message' => 'Image generated and saved successfully.',
        ], 201);
    }

    public function adminImageIndex(): View
    {
        $genimage = GeneratedImage::query()->with('user')->latest()->paginate(18);

        return view('admin.backend.generate.all_image', compact('genimage'));
    }

    public function adminImageFile(GeneratedImage $image): StreamedResponse
    {
        $this->authorize('view', $image);

        return $this->privateMediaResponse($image->image_path, $image->mime_type);
    }

    public function adminImageDestroy(GeneratedImage $image): RedirectResponse
    {
        $this->authorize('delete', $image);
        $path = $image->image_path;
        $this->deletePrivateMedia($image, $path);

        return back()->with(['message' => 'Generated image deleted successfully.', 'alert-type' => 'success']);
    }

    public function userImageCreate(): View
    {
        return view('client.backend.generate.generate_image');
    }

    public function userImageIndex(Request $request): View
    {
        $genimage = $request->user()->generatedImages()->latest()->paginate(18);

        return view('client.backend.generate.all_image', compact('genimage'));
    }

    public function userImageFile(Request $request, int $image): StreamedResponse
    {
        $image = $request->user()->generatedImages()->findOrFail($image);
        $this->authorize('view', $image);

        return $this->privateMediaResponse($image->image_path, $image->mime_type);
    }

    public function userImageDestroy(Request $request, int $image): RedirectResponse
    {
        $image = $request->user()->generatedImages()->findOrFail($image);
        $this->authorize('delete', $image);
        $path = $image->image_path;
        $this->deletePrivateMedia($image, $path);

        return back()->with('status', 'Generated image deleted.');
    }

    public function adminAudioCreate(): View
    {
        return view('admin.backend.generate.generate_audio');
    }

    public function storeAudio(
        GenerateAudioRequest $request,
        AiMediaGenerator $generator,
    ): JsonResponse {
        $generatedAudio = $generator->generateAudio($request->user(), $request->validated('text'));

        return response()->json([
            'status' => 'success',
            'audio_url' => route(
                $request->user()->isAdmin() ? 'admin.audio.file' : 'user.audio.file',
                $generatedAudio,
            ),
            'message' => 'Audio generated and saved successfully.',
        ], 201);
    }

    public function adminAudioIndex(): View
    {
        $genaudio = GeneratedAudio::query()->with('user')->latest()->paginate(18);

        return view('admin.backend.generate.all_audio', compact('genaudio'));
    }

    public function adminAudioFile(GeneratedAudio $audio): StreamedResponse
    {
        $this->authorize('view', $audio);

        return $this->privateMediaResponse($audio->audio_path, $audio->mime_type);
    }

    public function adminAudioDestroy(GeneratedAudio $audio): RedirectResponse
    {
        $this->authorize('delete', $audio);
        $path = $audio->audio_path;
        $this->deletePrivateMedia($audio, $path);

        return back()->with(['message' => 'Generated audio deleted successfully.', 'alert-type' => 'success']);
    }

    public function userAudioCreate(): View
    {
        return view('client.backend.generate.generate_audio');
    }

    public function userAudioIndex(Request $request): View
    {
        $genaudio = $request->user()->generatedAudios()->latest()->paginate(18);

        return view('client.backend.generate.all_audio', compact('genaudio'));
    }

    public function userAudioFile(Request $request, int $audio): StreamedResponse
    {
        $audio = $request->user()->generatedAudios()->findOrFail($audio);
        $this->authorize('view', $audio);

        return $this->privateMediaResponse($audio->audio_path, $audio->mime_type);
    }

    public function userAudioDestroy(Request $request, int $audio): RedirectResponse
    {
        $audio = $request->user()->generatedAudios()->findOrFail($audio);
        $this->authorize('delete', $audio);
        $path = $audio->audio_path;
        $this->deletePrivateMedia($audio, $path);

        return back()->with('status', 'Generated audio deleted.');
    }

    private function deletePrivateMedia(GeneratedImage|GeneratedAudio $media, string $path): void
    {
        $media->delete();

        try {
            Storage::disk('private')->delete($path);
        } catch (Throwable $exception) {
            Log::warning('Private generated media cleanup failed.', [
                'media_type' => $media::class,
                'media_id' => $media->getKey(),
                'exception_class' => $exception::class,
            ]);
        }
    }

    private function privateMediaResponse(string $path, string $mimeType): StreamedResponse
    {
        abort_unless(Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
