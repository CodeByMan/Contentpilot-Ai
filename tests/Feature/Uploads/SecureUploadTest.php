<?php

use App\Services\Storage\SecureUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('rejects executable and oversized profile uploads', function () {
    $user = testUser();

    $this->actingAs($user)->patch(route('user.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => UploadedFile::fake()->create('avatar.php', 10, 'application/x-php'),
    ])->assertSessionHasErrors('photo');

    $this->actingAs($user)->patch(route('user.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => UploadedFile::fake()->create('large.jpg', 3_000, 'image/jpeg'),
    ])->assertSessionHasErrors('photo');
});

it('re-encodes images to random webp filenames', function () {
    Storage::fake('public');
    $service = app(SecureUploadService::class);
    $path = $service->storeImage(
        UploadedFile::fake()->image('predictable-name.jpg', 600, 400),
        'profiles',
        512,
        512,
    );

    expect($path)->toMatch('/^profiles\/[0-9a-f-]{36}\.webp$/')
        ->and($path)->not->toContain('predictable-name');
    Storage::disk('public')->assertExists($path);
});

it('stores PDF receipts privately with random names', function () {
    Storage::fake('private');
    $service = app(SecureUploadService::class);
    $path = $service->storePrivateReceipt(
        UploadedFile::fake()->createWithContent(
            'bank-statement.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF",
        ),
    );

    expect($path)->toMatch('/^payment-receipts\/[0-9a-f-]{36}\.pdf$/')
        ->and($path)->not->toContain('bank-statement');
    Storage::disk('private')->assertExists($path);
});

it('rejects forged receipts when the content does not match the claimed type', function () {
    Storage::fake('private');
    $service = app(SecureUploadService::class);

    expect(fn () => $service->storePrivateReceipt(
        UploadedFile::fake()->createWithContent('forged.pdf', '<?php echo "unsafe";'),
    ))->toThrow(RuntimeException::class, 'valid image or PDF');

    expect(Storage::disk('private')->allFiles())->toBeEmpty();
});

it('rejects unsupported files when the upload service is called directly', function () {
    Storage::fake('public');
    $service = app(SecureUploadService::class);

    expect(fn () => $service->storeImage(
        UploadedFile::fake()->createWithContent('payload.jpg', '#!/bin/sh\necho unsafe'),
        'profiles',
        512,
        512,
    ))->toThrow(RuntimeException::class);

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('rejects receipt images outside the safe dimension range', function () {
    Storage::fake('private');
    $user = testUser();
    $plan = testPlan(['name' => 'Receipt Dimensions', 'price' => 25]);

    $this->actingAs($user)->post(route('user.checkout.store'), [
        'plan_id' => $plan->id,
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'SMALL-RECEIPT-123',
        'receipt' => UploadedFile::fake()->image('receipt.png', 100, 100),
    ])->assertSessionHasErrors('receipt');

    expect(Storage::disk('private')->allFiles())->toBeEmpty();
});
