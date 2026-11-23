@extends('errors.layout')
@section('title', 'Koneksi terputus')
@section('content')
    <section class="error-state" role="main" aria-labelledby="error-title">
        <div class="error-state__illustration error-state__illustration--offline" aria-hidden="true"><strong>!</strong></div>
        <p class="error-state__code">JARINGAN · TERPUTUS</p>
        <h1 id="error-title">Kamu sedang offline.</h1>
        <p>Periksa koneksi internet, lalu coba muat halaman kembali.</p>
        <div class="error-state__actions">
            <button class="button button--primary" type="button" onclick="location.reload()">Coba Lagi</button>
        </div>
    </section>
@endsection
