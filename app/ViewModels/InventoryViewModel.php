<?php

namespace App\ViewModels;

readonly class InventoryViewModel
{
    public function __construct(
        public array $summary,
        public array $slots,
        public array $shelfLifeRows,
        public array $restockHistory,
    ) {}
}
