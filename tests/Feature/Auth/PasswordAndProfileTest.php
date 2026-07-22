<?php

use App\Enums\PaymentStatus;
use App\Models\BillingHistory;
use App\Models\GeneratedAudio;
use App\Models\GeneratedImage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

it('updates a user password only after current-password validation', function () {
    $user = testUser();

    $this->actingAs($user)
        ->put(route('user.password.update'), [
            'old_password' => 'WrongPassword!23456',
            'new_password' => 'NewStrongPassword!23456',
            'new_password_confirmation' => 'NewStrongPassword!23456',
        ])->assertSessionHasErrors('old_password');

    $this->actingAs($user)
        ->put(route('user.password.update'), [
            'old_password' => 'TestPassword!23456',
            'new_password' => 'NewStrongPassword!23456',
            'new_password_confirmation' => 'NewStrongPassword!23456',
        ])->assertSessionHasNoErrors();

    expect(Hash::check('NewStrongPassword!23456', $user->fresh()->password))->toBeTrue();
});

it('requires re-verification when the profile email changes', function () {
    Notification::fake();
    $user = testUser();

    $this->actingAs($user)
        ->patch(route('user.profile.update'), [
            'name' => 'Updated User',
            'email' => 'updated@example.test',
            'phone' => '+92 300 0000000',
            'address' => 'Karachi, Pakistan',
            'current_password' => 'TestPassword!23456',
        ])->assertSessionHasNoErrors();

    $user->refresh();
    expect($user->name)->toBe('Updated User')
        ->and($user->email)->toBe('updated@example.test')
        ->and($user->email_verified_at)->toBeNull();
});

it('requires the current password before changing an email address', function () {
    $user = testUser();

    $this->actingAs($user)
        ->patch(route('user.profile.update'), [
            'name' => $user->name,
            'email' => 'blocked-change@example.test',
        ])
        ->assertSessionHasErrors('current_password');

    expect($user->fresh()->email)->toBe($user->email);
});

it('anonymizes a deleted account, closes pending payments, and removes owned files', function () {
    Storage::fake('public');
    Storage::fake('private');
    $user = testUser();
    $user->update(['photo' => 'profiles/delete-me.webp']);
    Storage::disk('public')->put('profiles/delete-me.webp', 'profile');
    $image = GeneratedImage::query()->create([
        'user_id' => $user->id,
        'prompt' => 'Delete this image',
        'image_path' => 'generated/images/delete-me.webp',
        'model' => 'gpt-image-1',
        'mime_type' => 'image/webp',
        'size_bytes' => 5,
    ]);
    $audio = GeneratedAudio::query()->create([
        'user_id' => $user->id,
        'prompt' => 'Delete this audio',
        'audio_path' => 'generated/audio/delete-me.mp3',
        'model' => 'tts-1',
        'voice' => 'alloy',
        'mime_type' => 'audio/mpeg',
        'size_bytes' => 5,
    ]);
    Storage::disk('private')->put($image->image_path, 'image');
    Storage::disk('private')->put($audio->audio_path, 'audio');
    $payment = BillingHistory::query()->create([
        'invoice_number' => (string) Str::uuid(),
        'user_id' => $user->id,
        'plan_id' => $user->plan_id,
        'submitted_at' => now(),
        'total' => 19,
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'DELETE-'.Str::random(12),
        'reference_fingerprint' => hash('sha256', Str::random(32)),
        'receipt_path' => 'payment-receipts/deletion-test.pdf',
        'status' => PaymentStatus::Pending,
        'pending_key' => (string) $user->id,
    ]);

    $this->actingAs($user)
        ->delete(route('user.profile.destroy'), ['password' => 'TestPassword!23456'])
        ->assertRedirect(route('home'));

    $deleted = $user->newQuery()->withTrashed()->findOrFail($user->id);
    expect($deleted->trashed())->toBeTrue()
        ->and($deleted->is_active)->toBeFalse()
        ->and($deleted->name)->toBe('Deleted User')
        ->and($deleted->email)->toEndWith('@example.invalid')
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Rejected);
    Storage::disk('public')->assertMissing('profiles/delete-me.webp');
    Storage::disk('private')->assertMissing('generated/images/delete-me.webp');
    Storage::disk('private')->assertMissing('generated/audio/delete-me.mp3');
    expect(GeneratedImage::query()->withTrashed()->find($image->id))->toBeNull()
        ->and(GeneratedAudio::query()->withTrashed()->find($audio->id))->toBeNull();
    $this->assertGuest();
});
