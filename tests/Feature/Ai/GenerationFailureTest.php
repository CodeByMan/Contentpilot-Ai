<?php

use App\Enums\ReservationStatus;
use App\Exceptions\AiGenerationException;
use App\Models\AiUsageReservation;
use App\Services\Ai\AiContentGenerator;
use App\Services\Ai\PromptBuilder;
use App\Services\Quota\AiQuotaService;

it('releases quota when prompt construction fails before the provider call', function () {
    config()->set('ai.content_models', ['gpt-test']);
    $user = testUser(testPlan(['monthly_word_limit' => 500]), ['word_limit' => 500]);
    $template = testTemplate();
    $promptBuilder = Mockery::mock(PromptBuilder::class);
    $promptBuilder->shouldReceive('build')->once()->andThrow(new \RuntimeException('Prompt failure'));
    $generator = new AiContentGenerator($promptBuilder, app(AiQuotaService::class));

    expect(fn () => $generator->generate(
        $user,
        $template,
        ['topic' => 'Secure systems'],
        'gpt-test',
        100,
    ))->toThrow(AiGenerationException::class);

    $reservation = AiUsageReservation::query()->sole();

    expect($reservation->status)->toBe(ReservationStatus::Released)
        ->and($user->fresh()->words_reserved)->toBe(0)
        ->and($user->fresh()->words_used)->toBe(0);
});
