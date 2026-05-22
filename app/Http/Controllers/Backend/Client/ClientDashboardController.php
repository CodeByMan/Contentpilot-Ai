<?php

namespace App\Http\Controllers\Backend\Client;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\ClientDashboardService;
use Illuminate\Support\Facades\Auth;

class ClientDashboardController extends Controller
{
    public function __invoke(ClientDashboardService $dashboardService)
    {
        return view('client.index', $dashboardService->metrics(Auth::user()));
    }
}
