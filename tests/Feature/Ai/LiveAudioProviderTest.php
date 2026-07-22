<?php

use OpenAI\Laravel\Facades\OpenAI;

it('generates speech through the configured live provider when explicitly enabled', function () {
    if (! filter_var(env('RUN_LIVE_OPENAI_TESTS', false), FILTER_VALIDATE_BOOL)) {
        $this->markTestSkipped('Set RUN_LIVE_OPENAI_TESTS=true and provide controlled OpenAI credentials.');
    }

    $model = (string) config('ai.audio_model');
    $voice = (string) config('ai.audio_voice');

    expect($model)->toBeIn((array) config('ai.audio_models'))
        ->and($voice)->toBeIn((array) config('ai.audio_voices'));

    $audio = OpenAI::audio()->speech([
        'model' => $model,
        'input' => 'ContentPilot live speech verification.',
        'voice' => $voice,
        'response_format' => 'mp3',
    ]);

    expect((string) $audio)->not->toBeEmpty();
})->group('live-openai');
