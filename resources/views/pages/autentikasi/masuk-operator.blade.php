@extends('layouts.tata-letak-tamu')

@section('title', 'Login Operator')

@section('content')
    <section class="login-card" aria-labelledby="login-title">
        <form class="login-card__body" method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="login-card__avatar" aria-hidden="true">
                <span class="login-card__avatar-logo" style="mask-image: url('{{ asset('images/logo.svg') }}'); -webkit-mask-image: url('{{ asset('images/logo.svg') }}')"></span>
            </div>
            <h1 class="login-card__title" id="login-title">LOGIN operator/UMKM</h1>
            <label class="login-field">
                <span class="visually-hidden">Username</span>
                <input name="username" type="text" value="{{ old('username', 'Budi') }}" autocomplete="username" aria-label="Username" required>
            </label>
            <label class="login-field">
                <span class="visually-hidden">Password</span>
                <input id="login-password" name="password" type="password" value="budi123" autocomplete="current-password" aria-label="Password" required>
                <button class="login-field__toggle" type="button" data-password-toggle="login-password" aria-label="Lihat password">lihat</button>
            </label>
            @error('username')
                <p class="form-error" role="alert">{{ $message }}</p>
            @enderror
            @error('password')
                <p class="form-error" role="alert">{{ $message }}</p>
            @enderror
            <button class="login-card__submit" type="submit">LOGIN</button>
        </form>
        <div class="login-card__forgot"><a href="{{ route('password.request') }}">Lupa Password?</a></div>
    </section>
@endsection

@push('styles')
    @vite('resources/css/masuk-operator.css')
@endpush
