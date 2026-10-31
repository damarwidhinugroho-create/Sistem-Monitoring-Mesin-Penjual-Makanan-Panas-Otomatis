<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AlertController extends Controller
{
    public function __invoke(ApiController $api): View
    {
        $alerts = [
            [
                'id' => 'ALT-001',
                'type' => 'Temperature Warning',
                'source' => 'Cabinet Temperature',
                'message' => 'Suhu Turun ke 50',
                'occurredAt' => '2026-09-27T03:41:00.000Z',
                'resolvedAt' => '2026-09-27T04:05:00.000Z',
                'priority' => 'Critical',
                'status' => 'Resolved',
            ],
            [
                'id' => 'ALT-002',
                'type' => 'Low Stock',
                'source' => 'Slot A2 - Mie Tarempa',
                'message' => 'Mie Tarempa Sisa 2 porsi',
                'occurredAt' => '2026-09-27T02:20:00.000Z',
                'resolvedAt' => null,
                'priority' => 'Warning',
                'status' => 'Unresolved',
            ],
            [
                'id' => 'ALT-003',
                'type' => 'Food Holding Time',
                'source' => 'Slot A1 - Mie Sagu',
                'message' => 'Produk Lewat Batas Simpan',
                'occurredAt' => '2026-09-28T05:20:00.000Z',
                'resolvedAt' => '2026-09-28T05:40:00.000Z',
                'priority' => 'Warning',
                'status' => 'Resolved',
            ],
            [
                'id' => 'ALT-004',
                'type' => 'Machine Offline',
                'source' => 'Vending Machine',
                'message' => 'VM tidak mengirim data',
                'occurredAt' => '2026-09-29T03:20:00.000Z',
                'resolvedAt' => '2026-09-29T03:30:00.000Z',
                'priority' => 'Critical',
                'status' => 'Resolved',
            ],
            [
                'id' => 'ALT-005',
                'type' => 'Temperature Warning',
                'source' => 'Cabinet Temperature',
                'message' => 'Suhu Turun ke 57',
                'occurredAt' => '2026-09-29T04:20:00.000Z',
                'resolvedAt' => '2026-09-29T04:30:00.000Z',
                'priority' => 'Critical',
                'status' => 'Resolved',
            ],
        ];
        $alertKinds = [
            ['type' => 'Temperature Warning', 'source' => 'Cabinet Temperature', 'message' => 'Suhu di bawah batas normal', 'priority' => 'Critical'],
            ['type' => 'Low Stock', 'source' => 'Slot A2 - Mie Tarempa', 'message' => 'Mie Tarempa tersisa 2 porsi', 'priority' => 'Warning'],
            ['type' => 'Food Holding Time', 'source' => 'Slot A1 - Mie Sagu', 'message' => 'Produk melewati batas waktu simpan', 'priority' => 'Warning'],
            ['type' => 'Machine Offline', 'source' => 'Vending Machine', 'message' => 'VM tidak mengirim data', 'priority' => 'Critical'],
            ['type' => 'Temperature Warning', 'source' => 'Cabinet Temperature', 'message' => 'Suhu kabinet perlu diperiksa', 'priority' => 'Critical'],
        ];

        for ($index = 0; $index < 45; $index++) {
            $occurredAt = now()
                ->startOfDay()
                ->subDays(($index * 9) % 395)
                ->setTime(8 + ($index % 9), ($index * 7) % 60);
            $isUnresolved = $index % 5 === 1;
            $alert = $alertKinds[$index % count($alertKinds)];
            $alert['id'] = 'ALT-'.str_pad((string) ($index + 6), 3, '0', STR_PAD_LEFT);
            $alert['occurredAt'] = $occurredAt->toISOString();
            $alert['resolvedAt'] = $isUnresolved
                ? null
                : $occurredAt->copy()->addMinutes(10 + ($index % 35))->toISOString();
            $alert['status'] = $isUnresolved ? 'Unresolved' : 'Resolved';
            $alerts[] = $alert;
        }

        return view('pages.riwayat_alert', [
            'alerts' => $alerts,
            'machineStatus' => $api->getMachineStatus(),
        ]);
    }
}
