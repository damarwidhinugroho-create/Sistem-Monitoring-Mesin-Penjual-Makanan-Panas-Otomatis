<?php

namespace App\Http\Controllers;

use App\Services\MachineService;
use Illuminate\Contracts\View\View;

class MachineController extends Controller
{
    public function __invoke(MachineService $service): View
    {
        return view('pages.detail_mesin', ['machine' => $service->details()]);
    }
}
