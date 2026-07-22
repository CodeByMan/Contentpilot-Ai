<?php

use App\Enums\ReservationStatus;
use App\Exceptions\AiGenerationException;
use App\Models\AiUsageReservation;
use App\Models\GeneratedAudio;
use App\Services\Ai\AiMediaGenerator;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Resources\Audio;

beforeEach(function () {
    config()->set('ai.audio_models', ['tts-1', 'tts-1-hd']);
    config()->set('ai.audio_model', 'tts-1');
    config()->set('ai.audio_voices', ['nova']);
    config()->set('ai.audio_voice', 'nova');
    config()->set('ai.retry_attempts', 1);
    Storage::fake('private');
});

it('uses an allowed current speech model and stores generated audio privately', function () {
    OpenAI::fake(['test-mp3-content']);
    $user = testUser(testPlan(['audio_generation_limit' => 1]));

    $audio = app(AiMediaGenerator::class)->generateAudio($user, 'A short portfolio test message.');

    expect($audio)->toBeInstanceOf(GeneratedAudio::class)
        ->and($audio->model)->toBe('tts-1')
        ->and($audio->voice)->toBe('nova')
        ->and($audio->mime_type)->toBe('audio/mpeg')
        ->and($audio->size_bytes)->toBe(strlen('test-mp3-content'))
        ->and($user->fresh()->audio_generations_reserved)->toBe(0)
        ->and($user->fresh()->audio_generations_used)->toBe(1);

    Storage::disk('private')->assertExists($audio->audio_path);

    OpenAI::assertSent(Audio::class, function (string $method, array $parameters): bool {
        return $method === 'speech'
            && $parameters['model'] === 'tts-1'
            && $parameters['voice'] === 'nova'
            && $parameters['response_format'] === 'mp3';
    });
});

it('rejects an unsupported speech model before reserving quota or contacting the provider', function () {
    config()->set('ai.audio_model', 'unsupported-speech-model');
    OpenAI::fake([]);
    $user = testUser(testPlan(['audio_generation_limit' => 1]));

    expect(fn () => app(AiMediaGenerator::class)->generateAudio($user, 'Rejected model test.'))
        ->toThrow(AiGenerationException::class, 'The configured audio model is not allowed.');

    expect(AiUsageReservation::query()->count())->toBe(0)
        ->and(GeneratedAudio::query()->count())->toBe(0)
        ->and($user->fresh()->audio_generations_reserved)->toBe(0);

    OpenAI::assertNothingSent();
});

it('releases reserved audio quota when the provider fails', function () {
    OpenAI::fake([new RuntimeException('Controlled provider failure')]);
    $user = testUser(testPlan(['audio_generation_limit' => 1]));

    expect(fn () => app(AiMediaGenerator::class)->generateAudio($user, 'Provider failure test.'))
        ->toThrow(AiGenerationException::class);

    $reservation = AiUsageReservation::query()->sole();

    expect($reservation->status)->toBe(ReservationStatus::Released)
        ->and($user->fresh()->audio_generations_reserved)->toBe(0)
        ->and($user->fresh()->audio_generations_used)->toBe(0)
        ->and(GeneratedAudio::query()->count())->toBe(0);

    expect(Storage::disk('private')->allFiles())->toBe([]);
});
