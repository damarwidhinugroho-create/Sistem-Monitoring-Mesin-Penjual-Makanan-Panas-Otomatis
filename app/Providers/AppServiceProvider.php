<?php

namespace App\Providers;

use App\Auth\DemoOperatorProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('demo-operator', fn ($app, array $config) => new DemoOperatorProvider());
        Blade::component(\App\View\Components\AlertBanner::class, 'alert');
        Blade::component(\App\View\Components\Badge::class, 'lencana-status');
        Blade::component(\App\View\Components\ChartCard::class, 'kartu-grafik');
        Blade::component(\App\View\Components\DataTable::class, 'tabel-data');
        Blade::component(\App\View\Components\Icon::class, 'ikon');
        Blade::component(\App\View\Components\Navbar::class, 'bilah-navigasi');
        Blade::component(\App\View\Components\ProgressBar::class, 'progress-bar');
        Blade::component(\App\View\Components\Sidebar::class, 'sidebar');
        Blade::component(\App\View\Components\StatCard::class, 'kartu-ringkasan');
    }
}
