@extends('layouts.tata-letak-panel')

@section('title', 'Monitoring Penjualan')

@section('content')
    <div class="sales__heading">
        <h1 class="page-title">Monitoring Penjualan</h1>
        <nav class="sales__periods" aria-label="Periode penjualan">
            @foreach (\App\Enums\SalesPeriod::cases() as $period)
                <a class="chart-card__control {{ $sales->period === $period ? 'chart-card__control--active' : '' }}" href="{{ route('sales', ['period' => $period->value]) }}">{{ $period->label() }}</a>
            @endforeach
        </nav>
    </div>

    <section class="stat-grid sales__metrics" aria-label="Statistik penjualan">
        <x-kartu-ringkasan :label="$sales->report['transactions_label']" :value="$sales->report['transactions']" />
        <x-kartu-ringkasan :label="$sales->report['revenue_label']" :value="$sales->report['revenue']" />
        <x-kartu-ringkasan
            :label="$sales->report['waste_label']"
            :value="$sales->report['waste']"
            :tag="$sales->period === \App\Enums\SalesPeriod::Minggu ? 'Hari Ini' : '05 Agu 2025'"
            tag-status="safe"
        />
    </section>

    <section class="sales__chart">
        <x-kartu-grafik
            title="Tren Omzet Penjualan"
            chart-id="sales-revenue-chart"
            type="bar"
            :labels="$sales->period->axisLabels()"
            :values="$sales->report['bars']"
        />
    </section>

    <section class="surface sales__products">
        <h2 class="section-heading sales__table-heading">Performa Produk</h2>
        <x-tabel-data :headers="['PRODUK', 'TERJUAL', 'TERBUANG', 'TERBUANG(%)']">
            @foreach ($sales->report['product_performance'] as $product)
                <tr>
                    <td>{{ $product['product'] }}</td>
                    <td>{{ $product['sold'] }}</td>
                    <td>{{ $product['wasted'] }}</td>
                    <td>{{ $product['waste_rate'] }}</td>
                </tr>
            @endforeach
        </x-tabel-data>
    </section>

    <section class="surface sales__transactions">
        <h2 class="section-heading sales__table-heading">Riwayat Transaksi</h2>
        <x-tabel-data :headers="[]">
            @foreach ($sales->transactions as $transaction)
                <tr>
                    <td>{{ $transaction['time'] }}</td>
                    <td>{{ $transaction['product'] }}</td>
                    <td class="data-table__right">{{ $transaction['amount'] }}</td>
                </tr>
            @endforeach
        </x-tabel-data>
        <button class="sales__load-more" type="button">Muat Lebih Banyak <span aria-hidden="true">↓</span></button>
    </section>
@endsection

@push('styles')
    @vite('resources/css/monitoring-penjualan.css')
@endpush
