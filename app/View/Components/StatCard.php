<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatCard extends Component
{
    public function __construct(
        public string $label,
        public string $value,
        public ?string $detail = null,
        public ?string $tag = null,
        public bool $icon = true,
        public string $tagStatus = 'warning',
    ) {}

    public function render(): View
    {
        return view('components.kartu-ringkasan');
    }
}
