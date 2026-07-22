<?php

use App\Enums\AiFeature;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Models\AiUsageReservation;
use App\Models\BillingHistory;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\PaymentReviewedNotification;
use App\Services\Billing\OfflinePaymentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

it('submits a pending payment without activating the requested plan', function () {
    Storage::fake('private');
    $currentPlan = testPlan(['name' => 'Current', 'price' => 9]);
    $requestedPlan = testPlan(['name' => 'Upgrade', 'price' => 49]);
    $user = testUser($currentPlan);

    $this->actingAs($user)->post(route('user.checkout.store'), [
        'plan_id' => $requestedPlan->id,
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'ref-'.Str::random(12),
        'receipt' => UploadedFile::fake()->createWithContent(
            'receipt.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF",
        ),
    ])->assertRedirect(route('user.payment.success'));

    $payment = BillingHistory::query()->sole();
    expect($payment->status)->toBe(PaymentStatus::Pending)
        ->and($user->fresh()->plan_id)->toBe($currentPlan->id)
        ->and($user->fresh()->plan_id)->not->toBe($requestedPlan->id);
    Storage::disk('private')->assertExists($payment->receipt_path);
});

it('requires valid proof and an active paid plan', function () {
    Storage::fake('private');
    $user = testUser();
    $free = testPlan(['name' => 'Free Validation', 'price' => 0]);
    $inactive = testPlan(['name' => 'Inactive Paid', 'price' => 30, 'is_active' => false]);

    $this->actingAs($user)->post(route('user.checkout.store'), [
        'plan_id' => $free->id,
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'FREE-REF',
    ])->assertSessionHasErrors(['plan_id', 'receipt']);

    $this->actingAs($user)->post(route('user.checkout.store'), [
        'plan_id' => $inactive->id,
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'INACTIVE-REF',
        'receipt' => UploadedFile::fake()->createWithContent(
            'receipt.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF",
        ),
    ])->assertSessionHasErrors('plan_id');
});

it('approves a pending payment atomically and notifies the user', function () {
    Notification::fake();
    $oldPlan = testPlan(['name' => 'Old']);
    $newPlan = testPlan(['name' => 'Approved', 'monthly_word_limit' => 8_000]);
    $user = testUser($oldPlan, ['words_used' => 700, 'words_reserved' => 50]);
    $admin = testAdmin();
    $service = app(OfflinePaymentService::class);
    $payment = $service->submit($user, $newPlan, [
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'APPROVE-'.Str::random(12),
        'receipt_path' => 'payment-receipts/test-'.Str::random(12).'.pdf',
    ]);

    $reviewed = $service->approve($payment, $admin);
    $user->refresh();

    expect($reviewed->status)->toBe(PaymentStatus::Approved)
        ->and($reviewed->reviewed_by)->toBe($admin->id)
        ->and($user->plan_id)->toBe($newPlan->id)
        ->and($user->word_limit)->toBe(8_000)
        ->and($user->words_used)->toBe(0)
        ->and($user->words_reserved)->toBe(0);
    Notification::assertSentTo($user, PaymentReviewedNotification::class);
});

it('releases outstanding AI reservations when a new plan is approved', function () {
    Notification::fake();
    $oldPlan = testPlan(['name' => 'Reserved Plan', 'monthly_word_limit' => 1_000]);
    $newPlan = testPlan(['name' => 'Fresh Plan', 'monthly_word_limit' => 2_000]);
    $user = testUser($oldPlan, ['words_reserved' => 400]);
    $admin = testAdmin();
    $reservation = AiUsageReservation::query()->create([
        'token' => (string) Str::uuid(),
        'user_id' => $user->id,
        'feature' => AiFeature::Content,
        'reserved_units' => 400,
        'status' => ReservationStatus::Reserved,
        'expires_at' => now()->addMinutes(10),
    ]);
    $service = app(OfflinePaymentService::class);
    $payment = $service->submit($user, $newPlan, [
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'RESERVATION-'.Str::random(12),
        'receipt_path' => 'payment-receipts/test-'.Str::random(12).'.pdf',
    ]);

    $service->approve($payment, $admin);

    expect($reservation->fresh()->status)->toBe(ReservationStatus::Released)
        ->and($reservation->fresh()->released_at)->not->toBeNull()
        ->and($user->fresh()->words_reserved)->toBe(0)
        ->and($user->fresh()->word_limit)->toBe(2_000);
});

