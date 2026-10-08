<?php

namespace App\Repositories;

use App\Contracts\InventoryRepositoryInterface;
use Illuminate\Contracts\Session\Session;

class DemoInventoryRepository implements InventoryRepositoryInterface
{
    public function __construct(private readonly Session $session) {}

    public function summary(): array
    {
        return [
            ['label' => 'Total Stok', 'value' => '23'],
            ['label' => 'Stok Rendah', 'value' => '1'],
            ['label' => 'Produk Habis', 'value' => '0'],
            ['label' => 'Perlu Perhatian (Masa Simpan)', 'value' => '3'],
        ];
    }

    public function slots(): array
    {
        $slots = [
            ['slot' => 'A1', 'slot_number' => 'Slot 1', 'status' => 'aman', 'product' => 'Mie Tarempa', 'duration' => '2j 44m di mesin', 'stock' => 8, 'capacity' => 10, 'filled_at' => '10 Sep 2026 08.00', 'price' => 'Rp 30.000', 'price_value' => 30000],
            ['slot' => 'A2', 'slot_number' => 'Slot 2', 'status' => 'rendah', 'product' => 'Luti Gendang', 'duration' => '4j 33m di mesin', 'stock' => 2, 'capacity' => 10, 'filled_at' => '10 Sep 2026 08.00', 'price' => 'Rp 30.000', 'price_value' => 30000],
            ['slot' => 'A3', 'slot_number' => 'Slot 3', 'status' => 'habis', 'product' => 'Mie Sagu', 'duration' => '5j 46m di mesin', 'stock' => 0, 'capacity' => 10, 'filled_at' => '10 Sep 2026 08.00', 'price' => 'Rp 30.000', 'price_value' => 30000],
        ];

        $updates = $this->session->get('operator.slot_updates', []);

        return array_map(
            fn (array $slot): array => [...$slot, ...($updates[$slot['slot']] ?? [])],
            $slots,
        );
    }

    public function shelfLifeRows(): array
    {
        $remainingBySlot = [
            'A1' => ['duration' => '2j 44m', 'remaining' => '2j 16m', 'remaining_minutes' => 136],
            'A2' => ['duration' => '4j 33m', 'remaining' => '26 menit', 'remaining_minutes' => 26],
            'A3' => ['duration' => '5j 46m', 'remaining' => 'Lewat 46 menit', 'remaining_minutes' => -46],
        ];

        return array_map(
            fn (array $slot): array => [
                'product' => $slot['product'],
                'temperature' => '61°C',
                'stock' => $slot['stock'],
                'capacity' => $slot['capacity'],
                ...$remainingBySlot[$slot['slot']],
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
