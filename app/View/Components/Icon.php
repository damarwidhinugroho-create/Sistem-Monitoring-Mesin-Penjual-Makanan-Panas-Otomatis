<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Icon extends Component
{
    public function __construct(
        public int $size = 16,
        public bool $active = false,
        public bool $large = false,
        public bool $white = false,
    ) {}

    public function render(): View
    {
        return view('components.ikon');
    }
}
