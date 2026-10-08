@extends('layouts.tata-letak-panel')

@section('title', 'History Alert')

@section('content')
    <div class="history-alert__heading">
        <div>
            <h1 class="page-title">History Alert</h1>
            <p class="page-subtitle">Riwayat peringatan mesin</p>
        </div>
        <div class="history-alert__toolbar">
            <x-period-picker context="alerts" />
            <div class="history-alert__filters" role="group" aria-label="Filter alert" data-alert-filter>
                <button class="history-alert__filter is-active" type="button" data-filter="all" aria-pressed="true">Semua</button>
                <button class="history-alert__filter" type="button" data-filter="Critical" aria-pressed="false">Critical</button>
                <button class="history-alert__filter" type="button" data-filter="Warning" aria-pressed="false">Warning</button>
                <button class="history-alert__filter" type="button" data-filter="Unresolved" aria-pressed="false">Unresolved</button>
            </div>
        </div>
    </div>

    <section class="surface history-alert__surface" aria-label="Riwayat alert">
        <x-tabel-data class="history-alert__table" :headers="['ALERT', 'WAKTU', 'PRIORITAS', 'STATUS']">
            <tr data-alert-empty hidden>
                <td class="history-alert__empty" colspan="4">Tidak ada alert untuk filter yang dipilih.</td>
            </tr>
        </x-tabel-data>
        <nav class="pagination history-alert__pagination" aria-label="Halaman riwayat alert" data-alert-pagination></nav>
    </section>
@endsection

@push('styles')
    @vite('resources/css/riwayat-alert.css')
@endpush
