<?php

use App\Enums\AiFeature;
use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Exceptions\QuotaExceededException;
use App\Models\AiUsageReservation;
use App\Models\User;
use App\Services\Quota\AiQuotaService;

it('reserves and commits quota atomically around persistence', function () {
    $plan = testPlan(['monthly_word_limit' => 100]);
    $user = testUser($plan, ['word_limit' => 100]);
    $quota = app(AiQuotaService::class);

    $reservation = $quota->reserve($user, AiFeature::Content, 60);
    expect($user->fresh()->words_reserved)->toBe(60);

    $result = $quota->commit($reservation, 42, fn (): string => 'persisted');

    expect($result)->toBe('persisted')
        ->and($user->fresh()->words_reserved)->toBe(0)
        ->and($user->fresh()->words_used)->toBe(42)
        ->and($reservation->fresh()->status)->toBe(ReservationStatus::Committed)
        ->and($reservation->fresh()->committed_units)->toBe(42);
});

it('releases reserved quota after a provider failure', function () {
    $user = testUser(testPlan(['monthly_word_limit' => 100]), ['word_limit' => 100]);
    $quota = app(AiQuotaService::class);
    $reservation = $quota->reserve($user, AiFeature::Content, 80);

    expect($quota->release($reservation))->toBeTrue()
        ->and($user->fresh()->words_reserved)->toBe(0)
        ->and($user->fresh()->words_used)->toBe(0)
        ->and($reservation->fresh()->status)->toBe(ReservationStatus::Released);
});

it('counts active reservations when preventing concurrent over-consumption', function () {
    $user = testUser(testPlan(['monthly_word_limit' => 100]), ['word_limit' => 100]);
    $quota = app(AiQuotaService::class);
    $quota->reserve($user, AiFeature::Content, 70);

    expect(fn () => $quota->reserve($user, AiFeature::Content, 31))
        ->toThrow(QuotaExceededException::class);

    expect($user->fresh()->words_reserved)->toBe(70)
        ->and(AiUsageReservation::query()->count())->toBe(1);
});

it('enforces separate image and audio limits', function () {
    $plan = testPlan(['image_generation_limit' => 1, 'audio_generation_limit' => 1]);
    $user = testUser($plan);
    $quota = app(AiQuotaService::class);

    $quota->reserve($user, AiFeature::Image, 1);
    $quota->reserve($user, AiFeature::Audio, 1);

    expect(fn () => $quota->reserve($user, AiFeature::Image, 1))->toThrow(QuotaExceededException::class)
        ->and(fn () => $quota->reserve($user, AiFeature::Audio, 1))->toThrow(QuotaExceededException::class);
});

it('releases expired reservations through maintenance cleanup', function () {
    $user = testUser(testPlan(['monthly_word_limit' => 100]), ['word_limit' => 100]);
    $quota = app(AiQuotaService::class);
    $reservation = $quota->reserve($user, AiFeature::Content, 25);
    $reservation->forceFill(['expires_at' => now()->subMinute()])->save();

    expect($quota->releaseExpiredReservations())->toBe(1)
        ->and($user->fresh()->words_reserved)->toBe(0)
        ->and($reservation->fresh()->status)->toBe(ReservationStatus::Released);
});

it('applies the atomic word quota path to assistant chat output', function () {
    $user = testUser(testPlan(['monthly_word_limit' => 100]), ['word_limit' => 100]);
    $quota = app(AiQuotaService::class);

    $reservation = $quota->reserve($user, AiFeature::Chat, 50);
    $conversation = $quota->commit($reservation, 18, fn (): string => 'conversation-persisted');

    expect($conversation)->toBe('conversation-persisted')
        ->and($user->fresh()->words_reserved)->toBe(0)
        ->and($user->fresh()->words_used)->toBe(18)
        ->and($reservation->fresh()->status)->toBe(ReservationStatus::Committed);
});

it('uses the locked current role instead of a stale administrator model', function () {
    $plan = testPlan(['monthly_word_limit' => 100]);
    $staleAdministrator = testAdmin();

    User::query()->whereKey($staleAdministrator->id)->update([
        'role' => UserRole::User->value,
        'plan_id' => $plan->id,
        'word_limit' => 100,
        'usage_reset_at' => now()->addMonthNoOverflow(),
    ]);

    $reservation = app(AiQuotaService::class)->reserve(
        $staleAdministrator,
        AiFeature::Content,
        25,
    );

    expect($reservation)->not->toBeNull()
        ->and($staleAdministrator->fresh()->words_reserved)->toBe(25);
});
