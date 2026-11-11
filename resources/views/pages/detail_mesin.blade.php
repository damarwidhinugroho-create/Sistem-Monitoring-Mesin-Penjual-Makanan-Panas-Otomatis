@extends('layouts.layout_dashboard')

@section('title', 'Detail Mesin')

@section('content')
    <section class="surface machine__hero">
        <x-ikon name="machine" :size="24" />
        <div>
            <x-lencana-status :label="$machine['status']->label()" :status="$machine['status']->value" />
            <div class="machine__hero-title">DETAIL MESIN</div>
        </div>
        <div class="machine__updated">Terakhir diperbarui<br>{{ $machine['details']['updated_at'] }}</div>
    </section>

    <section class="machine__grid">
        <article class="surface machine__card">
            <h2>Informasi Mesin</h2>
            <div class="machine__line"><span>ID Mesin</span><strong>{{ $machine['details']['machine_id'] }}</strong></div>
            <div class="machine__line"><span>Lokasi</span><strong>{{ $machine['details']['location'] }}</strong></div>
            <div class="machine__line"><span>Status</span><x-lencana-status :label="$machine['status']->label()" :status="$machine['status']->value" /></div>
        </article>
        <article class="surface machine__card">
            <h2>Koneksi</h2>
            <div class="machine__line"><span>Wi-Fi</span><strong>{{ $machine['details']['wifi'] }}</strong></div>
            <div class="machine__line"><span>Last Online</span><strong>{{ $machine['details']['last_online'] }}</strong></div>
            <div class="machine__line"><span>Last Update</span><strong>{{ $machine['details']['last_update'] }}</strong></div>
        </article>
        <article class="surface machine__card">
            <h2>Spesifikasi</h2>
            <div class="machine__line"><span>Jumlah Slot</span><strong>{{ $machine['details']['slots'] }}</strong></div>
            <div class="machine__line"><span>Pembayaran</span><strong>{{ $machine['details']['payment'] }}</strong></div>
            <div class="machine__line"><span>Tipe Mesin</span><strong>{{ $machine['details']['machine_type'] }}</strong></div>
        </article>
        {{-- <article class="surface machine__card">
            <h2>Maintenance</h2>
            <div class="machine__line"><span>Pengecekan Terakhir</span><strong>{{ $machine['details']['maintenance_date'] }}</strong></div>
            <div class="machine__line"><span>Oleh</span><strong>{{ $machine['details']['technician'] }}</strong></div>
            <div class="machine__line"><span>Pengecekan Berikutnya</span><strong>{{ $machine['details']['next_maintenance'] }}</strong></div>
        </article> --}}
    </section>
@endsection

@push('styles')
    @vite('resources/css/detail_mesin.css')
@endpush
