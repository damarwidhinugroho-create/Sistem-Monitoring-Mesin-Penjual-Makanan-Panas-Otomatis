@extends('errors.layout')
@section('title', 'Halaman tidak ditemukan')
@section('content')
    <section class="error-state" role="main" aria-labelledby="error-title">
        <div class="error-state__illustration" aria-hidden="true">
            <span class="error-state__orbit"></span><strong>404</strong>
        </div>
        <p class="error-state__code">404 · HALAMAN TIDAK DITEMUKAN</p>
        <h1 id="error-title">Halaman yang kamu cari sudah berpindah.</h1>
        <p>Pemeriksaan alamat atau kembali ke dashboard untuk melanjutkan pemantauan mesin.</p>
        <div class="error-state__actions">
            <a class="button button--secondary" href="javascript:history.back()">Kembali</a>
            <a class="button button--primary" href="{{ auth()->guard('operator')->check() ? route('dashboard') : route('login') }}">Ke Beranda</a>
        </div>
    </section>
@endsection
