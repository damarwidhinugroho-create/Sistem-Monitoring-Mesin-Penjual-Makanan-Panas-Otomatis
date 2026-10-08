<?php

namespace App\Repositories;

use App\Contracts\TemperatureRepositoryInterface;

class DemoTemperatureRepository implements TemperatureRepositoryInterface
{
    public function readings(): array
    {
        return [
            'title' => 'Monitoring Suhu',
            'temperature' => 62,
            'status' => 'Online',
            'updated_at' => '19:02 WIB',
            'safe_label' => 'Suhu aman >60C',
            'alert' => 'VM-02 - Marina tidak mengirim data selama 5 mnt',
            'labels' => ['08:00', '09:00', '10:00', '11:00', '12:00'],
            'values' => [66, 58, 61, 64, 56],
            'thresholds' => [60, 60, 60, 60, 60],
        ];
    }
}
