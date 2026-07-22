<?php

use App\Enums\PaymentStatus;
use App\Models\BillingHistory;
use App\Models\GeneratedAudio;
use App\Models\GeneratedContent;
use App\Models\GeneratedImage;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

it('prevents document IDOR for edit update and delete', function () {
    $owner = testUser();
    $attacker = testUser();
    $template = testTemplate();
    $document = GeneratedContent::query()->create([
        'user_id' => $owner->id,
        'template_id' => $template->id,
        'input' => ['topic' => 'Private'],
        'output' => 'Private generated content.',
        'word_count' => 3,
        'model' => 'gpt-4o-mini',
    ]);

    $this->actingAs($attacker)->get(route('user.documents.edit', $document))->assertNotFound();
    $this->actingAs($attacker)->patch(route('user.documents.update', $document), [
        'output' => 'Tampered content.',
    ])->assertNotFound();
    $this->actingAs($attacker)->delete(route('user.documents.destroy', $document))->assertNotFound();

    expect($document->fresh()->output)->toBe('Private generated content.');
});

it('prevents invoice IDOR before PDF rendering', function () {
    $owner = testUser();
    $attacker = testUser();
    $payment = BillingHistory::query()->create([
        'invoice_number' => (string) Str::uuid(),
        'user_id' => $owner->id,
        'plan_id' => $owner->plan_id,
        'submitted_at' => now(),
        'total' => 19,
        'bank_name' => 'Private Bank',
        'account_holder' => $owner->name,
        'payment_reference' => 'IDOR-'.Str::random(16),
        'reference_fingerprint' => hash('sha256', Str::random(32)),
        'receipt_path' => 'payment-receipts/idor-test.pdf',
        'status' => PaymentStatus::Pending,
        'pending_key' => (string) $owner->id,
    ]);

    $this->actingAs($attacker)
        ->get(route('user.invoices.download', $payment))
        ->assertNotFound();
});

it('prevents media IDOR and serves private media only to its owner', function () {
    Storage::fake('private');
    $owner = testUser();
    $attacker = testUser();
    $image = GeneratedImage::query()->create([
        'user_id' => $owner->id,
        'prompt' => 'Private image',
        'image_path' => 'generated/images/private.webp',
        'model' => 'gpt-image-1',
        'mime_type' => 'image/webp',
        'size_bytes' => 1024,
    ]);
    Storage::disk('private')->put('generated/images/private.webp', 'private-image');

    $audio = GeneratedAudio::query()->create([
        'user_id' => $owner->id,
        'prompt' => 'Private audio',
        'audio_path' => 'generated/audio/private.mp3',
        'model' => 'tts-1',
        'voice' => 'alloy',
        'mime_type' => 'audio/mpeg',
        'size_bytes' => 1024,
    ]);

    Storage::disk('private')->put('generated/audio/private.mp3', 'private-audio');

    $this->actingAs($attacker)->get(route('user.images.file', $image))->assertNotFound();
    $this->actingAs($attacker)->get(route('user.audio.file', $audio))->assertNotFound();
    $this->actingAs($attacker)->delete(route('user.images.destroy', $image))->assertNotFound();
    $this->actingAs($attacker)->delete(route('user.audio.destroy', $audio))->assertNotFound();

    $this->actingAs($owner)
        ->get(route('user.images.file', $image))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/webp');
    $this->actingAs($owner)
        ->get(route('user.audio.file', $audio))
        ->assertOk()
        ->assertHeader('Content-Type', 'audio/mpeg');

    expect($image->fresh())->not->toBeNull()
        ->and($audio->fresh())->not->toBeNull();
});

it('allows only the notification owner to mark it as read', function () {
    $owner = testUser();
    $attacker = testUser();
    $notification = $owner->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => DatabaseNotification::class,
        'data' => ['type' => 'payment_reviewed', 'message' => 'Private update'],
    ]);

    $this->actingAs($attacker)
        ->post(route('user.billing.notifications.read', $notification->id))
        ->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();

    $this->actingAs($owner)
        ->post(route('user.billing.notifications.read', $notification->id))
        ->assertRedirect();

    expect($notification->fresh()->read_at)->not->toBeNull();
});
