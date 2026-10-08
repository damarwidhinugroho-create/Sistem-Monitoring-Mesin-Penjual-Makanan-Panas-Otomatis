<?php

namespace App\Http\Controllers;

use App\Services\AlertService;
use Illuminate\Contracts\View\View;

class AlertController extends Controller
{
    public function __invoke(AlertService $service): View
    {
        return view('pages.riwayat-alert', ['alerts' => $service->history()]);
    }
}
