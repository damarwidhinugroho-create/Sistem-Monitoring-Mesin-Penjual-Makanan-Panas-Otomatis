<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $service): View
    {
        return view('pages.monitoring-dashboard', ['dashboard' => $service->dashboard()]);
    }
}
