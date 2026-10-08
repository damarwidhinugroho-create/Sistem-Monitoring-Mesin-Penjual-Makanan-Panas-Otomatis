@extends('layouts.tata-letak-panel')

@section('title', 'Laporan Penjualan')

@section('content')
    <div class="report__heading">
        <h1 class="page-title">Laporan Penjualan</h1>
        <div class="report__toolbar">
            <nav class="report__periods" aria-label="Periode laporan" data-report-period>
                <button class="report__period is-active" type="button" data-period="day" aria-pressed="true">Hari</button>
                <button class="report__period" type="button" data-period="week" aria-pressed="false">Minggu</button>
                <button class="report__period" type="button" data-period="month" aria-pressed="false">Bulan</button>
                <x-date-range-picker />
            </nav>
            <button class="report__export-button" type="button" data-open-export>
                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 1.5v8m0 0 3-3m-3 3-3-3M2.5 10v3.5h11V10" /></svg>
                Export Laporan
            </button>
        </div>
    </div>

    <section class="surface report__workspace" aria-label="Konten laporan">
        <!-- TODO: Isi area laporan saat visualisasi dan detail laporan tersedia. -->
    </section>

    <dialog class="report-dialog" aria-labelledby="report-dialog-title" data-export-dialog>
        <div class="report-dialog__content">
            <h2 id="report-dialog-title">Ekspor Laporan</h2>
            <p data-export-period-label>Hari ini?</p>
            <div class="report-dialog__actions">
                <span class="report-dialog__error" role="alert" data-export-error hidden></span>
                <button type="button" data-confirm-export>Ekspor</button>
            </div>
        </div>
    </dialog>
@endsection

@push('styles')
    @vite('resources/css/laporan.css')
@endpush
