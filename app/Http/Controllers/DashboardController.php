<?php

namespace App\Http\Controllers;

use App\Enums\MachineStatus;
use App\Enums\StockStatus;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(ApiController $api): View
    {
        $apiRows = $api->getInventory();
        $updates = session('operator.slot_updates', []);

        $shelfLifeRows = array_map(function (array $row, int $index) use ($updates): array {
            $slot = 'A'.($index + 1);
            $stock = (int) ($updates[$slot]['stock'] ?? $row['quantity'] ?? 0);
            $capacity = (int) ($row['maximum_stock'] ?? 0);
            $minimumStock = (int) ($row['minimum_stock'] ?? 0);
            $status = $stock <= 0
                ? StockStatus::Habis
                : ($stock <= $minimumStock ? StockStatus::Rendah : StockStatus::Aman);

            return [
                'product' => $updates[$slot]['product'] ?? $row['product']['name'] ?? 'Produk Slot '.($index + 1),
                'slot' => $slot,
                'duration' => '—',
                'remaining' => '—',
                'status' => null,
                'stock' => $stock,
                'capacity' => $capacity,
                'stock_status' => $status,
                'stock_percent' => $capacity > 0 ? (int) round(($stock / $capacity) * 100) : 0,
            ];
        }, $apiRows, array_keys($apiRows));

        $totalStock = array_sum(array_column($shelfLifeRows, 'stock'));

        return view('pages.monitoring_dashboard', [
            'machineStatus' => MachineStatus::Online,
            'dashboard' => [
                'summary' => [
                    'sales' => 'Rp 1.250.000',
                    'stock' => $totalStock.' Porsi',
                    'active_slots' => 'dari '.count($shelfLifeRows).' slot aktif',
                    'temperature' => '62°C',
                    'shelf_life_count' => '2 Produk',
                    'shelf_life_detail' => '1 Aman',
                    'shelf_life_warning' => '1 Mendekati Batas · 1 Lewat Batas',
                ],
                'shelfLifeRows' => $shelfLifeRows,
                'temperatureChart' => [
                    'title' => 'Data suhu VM - 00 Jam terakhir',
                    'labels' => ['08:00', '09:00', '10:00', '11:00', '12:00'],
                    'values' => [64, 59, 61, 62, 58],
                ],
                'salesChart' => [
                    'title' => 'Data penjualan',
                    'labels' => ['2j', '4j', '6j', '8j', '10j'],
                    'values' => [8, 3, 6, 8, 4, 6, 9, 5],
                    'daily_values' => [8, 6, 10, 8, 5],
                ],
            ],
        ]);
    }
}
