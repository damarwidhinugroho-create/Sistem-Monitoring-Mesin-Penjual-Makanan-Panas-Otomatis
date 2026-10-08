@extends('layouts.tata-letak-tamu')

@section('title', 'Lupa Password')

@section('content')
    <section class="login-card" aria-labelledby="forgot-password-title">
        <form class="login-card__body" data-reset-form>
            <div class="login-card__avatar" aria-hidden="true">
                <span class="login-card__avatar-logo" style="mask-image: url('{{ asset('images/logo.svg') }}'); -webkit-mask-image: url('{{ asset('images/logo.svg') }}')"></span>
            </div>
            <h1 class="login-card__title" id="forgot-password-title">LUPA PASSWORD</h1>
            <p class="page-subtitle">Masukkan username untuk memulihkan akses akun.</p>
            <label class="login-field">
                <span class="visually-hidden">Username</span>
                <input name="username" type="text" autocomplete="username" placeholder="Username" aria-label="Username" required>
            </label>
            <p class="login-card__notice" data-reset-message role="status" hidden>Silakan hubungi administrator untuk meminta reset password.</p>
            <button class="login-card__submit" type="submit">KIRIM</button>
        </form>
        <div class="login-card__forgot"><a href="{{ route('login') }}">Kembali ke Login</a></div>
    </section>
@endsection

@push('styles')
    @vite('resources/css/lupa-password.css')
@endpush
