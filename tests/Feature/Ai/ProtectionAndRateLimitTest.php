<?php

use Illuminate\Support\Facades\RateLimiter;

it('enforces prompt length and allowed model validation', function () {
    $user = testUser();
    $template = testTemplate();

    $this->actingAs($user)->postJson(route('user.content.generate', $template), [
        'topic' => str_repeat('A', 1600),
        'result_length' => 100,
        'ai_model' => 'unapproved-model',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['topic', 'ai_model']);
});

it('rate limits content generation per authenticated user', function () {
    config()->set('ai.rate_limits.content_per_minute', 2);
    $user = testUser();
    $template = testTemplate();
    RateLimiter::clear('content:'.$user->id);

    $this->actingAs($user)->postJson(route('user.content.generate', $template), [])->assertUnprocessable();
    $this->actingAs($user)->postJson(route('user.content.generate', $template), [])->assertUnprocessable();
    $this->actingAs($user)->postJson(route('user.content.generate', $template), [])->assertTooManyRequests();
});

it('rate limits media generation separately', function () {
    config()->set('ai.rate_limits.media_per_minute', 2);
    $user = testUser();
    RateLimiter::clear('media:'.$user->id);

    $this->actingAs($user)->postJson(route('user.images.store'), [])->assertUnprocessable();
    $this->actingAs($user)->postJson(route('user.images.store'), [])->assertUnprocessable();
    $this->actingAs($user)->postJson(route('user.images.store'), [])->assertTooManyRequests();
});
