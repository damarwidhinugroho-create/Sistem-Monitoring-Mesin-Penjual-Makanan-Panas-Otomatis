<?php

namespace App\Repositories;

use App\Contracts\MachineRepositoryInterface;

class DemoMachineRepository implements MachineRepositoryInterface
{
    public function details(): array
    {
        return [
            'machine_id' => 'VM - 001',
            'location' => 'Sonoan sikit',
            'wifi' => 'Terhubung',
            'last_online' => '18:45:21',
            'last_update' => '18:45:21',
            'updated_at' => '15 Sep 2026, 18:45 WIB',
            'temperature' => '62°C',
            'cabinet_temperature' => 'Suhu Cabinet Sekarang Aman +60C',
            'slots' => '3 slot aktif',
            'payment' => 'Qris',
            'machine_type' => 'Makanan panas',
            'current_problem' => 'Masalah Saat Ini Slot A3 - Lewat 46 menit',
            'maintenance_date' => '25 Sep 2026',
            'technician' => 'Teknisi A',
            'next_maintenance' => '2 Okt 2026',
            'alerts' => [
                ['time' => '10:48', 'message' => 'Stok Rendah - Luti Gendang', 'status' => 'Normal', 'tone' => 'warning'],
                ['time' => '08:52', 'message' => 'Temp < 60C', 'status' => 'Normal', 'tone' => 'critical'],
                ['time' => '27 Sep, 06:15', 'message' => 'Mesin Offline', 'status' => 'Normal', 'tone' => 'critical'],
            ],
        ];
    }
}
