<?php

namespace App\Contracts;

interface DashboardRepositoryInterface
{
    public function summary(): array;

    public function shelfLifeRows(): array;

    public function temperatureChart(): array;

    public function salesChart(): array;
}
