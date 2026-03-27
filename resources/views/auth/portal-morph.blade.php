@php
    $initialMode = $mode ?? 'login';
    $registerErrorFields = ['name', 'email', 'password', 'password_confirmation', 'plan', 'terms'];
    $loginErrorFields = ['login', 'password'];
    if (collect($registerErrorFields)->contains(fn($f) => $errors->has($f))) {
        $initialMode = 'register';
    } elseif (collect($loginErrorFields)->contains(fn($f) => $errors->has($f))) {
        $initialMode = 'login';
    }

    $googleEnabled = !empty(config('services.google.client_id'));

    // Check Midtrans configuration
    $midtransServerKey = \App\Models\SystemSetting::get('midtrans_server_key');
    $midtransClientKey = \App\Models\SystemSetting::get('midtrans_client_key');
    $midtransReady = !empty($midtransServerKey) && !empty($midtransClientKey);
    $midtransMode = \App\Models\SystemSetting::get('midtrans_environment', 'sandbox');

    $activePlans = \App\Models\SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
    $selectedPlan = old('plan', 'free');

    // Get site settings for navbar
    $siteName = \App\Models\SystemSetting::get('site_name', 'SFHUB');
    $siteLogo = \App\Models\SiteSetting::getValue('site_logo');
@endphp
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $initialMode === 'register' ? 'Daftar' : 'Login' }} - {{ $siteName }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
        };
    </script>
    <style>
        :root {
            --bg: #fafaf9;
            --surface: #ffffff;
            --surface-2: #f5f5f4;
            --text: #1c1917;
            --muted: #78716c;
            --border: #e7e5e4;
            --brand: #f97316;
            --brand-soft: #fb923c;
            --brand-strong: #ea580c;
        }

        .dark {
            --bg: #0c0a09;
            --surface: #1c1917;
            --surface-2: #292524;
            --text: #f5f5f4;
            --muted: #a8a29e;
            --border: #44403c;
            --brand: #f97316;
            --brand-soft: #fb923c;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background: var(--bg);
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* Navbar Styles - Same as Landing */
        .auth-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 60;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            background: rgba(255, 255, 255, 0.85);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(12px);
            transition: all 0.3s ease;
        }

        .dark .auth-nav {
            background: rgba(28, 25, 23, 0.85);
            border-color: #44403c;
        }

        .auth-nav-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text);
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.02em;
        }

        .auth-nav-pill {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(140deg, var(--brand), #fb7185);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 1rem;
        }

        .auth-nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .auth-nav-link {
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            color: var(--muted);
            padding: 8px 16px;
            border-radius: 999px;
            transition: all 0.2s ease;
        }

        .auth-nav-link:hover {
            color: var(--brand);
        }

        .theme-toggle {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .theme-toggle:hover {
            border-color: var(--brand);
            color: var(--brand);
        }

        /* Full Width Auth Layout */
        .auth-page {
            min-height: 100vh;
            padding-top: 90px;
            display: flex;
            background:
                radial-gradient(circle at 15% 20%, rgba(249, 115, 22, 0.08), transparent 40%),
                radial-gradient(circle at 85% 80%, rgba(59, 130, 246, 0.05), transparent 35%),
                var(--bg);
        }

        .auth-container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 24px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        /* Visual Side */
        .auth-visual {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
        }

        .auth-visual h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 24px;
            background: linear-gradient(135deg, var(--text) 0%, var(--muted) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .dark .auth-visual h1 {
            background: linear-gradient(135deg, #f5f5f4 0%, #a8a29e 100%);
            -webkit-background-clip: text;
        }

        .auth-visual p {
            font-size: 1.125rem;
            color: var(--muted);
            line-height: 1.7;
            max-width: 480px;
        }

        .feature-list {
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            color: var(--text);
        }

        .feature-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--brand-soft), var(--brand));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        /* Form Side */
        .auth-form-wrap {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 48px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
        }

        .dark .auth-form-wrap {
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2);
        }

        .auth-panes {
            position: relative;
            min-height: 520px;
        }

        .auth-pane {
            position: relative;
            inset: auto;
            opacity: 0;
            visibility: hidden;
            transform: translateX(20px);
            transition: opacity 0.4s ease, transform 0.4s cubic-bezier(0.22, 1, 0.36, 1), visibility 0s linear 0.4s;
            display: none;
        }

        .auth-pane.active {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
            transition-delay: 0s;
            display: block;
        }

        .auth-pane.exiting {
            opacity: 0;
            transform: translateX(-20px);
        }

        .auth-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text);
        }

        .auth-subtitle {
            font-size: 15px;
            color: var(--muted);
            margin-bottom: 32px;
        }

        .auth-switch {
            color: var(--brand);
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .auth-switch:hover {
            color: var(--brand-strong);
            text-decoration: underline;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 500;
            color: var(--muted);
        }

        .form-input {
            width: 100%;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 14px 16px;
            font-size: 15px;
            background: var(--surface-2);
            color: var(--text);
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12);
            background: var(--surface);
        }

        .form-input::placeholder {
            color: #a8a29e;
        }

        .input-wrap {
            position: relative;
        }

        .eye-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
        }

        .eye-btn:hover {
            color: var(--text);
        }

        /* Plan Selection */
        .plan-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 8px;
        }

        .plan-card {
            border: 2px solid var(--border);
            border-radius: 14px;
            padding: 16px 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            background: var(--surface);
        }

        .plan-card:hover:not(.disabled) {
            border-color: var(--brand-soft);
            transform: translateY(-2px);
        }

        .plan-card.selected {
            border-color: var(--brand);
            background: rgba(249, 115, 22, 0.05);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.15);
        }

        .plan-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            position: relative;
        }

        .plan-card.disabled::after {
            content: '🔒';
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 12px;
        }

        .plan-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 8px;
            background: var(--surface-2);
            color: var(--muted);
        }

        .plan-card.selected .plan-badge {
            background: var(--brand);
            color: white;
        }

        .plan-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--text);
            margin-bottom: 4px;
        }

        .plan-price {
            font-size: 12px;
            color: var(--muted);
        }

        /* Alert Box */
        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
        }

        .dark .alert-info {
            background: rgba(30, 64, 175, 0.2);
            border-color: rgba(59, 130, 246, 0.3);
            color: #60a5fa;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .dark .alert-error {
            background: rgba(220, 38, 38, 0.2);
            border-color: rgba(248, 113, 113, 0.3);
            color: #f87171;
        }

        .alert-warn {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            color: #b45309;
        }

        .dark .alert-warn {
            background: rgba(180, 83, 9, 0.2);
            border-color: rgba(251, 191, 36, 0.3);
            color: #fbbf24;
        }

        /* Buttons */
        .btn-primary {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 16px;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, var(--brand), var(--brand-strong));
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.35);
        }

        .btn-google {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 14px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text);
            background: var(--surface);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-google:hover {
            border-color: var(--brand);
            background: var(--surface-2);
        }

        .divider {
            color: var(--muted);
            font-size: 13px;
            text-align: center;
            margin: 24px 0;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .divider::before,
        .divider::after {
            content: "";
            height: 1px;
            flex: 1;
            background: var(--border);
        }

        .terms-check {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 20px 0;
            font-size: 13px;
            color: var(--muted);
        }

        .terms-check input {
            margin-top: 2px;
        }

        /* Setup Alert for Admin */
        .setup-alert {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #f59e0b;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #92400e;
        }

        .dark .setup-alert {
            background: linear-gradient(135deg, rgba(180, 83, 9, 0.3) 0%, rgba(146, 64, 14, 0.3) 100%);
            border-color: #f59e0b;
            color: #fbbf24;
        }

        .setup-alert strong {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .setup-alert code {
            background: rgba(0, 0, 0, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .auth-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .auth-visual {
                display: none;
            }

            .auth-form-wrap {
                max-width: 480px;
                margin: 0 auto;
                width: 100%;
            }
        }

        @media (max-width: 640px) {
            .auth-nav {
                height: 60px;
                padding: 0 16px;
            }

            .auth-page {
                padding-top: 76px;
            }

            .auth-form-wrap {
                padding: 32px 24px;
                border-radius: 20px;
            }

            .plan-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    {{-- Navbar - Same as Landing Page --}}
    <nav class="auth-nav">
        <a href="{{ route('home') }}" class="auth-nav-brand">
            <span class="auth-nav-pill"><i class="fa-solid fa-layer-group"></i></span>
            <span>Student<span style="color: var(--brand);">Hub</span></span>
        </a>
        <div class="auth-nav-actions">
            <a href="{{ route('home') }}" class="auth-nav-link">Home</a>
            <button class="theme-toggle" id="theme-toggle" aria-label="Toggle theme">
                <i class="fa-solid fa-moon" id="theme-icon"></i>
            </button>
        </div>
    </nav>

    <div class="auth-page">
        <div class="auth-container">
            {{-- Visual Side --}}
            <div class="auth-visual">
                <h1>Belajar. Berkarya.<br>Naik Level.</h1>
                <p>Kelola kuliah, freelance, dan investasi kamu dari satu dashboard yang dirancang khusus untuk
                    mahasiswa Indonesia yang ambisius.</p>

                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                        <span>Academic Hub & Manajemen Skripsi</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-briefcase"></i></div>
                        <span>PKL Manager & Laporan Otomatis</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-chart-line"></i></div>
                        <span>Finance & Investment Tracker</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fa-solid fa-calendar-check"></i></div>
                        <span>Smart Calendar Terintegrasi</span>
                    </div>
                </div>
            </div>

            {{-- Form Side --}}
            <div class="auth-form-wrap">
                <div class="auth-panes" id="auth-panes">
                    {{-- Login Pane --}}
                    <div id="login-pane" class="auth-pane {{ $initialMode === 'login' ? 'active' : '' }}">
                        <h2 class="auth-title">Selamat Datang Kembali</h2>
                        <p class="auth-subtitle">Belum punya akun? <span class="auth-switch" data-mode="register">Daftar
                                gratis</span></p>

                        @if (session('info'))
                            <div class="alert alert-info">
                                <i class="fa-solid fa-circle-info"></i>
                                {{ session('info') }}
                            </div>
                        @endif

                        @if ($errors->has('login') || $errors->has('password'))
                            <div class="alert alert-error">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $errors->first('login') ?: $errors->first('password') }}
                            </div>
                        @endif

                        @if ($googleEnabled)
                            <a href="{{ route('auth.google') }}" class="btn-google">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M23.52 12.27c0-.82-.07-1.49-.22-2.19H12.24v4.14h6.49c-.13 1.03-.86 2.58-2.48 3.62l-.02.14 3.5 2.66.24.02c2.22-2 3.51-4.96 3.51-8.39Z"
                                        fill="#4285F4" />
                                    <path
                                        d="M12.24 23.71c3.18 0 5.85-1.03 7.8-2.81l-3.72-2.82c-.99.67-2.33 1.15-4.08 1.15-3.12 0-5.77-2-6.72-4.76l-.14.01-3.64 2.77-.05.13c1.94 3.76 5.91 6.33 10.55 6.33Z"
                                        fill="#34A853" />
                                    <path
                                        d="M5.52 14.47a6.91 6.91 0 0 1-.4-2.29c0-.8.15-1.56.39-2.29l-.01-.15-3.69-2.82-.12.06A11.38 11.38 0 0 0 .49 12.18c0 1.83.43 3.57 1.2 5.2l3.83-2.9Z"
                                        fill="#FBBC05" />
                                    <path
                                        d="M12.24 5.14c2.21 0 3.7.93 4.56 1.7l3.33-3.17C18.08 1.83 15.42.65 12.24.65 7.6.65 3.63 3.22 1.69 6.98l3.82 2.9c.96-2.76 3.61-4.74 6.73-4.74Z"
                                        fill="#EA4335" />
                                </svg>
                                Masuk dengan Google
                            </a>
                            <div class="divider">atau dengan email</div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Email atau Username</label>
                                <input type="text" name="login" class="form-input" placeholder="email@contoh.com"
                                    value="{{ old('login') }}" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Password</label>
                                <div class="input-wrap">
                                    <input type="password" name="password" id="login-password" class="form-input"
                                        placeholder="••••••••" required>
                                    <button type="button" class="eye-btn" data-eye="login-password"><i
                                            class="fa-regular fa-eye"></i></button>
                                </div>
                            </div>

                            <label class="terms-check">
                                <input type="checkbox" name="remember">
                                <span>Ingat saya di perangkat ini</span>
                            </label>

                            <button type="submit" class="btn-primary">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                Masuk ke Dashboard
                            </button>
                        </form>
                    </div>

                    {{-- Register Pane --}}
                    <div id="register-pane" class="auth-pane {{ $initialMode === 'register' ? 'active' : '' }}">
                        <h2 class="auth-title">Buat Akun Baru</h2>
                        <p class="auth-subtitle">Sudah punya akun? <span class="auth-switch"
                                data-mode="login">Masuk</span></p>

                        @if (
                            $errors->any() &&
                                ($errors->has('name') || $errors->has('email') || $errors->has('password') || $errors->has('terms')))
                            <div class="alert alert-error">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif

                        {{-- Midtrans Setup Alert --}}
                        @if (!$midtransReady)
                            <div class="setup-alert">
                                <strong><i class="fa-solid fa-triangle-exclamation mr-2"></i>Pembayaran Belum
                                    Siap</strong>
                                Paket berbayar dinonaktifkan karena Midtrans belum dikonfigurasi.
                                @if (auth()->check() && auth()->user()->isAdmin())
                                    <br><br>
                                    <strong>Setup yang diperlukan:</strong>
                                    <ul style="margin: 8px 0; padding-left: 20px;">
                                        <li><code>midtrans_server_key</code> (Server Key dari Midtrans)</li>
                                        <li><code>midtrans_client_key</code> (Client Key dari Midtrans)</li>
                                        <li><code>midtrans_environment</code> (sandbox/production)</li>
                                    </ul>
                                    <a href="{{ route('admin.settings.index') }}"
                                        style="color: inherit; text-decoration: underline;">Konfigurasi di Admin
                                        Settings →</a>
                                @else
                                    Hubungi admin untuk mengaktifkan pembayaran.
                                @endif
                            </div>
                        @endif
                        <div class="divider">...</div>
                        <form method="POST" action="{{ route('register') }}" id="register-form">
                            {{-- Plan Selection --}}
                            <div class="form-group">
                                <label class="form-label">Pilih Paket</label>
                                <div class="plan-grid" id="plan-selector">
                                    {{-- FREE Plan (selalu tersedia) --}}
                                    <label class="plan-card {{ $selectedPlan === 'free' ? 'selected' : '' }}"
                                        data-plan="free">
                                        <input type="radio" name="plan" value="free"
                                            {{ $selectedPlan === 'free' ? 'checked' : '' }} hidden>
                                        <span class="plan-badge">FREE</span>
                                        <div class="plan-name">Mahasiswa</div>
                                        <div class="plan-price">Gratis</div>
                                    </label>

                                    {{-- Paid Plans dari Database - hanya yang ada --}}
                                    @foreach ($activePlans->where('price_monthly', '>', 0) as $plan)
                                        @php
                                            $planSlug = $plan->slug ?? strtolower($plan->name);
                                            $isSelected = $selectedPlan === $planSlug;
                                            $isDisabled = !$midtransReady;
                                        @endphp
                                        <label
                                            class="plan-card {{ $isSelected ? 'selected' : '' }} {{ $isDisabled ? 'disabled' : '' }}"
                                            data-plan="{{ $planSlug }}">
                                            <input type="radio" name="plan" value="{{ $planSlug }}"
                                                {{ $isSelected ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}
                                                hidden>
                                            <span class="plan-badge">{{ strtoupper($plan->name) }}</span>
                                            <div class="plan-name">
                                                {{ $plan->description ? explode('.', $plan->description)[0] : 'Premium' }}
                                            </div>
                                            <div class="plan-price">
                                                Rp{{ number_format($plan->price_monthly, 0, ',', '.') }}/bln
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                @if ($midtransReady && $activePlans->where('price_monthly', '>', 0)->count() > 0)
                                    <div id="payment-hint" class="alert alert-info"
                                        style="margin-top: 12px; margin-bottom: 0; {{ $selectedPlan === 'free' ? 'display: none;' : '' }}">
                                        <i class="fa-solid fa-credit-card"></i>
                                        <span>Anda akan diarahkan ke Midtrans untuk pembayaran setelah membuat
                                            akun.</span>
                                    </div>
                                @endif
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="name" class="form-input" placeholder="Nama lengkap"
                                    value="{{ old('name') }}" required>
                            </div>
                            <div class="form-group" style="margin-bottom: 16px;">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-input"
                                    placeholder="email@contoh.com" value="{{ old('email') }}" required>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group" style="margin-top: 16px;">
                                    <label class="form-label">Password</label>
                                    <div class="input-wrap">
                                        <input type="password" name="password" id="register-password"
                                            class="form-input" placeholder="Minimal 8 karakter" required>
                                        <button type="button" class="eye-btn" data-eye="register-password"><i
                                                class="fa-regular fa-eye"></i></button>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-top: 16px">
                                    <label class="form-label">Konfirmasi Password</label>
                                    <div class="input-wrap">
                                        <input type="password" name="password_confirmation"
                                            id="register-password-confirm" class="form-input"
                                            placeholder="Ulangi password" required>
                                        <button type="button" class="eye-btn"
                                            data-eye="register-password-confirm"><i
                                                class="fa-regular fa-eye"></i></button>
                                    </div>
                                </div>
                            </div>

                            <label class="terms-check">
                                <input type="checkbox" name="terms" {{ old('terms') ? 'checked' : '' }} required>
                                <span>Saya setuju dengan <a href="#" style="color: var(--brand);">Terms &
                                        Conditions</a> dan <a href="#" style="color: var(--brand);">Privacy
                                        Policy</a></span>
                            </label>

                            <button type="submit" class="btn-primary" id="btn-register">
                                <i class="fa-solid fa-user-plus"></i>
                                <span id="btn-register-text">Buat Akun</span>
                            </button>
                        </form>

                        @if ($googleEnabled)
                            <div class="divider">atau daftar dengan</div>
                            <a href="{{ route('auth.google') }}" class="btn-google">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M23.52 12.27c0-.82-.07-1.49-.22-2.19H12.24v4.14h6.49c-.13 1.03-.86 2.58-2.48 3.62l-.02.14 3.5 2.66.24.02c2.22-2 3.51-4.96 3.51-8.39Z"
                                        fill="#4285F4" />
                                    <path
                                        d="M12.24 23.71c3.18 0 5.85-1.03 7.8-2.81l-3.72-2.82c-.99.67-2.33 1.15-4.08 1.15-3.12 0-5.77-2-6.72-4.76l-.14.01-3.64 2.77-.05.13c1.94 3.76 5.91 6.33 10.55 6.33Z"
                                        fill="#34A853" />
                                    <path
                                        d="M5.52 14.47a6.91 6.91 0 0 1-.4-2.29c0-.8.15-1.56.39-2.29l-.01-.15-3.69-2.82-.12.06A11.38 11.38 0 0 0 .49 12.18c0 1.83.43 3.57 1.2 5.2l3.83-2.9Z"
                                        fill="#FBBC05" />
                                    <path
                                        d="M12.24 5.14c2.21 0 3.7.93 4.56 1.7l3.33-3.17C18.08 1.83 15.42.65 12.24.65 7.6.65 3.63 3.22 1.69 6.98l3.82 2.9c.96-2.76 3.61-4.74 6.73-4.74Z"
                                        fill="#EA4335" />
                                </svg>
                                Daftar dengan Google
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');

        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }

        themeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            const isDark = document.documentElement.classList.contains('dark');
            localStorage.setItem('color-theme', isDark ? 'dark' : 'light');
            themeIcon.classList.toggle('fa-moon');
            themeIcon.classList.toggle('fa-sun');
        });

        // Smooth Morph Animation
        const panes = document.getElementById('auth-panes');
        const loginPane = document.getElementById('login-pane');
        const registerPane = document.getElementById('register-pane');

        function setMode(mode) {
            const isRegister = mode === 'register';

            if (isRegister) {
                loginPane.classList.add('exiting');
                loginPane.classList.remove('active');
                setTimeout(() => {
                    loginPane.classList.remove('exiting');
                    registerPane.classList.add('active');
                }, 200);
            } else {
                registerPane.classList.add('exiting');
                registerPane.classList.remove('active');
                setTimeout(() => {
                    registerPane.classList.remove('exiting');
                    loginPane.classList.add('active');
                }, 200);
            }

            history.replaceState({}, '', isRegister ? '{{ route('register') }}' : '{{ route('login') }}');
        }

        document.querySelectorAll('.auth-switch, [data-mode]').forEach(el => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                const mode = el.getAttribute('data-mode');
                if (mode) setMode(mode);
            });
        });

        // Password Toggle
        document.querySelectorAll('[data-eye]').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.getAttribute('data-eye'));
                if (!input) return;
                const icon = btn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });

        // Plan Selection
        const planSelector = document.getElementById('plan-selector');
        const paymentHint = document.getElementById('payment-hint');
        const btnRegister = document.getElementById('btn-register');
        const btnRegisterText = document.getElementById('btn-register-text');

        if (planSelector) {
            planSelector.querySelectorAll('.plan-card').forEach(card => {
                card.addEventListener('click', () => {
                    if (card.classList.contains('disabled')) return;

                    const radio = card.querySelector('input[type="radio"]');
                    if (!radio) return;

                    radio.checked = true;
                    planSelector.querySelectorAll('.plan-card').forEach(c => c.classList.remove(
                        'selected'));
                    card.classList.add('selected');

                    const plan = card.getAttribute('data-plan');

                    // Show/hide payment hint
                    if (paymentHint) {
                        paymentHint.style.display = plan === 'free' ? 'none' : 'flex';
                    }

                    // Update button text
                    if (btnRegisterText) {
                        if (plan === 'free') {
                            btnRegisterText.textContent = 'Buat Akun Gratis';
                        } else {
                            btnRegisterText.textContent = 'Lanjut ke Pembayaran';
                        }
                    }
                });
            });
        }

        // Form submission handler for paid plans
        document.getElementById('register-form')?.addEventListener('submit', function(e) {
            const selectedPlan = document.querySelector('input[name="plan"]:checked')?.value;

            @if (!$midtransReady)
                if (selectedPlan !== 'free') {
                    e.preventDefault();
                    alert(
                        'Paket berbayar tidak tersedia. Silakan pilih paket Free atau hubungi admin untuk mengaktifkan pembayaran.'
                    );
                    return false;
                }
            @endif
        });
    </script>
</body>

</html>
