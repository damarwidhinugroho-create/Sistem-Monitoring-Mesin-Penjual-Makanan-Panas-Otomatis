<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ChartCard extends Component
{
    public function __construct(
        public string $title,
        public string $chartId,
        public string $type,
        public array $labels,
        public array $values,
        public array $secondaryValues = [],
        public array $controls = [],
        public ?string $activeControl = null,
        public array $alternateLabels = [],
        public array $alternateValues = [],
    ) {}

    public function render(): View
    {
        return view('components.kartu-grafik');
    }
}
