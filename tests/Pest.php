<?php

use App\Models\Plan;
use App\Models\Template;
use App\Models\TemplateInputField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function testPlan(array $attributes = []): Plan
{
    return Plan::query()->create(array_replace([
        'name' => 'Plan '.fake()->unique()->uuid(),
        'monthly_word_limit' => 1_000,
        'price' => 19.00,
        'templates' => 10,
        'image_generation_limit' => 5,
        'audio_generation_limit' => 5,
        'is_active' => true,
    ], $attributes));
}

function testUser(?Plan $plan = null, array $attributes = []): User
{
    $plan ??= testPlan();

    return User::factory()->create(array_replace([
        'plan_id' => $plan->id,
        'word_limit' => $plan->monthly_word_limit,
        'usage_reset_at' => now()->addMonthNoOverflow(),
    ], $attributes));
}

function testAdmin(array $attributes = []): User
{
    return User::factory()->admin()->create(array_replace([
        'plan_id' => null,
        'word_limit' => 0,
        'usage_reset_at' => null,
    ], $attributes));
}

function testTemplate(?User $creator = null, array $attributes = []): Template
{
    $creator ??= testAdmin();

    $template = Template::query()->create(array_replace([
        'title' => 'Template '.fake()->unique()->uuid(),
        'description' => 'A secure test template.',
        'category' => 'Testing',
        'icon' => 'cp-icon cp-icon-document',
        'prompt' => 'Write an English summary about {topic}.',
        'is_active' => true,
        'created_by' => $creator->id,
    ], $attributes));

    TemplateInputField::query()->create([
        'template_id' => $template->id,
        'key' => 'topic',
        'title' => 'Topic',
        'description' => 'The subject to summarize.',
        'type' => 'text',
        'is_required' => true,
        'order' => 0,
    ]);

    return $template;
}
