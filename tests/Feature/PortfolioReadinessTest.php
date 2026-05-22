<?php

use App\Models\GeneratedContent;
use App\Models\Plan;
use App\Models\Template;
use App\Models\TemplateInputFields;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('protects core SaaS models with explicit fillable fields', function () {
    expect((new User())->getFillable())->toContain('name', 'email', 'role', 'plan_id')
        ->and((new Template())->getFillable())->toContain('title', 'prompt', 'created_by')
        ->and((new GeneratedContent())->getFillable())->toContain('input', 'output', 'word_count')
        ->and((new Plan())->getFillable())->toContain('monthly_word_limit', 'templates');
});

it('allows demo users to reach role-specific dashboards', function () {
    $plan = Plan::create([
        'name' => 'Creator',
        'monthly_word_limit' => 25000,
        'price' => 19,
        'templates' => 10,
    ]);

    $admin = User::create([
        'name' => 'Admin Demo',
        'email' => 'admin@example.test',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'plan_id' => $plan->id,
        'current_word_usage' => 25000,
        'words_used' => 0,
    ]);

    $user = User::create([
        'name' => 'Client Demo',
        'email' => 'client@example.test',
        'password' => Hash::make('password'),
        'role' => 'user',
        'plan_id' => $plan->id,
        'current_word_usage' => 25000,
        'words_used' => 0,
    ]);

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

it('stores template input configuration for AI prompts', function () {
    $plan = Plan::create([
        'name' => 'Starter',
        'monthly_word_limit' => 5000,
        'price' => 0,
        'templates' => 3,
    ]);

    $admin = User::create([
        'name' => 'Admin Demo',
        'email' => 'template-admin@example.test',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'plan_id' => $plan->id,
        'current_word_usage' => 5000,
        'words_used' => 0,
    ]);

    $template = Template::create([
        'title' => 'Landing Page Copy',
        'description' => 'Generate SaaS landing page sections.',
        'category' => 'Marketing',
        'icon' => 'icon ni ni-layout',
        'prompt' => 'Write landing page copy for {Product}.',
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    TemplateInputFields::create([
        'template_id' => $template->id,
        'title' => 'Product',
        'description' => 'Product name',
        'type' => 'text',
        'is_required' => true,
        'order' => 0,
    ]);

    expect($template->fresh()->inputFields)->toHaveCount(1)
        ->and($template->fresh()->inputFields->first()->title)->toBe('Product');
});
