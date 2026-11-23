@extends('errors.layout')
@section('title', 'Akses ditolak')
@section('content')
    <section class="error-state" role="main" aria-labelledby="error-title">
        <div class="error-state__illustration error-state__illustration--warning" aria-hidden="true"><strong>403</strong></div>
        <p class="error-state__code">403 · AKSES DITOLAK</p>
        <h1 id="error-title">Akun ini tidak memiliki akses ke halaman tersebut.</h1>
        <p>Masuk dengan akun yang sesuai atau kembali ke halaman sebelumnya.</p>
        <div class="error-state__actions">
            <a class="button button--secondary" href="javascript:history.back()">Kembali</a>
            <a class="button button--primary" href="{{ route('login') }}">Masuk</a>
        </div>
    </section>
@endsection
