@extends('admin.dashboard')
@section('title','Payment Reviews')
@section('admin')
<div class="nk-content-inner"><div class="nk-content-body">
    <div class="nk-block-head nk-page-head"><h1 class="display-6">Payment Reviews</h1><p class="text-light">Plans activate only after an explicit approval.</p></div>
    <div class="card"><div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>User</th><th>Plan</th><th>Payment</th><th>Status</th><th>Submitted</th><th>Review</th></tr></thead>
        <tbody>
        @forelse($allData as $item)
            @php
                $statusValue = (string) $item->getRawOriginal('status');
                $statusClass = match($statusValue) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'warning',
                };
                $receiptPath = (string) $item->getRawOriginal('receipt_path');
            @endphp
            <tr>
                <td>{{ $item->user?->name ?? 'Deleted user' }}<div class="small">{{ $item->user?->email ?? 'Unavailable' }}</div></td>
                <td>{{ $item->plan?->name ?? 'Unavailable' }}<div class="small">${{ number_format((float)$item->total,2) }}</div></td>
                <td>{{ $item->bank_name }}<div class="small">{{ $item->account_holder }} · {{ $item->payment_reference }}</div>@if($receiptPath !== '')<a href="{{ route('admin.payments.receipt',$item) }}" class="btn btn-xs btn-secondary mt-1">Private Receipt</a>@endif</td>
                <td><span class="badge bg-{{ $statusClass }}">{{ ucfirst($statusValue ?: 'pending') }}</span>@if($item->reviewer)<div class="small">by {{ $item->reviewer->name }}</div>@endif @if($item->rejection_reason)<div class="small text-danger">{{ $item->rejection_reason }}</div>@endif</td>
                <td>{{ $item->submitted_at?->format('M j, Y H:i') ?? 'Unavailable' }}</td>
                <td>
                    @if($statusValue === 'pending')
                        <form method="POST" action="{{ route('admin.payments.review',$item) }}" class="mb-2" data-confirm="Approve this payment and activate the selected plan?">@csrf @method('PATCH')<input type="hidden" name="decision" value="approve"><button class="btn btn-sm btn-success" type="submit">Approve</button></form>
                        <form method="POST" action="{{ route('admin.payments.review',$item) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="reject"><label class="visually-hidden" for="reason-{{ $item->id }}">Rejection reason</label><textarea id="reason-{{ $item->id }}" name="rejection_reason" class="form-control form-control-sm mb-1" maxlength="2000" placeholder="Required rejection reason" required></textarea><button class="btn btn-sm btn-danger" type="submit">Reject</button></form>
                    @else
                        <span class="small text-light">Reviewed {{ $item->reviewed_at?->diffForHumans() ?? 'previously' }}</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6">No payment requests found.</td></tr>
        @endforelse
        </tbody>
    </table></div><div class="card-footer">{{ $allData->links() }}</div></div>
</div></div>
@endsection
