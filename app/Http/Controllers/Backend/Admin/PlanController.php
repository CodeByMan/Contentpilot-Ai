<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function AllPlans()
    {
        $plans = Plan::latest()->get();

        return view('admin.backend.plan.all_plan', compact('plans'));
    }

    public function AddPlans()
    {
        return view('admin.backend.plan.add_plan');
    }

    public function StorePlans(StorePlanRequest $request): RedirectResponse
    {
        Plan::create($request->validated());

        return redirect()->route('all.plans')->with([
            'message' => 'Plan added successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function EditPlans($id)
    {
        $plans = Plan::findOrFail($id);

        return view('admin.backend.plan.edit_plan', compact('plans'));
    }

    public function UpdatePlans(StorePlanRequest $request): RedirectResponse
    {
        Plan::findOrFail($request->integer('id'))->update($request->validated());

        return redirect()->route('all.plans')->with([
            'message' => 'Plan updated successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function DeletePlans($id): RedirectResponse
    {
        Plan::findOrFail($id)->delete();

        return redirect()->back()->with([
            'message' => 'Plan deleted successfully.',
            'alert-type' => 'success',
        ]);
    }
}
