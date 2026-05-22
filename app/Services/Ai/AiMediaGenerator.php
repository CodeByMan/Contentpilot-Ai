<?php

namespace App\Services\Ai;

use App\Models\GeneratedAudio;
use App\Models\GeneratedImage;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;

class AiMediaGenerator
{
    public function generateImage(User $user, string $prompt): GeneratedImage
    {
        $response = OpenAI::images()->create([
            'model' => config('services.openai.image_model', 'dall-e-3'),
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
        ]);

        $imageUrl = $response->data[0]->url;
        $imageContents = Http::timeout(30)->get($imageUrl)->throw()->body();

        $fileName = 'generated_'.now()->format('YmdHis').'_'.Str::random(8).'.png';
        $destinationPath = public_path('upload/generated_image');

        File::ensureDirectoryExists($destinationPath, 0755);
        file_put_contents($destinationPath.'/'.$fileName, $imageContents);

        return GeneratedImage::create([
            'user_id' => $user->id,
            'prompt' => $prompt,
            'image_path' => 'upload/generated_image/'.$fileName,
        ]);
    }

    public function generateAudio(User $user, string $text): GeneratedAudio
    {
        $response = OpenAI::audio()->speech([
            'model' => config('services.openai.tts_model', 'tts-1'),
            'input' => $text,
            'voice' => config('services.openai.tts_voice', 'nova'),
            'response_format' => 'mp3',
        ]);

        $fileName = 'tts_'.now()->format('YmdHis').'_'.Str::random(8).'.mp3';
        $destinationPath = public_path('upload/audio');

        File::ensureDirectoryExists($destinationPath, 0755);
        file_put_contents($destinationPath.'/'.$fileName, $response);

        return GeneratedAudio::create([
            'user_id' => $user->id,
            'prompt' => $text,
            'audio_path' => 'upload/audio/'.$fileName,
        ]);
    }
}
