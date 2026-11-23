<aside class="sidebar" id="operator-sidebar" aria-label="Navigasi utama">
    <div class="sidebar__panel">
        <div class="sidebar__brand">
            <span class="sidebar__logo" aria-hidden="true">S</span>
            <span class="sidebar__brand-copy"><strong>Saji</strong></span>
        </div>
        <section class="sidebar__account" aria-label="Akun operator">
            <img class="sidebar__avatar" src="{{ asset('images/sidebar/operator-avatar.svg') }}" alt="">
            <span class="sidebar__identity"><strong>Admin Operasional</strong></span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="sidebar__logout" type="submit" aria-label="Logout"><x-ikon name="logout" /></button>
            </form>
        </section>
        <div class="sidebar__theme" role="radiogroup" aria-label="Tampilan">
            <div class="sidebar__theme-options">
                <button type="button" role="radio" aria-checked="true" data-theme-choice="light">Terang</button>
                <button type="button" role="radio" aria-checked="false" data-theme-choice="dark">Gelap</button>
                <span class="sidebar__theme-indicator" aria-hidden="true"></span>
            </div>
        </div>
        <nav class="sidebar__navigation" aria-label="Workspace">
            <a class="sidebar__item {{ request()->routeIs('dashboard') ? 'sidebar__item--active' : '' }}" href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                <x-ikon name="dashboard" :size="20" /><span>Dashboard</span>
            </a>
            <a class="sidebar__item {{ request()->routeIs('inventory') ? 'sidebar__item--active' : '' }}" href="{{ route('inventory') }}" @if(request()->routeIs('inventory')) aria-current="page" @endif>
                <x-ikon name="box" :size="20" /><span>Stok</span>
            </a>
            <a class="sidebar__item {{ request()->routeIs('sales') ? 'sidebar__item--active' : '' }}" href="{{ route('sales') }}" @if(request()->routeIs('sales')) aria-current="page" @endif>
                <x-ikon name="cart" :size="20" /><span>Penjualan</span>
            </a>
            <a class="sidebar__item {{ request()->routeIs('machine') ? 'sidebar__item--active' : '' }}" href="{{ route('machine') }}" @if(request()->routeIs('machine')) aria-current="page" @endif>
                <x-ikon name="machine" :size="20" /><span>Mesin</span>
            </a>
            <a class="sidebar__item {{ request()->routeIs('alerts') ? 'sidebar__item--active' : '' }}" href="{{ route('alerts') }}" @if(request()->routeIs('alerts')) aria-current="page" @endif>
                <x-ikon name="bell" :size="20" /><span>Alerts</span>
                <span class="sidebar__badge" aria-label="3 alert belum diselesaikan">3</span>
            </a>
            <a class="sidebar__item {{ request()->routeIs('reports') ? 'sidebar__item--active' : '' }}" href="{{ route('reports') }}" @if(request()->routeIs('reports')) aria-current="page" @endif>
                <x-ikon name="report" :size="20" /><span>Laporan</span>
            </a>
        </nav>
    </div>
</aside>
