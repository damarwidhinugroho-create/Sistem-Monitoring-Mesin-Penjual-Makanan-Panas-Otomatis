<?php

namespace App\Enums;

enum StockStatus: string
{
    case Aman = 'aman';
    case Rendah = 'rendah';
    case Habis = 'habis';

    public static function fromStock(int $stock, int $minimumStock = 3): self
    {
        if ($stock <= 0) {
            return self::Habis;
        }

        return $stock <= max(3, $minimumStock) ? self::Rendah : self::Aman;
    }

    public function label(): string
    {
        return match ($this) {
            self::Aman => 'Aman',
            self::Rendah => 'Rendah',
            self::Habis => 'Habis',
        };
    }
}
