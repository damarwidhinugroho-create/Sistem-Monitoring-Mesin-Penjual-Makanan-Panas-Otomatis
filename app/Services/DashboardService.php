<?php

namespace App\Services;

use App\Contracts\DashboardRepositoryInterface;
use App\Enums\MachineStatus;
use App\ViewModels\DashboardViewModel;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepositoryInterface $repository,
        private readonly ShelfLifeService $shelfLifeService,
        private readonly StockService $stockService,
    ) {}

    public function dashboard(): DashboardViewModel
    {
        $rows = array_map(function (array $row): array {
            return [
                ...$row,
                'status' => $this->shelfLifeService->statusFor($row['remaining_minutes']),
                'stock_status' => $this->stockService->statusFor($row['stock'], $row['capacity']),
                'stock_percent' => $this->stockService->percentage($row['stock'], $row['capacity']),
            ];
        }, $this->repository->shelfLifeRows());

        return new DashboardViewModel(
            $this->repository->summary(),
            $rows,
            $this->repository->temperatureChart(),
            $this->repository->salesChart(),
            MachineStatus::Online,
        );
    }
}
