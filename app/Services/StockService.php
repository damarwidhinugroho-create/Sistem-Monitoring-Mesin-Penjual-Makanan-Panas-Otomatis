<?php

namespace App\Services;

use App\Enums\StockStatus;

class StockService
{
    public function statusFor(int $stock, int $capacity): StockStatus
    {
        if ($stock <= 0) {
            return StockStatus::Habis;
        }

        if ($capacity > 0 && ($stock / $capacity) <= 0.2) {
            return StockStatus::Rendah;
        }

        return StockStatus::Aman;
    }

    public function percentage(int $stock, int $capacity): int
    {
        return $capacity > 0 ? (int) round(($stock / $capacity) * 100) : 0;
    }
}
