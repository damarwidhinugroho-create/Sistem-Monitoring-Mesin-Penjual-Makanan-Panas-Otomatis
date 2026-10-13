<aside class="sidebar">
    <div class="sidebar__brand">
        <span class="sidebar__logo" style="mask-image: url('{{ asset('images/logo.svg') }}'); -webkit-mask-image: url('{{ asset('images/logo.svg') }}')" aria-hidden="true"></span>
        <span>Operator Panel</span>
    </div>
    <nav class="sidebar__navigation" aria-label="Navigasi operator">
        <a class="sidebar__item {{ request()->routeIs('dashboard') ? 'sidebar__item--active' : '' }}" href="{{ route('dashboard') }}">
            <x-ikon :white="request()->routeIs('dashboard')" /><span>Dashboard</span>
        </a>
        <a class="sidebar__item {{ request()->routeIs('alerts') ? 'sidebar__item--active' : '' }}" href="{{ route('alerts') }}">
            <x-ikon :white="request()->routeIs('alerts')" /><span>History Alert</span>
        </a>
        <a class="sidebar__item {{ request()->routeIs('inventory') ? 'sidebar__item--active' : '' }}" href="{{ route('inventory') }}">
            <x-ikon :white="request()->routeIs('inventory')" /><span>Inventory/Stok</span>
        </a>
        <a class="sidebar__item {{ request()->routeIs('sales') ? 'sidebar__item--active' : '' }}" href="{{ route('sales') }}">
            <x-ikon :white="request()->routeIs('sales')" /><span>Penjualan</span>
        </a>
        <a class="sidebar__item {{ request()->routeIs('temperature') ? 'sidebar__item--active' : '' }}" href="{{ route('temperature') }}">
            <x-ikon :white="request()->routeIs('temperature')" /><span>Suhu</span>
        </a>
        <a class="sidebar__item {{ request()->routeIs('machine') ? 'sidebar__item--active' : '' }}" href="{{ route('machine') }}">
            <x-ikon :white="request()->routeIs('machine')" /><span>Detail Mesin</span>
        </a>
        <a class="sidebar__item {{ request()->routeIs('reports') ? 'sidebar__item--active' : '' }}" href="{{ route('reports') }}">
            <x-ikon :white="request()->routeIs('reports')" />
            <span>{{ request()->routeIs('inventory', 'sales') ? 'Laporan Operasional' : 'Laporan Penjualan' }}</span>
        </a>
    </nav>
    <div class="sidebar__profile">
        <span class="sidebar__avatar">B</span>
        <span class="sidebar__identity"><span>Budi</span><small>Operator</small></span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="sidebar__logout" type="submit" aria-label="Logout"><x-ikon /></button>
        </form>
    </div>
</aside>
