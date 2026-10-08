<?php

namespace App\Repositories;

use App\Contracts\AlertRepositoryInterface;

class DemoAlertRepository implements AlertRepositoryInterface
{
    public function recent(): array
    {
        return [
            ['time' => '10:48', 'message' => 'Stok Rendah - Luti Gendang', 'tone' => 'warning'],
            ['time' => '08:52', 'message' => 'Temp < 60C', 'tone' => 'critical'],
            ['time' => '27 Sep, 06:15', 'message' => 'Mesin Offline', 'tone' => 'critical'],
        ];
    }
}