it('rejects a payment without changing the current plan', function () {
    Notification::fake();
    $currentPlan = testPlan(['name' => 'Existing']);
    $requestedPlan = testPlan(['name' => 'Rejected']);
    $user = testUser($currentPlan);
    $admin = testAdmin();
    $service = app(OfflinePaymentService::class);
    $payment = $service->submit($user, $requestedPlan, [
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'REJECT-'.Str::random(12),
        'receipt_path' => 'payment-receipts/test-'.Str::random(12).'.pdf',
    ]);

    $reviewed = $service->reject($payment, $admin, 'Receipt could not be verified.');

    expect($reviewed->status)->toBe(PaymentStatus::Rejected)
        ->and($reviewed->rejection_reason)->toBe('Receipt could not be verified.')
        ->and($user->fresh()->plan_id)->toBe($currentPlan->id);
    Notification::assertSentTo($user, PaymentReviewedNotification::class);
});

it('prevents non-administrators from reviewing payments', function () {
    $plan = testPlan();
    $user = testUser($plan);
    $service = app(OfflinePaymentService::class);
    $payment = $service->submit($user, $plan, [
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'NONADMIN-'.Str::random(12),
        'receipt_path' => 'payment-receipts/test-'.Str::random(12).'.pdf',
    ]);

    expect(fn () => $service->approve($payment, $user))
        ->toThrow(\LogicException::class, 'Only an active administrator');
    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending);
});

it('prevents duplicate pending requests and duplicate payment references', function () {
    $plan = testPlan();
    $user = testUser($plan);
    $otherUser = testUser($plan);
    $service = app(OfflinePaymentService::class);
    $reference = 'DUPLICATE-'.Str::random(12);

    $service->submit($user, $plan, [
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => $reference,
        'receipt_path' => 'payment-receipts/test-'.Str::random(12).'.pdf',
    ]);

    $differentPlan = testPlan(['name' => 'Different Pending Plan']);

    expect(fn () => $service->submit($user, $differentPlan, [
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'OTHER-'.Str::random(12),
        'receipt_path' => 'payment-receipts/test-'.Str::random(12).'.pdf',
    ]))->toThrow(\LogicException::class);

    expect(fn () => $service->submit($otherUser, $plan, [
        'bank_name' => 'Test Bank',
        'account_holder' => $otherUser->name,
        'payment_reference' => $reference,
        'receipt_path' => 'payment-receipts/test-'.Str::random(12).'.pdf',
    ]))->toThrow(\LogicException::class);
});

it('re-checks the locked current account and plan before accepting a payment', function () {
    $service = app(OfflinePaymentService::class);
    $plan = testPlan(['name' => 'Stale Active Plan', 'price' => 35]);
    $user = testUser($plan);

    Plan::query()->whereKey($plan->id)->update(['is_active' => false]);

    expect(fn () => $service->submit($user, $plan, [
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'STALE-PLAN-'.Str::random(12),
        'receipt_path' => 'payment-receipts/test-'.Str::random(12).'.pdf',
    ]))->toThrow(LogicException::class, 'Only active paid plans');

    Plan::query()->whereKey($plan->id)->update(['is_active' => true]);
    User::query()->whereKey($user->id)->update(['is_active' => false]);

    expect(fn () => $service->submit($user, $plan, [
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'STALE-USER-'.Str::random(12),
        'receipt_path' => 'payment-receipts/test-'.Str::random(12).'.pdf',
    ]))->toThrow(LogicException::class, 'Inactive accounts');

    expect(BillingHistory::query()->count())->toBe(0);
});

it('renders the user billing page with database-backed payment history', function () {
    $plan = testPlan(['name' => 'Billing Page Plan', 'price' => 29]);
    $user = testUser($plan);

    BillingHistory::query()->create([
        'invoice_number' => (string) Str::uuid(),
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'submitted_at' => now(),
        'total' => $plan->price,
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'PAGE-'.Str::random(12),
        'reference_fingerprint' => hash('sha256', Str::random(30)),
        'receipt_path' => 'payment-receipts/page-test.pdf',
        'status' => PaymentStatus::Pending,
        'pending_key' => (string) $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('user.checkout'))
        ->assertOk()
        ->assertSee('Plan and Billing')
        ->assertSee('Billing History');
});

it('renders the administrator payment review page', function () {
    $plan = testPlan(['name' => 'Admin Payment Page Plan', 'price' => 49]);
    $user = testUser($plan);
    $admin = testAdmin();

    BillingHistory::query()->create([
        'invoice_number' => (string) Str::uuid(),
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'submitted_at' => now(),
        'total' => $plan->price,
        'bank_name' => 'Test Bank',
        'account_holder' => $user->name,
        'payment_reference' => 'ADMIN-PAGE-'.Str::random(12),
        'reference_fingerprint' => hash('sha256', Str::random(30)),
        'receipt_path' => 'payment-receipts/admin-page-test.pdf',
        'status' => PaymentStatus::Pending,
        'pending_key' => (string) $user->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.payments.index'))
        ->assertOk()
        ->assertSee('Payment Reviews')
        ->assertSee($user->name);
});
