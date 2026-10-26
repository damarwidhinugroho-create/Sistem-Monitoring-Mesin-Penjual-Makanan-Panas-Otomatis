@extends('layouts.layout_dashboard')

@section("title", 'Dashboard Operator')

@section('content')
    <div class="dashboard__heading">
        <div>
            <h1 class="page-title">DASHBOARD OVERVIEW</h1>
            <p class="page-subtitle">Selamat Datang Budi</p>
        </div>
        <x-banner-peringatan class="dashboard__notice" message="VM-02 - Marina tidak mengirim data selama 5 mnt" />
        <div class="machine-status">Status VM : <x-lencana-status :label="$dashboard->machineStatus->label()" :status="$dashboard->machineStatus->value" /></div>
    </div>

    @php
        $dashboardStockTone = 'aman';
        foreach ($dashboard->shelfLifeRows as $row) {
            if ($row['stock_status']->value === 'habis') {
                $dashboardStockTone = 'habis';
                break;
            }

            if ($row['stock_status']->value === 'rendah') {
                $dashboardStockTone = 'rendah';
            }
        }
    @endphp

    <div class="dashboard__summary dashboard__summary--{{ $dashboardStockTone }}">
        <section class="stat-grid" aria-label="Ringkasan dashboard">
            <x-kartu-ringkasan label="Total penjualan hari ini" :value="$dashboard->summary['sales']" />
            <x-kartu-ringkasan label="Sisa stok keseluruhan" :value="$dashboard->summary['stock']" :detail="$dashboard->summary['active_slots']" />
            <x-kartu-ringkasan label="Status suhu vending machine" :value="$dashboard->summary['temperature']" />
            <x-kartu-ringkasan label="Status Masa Simpan Makanan" :value="$dashboard->summary['shelf_life_count']" :detail="$dashboard->summary['shelf_life_detail'].' · '.$dashboard->summary['shelf_life_warning']" tag="Perlu Tindakan" />
        </section>
    </div>

    <section class="surface dashboard__life">
        <div class="dashboard__life-header">
            <h2 class="section-heading">Masa Simpan Makanan</h2>
            <p class="page-subtitle">Daftar produk dengan masa simpannya</p>
        </div>
        <x-tabel-data :headers="['PRODUK', 'SLOT', 'DURASI DI MESIN', 'SISA WAKTU', 'STATUS', 'STOK']">
            @foreach ($dashboard->shelfLifeRows as $row)
                <tr>
                    <td>{{ $row['product'] }}</td>
                    <td>{{ $row['slot'] }}</td>
                    <td>{{ $row['duration'] }}</td>
                    <td>{{ $row['remaining'] }}</td>
                    <td>
                        @if ($row['status'])
                            <x-lencana-status :label="$row['status']->label()" :status="$row['status']->value" />
                        @else
                            —
                        @endif
                    </td>
                    <td><x-batang-progres :percentage="$row['stock'] === 0 ? 4 : $row['stock_percent']" :status="$row['stock_status']->value" :label="$row['stock'].'/'.$row['capacity']" /></td>
                </tr>
            @endforeach
        </x-tabel-data>
    </section>

    <section class="dashboard__charts" aria-label="Grafik dashboard">
        <x-kartu-grafik
            :title="$dashboard->temperatureChart['title']"
            chart-id="dashboard-temperature-chart"
            type="line"
            :labels="$dashboard->temperatureChart['labels']"
            :values="$dashboard->temperatureChart['values']"
            :secondary-values="[60, 60, 60, 60, 60]"
        />
        <x-kartu-grafik
            :title="$dashboard->salesChart['title']"
            chart-id="dashboard-sales-chart"
            type="bar"
            :labels="$dashboard->salesChart['labels']"
            :values="$dashboard->salesChart['values']"
            :alternate-values="$dashboard->salesChart['daily_values']"
            :alternate-labels="['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']"
            :controls="['Jam', 'Hari']"
            active-control="Jam"
        />
    </section>
@endsection

@push('styles')
    @vite('resources/css/monitoring_dashboard.css')
@endpush
