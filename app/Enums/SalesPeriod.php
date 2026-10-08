<?php

namespace App\Enums;

enum SalesPeriod: string
{
    case Jam = 'jam';
    case Hari = 'hari';
    case Minggu = 'minggu';

    public function label(): string
    {
        return match ($this) {
            self::Jam => 'Per Jam',
            self::Hari => 'Per Hari',
            self::Minggu => 'Per Minggu',
        };
    }

    public function axisLabels(): array
    {
        return match ($this) {
            self::Jam => ['2j', '4j', '6j', '8j', '10j'],
            self::Hari => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
            self::Minggu => ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Minggu 5'],
        };
    }
}
