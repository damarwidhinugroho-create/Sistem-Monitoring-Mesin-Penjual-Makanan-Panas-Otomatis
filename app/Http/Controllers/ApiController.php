<?php

namespace App\Http\Controllers;

use App\Enums\MachineStatus;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use UnexpectedValueException;

class ApiController extends Controller
{
    public const MACHINE_ID = 3;

    /**
     * @throws ConnectionException
     */
    public function getInventory(): array
    {
        return $this->normalizeInventory($this->fetchRows());
    }

    /**
     * @throws ConnectionException
     *
     * @return array{inventory: array, machineStatus: MachineStatus}
     */
    public function getMachineData(): array
    {
        $rows = $this->fetchRows();

        return [
            'inventory' => $this->normalizeInventory($rows),
            'machineStatus' => $this->machineStatusFromRows($rows),
        ];
    }

    /**
     * @throws ConnectionException
     */
    public function getMachineStatus(): MachineStatus
    {
        return $this->machineStatusFromRows($this->fetchRows());
    }

    /**
     * @throws ConnectionException
     */
    private function fetchRows(): array
    {
        $apiUrl = config('app.api_vending_url');

        if (! is_string($apiUrl) || trim($apiUrl) === '') {
            throw new RuntimeException('API_VENDING_URL belum diatur pada file .env.');
        }

        $cacheKey = 'vending.inventory.'.hash('sha256', $apiUrl);
        $staleCacheKey = $cacheKey.'.last-known-good';

        try {
            return Cache::flexible($cacheKey, [15, 300], function () use ($apiUrl, $staleCacheKey): array {
                $payload = Http::acceptJson()
                    ->timeout(20)
                    ->get($apiUrl)
                    ->throw()
                    ->json();

                if (! is_array($payload) || ! is_array($payload['data'] ?? null)) {
                    throw new UnexpectedValueException('Respons API inventory harus memiliki properti data berbentuk array.');
                }

                foreach ($payload['data'] as $row) {
                    if (! is_array($row)) {
                        throw new UnexpectedValueException('Setiap item inventory dari API harus berbentuk object JSON.');
                    }
                }

                Cache::put($staleCacheKey, $payload['data'], now()->addMinutes(15));

                return $payload['data'];
            });
        } catch (ConnectionException|RequestException $exception) {
            $lastKnownRows = Cache::get($staleCacheKey);

            if (is_array($lastKnownRows)) {
                return $lastKnownRows;
            }

            throw $exception;
        }
    }

    private function normalizeInventory(array $rows): array
    {
        return collect($rows)
            ->filter(fn (array $row): bool => $this->isMachineOnline($row))
            ->map(fn (array $row): array => array_merge($row, [
                'slot_code' => $row['slot']['slot_code'] ?? $row['slot_code'] ?? null,
                'maximum_stock' => $row['slot']['capacity'] ?? $row['maximum_stock'] ?? 0,
                'quantity' => $row['available_qty'] ?? $row['current_qty'] ?? $row['quantity'] ?? 0,
                'minimum_stock' => $row['minimum_stock'] ?? 0,
            ]))
            ->values()
            ->all();
    }

    private function machineStatusFromRows(array $rows): MachineStatus
    {
        $machineRows = array_filter($rows, fn (array $row): bool => $this->belongsToMachine($row));

        if ($machineRows === []) {
            return MachineStatus::Offline;
        }

        foreach ($machineRows as $row) {
            if (! $this->isMachineOnline($row)) {
                return MachineStatus::Offline;
            }
        }

        return MachineStatus::Online;
    }

    private function isMachineOnline(array $row): bool
    {
        if (! $this->belongsToMachine($row)) {
            return false;
        }

        $machine = is_array($row['machine'] ?? null) ? $row['machine'] : [];
        $status = $machine['status'] ?? $row['machine_status'] ?? $row['status'] ?? null;

        return MachineStatus::fromApiStatus(is_string($status) ? $status : null) === MachineStatus::Online;
    }

    private function belongsToMachine(array $row): bool
    {
        $machine = is_array($row['machine'] ?? null) ? $row['machine'] : [];
        $machineId = $machine['id'] ?? $row['machine_id'] ?? 0;

        return (int) $machineId === self::MACHINE_ID;
    }
}
