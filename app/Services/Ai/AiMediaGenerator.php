<?php

namespace App\Services\Ai;

use App\Enums\AiFeature;
use App\Exceptions\AiGenerationException;
use App\Models\AiUsageReservation;
use App\Models\GeneratedAudio;
use App\Models\GeneratedImage;
use App\Models\User;
use App\Services\Quota\AiQuotaService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;
use Throwable;

class AiMediaGenerator
{
    public function __construct(private readonly AiQuotaService $quota) {}

    public function generateImage(User $user, string $prompt): GeneratedImage
    {
        $model = (string) config('ai.image_model', 'gpt-image-2');

        if (! in_array($model, (array) config('ai.image_models', []), true)) {
            throw new AiGenerationException('The configured image model is not allowed.');
        }

        $reservation = $this->quota->reserve($user, AiFeature::Image, 1);
        $path = null;

        try {
            $response = retry(
                max(1, (int) config('ai.retry_attempts', 2)),
                fn () => OpenAI::images()->create([
                    'model' => $model,
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => '1024x1024',
                ]),
                max(0, (int) config('ai.retry_delay_ms', 250)),
            );

            $encoded = (string) ($response->data[0]->b64Json ?? '');
            $binary = base64_decode($encoded, true);

            if ($binary === false || $binary === '' || strlen($binary) > 15 * 1024 * 1024) {
                throw new RuntimeException('The AI provider returned invalid image data.');
            }

            $image = (new ImageManager(new Driver))->read($binary)->cover(1024, 1024);
            $contents = (string) $image->toWebp(85);
            $path = 'generated/images/'.Str::uuid().'.webp';
            if (! Storage::disk('private')->put($path, $contents)) {
                throw new RuntimeException('The generated image could not be stored.');
            }

            return $this->quota->commit(
                $reservation,
                1,
                fn (): GeneratedImage => GeneratedImage::query()->create([
                    'user_id' => $user->id,
                    'prompt' => $prompt,
                    'image_path' => $path,
                    'model' => $model,
                    'mime_type' => 'image/webp',
                    'size_bytes' => strlen($contents),
                ]),
            );
        } catch (Throwable $exception) {
            $this->deletePrivateFile($path);
            $this->releaseReservationSafely($reservation);
            $this->logFailure('image', $user, $model, $exception);

            throw new AiGenerationException(previous: $exception);
        }
    }

    public function generateAudio(User $user, string $text): GeneratedAudio
    {
        $model = (string) config('ai.audio_model');
        $voice = (string) config('ai.audio_voice', 'nova');

        if (! in_array($model, (array) config('ai.audio_models', []), true)) {
            throw new AiGenerationException('The configured audio model is not allowed.');
        }

        if (! in_array($voice, (array) config('ai.audio_voices', []), true)) {
            throw new AiGenerationException('The configured audio voice is not allowed.');
        }

        $reservation = $this->quota->reserve($user, AiFeature::Audio, 1);
        $path = null;

        try {
            $contents = retry(
                max(1, (int) config('ai.retry_attempts', 2)),
                fn () => OpenAI::audio()->speech([
                    'model' => $model,
                    'input' => $text,
                    'voice' => $voice,
                    'response_format' => 'mp3',
                ]),
                max(0, (int) config('ai.retry_delay_ms', 250)),
            );
            $contents = (string) $contents;

            if ($contents === '' || strlen($contents) > 20 * 1024 * 1024) {
                throw new RuntimeException('The AI provider returned invalid audio data.');
            }

            $path = 'generated/audio/'.Str::uuid().'.mp3';
            if (! Storage::disk('private')->put($path, $contents)) {
                throw new RuntimeException('The generated audio could not be stored.');
            }

            return $this->quota->commit(
                $reservation,
                1,
                fn (): GeneratedAudio => GeneratedAudio::query()->create([
                    'user_id' => $user->id,
                    'prompt' => $text,
                    'audio_path' => $path,
                    'model' => $model,
                    'voice' => $voice,
                    'mime_type' => 'audio/mpeg',
                    'size_bytes' => strlen($contents),
                ]),
            );
        } catch (Throwable $exception) {
            $this->deletePrivateFile($path);
            $this->releaseReservationSafely($reservation);
            $this->logFailure('audio', $user, $model, $exception);

            throw new AiGenerationException(previous: $exception);
        }
    }

    private function releaseReservationSafely(?AiUsageReservation $reservation): void
    {
        try {
            $this->quota->release($reservation);
        } catch (Throwable $releaseException) {
            Log::error('AI quota release failed after media generation error.', [
                'reservation_id' => $reservation?->id,
                'exception_class' => $releaseException::class,
            ]);
        }
    }

    private function deletePrivateFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        try {
            Storage::disk('private')->delete($path);
        } catch (Throwable $exception) {
            Log::warning('Failed AI media file cleanup.', [
                'exception_class' => $exception::class,
            ]);
        }
    }

    private function logFailure(string $feature, User $user, string $model, Throwable $exception): void
    {
        Log::warning('AI media generation failed.', [
            'feature' => $feature,
            'user_id' => $user->id,
            'model' => $model,
            'exception_class' => $exception::class,
        ]);
    }
}
