@extends('errors.layout')
@section('title', 'Pemeliharaan')
@section('content')
    <section class="error-state" role="main" aria-labelledby="error-title">
        <div class="error-state__illustration error-state__illustration--warning" aria-hidden="true"><strong>503</strong></div>
        <p class="error-state__code">503 · PEMELIHARAAN</p>
        <h1 id="error-title">Layanan sedang disiapkan kembali.</h1>
        <p>Sistem monitoring sedang dalam pemeliharaan. Silakan kembali dalam beberapa saat.</p>
        <div class="error-state__actions">
            <button class="button button--primary" type="button" onclick="location.reload()">Coba Lagi</button>
        </div>
    </section>
@endsection
