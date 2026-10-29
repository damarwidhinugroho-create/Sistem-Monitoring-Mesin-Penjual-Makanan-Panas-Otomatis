<?php

namespace App\Http\Controllers;

use App\Enums\MachineStatus;
use Illuminate\Contracts\View\View;

class ReportController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.laporan', ['machineStatus' => MachineStatus::Online]);
    }
}
