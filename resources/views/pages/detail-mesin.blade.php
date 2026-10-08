@extends('layouts.tata-letak-panel')

@section('title', 'Detail Mesin')

@section('content')
    <section class="surface machine__hero">
        <x-ikon active large />
        <div>
            <x-lencana-status :label="$machine->status->label()" :status="$machine->status->value" />
            <div class="machine__hero-title">DETAIL MESIN</div>
        </div>
        <div class="machine__updated">Terakhir diperbarui<br>{{ $machine->details['updated_at'] }}</div>
    </section>

    <section class="machine__grid">
        <article class="surface machine__card">
            <h2>Informasi Mesin</h2>
            <div class="machine__line"><span>ID Mesin</span><strong>{{ $machine->details['machine_id'] }}</strong></div>
            <div class="machine__line"><span>Lokasi</span><strong>{{ $machine->details['location'] }}</strong></div>
            <div class="machine__line"><span>Status</span><x-lencana-status :label="$machine->status->label()" :status="$machine->status->value" /></div>
        </article>
        <article class="surface machine__card">
            <h2>Koneksi</h2>
            <div class="machine__line"><span>Wi-Fi</span><strong>{{ $machine->details['wifi'] }}</strong></div>
            <div class="machine__line"><span>Last Online</span><strong>{{ $machine->details['last_online'] }}</strong></div>
            <div class="machine__line"><span>Last Update</span><strong>{{ $machine->details['last_update'] }}</strong></div>
        </article>
        <article class="surface machine__card">
            <h2>Suhu <x-lencana-status label="Normal" status="aman" /></h2>
            <strong class="machine__temperature">{{ $machine->details['temperature'] }}</strong>
            <span>{{ $machine->details['cabinet_temperature'] }}</span>
        </article>
        <article class="surface machine__card">
            <h2>Spesifikasi</h2>
            <div class="machine__line"><span>Jumlah Slot</span><strong>{{ $machine->details['slots'] }}</strong></div>
            <div class="machine__line"><span>Pembayaran</span><strong>{{ $machine->details['payment'] }}</strong></div>
            <div class="machine__line"><span>Tipe Mesin</span><strong>{{ $machine->details['machine_type'] }}</strong></div>
        </article>
        <article class="surface machine__card">
            <h2>Masalah Saat Ini</h2>
            <div class="machine__problem">{{ $machine->details['current_problem'] }}</div>
        </article>
        <article class="surface machine__card">
            <h2>Maintenance</h2>
            <div class="machine__line"><span>Pengecekan Terakhir</span><strong>{{ $machine->details['maintenance_date'] }}</strong></div>
            <div class="machine__line"><span>Oleh</span><strong>{{ $machine->details['technician'] }}</strong></div>
            <div class="machine__line"><span>Pengecekan Berikutnya</span><strong>{{ $machine->details['next_maintenance'] }}</strong></div>
        </article>
        <article class="surface machine__alerts">
            <div class="machine__alerts-header"><h2 class="section-heading">Alert Terbaru</h2><a class="button" href="{{ route('alerts') }}">Lihat History Alert</a></div>
            <x-tabel-data :headers="[]">
                @foreach ($machine->details['alerts'] as $alert)
                    <tr>
                        <td><x-lencana-status :label="$alert['time']" :status="$alert['tone']" /></td>
                        <td>{{ $alert['message'] }}</td>
                        <td><x-lencana-status :label="$alert['status']" status="aman" /></td>
                    </tr>
                @endforeach
            </x-tabel-data>
        </article>
    </section>
@endsection

@push('styles')
    @vite('resources/css/detail-mesin.css')
@endpush
