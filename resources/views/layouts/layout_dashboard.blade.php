<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Operator')</title>
    <script>
        try {
            const savedOperatorTheme = localStorage.getItem('operator-theme');
            if (savedOperatorTheme === 'dark') {
                document.documentElement.dataset.theme = 'dark';
            }
        } catch (_) {}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/gaya-umum.css', 'resources/js/app.js'])
    @unless (request()->routeIs('dashboard', 'reports'))
        @vite('resources/js/pageMotion.js')
    @endunless
    @stack('styles')
</head>
<body>
    <div class="initial-page-loader" data-initial-page-loader role="status" aria-live="polite">
        <span class="initial-page-loader__mark" aria-hidden="true"></span>
        <span>Memuat SAJI...</span>
    </div>
    <div class="operator-layout">
        <button class="sidebar-backdrop" type="button" data-sidebar-close aria-label="Tutup navigasi" tabindex="-1"></button>
        <x-sidebar :machine-status="$machineStatus" />
        <main class="operator-main">
            <header class="operator-header">
                <div class="operator-header__copy">
                    <button class="sidebar-toggle" type="button" data-sidebar-open aria-controls="operator-sidebar" aria-expanded="false" aria-label="Buka navigasi">
                        <span></span><span></span><span></span>
                    </button>
                    <div>
                        <p class="operator-header__breadcrumb">OPERASIONAL / @yield('breadcrumb', 'DASHBOARD')</p>
                        <h1 class="page-title">@yield('page-heading', 'Panel Operator')</h1>
                        @hasSection('page-subtitle')
                            <p class="page-subtitle">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>
                <div class="operator-header__tools">
                    <span class="operator-header__live"><i aria-hidden="true"></i> Online</span>
                    <a class="operator-header__notification" href="{{ route('alerts') }}" aria-label="Lihat History Alert">
                        <x-ikon name="bell" />
                    </a>
                    <span class="operator-header__user">
                        <span class="operator-header__avatar">B</span>
                        <span>Budi</span>
                    </span>
                </div>
            </header>
            <section class="operator-content">
                <div class="page-loading" data-page-loading hidden aria-hidden="true">
                    <div class="page-loading__main" aria-hidden="true">
                        <header class="page-loading__heading">
                            <span class="page-loading__placeholder page-loading__title"></span>
                            <span class="page-loading__placeholder page-loading__toolbar"></span>
                        </header>
                        <section class="page-loading__stats">
                            <span class="page-loading__placeholder page-loading__stat"></span>
                            <span class="page-loading__placeholder page-loading__stat"></span>
                            <span class="page-loading__placeholder page-loading__stat"></span>
                            <span class="page-loading__placeholder page-loading__stat"></span>
                        </section>
                        <section class="page-loading__content">
                            <span class="page-loading__placeholder page-loading__panel"></span>
                            <span class="page-loading__placeholder page-loading__panel"></span>
                            <span class="page-loading__placeholder page-loading__panel"></span>
                            <span class="page-loading__placeholder page-loading__panel"></span>
                        </section>
                    </div>
                    <span class="visually-hidden" role="status" aria-live="polite">Memuat halaman berikutnya</span>
                </div>
                @yield('content')
            </section>
            <footer class="operator-footer">
                <span>SAJI · MONITORING SISTEM VENDING MACHINE MAKANAN PANAS</span>
                <span>Panel Operator</span>
            </footer>
        </main>
    </div>
</body>
</html>
