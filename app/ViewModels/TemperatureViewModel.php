<?php

namespace App\ViewModels;

use App\Enums\MachineStatus;

readonly class TemperatureViewModel
{
    public function __construct(
        public array $readings,
        public MachineStatus $machineStatus,
    ) {}
}
