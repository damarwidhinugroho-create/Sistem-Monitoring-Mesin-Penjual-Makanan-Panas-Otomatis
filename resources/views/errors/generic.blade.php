@extends('errors.layout')
@section('title', 'Terjadi kesalahan')
@section('content')
    <section class="error-state" role="main" aria-labelledby="error-title">
        <div class="error-state__illustration error-state__illustration--critical" aria-hidden="true"><strong>!</strong></div>
        <p class="error-state__code">KESALAHAN TAK TERDUGA</p>
        <h1 id="error-title">Maaf, sesuatu berjalan tidak semestinya.</h1>
        <p>Kamu dapat kembali ke halaman sebelumnya atau mencoba lagi.</p>
        <div class="error-state__actions">
            <a class="button button--secondary" href="javascript:history.back()">Kembali</a>
            <a class="button button--primary" href="{{ route('login') }}">Ke Beranda</a>
        </div>
    </section>
@endsection
