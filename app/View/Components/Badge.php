<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Badge extends Component
{
    public function __construct(
        public string $label,
        public string $status = 'neutral',
    ) {}

    public function tone(): string
    {
        return match ($this->status) {
            'aman', 'online' => 'safe',
            'mendekati-batas', 'rendah', 'warning' => 'warning',
            'lewat-batas', 'habis', 'offline', 'critical' => 'critical',
            default => 'neutral',
        };
    }

    public function render(): View
    {
        return view('components.lencana-status');
    }
}
