<?php

namespace App\Http\Controllers;

use App\Enums\StockStatus;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(ApiController $api): View
    {
        $machineData = $api->getMachineData();
        $slots = array_map(function (array $row): array {
            $stock = max(0, (int) ($row['quantity'] ?? 0));
            $productName = $row['product']['name'] ?? null;

            return [
                'code' => $row['slot_code'] ?? null,
                'product' => is_string($productName) ? $productName : null,
                'stock' => $stock,
                'capacity' => max(0, (int) ($row['maximum_stock'] ?? 0)),
                'status' => $this->slotStatus($stock),
            ];
        }, $machineData['inventory']);

        $inventoryRows = array_map(function (array $row, int $index): array {
            $stock = max(0, (int) ($row['quantity'] ?? 0));
            $minimumStock = max(0, (int) ($row['minimum_stock'] ?? 0));
            $productName = $row['product']['name'] ?? null;

            return [
                'code' => $row['slot_code'] ?? 'Slot '.($index + 1),
                'product' => is_string($productName) ? $productName : 'Nama menu tidak tersedia',
                'stock' => $stock,
                'capacity' => max(0, (int) ($row['maximum_stock'] ?? 0)),
                'status' => StockStatus::fromStock($stock, $minimumStock),
            ];
        }, $machineData['inventory'], array_keys($machineData['inventory']));

        usort($inventoryRows, fn (array $left, array $right): int => $left['stock'] <=> $right['stock']);

        $totalSlots = count($slots);
        $filledSlots = count(array_filter($slots, fn (array $slot): bool => $slot['stock'] > 0));
        $emptySlots = $totalSlots - $filledSlots;
        $slotStatusCounts = array_count_values(array_column($slots, 'status'));
        $lowStockSlots = $slotStatusCounts['kritis'] ?? 0;
        $emptyStockSlots = $slotStatusCounts['habis'] ?? 0;
        $overviewStatus = $totalSlots === 0
            ? 'tidak-tersedia'
            : ($emptyStockSlots > 0 ? 'habis' : ($lowStockSlots > 0 ? 'rendah' : 'aman'));

        $todaySalesByHour = [75000, 125000, 180000, 225000, 260000, 220000, 165000];
        $weeklySales = [980000, 1120000, 1050000, 1450000, 1525000, 1800000, 1250000];
        $previousWeekSales = [870000, 980000, 1000000, 1320000, 1400000, 1620000, 1150000];
        $yesterdayRevenue = 1150000;
        $todayRevenue = array_sum($todaySalesByHour);
        $orderCount = 38;
        $temperature = 62.5;
        $temperatureHistory = [
            61.2, 62.8, 62.5, 62.1, 61.9, 62.3, 62.0, 62.1,
            61.2, 63.0, 62.7, 62.8, 63.1, 63.9, 61.5, 63.3,
            62.1, 63.0, 62.2, 63.4, 61.7, 62.2, 62.0, 64.5,
        ];

        $percentageChange = static fn (int $current, int $previous): float => $previous > 0
            ? round((($current - $previous) / $previous) * 100, 1)
            : 0.0;
        $formatPercent = static fn (float $percent): string => ($percent > 0 ? '+' : '')
            .number_format($percent, 1, ',', '.').'%';

        $todayChange = $percentageChange($todayRevenue, $yesterdayRevenue);
        $weeklyTotal = array_sum($weeklySales);
        $weeklyChange = $percentageChange($weeklyTotal, array_sum($previousWeekSales));
        $today = now('Asia/Jakarta')->locale('id');
        $currentHour = (int) $today->format('G');
        $temperatureRange = $currentHour <= 12 ? range(0, 12) : range(13, 23);
        $temperatureLabels = collect($temperatureRange)
            ->map(fn (int $hour): string => sprintf('%02d:00', $hour))
            ->all();
        $weekLabels = collect(range(6, 0))
            ->map(fn (int $daysAgo): string => $today->copy()->subDays($daysAgo)->translatedFormat('D'))
            ->all();

        return view('pages.monitoring_dashboard', [
            'machineStatus' => $machineData['machineStatus'],
            'dashboard' => [
                'rendered_at' => now('Asia/Jakarta'),
                'sales' => [
                    'today' => [
                        'revenue' => $todayRevenue,
                        'change_label' => $formatPercent($todayChange),
                        'orders' => $orderCount,
                        'average_order' => (int) round($todayRevenue / $orderCount),
                    ],
                    'temperature' => [
                        'value' => $temperature,
                        'status' => $temperature >= 60 && $temperature <= 70 ? 'Normal' : 'Peringatan',
                        'chart' => [
                            'labels' => $temperatureLabels,
                            'values' => array_map(
                                fn (int $hour): float => $temperatureHistory[$hour],
                                $temperatureRange,
                            ),
                        ],
                    ],
                    'chart' => [
                        'day' => [
                            'labels' => ['08.00', '10.00', '12.00', '14.00', '16.00', '18.00', '20.00'],
                            'values' => $todaySalesByHour,
                            'total_label' => 'Rp'.number_format($todayRevenue, 0, ',', '.'),
                            'change_label' => $formatPercent($todayChange),
                            'comparison_label' => 'Perubahan vs kemarin',
                        ],
                        'week' => [
                            'labels' => $weekLabels,
                            'values' => $weeklySales,
                            'total_label' => 'Rp'.number_format($weeklyTotal, 0, ',', '.'),
                            'change_label' => $formatPercent($weeklyChange),
                            'comparison_label' => 'Perubahan vs minggu lalu',
                        ],
                    ],
                ],
                'slots' => $slots,
                'inventory_preview' => array_slice($inventoryRows, 0, 4),
                'inventory_count' => count($inventoryRows),
                'slot_summary' => [
                    'filled' => $filledSlots,
                    'total' => $totalSlots,
                    'empty' => $emptySlots,
                    'percent' => $totalSlots > 0 ? (int) round(($filledSlots / $totalSlots) * 100) : null,
                    'total_stock' => array_sum(array_column($slots, 'stock')),
                    'product_count' => count(array_unique(array_filter(array_column($slots, 'product')))),
                    'status_counts' => [
                        'aman' => $slotStatusCounts['aman'] ?? 0,
                        'kritis' => $lowStockSlots,
                        'habis' => $emptyStockSlots,
                    ],
                    'overview_status' => $overviewStatus,
                ],
            ],
        ]);
    }

    private function slotStatus(int $stock): string
    {
        if ($stock === 0) {
            return 'habis';
        }

        if ($stock < 3) {
            return 'kritis';
        }

        // Stok tepat 3 dianggap aman; ubah ambang ini bila kebijakan operasional berubah.
        return 'aman';
    }
}
