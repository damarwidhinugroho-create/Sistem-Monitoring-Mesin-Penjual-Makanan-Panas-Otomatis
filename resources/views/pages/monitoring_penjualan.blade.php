@extends('layouts.layout_dashboard')

@section('title', 'Monitoring Penjualan')

@section('content')
    <div class="sales__heading">
        <h1 class="page-title">Monitoring Penjualan</h1>
        <nav class="sales__periods" aria-label="Periode penjualan">
            @foreach (\App\Enums\SalesPeriod::cases() as $period)
                <a class="chart-card__control {{ $sales['period'] === $period ? 'chart-card__control--active' : '' }}" href="{{ route('sales', ['period' => $period->value]) }}">{{ $period->label() }}</a>
            @endforeach
        </nav>
    </div>

    <section class="stat-grid sales__metrics" aria-label="Statistik penjualan">
        <x-kartu-ringkasan :label="$sales['report']['transactions_label']" :value="$sales['report']['transactions']" icon-name="receipt" />
        <x-kartu-ringkasan :label="$sales['report']['revenue_label']" :value="$sales['report']['revenue']" icon-name="money" />
        <x-kartu-ringkasan
            :label="$sales['report']['waste_label']"
            :value="$sales['report']['waste']"
            icon-name="food"
            :tag="$sales['period'] === \App\Enums\SalesPeriod::Minggu ? 'Hari Ini' : '05 Agu 2025'"
            tag-status="safe"
        />
    </section>

    <section class="sales__chart">
        <x-kartu-grafik
            title="Tren Omzet Penjualan"
            chart-id="sales-revenue-chart"
            type="bar"
            :labels="$sales['period']->axisLabels()"
            :values="$sales['report']['bars']"
        />
    </section>

    <section class="surface sales__products" data-paginated-list data-item-label="produk">
        <h2 class="section-heading sales__table-heading">Performa Produk</h2>
        <x-tabel-data :headers="['PRODUK', 'TERJUAL', 'TERBUANG', 'TERBUANG(%)']">
            @foreach ($sales['report']['product_performance'] as $product)
                <tr data-pagination-row>
                    <td>{{ $product['product'] }}</td>
                    <td>{{ $product['sold'] }}</td>
                    <td>{{ $product['wasted'] }}</td>
                    <td>{{ $product['waste_rate'] }}</td>
                </tr>
            @endforeach
        </x-tabel-data>
        <div class="sales__list-footer" data-pagination-controls hidden>
            <p class="sales__list-count" data-pagination-count aria-live="polite"></p>
            <div class="sales__list-actions">
                <button class="button" type="button" data-pagination-view-all>Lihat semua</button>
                <nav class="pagination sales__list-pagination" aria-label="Halaman performa produk" data-pagination-nav></nav>
            </div>
        </div>
    </section>

    <section class="surface sales__transactions" data-paginated-list data-item-label="transaksi">
        <h2 class="section-heading sales__table-heading">Riwayat Transaksi</h2>
        <x-tabel-data :headers="[]">
            @foreach ($sales['transactions'] as $transaction)
                <tr data-pagination-row>
                    <td>{{ $transaction['time'] }}</td>
                    <td>{{ $transaction['product'] }}</td>
                    <td class="data-table__right">{{ $transaction['amount'] }}</td>
                </tr>
            @endforeach
        </x-tabel-data>
        <div class="sales__list-footer" data-pagination-controls hidden>
            <p class="sales__list-count" data-pagination-count aria-live="polite"></p>
            <div class="sales__list-actions">
                <button class="button" type="button" data-pagination-view-all>Lihat semua</button>
                <nav class="pagination sales__list-pagination" aria-label="Halaman riwayat transaksi" data-pagination-nav></nav>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    @vite('resources/css/monitoring_penjualan.css')
@endpush
