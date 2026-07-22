<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewOfflinePaymentRequest;
use App\Models\BillingHistory;
use App\Services\Billing\OfflinePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use LogicException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(): View
    {
        $this->authorize('review-payments');
        $allData = BillingHistory::query()
            ->with(['user', 'plan', 'reviewer'])
            ->latest('submitted_at')
            ->paginate(20);

        return view('admin.backend.order.all_order', compact('allData'));
    }

    public function review(
        ReviewOfflinePaymentRequest $request,
        BillingHistory $payment,
        OfflinePaymentService $payments,
    ): RedirectResponse {
        $this->authorize('review-payments');

        try {
            if ($request->validated('decision') === 'approve') {
                $payments->approve($payment, $request->user());
                $message = 'Payment approved and the plan was activated.';
            } else {
                $payments->reject($payment, $request->user(), $request->validated('rejection_reason'));
                $message = 'Payment rejected. The user plan was not changed.';
            }
        } catch (LogicException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return back()->with([
            'message' => $message,
            'alert-type' => 'success',
        ]);
    }

    public function receipt(Request $request, BillingHistory $payment): StreamedResponse
    {
        $this->authorize('review-payments');
        abort_unless($payment->receipt_path && Storage::disk('private')->exists($payment->receipt_path), 404);

        return Storage::disk('private')->download(
            $payment->receipt_path,
            'payment-receipt-'.$payment->invoice_number.'.'.pathinfo($payment->receipt_path, PATHINFO_EXTENSION),
            [
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
