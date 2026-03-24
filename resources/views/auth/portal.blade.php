@extends('layouts.app-landing')

@section('title', 'Masuk / Daftar — SFHUB')

@php
    $initialMode = $mode ?? 'login';
    $registerErrorFields = ['name', 'email', 'password', 'password_confirmation', 'plan', 'terms'];
    $loginErrorFields    = ['login', 'password'];
    $hasRegisterErrors   = collect($registerErrorFields)->contains(fn($f) => $errors->has($f));
    $hasLoginErrors      = collect($loginErrorFields)->contains(fn($f) => $errors->has($f));
    if ($hasRegisterErrors) $initialMode = 'register';
    elseif ($hasLoginErrors) $initialMode = 'login';

    // Midtrans status check for plan display
    $midtransReady = !empty(\App\Models\SystemSetting::get('midtrans_server_key'))
                  && !empty(\App\Models\SystemSetting::get('midtrans_client_key'));

    // Google OAuth status
    $googleEnabled = !empty(config('services.google.client_id'));

    // Active plans from DB
    $activePlans = \App\Models\SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
@endphp

@push('styles')
<style>
    /* ── Font ── */
    @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap');

    :root {
        --clr-bg:       #f5f3ef;
        --clr-surface:  rgba(255,255,255,0.88);
        --clr-dark:     #111110;
        --clr-muted:    #78716c;
        --clr-brand:    #f97316;
        --clr-brand2:   #10b981;
        --clr-accent:   #fbbf24;
        --clr-border:   rgba(0,0,0,0.10);
        --radius-xl:    24px;
        --radius-lg:    16px;
        --radius-md:    12px;
        --shadow-card:  0 4px 6px -1px rgba(0,0,0,.04), 0 24px 48px -8px rgba(0,0,0,.12);
        --shadow-hover: 0 8px 12px -2px rgba(0,0,0,.06), 0 32px 64px -8px rgba(0,0,0,.18);
        --ease-spring:  cubic-bezier(.34,1.56,.64,1);
        --ease-smooth:  cubic-bezier(.22,1,.36,1);
        --dur-fast:     220ms;
        --dur-med:      420ms;
        --dur-slow:     640ms;
    }

    .dark {
        --clr-bg:      #0f0e0d;
        --clr-surface: rgba(26,24,22,0.92);
        --clr-dark:    #f5f3ef;
        --clr-muted:   #a8a29e;
        --clr-border:  rgba(255,255,255,0.08);
        --shadow-card: 0 4px 6px rgba(0,0,0,.3), 0 24px 48px rgba(0,0,0,.5);
    }

    body { font-family: 'DM Sans', sans-serif; }
    .font-display { font-family: 'Syne', sans-serif; }

    /* ── Auth wrapper ── */
    .auth-wrap {
        min-height: 100svh;
        display: grid;
        place-items: center;
        background: var(--clr-bg);
        padding: 3rem 1rem;
        position: relative;
        overflow: hidden;
    }

    /* Ambient blobs */
    .auth-wrap::before, .auth-wrap::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        pointer-events: none;
        z-index: 0;
    }
    .auth-wrap::before {
        width: 520px; height: 520px;
        top: -160px; left: -120px;
        background: radial-gradient(circle, rgba(16,185,129,.18), transparent 70%);
    }
    .auth-wrap::after {
        width: 440px; height: 440px;
        bottom: -100px; right: -80px;
        background: radial-gradient(circle, rgba(249,115,22,.14), transparent 70%);
    }

    /* ── Card shell ── */
    .auth-card {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 480px;
        background: var(--clr-surface);
        border: 1px solid var(--clr-border);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-card);
        backdrop-filter: blur(20px) saturate(1.4);
        -webkit-backdrop-filter: blur(20px) saturate(1.4);
        padding: 2.5rem 2.25rem;
        transition: box-shadow var(--dur-med) var(--ease-smooth),
                    transform var(--dur-med) var(--ease-smooth);
    }
    .auth-card:hover { box-shadow: var(--shadow-hover); }

    /* ── Tab switcher ── */
    .tab-bar {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px;
        background: rgba(0,0,0,.06);
        border-radius: var(--radius-md);
        padding: 4px;
        margin-bottom: 2rem;
    }
    .dark .tab-bar { background: rgba(255,255,255,.06); }

    .tab-btn {
        padding: .55rem 1rem;
        border-radius: 10px;
        font-family: 'Syne', sans-serif;
        font-weight: 600;
        font-size: .875rem;
        color: var(--clr-muted);
        transition: all var(--dur-fast) var(--ease-smooth);
        border: none;
        background: transparent;
        cursor: pointer;
        position: relative;
    }
    .tab-btn.active {
        background: var(--clr-surface);
        color: var(--clr-dark);
        box-shadow: 0 2px 8px rgba(0,0,0,.10);
    }
    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: 4px; left: 50%; transform: translateX(-50%);
        width: 20px; height: 2px;
        border-radius: 2px;
        background: var(--clr-brand);
    }

    /* ── Pane transitions ── */
    .pane { display: none; animation: paneIn var(--dur-med) var(--ease-spring) both; }
    .pane.active { display: block; }

    @keyframes paneIn {
        from { opacity: 0; transform: translateY(10px) scale(.99); }
        to   { opacity: 1; transform: translateY(0)    scale(1);   }
    }

    /* ── Inputs ── */
    .field-wrap { position: relative; }
    .field-label {
        display: block;
        font-size: .8125rem;
        font-weight: 500;
        color: var(--clr-muted);
        margin-bottom: .4rem;
        letter-spacing: .02em;
    }
    .field-input {
        width: 100%;
        background: rgba(255,255,255,.7);
        border: 1.5px solid rgba(0,0,0,.1);
        border-radius: var(--radius-md);
        padding: .75rem 1rem;
        font-size: .9375rem;
        color: var(--clr-dark);
        transition: border-color var(--dur-fast), box-shadow var(--dur-fast);
        outline: none;
        font-family: 'DM Sans', sans-serif;
    }
    .dark .field-input {
        background: rgba(255,255,255,.05);
        border-color: rgba(255,255,255,.1);
        color: #f5f3ef;
    }
    .field-input::placeholder { color: rgba(120,113,108,.5); }
    .field-input:focus {
        border-color: var(--clr-brand);
        box-shadow: 0 0 0 3px rgba(249,115,22,.15);
        background: #fff;
    }
    .dark .field-input:focus { background: rgba(255,255,255,.08); }
    .field-input.has-icon { padding-right: 2.75rem; }

    .field-icon {
        position: absolute; right: .875rem; top: 50%; transform: translateY(-50%);
        color: var(--clr-muted); font-size: .9rem; cursor: pointer;
        transition: color var(--dur-fast);
    }
    .field-icon:hover { color: var(--clr-brand); }

    /* ── Primary button ── */
    .btn-primary {
        width: 100%;
        display: flex; align-items: center; justify-content: center; gap: .5rem;
        padding: .875rem 1.5rem;
        border-radius: var(--radius-md);
        background: var(--clr-brand);
        color: #fff;
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: .9375rem;
        letter-spacing: .01em;
        border: none; cursor: pointer;
        transition: all var(--dur-fast) var(--ease-spring);
        position: relative; overflow: hidden;
    }
    .btn-primary:hover { background: #ea6c10; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(249,115,22,.35); }
    .btn-primary:active { transform: translateY(0); }
    .btn-primary::after {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,.15) 0%, transparent 60%);
        pointer-events: none;
    }

    /* Emerald variant */
    .btn-emerald {
        background: var(--clr-brand2);
    }
    .btn-emerald:hover { background: #0a9565; box-shadow: 0 6px 20px rgba(16,185,129,.3); }

    /* ── Divider ── */
    .or-divider {
        display: flex; align-items: center; gap: .75rem;
        color: var(--clr-muted); font-size: .8125rem;
        margin: 1.25rem 0;
    }
    .or-divider::before, .or-divider::after {
        content: ''; flex: 1;
        height: 1px; background: var(--clr-border);
    }

    /* ── Social OAuth btn ── */
    .btn-oauth {
        width: 100%;
        display: flex; align-items: center; justify-content: center; gap: .625rem;
        padding: .75rem 1.25rem;
        border-radius: var(--radius-md);
        background: transparent;
        border: 1.5px solid var(--clr-border);
        color: var(--clr-dark);
        font-size: .9rem; font-weight: 500;
        cursor: pointer;
        transition: all var(--dur-fast) var(--ease-smooth);
    }
    .btn-oauth:hover {
        border-color: rgba(0,0,0,.22);
        background: rgba(0,0,0,.03);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,.08);
    }
    .dark .btn-oauth { color: #f5f3ef; }
    .dark .btn-oauth:hover { border-color: rgba(255,255,255,.2); background: rgba(255,255,255,.05); }

    /* Google icon */
    .google-icon { width: 18px; height: 18px; flex-shrink: 0; }

    /* ── Plan cards ── */
    .plan-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
    .plan-card-label {
        border: 1.5px solid var(--clr-border);
        border-radius: var(--radius-md);
        padding: .75rem .625rem;
        cursor: pointer;
        transition: all var(--dur-fast) var(--ease-smooth);
        text-align: center;
    }
    .plan-card-label:hover { border-color: var(--clr-brand); transform: translateY(-1px); }
    .plan-card-label.selected-free  { border-color: var(--clr-brand2); background: rgba(16,185,129,.06); }
    .plan-card-label.selected-pro   { border-color: var(--clr-brand);  background: rgba(249,115,22,.06); }
    .plan-card-label.selected-team  { border-color: #8b5cf6;           background: rgba(139,92,246,.06); }

    /* Badge */
    .badge {
        display: inline-block; font-size: .625rem; font-weight: 700;
        padding: .15rem .45rem; border-radius: 999px;
        text-transform: uppercase; letter-spacing: .04em;
    }
    .badge-free   { background: rgba(16,185,129,.15); color: #065f46; }
    .badge-paid   { background: rgba(249,115,22,.15); color: #9a3412; }
    .badge-locked { background: rgba(156,163,175,.15); color: #6b7280; }

    /* Midtrans note */
    .midtrans-note {
        display: flex; align-items: center; gap: .5rem;
        font-size: .78rem; color: var(--clr-muted);
        background: rgba(249,115,22,.06);
        border: 1px solid rgba(249,115,22,.2);
        border-radius: var(--radius-md);
        padding: .625rem .875rem;
        margin-top: .75rem;
    }
    .midtrans-note.locked {
        background: rgba(156,163,175,.06);
        border-color: rgba(156,163,175,.2);
    }

    /* ── Error box ── */
    .error-box {
        padding: .75rem 1rem;
        background: rgba(239,68,68,.08);
        border: 1px solid rgba(239,68,68,.25);
        border-radius: var(--radius-md);
        color: #b91c1c;
        font-size: .85rem;
        margin-bottom: 1.25rem;
    }

    /* ── Success box ── */
    .success-box {
        padding: .75rem 1rem;
        background: rgba(16,185,129,.08);
        border: 1px solid rgba(16,185,129,.25);
        border-radius: var(--radius-md);
        color: #065f46;
        font-size: .85rem;
        margin-bottom: 1.25rem;
    }

    /* ── Password strength bar ── */
    .strength-bar { display: flex; gap: 3px; margin-top: .4rem; }
    .strength-seg {
        flex: 1; height: 3px; border-radius: 3px;
        background: rgba(0,0,0,.08);
        transition: background var(--dur-fast);
    }
    .strength-seg.filled-weak   { background: #ef4444; }
    .strength-seg.filled-fair   { background: #f59e0b; }
    .strength-seg.filled-good   { background: #10b981; }
    .strength-seg.filled-strong { background: #059669; }

    /* ── Checkbox ── */
    .custom-check {
        display: flex; align-items: flex-start; gap: .625rem;
        cursor: pointer; font-size: .85rem; color: var(--clr-muted);
    }
    .custom-check input[type=checkbox] {
        width: 16px; height: 16px; flex-shrink: 0; margin-top: .15rem;
        accent-color: var(--clr-brand);
    }

    /* ── Link ── */
    .auth-link {
        color: var(--clr-brand); font-weight: 500;
        text-decoration: none; transition: color var(--dur-fast);
    }
    .auth-link:hover { color: #ea6c10; text-decoration: underline; }

    /* ── Loading spinner ── */
    .btn-primary.loading { pointer-events: none; opacity: .75; }
    .spinner {
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Page entrance ── */
    @keyframes cardIn {
        from { opacity: 0; transform: translateY(24px) scale(.98); }
        to   { opacity: 1; transform: translateY(0)    scale(1);   }
    }
    .auth-card { animation: cardIn var(--dur-slow) var(--ease-spring) both; }

    /* ── Responsive ── */
    @media (max-width: 480px) {
        .auth-card { padding: 2rem 1.5rem; }
        .plan-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="auth-wrap">
    <div class="auth-card">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 mb-6">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, #f97316, #10b981);">
                <i class="fa-solid fa-bolt text-white text-sm"></i>
            </div>
            <span class="font-display font-800 text-lg" style="color: var(--clr-dark);">SFHUB</span>
        </a>

        {{-- Tab bar --}}
        <div class="tab-bar" role="tablist">
            <button class="tab-btn font-display {{ $initialMode === 'login' ? 'active' : '' }}"
                    id="tab-login" role="tab" data-target="pane-login">
                Masuk
            </button>
            <button class="tab-btn font-display {{ $initialMode === 'register' ? 'active' : '' }}"
                    id="tab-register" role="tab" data-target="pane-register">
                Daftar
            </button>
        </div>

        {{-- ═══════════════════════ LOGIN PANE ═══════════════════════ --}}
        <div id="pane-login" class="pane {{ $initialMode === 'login' ? 'active' : '' }}">

            <h2 class="font-display font-700 text-2xl mb-1" style="color: var(--clr-dark);">
                Selamat Datang! 👋
            </h2>
            <p class="text-sm mb-6" style="color: var(--clr-muted);">
                Lanjutkan ke dashboard produktivitasmu.
            </p>

            @if (session('status'))
                <div class="success-box">{{ session('status') }}</div>
            @endif
            @if (session('info'))
                <div class="midtrans-note mb-4">
                    <i class="fa-solid fa-circle-info text-orange-500"></i>
                    {{ session('info') }}
                </div>
            @endif
            @if ($hasLoginErrors)
                <div class="error-box">
                    @foreach ($errors->all() as $err)<div>{{ $err }}</div>@endforeach
                </div>
            @endif

            {{-- Google OAuth --}}
            @if ($googleEnabled)
            <a href="{{ route('auth.google') }}" class="btn-oauth mb-3" id="btn-google">
                <svg class="google-icon" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Masuk dengan Google
            </a>
            <div class="or-divider">atau masuk dengan email</div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="form-login" class="space-y-4">
                @csrf
                <div>
                    <label class="field-label" for="login-field">Email atau Username</label>
                    <div class="field-wrap">
                        <input id="login-field" class="field-input" type="text" name="login"
                               value="{{ old('login') }}" required autofocus
                               placeholder="email@contoh.com atau username">
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="field-label" for="login-password" style="margin:0">Password</label>
                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="auth-link text-xs">Lupa password?</a>
                        @endif
                    </div>
                    <div class="field-wrap">
                        <input id="login-password" class="field-input has-icon" type="password"
                               name="password" required placeholder="••••••••">
                        <button type="button" class="field-icon" data-toggle="login-password">
                            <i class="fa-regular fa-eye" id="eye-login-password"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="custom-check">
                        <input type="checkbox" name="remember">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary mt-2" id="btn-login-submit">
                    <span>Masuk ke Akun</span>
                    <i class="fa-solid fa-arrow-right text-sm"></i>
                </button>
            </form>

            <p class="text-center text-sm mt-5" style="color: var(--clr-muted);">
                Belum punya akun?
                <button class="auth-link font-semibold" id="to-register-link">Daftar gratis</button>
            </p>
        </div>

        {{-- ═══════════════════════ REGISTER PANE ═══════════════════════ --}}
        <div id="pane-register" class="pane {{ $initialMode === 'register' ? 'active' : '' }}">

            <h2 class="font-display font-700 text-2xl mb-1" style="color: var(--clr-dark);">
                Mulai Gratis Hari Ini ✨
            </h2>
            <p class="text-sm mb-6" style="color: var(--clr-muted);">
                Bergabung dengan ribuan mahasiswa produktif Indonesia.
            </p>

            @if ($hasRegisterErrors)
                <div class="error-box">
                    @foreach ($errors->all() as $err)<div>{{ $err }}</div>@endforeach
                </div>
            @endif

            {{-- Google OAuth --}}
            @if ($googleEnabled)
            <a href="{{ route('auth.google') }}" class="btn-oauth mb-3">
                <svg class="google-icon" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Daftar dengan Google
            </a>
            <div class="or-divider">atau daftar dengan email</div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="form-register" class="space-y-4">
                @csrf

                <div>
                    <label class="field-label" for="reg-name">Nama Lengkap</label>
                    <input id="reg-name" class="field-input" type="text" name="name"
                           value="{{ old('name') }}" required placeholder="Nama kamu">
                </div>

                <div>
                    <label class="field-label" for="reg-email">Email</label>
                    <input id="reg-email" class="field-input" type="email" name="email"
                           value="{{ old('email') }}" required placeholder="nama@email.com">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="field-label" for="reg-pass">Password</label>
                        <div class="field-wrap">
                            <input id="reg-pass" class="field-input has-icon" type="password"
                                   name="password" required placeholder="Min. 8 karakter"
                                   oninput="checkStrength(this.value)">
                            <button type="button" class="field-icon" data-toggle="reg-pass">
                                <i class="fa-regular fa-eye" id="eye-reg-pass"></i>
                            </button>
                        </div>
                        <div class="strength-bar" id="strength-bar">
                            <div class="strength-seg" id="s1"></div>
                            <div class="strength-seg" id="s2"></div>
                            <div class="strength-seg" id="s3"></div>
                            <div class="strength-seg" id="s4"></div>
                        </div>
                    </div>
                    <div>
                        <label class="field-label" for="reg-confirm">Konfirmasi</label>
                        <div class="field-wrap">
                            <input id="reg-confirm" class="field-input has-icon" type="password"
                                   name="password_confirmation" required placeholder="Ulangi password">
                            <button type="button" class="field-icon" data-toggle="reg-confirm">
                                <i class="fa-regular fa-eye" id="eye-reg-confirm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Plan selector --}}
                <div>
                    <label class="field-label">Paket Awal</label>
                    <div class="plan-grid" id="plan-selector">
                        @php $selPlan = old('plan', 'free'); @endphp

                        {{-- Free plan always shown --}}
                        <label class="plan-card-label {{ $selPlan === 'free' ? 'selected-free' : '' }}" id="label-free">
                            <input type="radio" name="plan" value="free" class="sr-only"
                                   {{ $selPlan === 'free' ? 'checked' : '' }}>
                            <span class="badge badge-free block mb-1">Free</span>
                            <div class="font-display font-700 text-sm" style="color: var(--clr-dark);">Mahasiswa</div>
                            <div class="text-xs mt-0.5" style="color: var(--clr-muted);">Gratis selamanya</div>
                        </label>

                        {{-- Pro/Team from DB or fallback --}}
                        @if ($activePlans->count() >= 1)
                            @php $proPlan = $activePlans->where('slug','pro')->first() ?? $activePlans->skip(0)->first(); @endphp
                            <label class="plan-card-label {{ $selPlan === 'pro' ? 'selected-pro' : '' }}" id="label-pro">
                                <input type="radio" name="plan" value="pro" class="sr-only"
                                       {{ $selPlan === 'pro' ? 'checked' : '' }}>
                                @if ($midtransReady)
                                    <span class="badge badge-paid block mb-1">Pro</span>
                                @else
                                    <span class="badge badge-locked block mb-1">Pro</span>
                                @endif
                                <div class="font-display font-700 text-sm" style="color: var(--clr-dark);">
                                    {{ $proPlan?->name ?? 'Kreator' }}
                                </div>
                                <div class="text-xs mt-0.5" style="color: var(--clr-muted);">
                                    @if($proPlan)
                                        Rp {{ number_format($proPlan->price_monthly,0,',','.') }}/bln
                                    @else
                                        Bayar lewat Midtrans
                                    @endif
                                </div>
                            </label>
                        @else
                        <label class="plan-card-label {{ $selPlan === 'pro' ? 'selected-pro' : '' }}" id="label-pro">
                            <input type="radio" name="plan" value="pro" class="sr-only"
                                   {{ $selPlan === 'pro' ? 'checked' : '' }}>
                            <span class="badge {{ $midtransReady ? 'badge-paid' : 'badge-locked' }} block mb-1">Pro</span>
                            <div class="font-display font-700 text-sm" style="color: var(--clr-dark);">Kreator</div>
                            <div class="text-xs mt-0.5" style="color: var(--clr-muted);">Rp 49k/bln</div>
                        </label>
                        @endif

                        @if ($activePlans->count() >= 2)
                            @php $teamPlan = $activePlans->where('slug','team')->first() ?? $activePlans->skip(1)->first(); @endphp
                            <label class="plan-card-label {{ $selPlan === 'team' ? 'selected-team' : '' }}" id="label-team">
                                <input type="radio" name="plan" value="team" class="sr-only"
                                       {{ $selPlan === 'team' ? 'checked' : '' }}>
                                @if ($midtransReady)
                                    <span class="badge badge-paid block mb-1">Tim</span>
                                @else
                                    <span class="badge badge-locked block mb-1">Tim</span>
                                @endif
                                <div class="font-display font-700 text-sm" style="color: var(--clr-dark);">
                                    {{ $teamPlan?->name ?? 'Tim' }}
                                </div>
                                <div class="text-xs mt-0.5" style="color: var(--clr-muted);">
                                    @if($teamPlan)
                                        Rp {{ number_format($teamPlan->price_monthly,0,',','.') }}/bln
                                    @else
                                        Bayar lewat Midtrans
                                    @endif
                                </div>
                            </label>
                        @else
                        <label class="plan-card-label {{ $selPlan === 'team' ? 'selected-team' : '' }}" id="label-team">
                            <input type="radio" name="plan" value="team" class="sr-only"
                                   {{ $selPlan === 'team' ? 'checked' : '' }}>
                            <span class="badge {{ $midtransReady ? 'badge-paid' : 'badge-locked' }} block mb-1">Tim</span>
                            <div class="font-display font-700 text-sm" style="color: var(--clr-dark);">Tim</div>
                            <div class="text-xs mt-0.5" style="color: var(--clr-muted);">Rp 99k/bln</div>
                        </label>
                        @endif
                    </div>

                    {{-- Midtrans status note --}}
                    <div id="midtrans-note" class="midtrans-note {{ $midtransReady ? '' : 'locked' }}"
                         style="{{ $selPlan === 'free' ? 'display:none' : '' }}">
                        @if ($midtransReady)
                            <i class="fa-brands fa-cc-mastercard text-orange-500"></i>
                            <span>Setelah daftar, kamu akan diarahkan ke halaman pembayaran Midtrans.</span>
                        @else
                            <i class="fa-solid fa-lock text-stone-400"></i>
                            <span>Pembayaran belum aktif. Admin perlu mengisi Midtrans key di
                                <strong>Settings → Payment</strong>.</span>
                        @endif
                    </div>
                </div>

                <label class="custom-check">
                    <input type="checkbox" name="terms" required
                           {{ old('terms') ? 'checked' : '' }}>
                    <span>Saya setuju dengan
                        <a href="#" class="auth-link">Ketentuan Layanan</a>
                        dan <a href="#" class="auth-link">Kebijakan Privasi</a>.
                    </span>
                </label>

                <button type="submit" class="btn-primary btn-emerald mt-1" id="btn-register-submit">
                    <span>Buat Akun SFHUB</span>
                    <i class="fa-solid fa-arrow-right text-sm"></i>
                </button>
            </form>

            <p class="text-center text-sm mt-5" style="color: var(--clr-muted);">
                Sudah punya akun?
                <button class="auth-link font-semibold" id="to-login-link">Masuk di sini</button>
            </p>
        </div>

        {{-- Back link --}}
        <div class="mt-6 pt-5 text-center" style="border-top: 1px solid var(--clr-border);">
            <a href="{{ route('home') }}" class="text-sm" style="color: var(--clr-muted);">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke beranda
            </a>
        </div>

    </div>{{-- /auth-card --}}
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Tab switching ─────────────────────────────────────────── */
    const tabs  = document.querySelectorAll('.tab-btn');
    const panes = document.querySelectorAll('.pane');

    function switchTab(targetId) {
        tabs.forEach(t => t.classList.toggle('active', t.dataset.target === targetId));
        panes.forEach(p => {
            if (p.id === targetId) {
                p.style.display = 'block';
                // Re-trigger animation
                p.classList.remove('active');
                requestAnimationFrame(() => p.classList.add('active'));
            } else {
                p.classList.remove('active');
                setTimeout(() => { if (!p.classList.contains('active')) p.style.display = 'none'; }, 400);
            }
        });
        // Update URL without reload
        const isLogin = targetId === 'pane-login';
        window.history.replaceState({}, '', isLogin ? '{{ route('login') }}' : '{{ route('register') }}');
    }

    tabs.forEach(t => t.addEventListener('click', () => switchTab(t.dataset.target)));

    // Inline switch links
    document.getElementById('to-register-link')?.addEventListener('click', () => switchTab('pane-register'));
    document.getElementById('to-login-link')?.addEventListener('click',    () => switchTab('pane-login'));

    /* ── Password toggle ───────────────────────────────────────── */
    document.querySelectorAll('[data-toggle]').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.toggle);
            const icon  = this.querySelector('i');
            if (!input) return;
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            icon.classList.toggle('fa-eye',       showing);
            icon.classList.toggle('fa-eye-slash', !showing);
        });
    });

    /* ── Password strength ─────────────────────────────────────── */
    window.checkStrength = function(pw) {
        const segs  = [1,2,3,4].map(i => document.getElementById('s'+i));
        let score   = 0;
        if (pw.length >= 8)           score++;
        if (/[A-Z]/.test(pw))         score++;
        if (/[0-9]/.test(pw))         score++;
        if (/[^A-Za-z0-9]/.test(pw))  score++;
        const clsMap = ['filled-weak','filled-fair','filled-good','filled-strong'];
        segs.forEach((s,i) => {
            s.className = 'strength-seg';
            if (i < score) s.classList.add(clsMap[Math.min(score-1, 3)]);
        });
    };

    /* ── Plan card highlight ───────────────────────────────────── */
    const planRadios = document.querySelectorAll('input[name="plan"]');
    const midtransNote = document.getElementById('midtrans-note');

    planRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Reset all labels
            document.getElementById('label-free')?.classList.remove('selected-free','selected-pro','selected-team');
            document.getElementById('label-pro')?.classList.remove('selected-free','selected-pro','selected-team');
            document.getElementById('label-team')?.classList.remove('selected-free','selected-pro','selected-team');

            // Highlight selected
            const val = this.value;
            if (val === 'free')  { document.getElementById('label-free')?.classList.add('selected-free'); }
            if (val === 'pro')   { document.getElementById('label-pro')?.classList.add('selected-pro');   }
            if (val === 'team')  { document.getElementById('label-team')?.classList.add('selected-team'); }

            // Show/hide Midtrans note
            if (midtransNote) {
                midtransNote.style.display = val === 'free' ? 'none' : 'flex';
            }
        });
    });

    /* ── Form submit loading state ─────────────────────────────── */
    function attachLoading(formId, btnId) {
        const form = document.getElementById(formId);
        const btn  = document.getElementById(btnId);
        if (!form || !btn) return;
        form.addEventListener('submit', function() {
            btn.classList.add('loading');
            btn.innerHTML = '<div class="spinner"></div><span>Memproses...</span>';
        });
    }
    attachLoading('form-login',    'btn-login-submit');
    attachLoading('form-register', 'btn-register-submit');

    /* ── Google button hover ripple ────────────────────────────── */
    document.getElementById('btn-google')?.addEventListener('click', function() {
        this.style.opacity = '.7';
    });

});
</script>
@endpush
