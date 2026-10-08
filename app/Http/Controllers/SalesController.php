<?php

namespace App\Http\Controllers;

use App\Enums\SalesPeriod;
use App\Http\Requests\SalesPeriodRequest;
use App\Services\SalesService;
use Illuminate\Contracts\View\View;

class SalesController extends Controller
{
    public function __invoke(SalesPeriodRequest $request, SalesService $service): View
    {
        $period = SalesPeriod::from($request->validated('period'));

        return view('pages.monitoring-penjualan', ['sales' => $service->report($period)]);
    }
}
