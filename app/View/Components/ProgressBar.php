<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProgressBar extends Component
{
    public function __construct(
        public int $percentage,
        public string $status = 'aman',
        public ?string $label = null,
    ) {
        $this->percentage = max(0, min(100, $percentage));
    }

    public function tone(): string
    {
        return match ($this->status) {
            'mendekati-batas', 'rendah', 'warning' => 'warning',
            'lewat-batas', 'habis', 'critical' => 'critical',
            default => 'safe',
        };
    }

    public function render(): View
    {
        return view('components.batang-progres');
    }
}
