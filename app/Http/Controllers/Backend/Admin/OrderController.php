<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\billingHistory;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function AllOrder()
    {
        $allData = billingHistory::with(['user', 'plan'])->latest()->get();

        return view('admin.backend.order.all_order', compact('allData'));
    }

    public function UpdateOrderStatus($id): RedirectResponse
    {
        billingHistory::findOrFail($id)->update(['status' => 'Paid']);

        return redirect()->back()->with([
            'message' => 'Status updated successfully.',
            'alert-type' => 'success',
        ]);
    }
}
