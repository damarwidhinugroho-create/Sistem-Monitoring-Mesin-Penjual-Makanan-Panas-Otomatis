@extends('layouts.tata-letak-panel')

@section('title', 'Monitoring Suhu')

@section('content')
    <div class="temperature__heading"><h1 class="page-title">Monitoring Suhu</h1></div>
    <x-banner-peringatan
        class="temperature__alert"
        :message="$temperature->readings['alert']"
        link-label="Lihat Riwayat Alert"
        link-route="alerts"
    />
    <section class="temperature__grid">
        <article class="surface temperature__reading">
            <x-ikon active large />
            <strong class="temperature__value">{{ $temperature->readings['temperature'] }}°C</strong>
            <x-lencana-status :label="$temperature->machineStatus->label()" :status="$temperature->machineStatus->value" />
            <span class="temperature__update">Last update {{ $temperature->readings['updated_at'] }}</span>
            <span class="temperature__safe">{{ $temperature->readings['safe_label'] }}</span>
        </article>
        <x-kartu-grafik
            class="temperature__chart"
            title="Data suhu VM - 00 Jam terakhir"
            chart-id="temperature-chart"
            type="line"
            :labels="$temperature->readings['labels']"
            :values="$temperature->readings['values']"
            :secondary-values="$temperature->readings['thresholds']"
        />
    </section>
@endsection

@push('styles')
    @vite('resources/css/monitoring-suhu.css')
@endpush
