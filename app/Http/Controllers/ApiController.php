<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class ApiController extends Controller
{
    public const INVENTORY_URL = 'https://vending-machines-rust.vercel.app/api/v1/inventory?machine_id=3';

    public const MACHINE_ID = 3;

    /**
     * @throws ConnectionException
     */
    public function getInventory(): array
    {
        $payload = Http::acceptJson()
            ->timeout(10)
            ->get(self::INVENTORY_URL)
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

        return collect($payload['data'])
            ->filter(fn (array $row): bool => (int) ($row['machine_id'] ?? 0) === self::MACHINE_ID)
            ->unique(fn (array $row): ?string => $row['product']['name'] ?? null)
            ->take(4)
            ->values()
            ->all();
    }
}
