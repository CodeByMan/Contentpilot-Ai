<?php

use App\Enums\UserRole;
use App\Models\User;

it('creates a verified administrator through the protected console workflow', function () {
    $this->artisan('contentpilot:admin:create', [
        '--name' => 'Portfolio Administrator',
        '--email' => 'ADMIN@EXAMPLE.COM',
    ])
        ->expectsQuestion('Password', 'StrongAdmin!23456')
        ->expectsQuestion('Confirm password', 'StrongAdmin!23456')
        ->expectsOutput('Administrator created for admin@example.com.')
        ->assertSuccessful();

    $administrator = User::query()->where('email', 'admin@example.com')->sole();

    expect($administrator->role)->toBe(UserRole::Admin)
        ->and($administrator->is_active)->toBeTrue()
        ->and($administrator->hasVerifiedEmail())->toBeTrue()
        ->and($administrator->plan_id)->toBeNull();
});

it('rejects weak passwords and duplicate administrator emails', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $this->artisan('contentpilot:admin:create', [
        '--name' => 'Existing Account',
        '--email' => 'existing@example.com',
    ])
        ->expectsQuestion('Password', 'weak')
        ->expectsQuestion('Confirm password', 'weak')
        ->assertFailed();

    expect(User::query()->where('email', 'existing@example.com')->count())->toBe(1);
});
