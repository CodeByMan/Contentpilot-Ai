<?php

namespace App\Http\Controllers\Backend\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\SubmitOfflinePaymentRequest;
use App\Models\Plan;
use App\Services\Billing\OfflinePaymentService;
use App\Services\Storage\SecureUploadService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use LogicException;
use Throwable;

class CheckoutController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->loadMissing('plan');
        $userPlan = $user->plan;
        $allPlans = Plan::query()->where('is_active', true)->where('price', '>', 0)->orderBy('price')->get();
        $billingHistory = $user->billingHistories()->with('plan')->latest()->paginate(10);
        $paymentNotifications = $user->notifications()
            ->where('data->type', 'payment_reviewed')
            ->latest()
            ->limit(10)
            ->get();

        return view('client.backend.checkout.user_checkout', compact(
            'allPlans',
            'userPlan',
            'billingHistory',
            'paymentNotifications',
        ));
    }

    public function store(
        SubmitOfflinePaymentRequest $request,
        OfflinePaymentService $payments,
        SecureUploadService $uploads,
    ): RedirectResponse {
        $plan = Plan::query()->where('is_active', true)->findOrFail($request->integer('plan_id'));
        $receiptPath = $request->hasFile('receipt')
            ? $uploads->storePrivateReceipt($request->file('receipt'))
            : null;

        try {
            $payments->submit($request->user(), $plan, [
                ...$request->safe()->only(['bank_name', 'account_holder', 'payment_reference']),
                'receipt_path' => $receiptPath,
            ]);
        } catch (Throwable $exception) {
            $uploads->delete($receiptPath, 'private');

            if ($exception instanceof LogicException) {
                return back()->withErrors(['plan_id' => $exception->getMessage()])->withInput();
            }

            throw $exception;
        }

        return redirect()->route('user.payment.success')->with([
            'message' => 'Your payment was submitted for administrator review.',
            'alert-type' => 'success',
        ]);
    }

    public function success(): View
    {
        return view('client.backend.checkout.payment_success');
    }

    public function markNotificationAsRead(Request $request, string $notification): RedirectResponse
    {
        $notification = $request->user()->notifications()
            ->where('data->type', 'payment_reviewed')
            ->findOrFail($notification);
        $notification->markAsRead();

        return back();
    }

    public function invoice(Request $request, int $billing): Response
    {
        $billing = $request->user()->billingHistories()->with(['user', 'plan'])->findOrFail($billing);
        $this->authorize('view', $billing);

        return Pdf::loadView('client.backend.checkout.invoice', compact('billing'))
            ->download('invoice-'.$billing->invoice_number.'.pdf');
    }
}
