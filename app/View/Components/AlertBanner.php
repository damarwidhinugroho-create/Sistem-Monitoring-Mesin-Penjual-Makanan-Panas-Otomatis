<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AlertBanner extends Component
{
    public function __construct(
        public string $message,
        public ?string $linkLabel = null,
        public ?string $linkRoute = null,
    ) {}

    public function render(): View
    {
        return view('components.banner-peringatan');
    }
}
