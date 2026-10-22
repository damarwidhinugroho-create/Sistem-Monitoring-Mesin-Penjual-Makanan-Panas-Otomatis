<?php

namespace App\Services;

use App\Contracts\DashboardRepositoryInterface;
use App\Contracts\InventoryRepositoryInterface;
use App\Enums\MachineStatus;
use App\ViewModels\DashboardViewModel;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepositoryInterface $repository,
        private readonly InventoryRepositoryInterface $inventoryRepository,
        private readonly StockService $stockService,
    ) {}

    public function dashboard(): DashboardViewModel
    {
        $inventorySlots = $this->inventoryRepository->slots();
        $rows = array_map(function (array $slot): array {
            return [
                'product' => $slot['product'],
                'slot' => $slot['slot'],
                'duration' => $slot['duration'],
                'remaining' => '—',
                'status' => null,
                'stock' => $slot['stock'],
                'capacity' => $slot['capacity'],
                'stock_status' => $this->stockService->statusFor(
                    $slot['stock'],
                    $slot['capacity'],
                    $slot['minimum_stock'] ?? null,
                ),
                'stock_percent' => $this->stockService->percentage($slot['stock'], $slot['capacity']),
            ];
        }, $inventorySlots);

        $summary = $this->repository->summary();
        $summary['stock'] = array_sum(array_column($inventorySlots, 'stock')).' Porsi';
        $summary['active_slots'] = 'dari '.count($inventorySlots).' slot aktif';

        return new DashboardViewModel(
            $summary,
            $rows,
            $this->repository->temperatureChart(),
            $this->repository->salesChart(),
            MachineStatus::Online,
        );
    }
}
