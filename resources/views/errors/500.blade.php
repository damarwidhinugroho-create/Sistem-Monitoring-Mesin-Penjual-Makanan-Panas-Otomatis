@extends('errors.layout')
@section('title', 'Kesalahan server')
@section('content')
    <section class="error-state" role="main" aria-labelledby="error-title">
        <div class="error-state__illustration error-state__illustration--critical" aria-hidden="true"><strong>500</strong></div>
        <p class="error-state__code">500 · SERVER TIDAK MERESPON</p>
        <h1 id="error-title">Terjadi gangguan saat memuat data.</h1>
        <p>Silakan coba lagi sebentar. Data yang tersimpan tetap aman.</p>
        <div class="error-state__actions">
            <button class="button button--secondary" type="button" onclick="location.reload()">Coba Lagi</button>
            <a class="button button--primary" href="{{ route('login') }}">Ke Beranda</a>
        </div>
    </section>
@endsection
