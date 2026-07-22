@extends('client.client_dashboard')

@section('title', 'Plan and Billing')

@section('client')
<div class="nk-content-inner">
    <div class="nk-content-body">
        <div class="nk-block-head nk-page-head">
            <h1 class="display-6">Plan and Billing</h1>
            <p class="text-light">Offline payment requests remain pending until an administrator approves them.</p>
        </div>

        @if ($paymentNotifications->isNotEmpty())
            <section class="card mb-4" aria-labelledby="payment-notifications-title">
                <div class="card-header"><h2 id="payment-notifications-title" class="h5 mb-0">Payment Updates</h2></div>
                <div class="list-group list-group-flush">
                    @foreach ($paymentNotifications as $notification)
                        <div class="list-group-item d-flex justify-content-between align-items-start gap-3 {{ $notification->read_at ? '' : 'bg-light' }}">
                            <div>
                                <p class="mb-1">{{ data_get($notification->data, 'message', 'Your payment request was reviewed.') }}</p>
                                <small class="text-muted">{{ $notification->created_at?->diffForHumans() ?? 'Recently' }}</small>
                            </div>
                            @unless ($notification->read_at)
                                <form method="POST" action="{{ route('user.billing.notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Mark as read</button>
                                </form>
                            @endunless
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="row g-gs">
            <div class="col-xl-5">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5">Current Plan</h2>
                        <p class="h3">{{ $userPlan?->name ?? 'No active plan' }}</p>

                        @if ($userPlan)
                            <ul class="list">
                                <li>{{ number_format($userPlan->monthly_word_limit) }} words</li>
                                <li>{{ $userPlan->templates }} templates</li>
                                <li>{{ $userPlan->image_generation_limit }} image generations</li>
                                <li>{{ $userPlan->audio_generation_limit }} audio generations</li>
                            </ul>
                        @endif

                        <hr>

                        <h2 class="h5">Submit Offline Payment</h2>
                        @if($allPlans->isEmpty())
                            <div class="alert alert-info mb-0">No active paid plans are currently available.</div>
                        @else
                            <form action="{{ route('user.checkout.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mb-3">
                                    <label for="plan_id" class="form-label">Requested plan</label>
                                    <select id="plan_id" name="plan_id" class="form-select @error('plan_id') is-invalid @enderror" required>
                                        <option value="">Select a paid plan</option>
                                        @foreach ($allPlans as $plan)
                                            <option value="{{ $plan->id }}" @selected((int) old('plan_id') === $plan->id)>{{ $plan->name }} — ${{ number_format((float) $plan->price, 2) }}</option>
                                        @endforeach
                                    </select>
                                    @error('plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group mb-3"><label for="bank_name" class="form-label">Bank or payment service</label><input id="bank_name" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name') }}" maxlength="120" required>@error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="form-group mb-3"><label for="account_holder" class="form-label">Account holder</label><input id="account_holder" name="account_holder" class="form-control @error('account_holder') is-invalid @enderror" value="{{ old('account_holder') }}" maxlength="120" required>@error('account_holder')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="form-group mb-3"><label for="payment_reference" class="form-label">Payment reference</label><input id="payment_reference" name="payment_reference" class="form-control @error('payment_reference') is-invalid @enderror" value="{{ old('payment_reference') }}" maxlength="120" required autocomplete="off">@error('payment_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="form-group mb-3"><label for="receipt" class="form-label">Payment receipt</label><input id="receipt" type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror" accept="image/jpeg,image/png,image/webp,application/pdf" required><div class="form-note">JPEG, PNG, WebP, or PDF up to 5 MB. The receipt is stored privately.</div>@error('receipt')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="alert alert-info">Submitting this form does not activate the plan. An administrator must review and approve the payment.</div>
                                <button class="btn btn-primary" type="submit">Submit for Review</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card">
                    <div class="card-header"><h2 class="h5 mb-0">Billing History</h2></div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Invoice</th><th>Plan</th><th>Amount</th><th>Status</th><th>Submitted</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
                            <tbody>
                                @forelse ($billingHistory as $item)
                                    @php
                                        $statusValue = (string) $item->getRawOriginal('status');
                                        $statusClass = match($statusValue) {
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            default => 'warning',
                                        };
                                    @endphp
                                    <tr>
                                        <td><code>{{ $item->invoice_number }}</code></td>
                                        <td>{{ $item->plan?->name ?? 'Unavailable' }}</td>
                                        <td>${{ number_format((float) $item->total, 2) }}</td>
                                        <td><span class="badge bg-{{ $statusClass }}">{{ ucfirst($statusValue ?: 'pending') }}</span>@if ($item->rejection_reason)<div class="small text-danger mt-1">{{ $item->rejection_reason }}</div>@endif</td>
                                        <td>{{ $item->submitted_at?->format('M j, Y H:i') ?? 'Unavailable' }}</td>
                                        <td>@if($statusValue === 'approved')<a href="{{ route('user.invoices.download', $item) }}" class="btn btn-sm btn-secondary">Download invoice</a>@else<span class="small text-light">Available after approval</span>@endif</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6">No billing records found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">{{ $billingHistory->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
