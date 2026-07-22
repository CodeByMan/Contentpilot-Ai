<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $allData = Plan::query()->withCount(['users', 'billingHistories'])->latest()->paginate(20);

        return view('admin.backend.plan.all_plan', compact('allData'));
    }

    public function create(): View
    {
        return view('admin.backend.plan.add_plan');
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        Plan::query()->create($request->validated());

        return redirect()->route('admin.plans.index')->with([
            'message' => 'Plan created successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function edit(Plan $plan): View
    {
        return view('admin.backend.plan.edit_plan', ['plans' => $plan]);
    }

    public function update(StorePlanRequest $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($plan, $validated): void {
            User::query()
                ->where('plan_id', $plan->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);

            $lockedPlan = Plan::query()->lockForUpdate()->findOrFail($plan->id);
            $lockedPlan->update($validated);
            $lockedPlan->users()->update(['word_limit' => $lockedPlan->monthly_word_limit]);
        }, 3);

        return redirect()->route('admin.plans.index')->with([
            'message' => 'Plan updated successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->users()->exists() || $plan->billingHistories()->exists()) {
            return back()->withErrors(['plan' => 'Plans with users or billing records cannot be deleted. Deactivate the plan instead.']);
        }

        try {
            $plan->delete();
        } catch (QueryException) {
            return back()->withErrors(['plan' => 'This plan is referenced by existing records and cannot be deleted.']);
        }

        return back()->with([
            'message' => 'Plan deleted successfully.',
            'alert-type' => 'success',
        ]);
    }
}
