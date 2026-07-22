<?php

it('blocks guests from user, admin, and AI endpoints', function () {
    $template = testTemplate();

    $this->get(route('user.dashboard'))->assertRedirect(route('login'));
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->postJson(route('user.content.generate', $template), [])->assertUnauthorized();
});

it('separates administrator and user role boundaries', function () {
    $user = testUser();
    $admin = testAdmin();

    $this->actingAs($user)->get(route('admin.cms.questions.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.payments.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('user.dashboard'))->assertForbidden();
});

it('blocks inactive accounts at the route boundary', function () {
    $inactiveUser = testUser(attributes: ['is_active' => false]);
    $inactiveAdmin = testAdmin(['is_active' => false]);

    $this->actingAs($inactiveUser)->get(route('user.dashboard'))->assertForbidden();
    $this->actingAs($inactiveAdmin)->get(route('admin.dashboard'))->assertForbidden();
});

it('requires an active plan for user AI endpoints', function () {
    $user = testUser(attributes: ['plan_id' => null, 'word_limit' => 0]);

    $this->actingAs($user)
        ->postJson(route('user.images.store'), ['prompt' => 'A secure abstract image'])
        ->assertForbidden();
});

it('blocks AI access when the assigned plan has been deactivated', function () {
    $plan = testPlan(['is_active' => false]);
    $user = testUser($plan);

    $this->actingAs($user)
        ->postJson(route('user.images.store'), ['prompt' => 'A secure abstract image'])
        ->assertForbidden();
});

it('uses mutation-safe HTTP methods', function () {
    $user = testUser();
    $admin = testAdmin();
    $template = testTemplate($admin);

    $this->actingAs($user)->get(route('user.content.generate', $template))->assertMethodNotAllowed();
    $this->actingAs($user)->get(route('user.logout'))->assertMethodNotAllowed();
    $this->actingAs($admin)->get(route('admin.logout'))->assertMethodNotAllowed();
});

it('returns not found for malformed numeric resource identifiers', function () {
    $user = testUser();

    $this->actingAs($user)->get('/user/documents/not-a-number/edit')->assertNotFound();
    $this->actingAs($user)->get('/user/images/not-a-number/file')->assertNotFound();
    $this->actingAs($user)->get('/user/invoices/not-a-number/download')->assertNotFound();
});
