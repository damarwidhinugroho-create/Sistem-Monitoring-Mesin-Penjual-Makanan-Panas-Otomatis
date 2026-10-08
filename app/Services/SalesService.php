<?php

namespace App\Services;

use App\Contracts\SalesRepositoryInterface;
use App\Enums\SalesPeriod;
use App\ViewModels\SalesViewModel;

class SalesService
{
    public function __construct(private readonly SalesRepositoryInterface $repository) {}

    public function report(SalesPeriod $period): SalesViewModel
    {
        return new SalesViewModel(
            $period,
            $this->repository->reportFor($period),
            $this->repository->transactions(),
        );
    }
}
