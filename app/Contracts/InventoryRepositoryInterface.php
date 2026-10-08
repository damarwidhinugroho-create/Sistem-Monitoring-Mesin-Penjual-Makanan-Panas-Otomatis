<?php

namespace App\Contracts;

interface InventoryRepositoryInterface
{
    public function summary(): array;

    public function slots(): array;

    public function shelfLifeRows(): array;

    public function restockHistory(): array;

    public function updateSlot(string $slot, array $attributes): void;
}
