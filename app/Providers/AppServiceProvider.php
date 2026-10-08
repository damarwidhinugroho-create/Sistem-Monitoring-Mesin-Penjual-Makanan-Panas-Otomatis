<?php

namespace App\Providers;

use App\Auth\DemoOperatorProvider;
use App\Contracts\AlertRepositoryInterface;
use App\Contracts\DashboardRepositoryInterface;
use App\Contracts\InventoryRepositoryInterface;
use App\Contracts\MachineRepositoryInterface;
use App\Contracts\SalesRepositoryInterface;
use App\Contracts\TemperatureRepositoryInterface;
use App\Repositories\DemoAlertRepository;
use App\Repositories\DemoDashboardRepository;
use App\Repositories\DemoInventoryRepository;
use App\Repositories\DemoMachineRepository;
use App\Repositories\DemoSalesRepository;
use App\Repositories\DemoTemperatureRepository;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DashboardRepositoryInterface::class, DemoDashboardRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, DemoInventoryRepository::class);
        $this->app->bind(SalesRepositoryInterface::class, DemoSalesRepository::class);
        $this->app->bind(MachineRepositoryInterface::class, DemoMachineRepository::class);
        $this->app->bind(TemperatureRepositoryInterface::class, DemoTemperatureRepository::class);
        $this->app->bind(AlertRepositoryInterface::class, DemoAlertRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('demo-operator', fn ($app, array $config) => new DemoOperatorProvider());
        Blade::component(\App\View\Components\AlertBanner::class, 'banner-peringatan');
        Blade::component(\App\View\Components\Badge::class, 'lencana-status');
        Blade::component(\App\View\Components\ChartCard::class, 'kartu-grafik');
        Blade::component(\App\View\Components\DataTable::class, 'tabel-data');
        Blade::component(\App\View\Components\Icon::class, 'ikon');
        Blade::component(\App\View\Components\Navbar::class, 'bilah-navigasi');
        Blade::component(\App\View\Components\ProgressBar::class, 'batang-progres');
        Blade::component(\App\View\Components\Sidebar::class, 'menu-samping');
        Blade::component(\App\View\Components\StatCard::class, 'kartu-ringkasan');
    }
}
