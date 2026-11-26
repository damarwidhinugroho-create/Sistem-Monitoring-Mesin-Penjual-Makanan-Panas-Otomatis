@extends('layouts.layout_dashboard')

@section('title', 'Dashboard')
@section('breadcrumb', 'DASHBOARD')
@section('page-heading', 'Dashboard OVERVIEW')
@section('page-subtitle', 'SJI-001 · Grand Indonesia / Pantau sajian, jaga kualitas.')

@section('content')
    @php
        $slotSummary = $dashboard['slot_summary'];
        $renderedAt = $dashboard['rendered_at']->locale('id');
        $slotStatusLabels = [
            'aman' => 'Aman',
            'kritis' => 'Kritis',
            'habis' => 'Habis',
        ];
        $dailySales = $dashboard['sales']['chart']['day'];
        $weeklySales = $dashboard['sales']['chart']['week'];
    @endphp

    <div class="dashboard__content">
        <div class="dashboard__toolbar">
            <div class="dashboard__meta" aria-label="Waktu pemuatan halaman">
                <time datetime="{{ $renderedAt->toIso8601String() }}">
                    Halaman dimuat {{ $renderedAt->translatedFormat('l, d F Y · H.i') }} WIB
                </time>
            </div>
        </div>

        <section
            class="dashboard__overview dashboard__overview--{{ $slotSummary['overview_status'] }}"
            aria-label="Ringkasan dashboard"
        >
            <div class="stat-grid dashboard__summary" aria-label="Statistik ringkasan">
                <x-kartu-ringkasan
                    label="Penjualan hari ini"
                    :value="'Rp'.number_format($dashboard['sales']['today']['revenue'], 0, ',', '.')"
                    :detail="$dashboard['sales']['today']['change_label'].' dibanding kemarin · Data simulasi'"
                    icon-name="money"
                    class="dashboard__summary-card"
                />
                <x-kartu-ringkasan
                    label="Pesanan hari ini"
                    :value="number_format($dashboard['sales']['today']['orders'], 0, ',', '.')"
                    :detail="'Rata-rata Rp'.number_format($dashboard['sales']['today']['average_order'], 0, ',', '.').' · Data simulasi'"
                    icon-name="receipt"
                    class="dashboard__summary-card"
                />
                <x-kartu-ringkasan
                    label="Stok tersedia"
                    :value="$slotSummary['total'] > 0 ? $slotSummary['filled'].' / '.$slotSummary['total'] : '—'"
                    :detail="$slotSummary['percent'] !== null
                        ? $slotSummary['percent'].'% terisi · '.$slotSummary['empty'].' slot kosong'
                        : 'Data slot tidak tersedia dari API.'"
                    icon-name="box"
                    class="dashboard__summary-card"
                />
                <x-kartu-ringkasan
                    label="Suhu mesin"
                    :value="number_format($dashboard['sales']['temperature']['value'], 1, ',', '.').' °C'"
                    :detail="$dashboard['sales']['temperature']['status'].' · Data simulasi'"
                    icon-name="thermometer"
                    class="dashboard__summary-card"
                />
            </div>
        </section>

        <section class="dashboard__split-grid dashboard__split-grid--charts" aria-label="Penjualan mingguan dan riwayat suhu mesin">
            <x-panel
                title="Penjualan mingguan"
                description="Pendapatan simulasi · bukan data transaksi aktual."
                class="dashboard__panel dashboard__panel--sales"
            >
                <x-slot:actions>
                    <label class="dashboard__filter">
                        <span class="visually-hidden">Filter periode penjualan</span>
                        <select
                            aria-label="Filter periode penjualan simulasi"
                            data-dashboard-chart-period
                            data-chart-target="dashboard-sales-chart"
                        >
                            <option
                                value="day"
                                data-total="{{ $dailySales['total_label'] }}"
                                data-change="{{ $dailySales['change_label'] }}"
                                data-comparison="{{ $dailySales['comparison_label'] }}"
                                selected
                            >Per hari</option>
                            <option
                                value="week"
                                data-total="{{ $weeklySales['total_label'] }}"
                                data-change="{{ $weeklySales['change_label'] }}"
                                data-comparison="{{ $weeklySales['comparison_label'] }}"
                            >7 hari terakhir</option>
                        </select>
                    </label>
                </x-slot:actions>

                <div class="dashboard__sales-summary" aria-label="Ringkasan periode penjualan simulasi">
                    <div>
                        <span>Total periode</span>
                        <strong data-dashboard-sales-total>{{ $dailySales['total_label'] }}</strong>
                    </div>
                    <div>
                        <span data-dashboard-sales-comparison>{{ $dailySales['comparison_label'] }}</span>
                        <strong data-dashboard-sales-change>{{ $dailySales['change_label'] }}</strong>
                    </div>
                </div>
                <div class="chart-card__plot dashboard__sales-chart">
                    <canvas
                        id="dashboard-sales-chart"
                        class="chart-card__canvas"
                        data-chart-type="bar"
                        data-chart-labels='@json($dailySales['labels'])'
                        data-chart-values='@json($dailySales['values'])'
                        data-chart-alt-labels='@json($weeklySales['labels'])'
                        data-chart-alt-values='@json($weeklySales['values'])'
                        data-chart-highlight-latest="true"
                        data-chart-value-format="currency"
                        aria-label="Grafik penjualan simulasi per jam"
                        role="img"
                    ></canvas>
                </div>
            </x-panel>

            <x-panel
                title="Data suhu VM - 24 jam terakhir"
                description="Riwayat suhu per jam."
                class="dashboard__panel dashboard__panel--temperature"
            >
                <div class="chart-card__plot dashboard__temperature-chart">
                    <canvas
                        id="dashboard-temperature-chart"
                        class="chart-card__canvas"
                        data-chart-type="line"
                        data-chart-labels='@json($dashboard['sales']['temperature']['chart']['labels'])'
                        data-chart-values='@json($dashboard['sales']['temperature']['chart']['values'])'
                        data-chart-value-format="temperature"
                        aria-label="Grafik riwayat suhu VM per jam pada rentang waktu yang sedang ditampilkan, dalam derajat Celsius"
                        role="img"
                    ></canvas>
                </div>
            </x-panel>
        </section>

        <section class="dashboard__split-grid" aria-label="Stok dan ketersediaan slot">
            <x-panel
                title="Stok & kedaluwarsa"
                :description="$slotSummary['total'] > 0
                    ? number_format($slotSummary['total_stock'], 0, ',', '.').' porsi tersedia · '.$slotSummary['product_count'].' menu'
                    : 'Data stok belum tersedia dari API.'"
                class="dashboard__panel dashboard__panel--expiry"
            >
                <x-slot:actions>
                    <a class="button button--secondary dashboard__action-link" href="{{ route('inventory') }}">
                        Kelola stok
                    </a>
                </x-slot:actions>

                <x-tabel-data :headers="['MENU / SLOT', 'STOK / KAPASITAS', 'KEDALUWARSA', 'STATUS STOK']">
                    @forelse ($dashboard['inventory_preview'] as $inventoryRow)
                        <tr>
                            <td>
                                <span class="dashboard__inventory-product">
                                    <strong>{{ $inventoryRow['product'] }}</strong>
                                    <span>Slot {{ $inventoryRow['code'] }}</span>
                                </span>
                            </td>
                            <td>
                                {{ number_format($inventoryRow['stock'], 0, ',', '.') }}
                                /
                                {{ $inventoryRow['capacity'] > 0
                                    ? number_format($inventoryRow['capacity'], 0, ',', '.')
                                    : '—' }}
                                porsi
                            </td>
                            <td><span class="dashboard__expiry-unavailable">Belum tersedia</span></td>
                            <td>
                                <x-lencana-status
                                    :label="$inventoryRow['status']->label()"
                                    :status="$inventoryRow['status']->value"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="dashboard__table-empty" colspan="4">
                                Data inventory belum tersedia dari API.
                            </td>
                        </tr>
                    @endforelse
                </x-tabel-data>
                @if ($dashboard['inventory_count'] > count($dashboard['inventory_preview']))
                    <p class="dashboard__inventory-note">
                        Menampilkan {{ count($dashboard['inventory_preview']) }} dari {{ $dashboard['inventory_count'] }} slot,
                        diurutkan dari stok terendah.
                    </p>
                @endif
            </x-panel>

            <x-panel title="Ketersediaan slot" class="dashboard__panel dashboard__panel--slots">
                <x-slot:actions>
                    <strong class="dashboard__slot-total" aria-label="Jumlah slot terisi dari total slot">
                        {{ $slotSummary['total'] > 0 ? $slotSummary['filled'].'/'.$slotSummary['total'] : '—' }}
                    </strong>
                </x-slot:actions>

                <p class="dashboard__slot-description">Data ini berdasarkan stok dari API.</p>
                @if (count($dashboard['slots']) > 0)
                    <div class="dashboard__slot-grid" role="list" aria-label="Ketersediaan slot mesin">
                        @foreach ($dashboard['slots'] as $slot)
                            @php
                                $slotCode = $slot['code'] ?? 'Kode tidak tersedia';
                                $slotStatusLabel = $slotStatusLabels[$slot['status']];
                                $slotProduct = $slot['product'] ?? 'Nama menu tidak tersedia';
                            @endphp
                            <div
                                class="dashboard__slot dashboard__slot--{{ $slot['status'] }}"
                                role="listitem"
                                title="Slot {{ $slotCode }} · {{ $slotProduct }} · {{ $slot['stock'] }} porsi · {{ $slotStatusLabel }}"
                                aria-label="Slot {{ $slotCode }}, {{ $slotProduct }}, {{ $slot['stock'] }} porsi, {{ $slotStatusLabel }}"
                            >
                                <span>{{ $slot['code'] ?? '—' }}</span>
                                <span class="visually-hidden">{{ $slotStatusLabel }}</span>
                            </div>
                        @endforeach
                    </div>

                    <ul class="dashboard__slot-legend" aria-label="Legenda status slot">
                        <li>
                            <span class="dashboard__legend-marker dashboard__legend-marker--aman" aria-hidden="true"></span>
                            Aman <strong>{{ $slotSummary['status_counts']['aman'] }}</strong>
                        </li>
                        <li>
                            <span class="dashboard__legend-marker dashboard__legend-marker--kritis" aria-hidden="true"></span>
                            Kritis <strong>{{ $slotSummary['status_counts']['kritis'] }}</strong>
                        </li>
                        <li>
                            <span class="dashboard__legend-marker dashboard__legend-marker--habis" aria-hidden="true"></span>
                            Habis <strong>{{ $slotSummary['status_counts']['habis'] }}</strong>
                        </li>
                    </ul>
                @else
                    <div class="dashboard__empty-state" role="status">
                        <x-ikon name="box" size="24" />
                        <p>Data slot belum tersedia dari API.</p>
                    </div>
                @endif
            </x-panel>
        </section>
    </div>
@endsection

@push('styles')
    @vite('resources/css/monitoring_dashboard.css')
@endpush
