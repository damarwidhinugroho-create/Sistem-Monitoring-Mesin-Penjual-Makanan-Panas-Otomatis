<?php

namespace App\Http\Controllers;

use App\Services\TemperatureService;
use Illuminate\Contracts\View\View;

class TemperatureController extends Controller
{
    public function __invoke(TemperatureService $service): View
    {
        return view('pages.monitoring-suhu', ['temperature' => $service->monitor()]);
    }
}
