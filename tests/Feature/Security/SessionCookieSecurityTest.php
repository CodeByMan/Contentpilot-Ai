<?php

it('marks the session cookie secure when the production setting is enabled', function () {
    config()->set('app.env', 'production');
    config()->set('session.secure', true);

    $response = $this
        ->withSession(['session-cookie-check' => true])
        ->get(route('home'));

    $sessionCookie = collect($response->headers->getCookies())
        ->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));

    expect($sessionCookie)->not->toBeNull()
        ->and($sessionCookie->isSecure())->toBeTrue()
        ->and($sessionCookie->isHttpOnly())->toBeTrue()
        ->and($sessionCookie->getSameSite())->toBe('lax');
});
