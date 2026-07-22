<?php

use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

it('requires verified email for protected dashboards', function () {
    $user = testUser(attributes: ['email_verified_at' => null]);

    $this->actingAs($user)
        ->get(route('user.dashboard'))
        ->assertRedirect(route('verification.notice'));
});

it('verifies an email only with a valid signed hash', function () {
    Event::fake();
    $user = testUser(attributes: ['email_verified_at' => null]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)->get($verificationUrl)
        ->assertRedirect(route('user.dashboard').'?verified=1');

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('does not verify an email with an invalid hash', function () {
    $user = testUser(attributes: ['email_verified_at' => null]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong@example.test')],
    );

    $this->actingAs($user)->get($verificationUrl)->assertForbidden();
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
