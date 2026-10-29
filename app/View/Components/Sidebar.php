<?php

namespace App\View\Components;

use App\Enums\MachineStatus;
use App\Services\MachineService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Sidebar extends Component
{
    public MachineStatus $machineStatus;

    public function __construct(MachineService $machineService)
    {
        $this->machineStatus = $machineService->details()->status;
    }

    public function render(): View
    {
        return view('components.sidebar');
    }
}
