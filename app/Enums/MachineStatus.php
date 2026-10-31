<?php

namespace App\Enums;

enum MachineStatus: string
{
    case Online = 'online';
    case Offline = 'offline';

    public static function fromApiStatus(?string $status): self
    {
        return is_string($status) && strtoupper(trim($status)) === 'ONLINE'
            ? self::Online
            : self::Offline;
    }

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online',
            self::Offline => 'Offline',
        };
    }
}
