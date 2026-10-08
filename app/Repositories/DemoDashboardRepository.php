<?php

namespace App\Repositories;

use App\Contracts\DashboardRepositoryInterface;

class DemoDashboardRepository implements DashboardRepositoryInterface
{
    public function summary(): array
    {
        return [
            'sales' => 'Rp 1.250.000',
            'stock' => '10 Porsi',
            'active_slots' => 'dari 3 slot aktif',
            'temperature' => '62°C',
            'shelf_life_count' => '2 Produk',
            'shelf_life_detail' => '1 Aman',
            'shelf_life_warning' => '1 Mendekati Batas · 1 Lewat Batas',
        ];
    }

    public function shelfLifeRows(): array
    {
        return [
            ['product' => 'Mie Tarempa', 'slot' => 'A1', 'duration' => '2j 44m', 'remaining' => '2j 16m', 'remaining_minutes' => 136, 'stock' => 8, 'capacity' => 10],
            ['product' => 'Luti Gendang', 'slot' => 'A2', 'duration' => '4j 33m', 'remaining' => '26 menit', 'remaining_minutes' => 26, 'stock' => 2, 'capacity' => 10],
            ['product' => 'Mie Sagu', 'slot' => 'A3', 'duration' => '5j 46m', 'remaining' => 'Lewat 46 menit', 'remaining_minutes' => -46, 'stock' => 0, 'capacity' => 10],
        ];
    }

    public function temperatureChart(): array
    {
        return [
            'title' => 'Data suhu VM - 00 Jam terakhir',
            'labels' => ['08:00', '09:00', '10:00', '11:00', '12:00'],
            'values' => [64, 59, 61, 62, 58],
        ];
    }

    public function salesChart(): array
    {
        return [
            'title' => 'Data penjualan',
            'labels' => ['2j', '4j', '6j', '8j', '10j'],
            'values' => [8, 3, 6, 8, 4, 6, 9, 5],
            'daily_values' => [8, 6, 10, 8, 5],
        ];
    }
}
