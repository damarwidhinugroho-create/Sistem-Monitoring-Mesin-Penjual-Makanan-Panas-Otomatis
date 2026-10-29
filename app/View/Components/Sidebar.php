<?php

namespace App\View\Components;

use App\Enums\MachineStatus;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Sidebar extends Component
{
    public function __construct(public MachineStatus $machineStatus) {}

    public function render(): View
    {
        return view('components.sidebar');
    }
}
