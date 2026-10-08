<?php

namespace App\Enums;

enum ShelfLifeStatus: string
{
    case Aman = 'aman';
    case MendekatiBatas = 'mendekati-batas';
    case LewatBatas = 'lewat-batas';

    public function label(): string
    {
        return match ($this) {
            self::Aman => 'Aman',
            self::MendekatiBatas => 'Mendekati Batas',
            self::LewatBatas => 'Lewat Batas',
        };
    }
}
