@php
    $initialMode = $mode ?? 'login';
    $registerErrorFields = ['name', 'email', 'password', 'password_confirmation', 'plan', 'terms'];
    $loginErrorFields = ['login', 'password'];
    if (collect($registerErrorFields)->contains(fn ($f) => $errors->has($f))) {
        $initialMode = 'register';
    } elseif (collect($loginErrorFields)->contains(fn ($f) => $errors->has($f))) {
        $initialMode = 'login';
    }

    $googleEnabled = !empty(config('services.google.client_id'));
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $initialMode === 'register' ? 'Daftar' : 'Login' }} - SFHUB</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Manrope:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-1: #8580a0;
            --bg-2: #6b6789;
            --card: #252136;
            --card-soft: #2f2a45;
            --line: rgba(255,255,255,.14);
            --txt: #ffffff;
            --muted: rgba(255,255,255,.62);
            --primary: #7456d9;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(165deg, var(--bg-1), var(--bg-2));
            color: var(--txt);
            font-family: Manrope, sans-serif;
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .shell {
            width: min(1020px, 100%);
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 14px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            box-shadow: 0 24px 64px rgba(0,0,0,.35);
            overflow: hidden;
        }
        .visual {
            border-radius: 14px;
            overflow: hidden;
            min-height: 620px;
            position: relative;
            transition: transform .45s ease;
            order: 1;
        }
        .visual::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 15%, rgba(116,86,217,.35), transparent 45%),
                        linear-gradient(155deg, rgba(36,31,58,.2), rgba(7,6,14,.55));
            z-index: 2;
        }
        .visual img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            filter: saturate(.92);
        }
        .visual-caption {
            position: absolute;
            left: 26px;
            right: 26px;
            bottom: 28px;
            z-index: 3;
            font-family: Outfit, sans-serif;
            font-size: 38px;
            line-height: 1.08;
            font-weight: 600;
        }
        .form-wrap {
            padding: 18px 18px 18px 12px;
            align-self: center;
            order: 2;
        }
        .title { font-family: Outfit, sans-serif; font-size: 48px; margin: 0 0 8px; line-height: 1; }
        .subtitle { color: var(--muted); margin: 0 0 22px; font-size: 14px; }
        .switch-link { color: #c5b8ff; text-decoration: none; font-weight: 700; border-bottom: 1px solid transparent; }
        .switch-link:hover { border-color: #c5b8ff; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .field { width: 100%; background: var(--card-soft); border: 1px solid var(--line); color: var(--txt); border-radius: 10px; padding: 12px 14px; font-size: 14px; }
        .field::placeholder { color: rgba(255,255,255,.4); }
        .field:focus { outline: none; border-color: #8c73e3; }
        .pass-wrap { position: relative; }
        .eye { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); border: 0; background: transparent; color: var(--muted); cursor: pointer; }
        .terms { margin: 12px 0 16px; color: var(--muted); font-size: 13px; display: flex; gap: 10px; align-items: flex-start; }
        .btn-main { width: 100%; border: 0; border-radius: 10px; padding: 12px 16px; font-size: 15px; font-weight: 700; color: #fff; background: var(--primary); cursor: pointer; }
        .btn-main:hover { filter: brightness(1.06); }
        .divider { color: var(--muted); font-size: 12px; text-align: center; margin: 16px 0; display: flex; align-items: center; gap: 10px; }
        .divider::before, .divider::after { content: ""; height: 1px; flex: 1; background: var(--line); }
        .btn-google { width: 100%; display: flex; align-items: center; justify-content: center; gap: 9px; border-radius: 10px; border: 1px solid var(--line); padding: 11px 14px; color: #fff; text-decoration: none; font-size: 14px; }
        .btn-google:hover { background: rgba(255,255,255,.04); }
        .error-box { margin-bottom: 14px; background: rgba(239,68,68,.16); border: 1px solid rgba(239,68,68,.4); color: #fecaca; border-radius: 10px; padding: 10px 12px; font-size: 13px; }
        .hidden { display: none; }
        .mode-register .visual { order: 2; }
        .mode-register .form-wrap { order: 1; padding: 18px 12px 18px 18px; }
        @media (max-width: 900px) {
            .shell { grid-template-columns: 1fr; }
            .visual { min-height: 260px; }
            .visual-caption { font-size: 24px; }
            .mode-register .visual, .mode-register .form-wrap { order: initial; padding: 0; }
            .form-wrap { padding: 4px; }
            .title { font-size: 38px; }
        }
    </style>
</head>
<body>
<div class="shell {{ $initialMode === 'register' ? 'mode-register' : '' }}" id="morph-shell">
    <section class="visual">
        <img src="https://images.unsplash.com/photo-1682686581498-5e85c7228119?auto=format&fit=crop&w=1200&q=80" alt="Visual">
        <div class="visual-caption">Capturing Moments,<br>Creating Memories</div>
    </section>

    <section class="form-wrap">
        <div id="login-pane" class="{{ $initialMode === 'register' ? 'hidden' : '' }}">
            <h1 class="title">Login</h1>
            <p class="subtitle">Belum punya akun? <a href="#" class="switch-link" data-mode="register">Daftar</a></p>
            @if(session('error'))<div class="error-box">{{ session('error') }}</div>@endif
            @if($errors->has('login') || $errors->has('password'))
                <div class="error-box">{{ $errors->first('login') ?: $errors->first('password') }}</div>
            @endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input class="field" type="text" name="login" placeholder="Email atau username" value="{{ old('login') }}" required>
                <div class="pass-wrap" style="margin-top:10px;">
                    <input class="field" id="login-password" type="password" name="password" placeholder="Password" required>
                    <button type="button" class="eye" data-eye="login-password">👁</button>
                </div>
                <label class="terms"><input type="checkbox" name="remember"> Ingat saya</label>
                <button class="btn-main" type="submit">Masuk</button>
            </form>
        </div>

        <div id="register-pane" class="{{ $initialMode === 'register' ? '' : 'hidden' }}">
            <h1 class="title">Create an account</h1>
            <p class="subtitle">Sudah punya akun? <a href="#" class="switch-link" data-mode="login">Login</a></p>
            @if($errors->has('name') || $errors->has('email') || $errors->has('password') || $errors->has('terms'))
                <div class="error-box">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="grid-2">
                    <input class="field" type="text" name="name" placeholder="Nama lengkap" value="{{ old('name') }}" required>
                    <input class="field" type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
                </div>
                <div class="pass-wrap" style="margin-top:10px;">
                    <input class="field" id="register-password" type="password" name="password" placeholder="Password" required>
                    <button type="button" class="eye" data-eye="register-password">👁</button>
                </div>
                <div class="pass-wrap" style="margin-top:10px;">
                    <input class="field" id="register-password-confirm" type="password" name="password_confirmation" placeholder="Konfirmasi password" required>
                    <button type="button" class="eye" data-eye="register-password-confirm">👁</button>
                </div>
                <input type="hidden" name="plan" value="free">
                <label class="terms"><input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required> Saya setuju dengan Terms & Conditions.</label>
                <button class="btn-main" type="submit">Create account</button>
            </form>
        </div>

        @if($googleEnabled)
            <div class="divider">Atau lanjut dengan</div>
            <a href="{{ route('auth.google') }}" class="btn-google">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23.52 12.27c0-.82-.07-1.49-.22-2.19H12.24v4.14h6.49c-.13 1.03-.86 2.58-2.48 3.62l-.02.14 3.5 2.66.24.02c2.22-2 3.51-4.96 3.51-8.39Z" fill="#4285F4"/><path d="M12.24 23.71c3.18 0 5.85-1.03 7.8-2.81l-3.72-2.82c-.99.67-2.33 1.15-4.08 1.15-3.12 0-5.77-2-6.72-4.76l-.14.01-3.64 2.77-.05.13c1.94 3.76 5.91 6.33 10.55 6.33Z" fill="#34A853"/><path d="M5.52 14.47a6.91 6.91 0 0 1-.4-2.29c0-.8.15-1.56.39-2.29l-.01-.15-3.69-2.82-.12.06A11.38 11.38 0 0 0 .49 12.18c0 1.83.43 3.57 1.2 5.2l3.83-2.9Z" fill="#FBBC05"/><path d="M12.24 5.14c2.21 0 3.7.93 4.56 1.7l3.33-3.17C18.08 1.83 15.42.65 12.24.65 7.6.65 3.63 3.22 1.69 6.98l3.82 2.9c.96-2.76 3.61-4.74 6.73-4.74Z" fill="#EA4335"/></svg>
                Login dengan Google
            </a>
        @endif
    </section>
</div>

<script>
(() => {
    const shell = document.getElementById('morph-shell');
    const loginPane = document.getElementById('login-pane');
    const registerPane = document.getElementById('register-pane');

    function setMode(mode) {
        const isRegister = mode === 'register';
        shell.classList.toggle('mode-register', isRegister);
        loginPane.classList.toggle('hidden', isRegister);
        registerPane.classList.toggle('hidden', !isRegister);
        history.replaceState({}, '', isRegister ? '{{ route('register') }}' : '{{ route('login') }}');
    }

    document.querySelectorAll('[data-mode]').forEach(el => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            setMode(el.getAttribute('data-mode'));
        });
    });

    document.querySelectorAll('[data-eye]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.getAttribute('data-eye'));
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
})();
</script>
</body>
</html>
