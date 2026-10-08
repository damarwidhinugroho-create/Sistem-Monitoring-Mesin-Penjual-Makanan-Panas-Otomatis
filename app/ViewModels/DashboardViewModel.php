<?php

namespace App\ViewModels;

use App\Enums\MachineStatus;

readonly class DashboardViewModel
{
    public function __construct(
        public array $summary,
        public array $shelfLifeRows,
        public array $temperatureChart,
        public array $salesChart,
        public MachineStatus $machineStatus,
    ) {}
}
