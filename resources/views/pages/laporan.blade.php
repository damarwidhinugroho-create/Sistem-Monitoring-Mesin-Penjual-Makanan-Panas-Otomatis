@extends('layouts.layout_dashboard')

@section('title', 'Laporan Penjualan')
@section('breadcrumb', 'LAPORAN')
@section('page-heading', 'Laporan Penjualan')
@section('page-subtitle', 'Ringkasan dan ekspor aktivitas vending machine')

@section('content')
    <div class="report-page" data-report-page>
        <header class="report__heading">
            <div class="report__toolbar">
                <nav class="report__periods" aria-label="Periode laporan" data-report-period>
                    <button class="report__period is-active" type="button" data-period="day" aria-pressed="true">Hari</button>
                    <button class="report__period" type="button" data-period="week" aria-pressed="false">Minggu</button>
                    <button class="report__period" type="button" data-period="month" aria-pressed="false">Bulan</button>
                    <x-filter-tanggal />
                </nav>
                <button class="report__export-button" type="button" data-open-export>
                    <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 1.5v8m0 0 3-3m-3 3-3-3M2.5 10v3.5h11V10" /></svg>
                    Export Laporan
                </button>
            </div>
        </header>

        <section class="stat-grid report__summary" aria-label="Ringkasan laporan">
            <x-kartu-ringkasan label="Total Transaksi" value="0" detail="Belum ada ekspor sebelumnya" :icon="false"
                data-report-metric="transactions" />
            <x-kartu-ringkasan label="Pendapatan" value="Rp 0" detail="Belum ada ekspor sebelumnya" :icon="false"
                data-report-metric="revenue" />
            <x-kartu-ringkasan label="Produk Terjual" value="0" detail="Belum ada ekspor sebelumnya" :icon="false"
                data-report-metric="quantity" />
            <x-kartu-ringkasan label="Aktivitas Restok" value="0" detail="Belum ada ekspor sebelumnya" :icon="false"
                data-report-metric="restocks" />
        </section>

        <section class="surface report__preview" aria-label="Riwayat ekspor laporan">
            <header class="report__preview-header">
                <div>
                    <h2 class="panel__title">Riwayat Ekspor Laporan</h2>
                    <p class="panel__description">Unduh kembali laporan Excel yang pernah diekspor dari browser ini.</p>
                    <p class="report__history-error" role="alert" data-history-error hidden></p>
                </div>
            </header>

            <x-tabel-data class="report__table report__history-table" data-report-history-table
                :headers="['TANGGAL EKSPOR', 'FILTER PERIODE', 'RENTANG TANGGAL', 'TRANSAKSI', 'PENDAPATAN', 'AKSI']">
                <tr><td class="report__empty" colspan="6">Belum ada riwayat ekspor laporan.</td></tr>
            </x-tabel-data>
            <footer class="report__table-footer" data-report-history-footer hidden>
                <p class="report__table-count" data-report-history-count aria-live="polite"></p>
                <nav class="report__pagination" aria-label="Halaman riwayat ekspor" data-report-history-pagination></nav>
            </footer>
        </section>
    </div>

    <dialog class="report-dialog" aria-labelledby="report-dialog-title" data-export-dialog>
        <div class="report-dialog__content">
            <h2 id="report-dialog-title">Ekspor Laporan</h2>
            <p data-export-period-label></p>
            <p class="report-dialog__empty-note" data-export-empty-note hidden>
                Tidak ada data pada periode ini. File Excel akan tetap dibuat dengan header kolom.
            </p>
            <div class="report-dialog__actions">
                <span class="report-dialog__error" role="alert" data-export-error hidden></span>
                <button class="report-dialog__cancel" type="button" data-cancel-export>Batal</button>
                <button class="report-dialog__confirm" type="button" data-confirm-export>Export Excel</button>
            </div>
        </div>
    </dialog>
@endsection

@push('styles')
    @vite('resources/css/laporan.css')
@endpush
