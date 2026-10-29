<aside class="sidebar">
    <div class="sidebar__brand">
        <x-ikon name="logo" size="30" class="sidebar__logo" />
        <span>Operator Panel</span>
        <button
            class="sidebar__theme-toggle"
            type="button"
            data-theme-toggle
            aria-label="Aktifkan mode gelap"
            aria-pressed="false"
            title="Aktifkan mode gelap"
        >
            <span class="sidebar__theme-switch" aria-hidden="true"></span>
            <span class="visually-hidden">Mode gelap</span>
        </button>
    </div>
    <div class="sidebar__vm">
        <div class="sidebar__vm-card sidebar__vm-card--{{ $machineStatus->value }}">
            <span class="sidebar__vm-label">Status VM</span>
            <span class="sidebar__vm-value">
                <span class="sidebar__vm-dot" aria-hidden="true"></span>
                {{ $machineStatus->label() }}
            </span>
        </div>
    </div>
    <nav class="sidebar__navigation" aria-label="Navigasi operator">
        <a class="sidebar__item {{ request()->routeIs('dashboard') ? 'sidebar__item--active' : '' }}" href="{{ route('dashboard') }}">
            <x-ikon name="dashboard" /><span>Dashboard</span>
        </a>
        <a class="sidebar__item {{ request()->routeIs('alerts') ? 'sidebar__item--active' : '' }}" href="{{ route('alerts') }}">
            <x-ikon name="bell" /><span>History Alert</span>
        </a>
        <a class="sidebar__item {{ request()->routeIs('inventory') ? 'sidebar__item--active' : '' }}" href="{{ route('inventory') }}">
            <x-ikon name="box" /><span>Inventory/Stok</span>
        </a>
        <a class="sidebar__item {{ request()->routeIs('sales') ? 'sidebar__item--active' : '' }}" href="{{ route('sales') }}">
            <x-ikon name="cart" /><span>Penjualan</span>
        </a>
        <a class="sidebar__item {{ request()->routeIs('temperature') ? 'sidebar__item--active' : '' }}" href="{{ route('temperature') }}">
            <x-ikon name="thermometer" /><span>Suhu</span>
        </a>
        <a class="sidebar__item {{ request()->routeIs('machine') ? 'sidebar__item--active' : '' }}" href="{{ route('machine') }}">
            <x-ikon name="machine" /><span>Detail Mesin</span>
        </a>
        <a class="sidebar__item {{ request()->routeIs('reports') ? 'sidebar__item--active' : '' }}" href="{{ route('reports') }}">
            <x-ikon name="report" />
            <span>{{ request()->routeIs('inventory', 'sales') ? 'Laporan Operasional' : 'Laporan Penjualan' }}</span>
        </a>
    </nav>
    <div class="sidebar__profile">
        <span class="sidebar__avatar">B</span>
        <span class="sidebar__identity"><span>Budi</span><small>Operator</small></span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="sidebar__logout" type="submit" aria-label="Logout"><x-ikon name="logout" /></button>
        </form>
    </div>
</aside>
