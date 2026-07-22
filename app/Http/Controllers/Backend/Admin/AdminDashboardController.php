<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\AdminDashboardService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(AdminDashboardService $dashboardService): View
    {
        return view('admin.index', $dashboardService->metrics());
    }
}
