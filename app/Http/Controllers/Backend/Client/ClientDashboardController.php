<?php

namespace App\Http\Controllers\Backend\Client;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\ClientDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientDashboardController extends Controller
{
    public function __invoke(Request $request, ClientDashboardService $dashboardService): View
    {
        return view('client.index', $dashboardService->metrics($request->user()));
    }
}
