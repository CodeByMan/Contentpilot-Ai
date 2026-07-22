<?php

use App\Models\User;
use Illuminate\Support\Facades\Event;

it('renders the login and registration screens', function () {
    $this->get(route('login'))->assertOk();
    $this->get(route('register'))->assertOk();
});

it('authenticates verified users and sends them to their role dashboard', function () {
    $user = testUser();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'TestPassword!23456',
        'remember' => '1',
    ])->assertRedirect(route('user.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials and inactive accounts', function () {
    $user = testUser();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'WrongPassword!23456',
    ])->assertSessionHasErrors('email');
    $this->assertGuest();

    $inactive = testUser(attributes: ['is_active' => false]);
    $this->post(route('login'), [
        'email' => $inactive->email,
        'password' => 'TestPassword!23456',
    ])->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('registers without granting a paid plan for free', function () {
    Event::fake();
    $paidPlan = testPlan(['price' => 49.00]);

    $this->post(route('register'), [
        'name' => 'New User',
        'email' => 'new-user@example.test',
        'password' => 'StrongPassword!23456',
        'password_confirmation' => 'StrongPassword!23456',
    ])->assertRedirect(route('verification.notice'));

    $user = User::query()->where('email', 'new-user@example.test')->firstOrFail();
    expect($user->plan_id)->toBeNull()
        ->and($user->word_limit)->toBe(0)
        ->and($user->plan_id)->not->toBe($paidPlan->id);
});

it('assigns only an active zero-price registration plan', function () {
    Event::fake();
    $freePlan = testPlan(['name' => 'Free', 'price' => 0]);
    testPlan(['name' => 'Inactive Free', 'price' => 0, 'is_active' => false]);

    $this->post(route('register'), [
        'name' => 'Free User',
        'email' => 'free-user@example.test',
        'password' => 'StrongPassword!23456',
        'password_confirmation' => 'StrongPassword!23456',
    ])->assertRedirect(route('verification.notice'));

    $user = User::query()->where('email', 'free-user@example.test')->firstOrFail();
    expect($user->plan_id)->toBe($freePlan->id)
        ->and($user->word_limit)->toBe($freePlan->monthly_word_limit);
});

it('logs users out and invalidates their session', function () {
    $user = testUser();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});
