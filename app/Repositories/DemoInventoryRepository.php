<?php

namespace App\Repositories;

use App\Contracts\InventoryRepositoryInterface;
use App\Services\VendingInventoryData;
use Carbon\Carbon;
use Illuminate\Contracts\Session\Session;

class DemoInventoryRepository implements InventoryRepositoryInterface
{
    public function __construct(
        private readonly Session $session,
        private readonly VendingInventoryData $vendingInventoryData,
    ) {}

    public function summary(): array
    {
        $slots = $this->slots();
        $totalStock = array_sum(array_column($slots, 'stock'));
        $lowStock = count(array_filter(
            $slots,
            fn (array $slot): bool => $slot['stock'] > 0 && $slot['stock'] <= $slot['minimum_stock'],
        ));
        $outOfStock = count(array_filter($slots, fn (array $slot): bool => $slot['stock'] === 0));

        return [
            ['label' => 'Total Stok', 'value' => $totalStock],
            ['label' => 'Stok Rendah', 'value' => $lowStock],
            ['label' => 'Produk Habis', 'value' => $outOfStock],
            ['label' => 'Perlu Perhatian (Masa Simpan)', 'value' => $lowStock + $outOfStock],
        ];
    }

    public function slots(): array
    {
        $rows = $this->vendingInventoryData->rows();
        $slots = array_map(function (array $row, int $index): array {
            $number = $index + 1;
            $price = (int) ($row['product']['price'] ?? 0);
            $restockedAt = $row['last_restocked_at'] ?? null;

            return [
                'slot' => 'A'.$number,
                'slot_number' => 'Slot '.$number,
                'product' => $row['product']['name'] ?? 'Produk Slot '.$number,
                'duration' => '—',
                'stock' => (int) ($row['quantity'] ?? 0),
                'capacity' => (int) ($row['maximum_stock'] ?? 0),
                'minimum_stock' => (int) ($row['minimum_stock'] ?? 0),
                'filled_at' => $restockedAt ? Carbon::parse($restockedAt)->format('d M Y H.i') : '—',
                'price' => 'Rp '.number_format($price, 0, ',', '.'),
                'price_value' => $price,
            ];
        }, $rows, array_keys($rows));

        $updates = $this->session->get('operator.slot_updates', []);

        return array_map(
            fn (array $slot): array => [...$slot, ...($updates[$slot['slot']] ?? [])],
            $slots,
        );
    }

    public function shelfLifeRows(): array
    {
        return array_map(
            fn (array $slot): array => [
                'product' => $slot['product'],
                'temperature' => '61°C',
                'stock' => $slot['stock'],
                'capacity' => $slot['capacity'],
                'minimum_stock' => $slot['minimum_stock'],
                'duration' => '—',
                'remaining' => '—',
                'slot' => $slot['slot'],
            ],
            $this->slots(),
        );
    }

    public function restockHistory(): array
    {
        return [
            ['date' => '02/10/2026 09:00', 'slot' => 'A1', 'product' => 'Mie Tarempa', 'quantity' => '+6'],
            ['date' => '01/10/2026 10:00', 'slot' => 'A1', 'product' => 'Mie Tarempa', 'quantity' => '+4'],
            ['date' => '01/10/2026 08:00', 'slot' => 'A2', 'product' => 'Luti Gendang', 'quantity' => '+10'],
            ['date' => '30/10/2026 13:00', 'slot' => 'A3', 'product' => 'Mie Sagu', 'quantity' => '+4'],
            ['date' => '30/10/2026 14:00', 'slot' => 'A1', 'product' => 'Mie Tarempa', 'quantity' => '+5'],
        ];
    }

    public function updateSlot(string $slot, array $attributes): void
    {
        $updates = $this->session->get('operator.slot_updates', []);
        $updates[$slot] = [
            'product' => $attributes['product'],
            'stock' => (int) $attributes['stock'],
            'filled_at' => $attributes['filled_at'],
            'price' => 'Rp '.number_format((int) $attributes['price'], 0, ',', '.'),
            'price_value' => (int) $attributes['price'],
        ];

        $this->session->put('operator.slot_updates', $updates);
    }
}
