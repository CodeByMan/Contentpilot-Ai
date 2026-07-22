<?php

it('adds nonce-based defensive browser security headers to web responses', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()')
        ->assertHeader('Content-Security-Policy');

    $policy = (string) $response->headers->get('Content-Security-Policy');

    expect($policy)
        ->toContain("default-src 'self'")
        ->toMatch("/script-src 'self' 'nonce-[A-Za-z0-9+\/=]+'/")
        ->toMatch("/style-src 'self' 'nonce-[A-Za-z0-9+\/=]+'/")
        ->not->toContain("'unsafe-inline'")
        ->not->toContain("'unsafe-eval'");
});
