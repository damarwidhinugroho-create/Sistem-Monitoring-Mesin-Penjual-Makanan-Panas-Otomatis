<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ReportController extends Controller
{
    public function __invoke(ApiController $api): View
    {
        return view('pages.laporan', ['machineStatus' => $api->getMachineStatus()]);
    }
}
