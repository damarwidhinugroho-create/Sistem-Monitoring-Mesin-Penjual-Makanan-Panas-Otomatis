@extends('layouts.layout_login')

@section('title', 'Login Operator')

@section('content')
    <div class="vending-login" data-vending-login>
        <div class="login-products" aria-hidden="true">
            <img class="login-products__image login-products__image--gonggong" src="{{ asset('images/products/gonggong.png') }}" alt="" loading="lazy" decoding="async">
            <img class="login-products__image login-products__image--mie-lendir" src="{{ asset('images/products/mie_lendir.png') }}" alt="" loading="lazy" decoding="async">
            <img class="login-products__image login-products__image--mie-tarempa" src="{{ asset('images/products/mie_tarempa.png') }}" alt="" loading="lazy" decoding="async">
            <img class="login-products__image login-products__image--sup-ikan" src="{{ asset('images/products/sup_ikan.png') }}" alt="" loading="lazy" decoding="async">
            <img class="login-products__image login-products__image--ayam-goreng" src="{{ asset('images/products/ayam_goreng.png') }}" alt="" loading="lazy" decoding="async">
            <img class="login-products__image login-products__image--lontong-sayur" src="{{ asset('images/products/lontong_sayur.png') }}" alt="" loading="lazy" decoding="async">
            <img class="login-products__image login-products__image--naspad" src="{{ asset('images/products/naspad.png') }}" alt="" loading="lazy" decoding="async">
            <img class="login-products__image login-products__image--otakotak" src="{{ asset('images/products/otakotak.png') }}" alt="" loading="lazy" decoding="async">
        </div>

        <div class="vending-machine" data-vending-machine>
            <div class="vending-machine__front">
                <header class="vending-machine__header">
                    <span class="vending-machine__logo" aria-hidden="true">S</span>
                    <span class="vending-machine__brand">
                        <strong>SAJI!</strong>
                    </span>
                </header>

                <main class="vending-machine__body">
                    <section class="vending-login-screen" aria-labelledby="login-title" data-login-screen>
                        <div class="vending-login-screen__top">
                            <span class="vending-login-screen__led" aria-label="Lampu mesin aktif"></span>
                        </div>

                        <div class="vending-login-screen__copy">
                            <h1 id="login-title">Welcome back!</h1>
                            <p>Masuk untuk memantau mesin SAJI kamu.</p>
                        </div>

                        <form class="login-form" method="POST" action="{{ route('login.store') }}">
                            @csrf

                            <div class="login-form__field">
                                <label for="username">USERNAME</label>
                                <div class="login-input">
                                    <span class="login-input__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <rect x="3" y="5" width="18" height="14" rx="2.5"></rect>
                                            <path d="m4 7 8 6 8-6"></path>
                                        </svg>
                                    </span>
                                    <input
                                        id="username"
                                        name="username"
                                        type="text"
                                        value="{{ old('username') }}"
                                        placeholder="Nama pengguna"
                                        autocomplete="username"
                                        required
                                        @if ($errors->has('username')) aria-invalid="true" aria-describedby="username-error" @endif
                                    >
                                </div>
                                @error('username')
                                    <p class="login-error" id="username-error" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="login-form__field">
                                <label for="login-password">PASSWORD</label>
                                <div class="login-input">
                                    <span class="login-input__icon login-input__icon--lock" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <rect x="4" y="10" width="16" height="11" rx="2.5"></rect>
                                            <path d="M8 10V7a4 4 0 1 1 8 0v3"></path>
                                            <circle cx="12" cy="15.5" r="1"></circle>
                                        </svg>
                                    </span>
                                    <input
                                        id="login-password"
                                        name="password"
                                        type="password"
                                        placeholder="Masukkan password"
                                        autocomplete="current-password"
                                        required
                                        @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
                                    >
                                    <button
                                        class="login-field__toggle"
                                        type="button"
                                        data-password-toggle="login-password"
                                        aria-label="Lihat password"
                                        aria-controls="login-password"
                                        aria-pressed="false"
                                    >LIHAT</button>
                                </div>
                                @error('password')
                                    <p class="login-error" id="password-error" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <button class="login-submit" type="submit" data-login-submit>
                                <span>MASUK KE DASHBOARD</span>
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 12h15m-6-6 6 6-6 6"></path>
                                </svg>
                            </button>
                        </form>

                    </section>

                    <aside class="vending-side-panel" aria-hidden="true">
                        <div class="vending-side-panel__reader">
                            <span></span>
                        </div>
                        <div class="vending-side-panel__keypad">
                            @foreach (range(1, 9) as $key)
                                <i></i>
                            @endforeach
                        </div>
                        <div class="vending-side-panel__coin"></div>
                        <div class="vending-side-panel__led"></div>
                    </aside>
                </main>

                <footer class="vending-machine__tray" aria-hidden="true">
                    <div class="vending-machine__tray-flap"></div>
                    <div class="vending-machine__tray-outlet"></div>
                </footer>
            </div>

            <div class="vending-machine__feet" aria-hidden="true">
                <span></span><span></span>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @vite('resources/css/login.css')
@endpush
