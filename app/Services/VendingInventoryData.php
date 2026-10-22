<?php

namespace App\Services;

use RuntimeException;

class VendingInventoryData
{
    private const MACHINE_ID = 1;

    public function rows(): array
    {
        $path = storage_path('app/vending.json');

        if (! is_readable($path)) {
            throw new RuntimeException('File storage/app/vending.json tidak ditemukan atau tidak dapat dibaca.');
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException('File storage/app/vending.json tidak dapat dibaca.');
        }

        $collection = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($collection) || ! is_array($collection['item'] ?? null)) {
            throw new RuntimeException('Format koleksi API di vending.json tidak valid.');
        }

        $inventoryResponse = collect($collection['item'])
            ->first(function ($item): bool {
                $url = is_array($item) ? ($item['request']['url']['raw'] ?? null) : null;

                return is_string($url) && parse_url($url, PHP_URL_PATH) === '/api/inventories';
            });

        if (! is_array($inventoryResponse)) {
            throw new RuntimeException('Respons API /api/inventories tidak ditemukan di vending.json.');
        }

        $response = collect($inventoryResponse['response'] ?? [])
            ->first(fn ($response): bool => is_array($response) && ($response['code'] ?? null) === 200);

        if (! is_array($response) || ! is_string($response['body'] ?? null)) {
            throw new RuntimeException('Contoh respons berhasil API /api/inventories tidak tersedia di vending.json.');
        }

        $payload = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($payload) || ! is_array($payload['data'] ?? null)
            || collect($payload['data'])->contains(fn ($row): bool => ! is_array($row))) {
            throw new RuntimeException('Format data inventory di vending.json tidak valid.');
        }

        $rows = collect($payload['data'])
            ->filter(fn (array $row): bool => (int) ($row['machine_id'] ?? 0) === self::MACHINE_ID)
            ->values()
            ->all();

        if ($rows === []) {
            throw new RuntimeException('Data inventory untuk vending machine 1 tidak ditemukan.');
        }

        return $rows;
    }
}
