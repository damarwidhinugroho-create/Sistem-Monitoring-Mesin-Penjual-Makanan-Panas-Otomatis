<?php

namespace App\Http\Controllers;

use App\Enums\MachineStatus;
use App\Enums\StockStatus;
use App\Http\Requests\UpdateSlotRequest;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class InventoryController extends Controller
{
    public function index(ApiController $api): View
    {
        $apiRows = $api->getInventory();
        $updates = session('operator.slot_updates', []);

        $slots = array_map(function (array $row, int $index) use ($updates): array {
            $number = $index + 1;
            $slot = $row['slot_code'] ?? 'A'.$number;
            $price = (int) ($row['product']['price'] ?? 0);
            $restockedAt = $row['last_restocked_at'] ?? null;
            $stock = (int) ($updates[$slot]['stock'] ?? $row['quantity'] ?? 0);
            $capacity = (int) ($row['maximum_stock'] ?? 0);
            $minimumStock = (int) ($row['minimum_stock'] ?? 0);
            $status = $this->stockStatus($stock, $minimumStock);

            return [
                'slot' => $slot,
                'slot_number' => 'Slot '.$slot,
                'product' => $updates[$slot]['product'] ?? $row['product']['name'] ?? 'Produk Slot '.$number,
                'duration' => '—',
                'stock' => $stock,
                'capacity' => $capacity,
                'minimum_stock' => $minimumStock,
                'filled_at' => $updates[$slot]['filled_at'] ?? ($restockedAt ? Carbon::parse($restockedAt)->format('d M Y H.i') : '—'),
                'price' => $updates[$slot]['price'] ?? 'Rp '.number_format($price, 0, ',', '.'),
                'price_value' => (int) ($updates[$slot]['price_value'] ?? $price),
                'status' => $status,
                'stock_percent' => $this->stockPercentage($stock, $capacity),
            ];
        }, $apiRows, array_keys($apiRows));

        $totalStock = array_sum(array_column($slots, 'stock'));
        $lowStock = count(array_filter($slots, fn (array $slot): bool => $slot['status'] === StockStatus::Rendah));
        $outOfStock = count(array_filter($slots, fn (array $slot): bool => $slot['status'] === StockStatus::Habis));
        $shelfLifeRows = array_map(fn (array $slot): array => [
            'product' => $slot['product'],
            'temperature' => '61°C',
            'stock' => $slot['stock'],
            'capacity' => $slot['capacity'],
            'minimum_stock' => $slot['minimum_stock'],
            'duration' => $slot['duration'],
            'remaining' => '—',
            'status' => null,
            'stock_status' => $slot['status'],
            'stock_percent' => $slot['stock_percent'],
        ], $slots);

        return view('pages.inventory_stok', [
            'machineStatus' => MachineStatus::Online,
            'inventory' => [
                'summary' => [
                    ['label' => 'Total Stok', 'value' => $totalStock],
                    ['label' => 'Stok Rendah', 'value' => $lowStock],
                    ['label' => 'Produk Habis', 'value' => $outOfStock],
                    ['label' => 'Perlu Perhatian (Masa Simpan)', 'value' => $lowStock + $outOfStock],
                ],
                'slots' => $slots,
                'shelfLifeRows' => $shelfLifeRows,
                'restockHistory' => $apiRows === [] ? [] : [
                    ['date' => '02/10/2026 09:00', 'slot' => 'A1', 'product' => 'Mie Tarempa', 'quantity' => '+6'],
                    ['date' => '01/10/2026 10:00', 'slot' => 'A1', 'product' => 'Mie Tarempa', 'quantity' => '+4'],
                    ['date' => '01/10/2026 08:00', 'slot' => 'A2', 'product' => 'Luti Gendang', 'quantity' => '+10'],
                    ['date' => '30/10/2026 13:00', 'slot' => 'A3', 'product' => 'Mie Sagu', 'quantity' => '+4'],
                    ['date' => '30/10/2026 14:00', 'slot' => 'A1', 'product' => 'Mie Tarempa', 'quantity' => '+5'],
                ],
            ],
        ]);
    }

    public function update(UpdateSlotRequest $request, string $slot): RedirectResponse
    {
        $attributes = $request->validated();
        $updates = session('operator.slot_updates', []);
        $price = (int) $attributes['price'];
        $updates[$slot] = [
            'product' => $attributes['product'],
            'stock' => (int) $attributes['stock'],
            'filled_at' => $attributes['filled_at'],
            'price' => 'Rp '.number_format($price, 0, ',', '.'),
            'price_value' => $price,
        ];

        session(['operator.slot_updates' => $updates]);

        return redirect()->route('inventory');
    }

    private function stockStatus(int $stock, int $minimumStock): StockStatus
    {
        if ($stock <= 0) {
            return StockStatus::Habis;
        }

        if ($stock <= $minimumStock) {
            return StockStatus::Rendah;
        }

        return StockStatus::Aman;
    }

    private function stockPercentage(int $stock, int $capacity): int
    {
        return $capacity > 0 ? (int) round(($stock / $capacity) * 100) : 0;
    }
}
