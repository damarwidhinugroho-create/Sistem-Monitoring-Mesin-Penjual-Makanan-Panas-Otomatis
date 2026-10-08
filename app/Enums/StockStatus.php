<?php

namespace App\Enums;

enum StockStatus: string
{
    case Aman = 'aman';
    case Rendah = 'rendah';
    case Habis = 'habis';

    public function label(): string
    {
        return match ($this) {
            self::Aman => 'Aman',
            self::Rendah => 'Rendah',
            self::Habis => 'Habis',
        };
    }
}
