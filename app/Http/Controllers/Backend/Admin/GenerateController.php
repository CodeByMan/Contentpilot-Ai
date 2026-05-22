<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreMediaGenerationRequest;
use App\Models\GeneratedAudio;
use App\Models\GeneratedImage;
use App\Services\Ai\AiMediaGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GenerateController extends Controller
{
    public function GenerateImage()
    {
        return view('admin.backend.generate.generate_image');
    }

    public function GenerateAndSaveImage(StoreMediaGenerationRequest $request, AiMediaGenerator $generator): JsonResponse
    {
        $generatedImage = $generator->generateImage(Auth::user(), $request->string('prompt')->toString());

        return response()->json([
            'status' => 'success',
            'image_local_path' => asset($generatedImage->image_path),
            'message' => 'Image generated and saved successfully.',
        ]);
    }

    public function AllGenerateImage()
    {
        $genimage = GeneratedImage::with('user')->latest()->get();

        return view('admin.backend.generate.all_image', compact('genimage'));
    }

    public function UserGenerateImage()
    {
        return view('client.backend.generate.generate_image');
    }

    public function UserAllGenerateImage()
    {
        $genimage = GeneratedImage::where('user_id', Auth::id())->latest()->get();

        return view('client.backend.generate.all_image', compact('genimage'));
    }

    public function GenerateAudio()
    {
        return view('admin.backend.generate.generate_audio');
    }

    public function GenerateAndSaveAudio(StoreMediaGenerationRequest $request, AiMediaGenerator $generator): JsonResponse
    {
        $generatedAudio = $generator->generateAudio(Auth::user(), $request->string('text')->toString());

        return response()->json([
            'status' => 'success',
            'audio_url' => asset($generatedAudio->audio_path),
            'message' => 'Audio generated and saved successfully.',
        ]);
    }

    public function AllGenerateAudio()
    {
        $genaudio = GeneratedAudio::with('user')->latest()->get();

        return view('admin.backend.generate.all_audio', compact('genaudio'));
    }

    public function UserGenerateAudio()
    {
        return view('client.backend.generate.generate_audio');
    }

    public function UserAllGenerateAudio()
    {
        $genaudio = GeneratedAudio::where('user_id', Auth::id())->latest()->get();

        return view('client.backend.generate.all_audio', compact('genaudio'));
    }
}
