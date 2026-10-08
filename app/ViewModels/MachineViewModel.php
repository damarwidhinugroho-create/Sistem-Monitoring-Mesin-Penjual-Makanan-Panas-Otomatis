<?php

namespace App\ViewModels;

use App\Enums\MachineStatus;

readonly class MachineViewModel
{
    public function __construct(
        public array $details,
        public MachineStatus $status,
    ) {}
}
