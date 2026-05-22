<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\AdminDashboardService;

class AdminDashboardController extends Controller
{
    public function __invoke(AdminDashboardService $dashboardService)
    {
        return view('admin.index', $dashboardService->metrics());
    }
}
