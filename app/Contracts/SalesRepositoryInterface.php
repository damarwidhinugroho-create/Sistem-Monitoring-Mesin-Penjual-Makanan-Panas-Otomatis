<?php

namespace App\Contracts;

use App\Enums\SalesPeriod;

interface SalesRepositoryInterface
{
    public function reportFor(SalesPeriod $period): array;

    public function transactions(): array;
}
