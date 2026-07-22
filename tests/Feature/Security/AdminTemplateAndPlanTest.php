<?php

use App\Models\GeneratedContent;

it('rejects undeclared template prompt placeholders', function (): void {
    $admin = testAdmin();

    $response = $this->actingAs($admin)->post(route('admin.templates.store'), [
        'title' => 'Secure Summary',
        'description' => 'Creates a concise English summary.',
        'category' => 'Writing',
        'icon' => 'cp-icon cp-icon-document',
        'prompt' => 'Summarize {topic} for {undeclared_audience}.',
        'is_active' => true,
        'input_fields' => [[
            'key' => 'topic',
            'title' => 'Topic',
            'description' => 'The subject to summarize.',
            'type' => 'text',
            'is_required' => true,
        ]],
    ]);

    $response->assertSessionHasErrors('prompt');
    $this->assertDatabaseMissing('templates', ['title' => 'Secure Summary']);
});

it('prevents deleting templates referenced by soft-deleted generated content', function (): void {
    $admin = testAdmin();
    $user = testUser();
    $template = testTemplate($admin);

    $document = GeneratedContent::query()->create([
        'user_id' => $user->id,
        'template_id' => $template->id,
        'input' => ['topic' => 'Security'],
        'output' => 'A stored generated document.',
        'model' => 'gpt-4o-mini',
    ]);
    $document->delete();

    $response = $this->actingAs($admin)->delete(route('admin.templates.destroy', $template));

    $response->assertSessionHasErrors('template');
    $this->assertDatabaseHas('templates', ['id' => $template->id]);
});

it('synchronizes user word limits when an administrator updates a plan', function (): void {
    $admin = testAdmin();
    $plan = testPlan(['monthly_word_limit' => 1_000]);
    $user = testUser($plan, ['word_limit' => 1_000]);

    $response = $this->actingAs($admin)->put(route('admin.plans.update', $plan), [
        'name' => $plan->name,
        'monthly_word_limit' => 2_500,
        'price' => $plan->price,
        'templates' => $plan->templates,
        'image_generation_limit' => $plan->image_generation_limit,
        'audio_generation_limit' => $plan->audio_generation_limit,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.plans.index'));
    expect($user->fresh()?->word_limit)->toBe(2_500);
});
