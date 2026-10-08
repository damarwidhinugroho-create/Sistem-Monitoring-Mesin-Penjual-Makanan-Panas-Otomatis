<?php

namespace App\Services;

use App\Contracts\InventoryRepositoryInterface;
use App\ViewModels\InventoryViewModel;

class InventoryService
{
    public function __construct(
        private readonly InventoryRepositoryInterface $repository,
        private readonly ShelfLifeService $shelfLifeService,
        private readonly StockService $stockService,
    ) {}

    public function inventory(): InventoryViewModel
    {
        $slots = array_map(function (array $slot): array {
            $status = $this->stockService->statusFor($slot['stock'], $slot['capacity']);

            return [
                ...$slot,
                'status' => $status,
                'stock_percent' => $this->stockService->percentage($slot['stock'], $slot['capacity']),
            ];
        }, $this->repository->slots());

        $shelfLife = array_map(function (array $row): array {
            return [
                ...$row,
                'status' => $this->shelfLifeService->statusFor($row['remaining_minutes']),
                'stock_status' => $this->stockService->statusFor($row['stock'], $row['capacity']),
                'stock_percent' => $this->stockService->percentage($row['stock'], $row['capacity']),
            ];
        }, $this->repository->shelfLifeRows());

        return new InventoryViewModel(
            $this->repository->summary(),
            $slots,
            $shelfLife,
            $this->repository->restockHistory(),
        );
    }

    public function updateSlot(string $slot, array $attributes): void
    {
        $this->repository->updateSlot($slot, $attributes);
    }
}
