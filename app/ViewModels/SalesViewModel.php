<?php

namespace App\ViewModels;

use App\Enums\SalesPeriod;

readonly class SalesViewModel
{
    public function __construct(
        public SalesPeriod $period,
        public array $report,
        public array $transactions,
    ) {}
}
