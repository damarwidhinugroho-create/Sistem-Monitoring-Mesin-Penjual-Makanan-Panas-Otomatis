<?php

namespace App\Services;

use App\Enums\ShelfLifeStatus;

class ShelfLifeService
{
    public function statusFor(int $remainingMinutes): ShelfLifeStatus
    {
        if ($remainingMinutes < 0) {
            return ShelfLifeStatus::LewatBatas;
        }

        if ($remainingMinutes <= 30) {
            return ShelfLifeStatus::MendekatiBatas;
        }

        return ShelfLifeStatus::Aman;
    }
}
