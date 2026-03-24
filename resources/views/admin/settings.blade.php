{{--
    Admin Settings — tabs: General · Security · Email · Payment · Social · OAuth · Maintenance
    Payment tab now includes: Midtrans connection wizard, status indicator, webhook URL copy
    OAuth tab: Google, GitHub toggle with client_id/secret fields
--}}

@extends('layouts.app')

@section('title', 'System Settings | SFHUB Admin')
@section('page-title', 'System Settings')

@section('content')
    <div class="animate-fade-in-up space-y-6">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">System Settings</h2>
                <p class="text-stone-500 dark:text-stone-400 text-sm">Kelola pengaturan sistem platform</p>
            </div>
            <button onclick="saveSettings()"
                class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-all shadow-sm hover:shadow-md">
                <i class="fa-solid fa-save"></i> Simpan Semua
            </button>
        </div>

        {{-- Tabs --}}
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl p-2 border border-stone-200 dark:border-stone-800 overflow-x-auto">
            <div class="flex gap-1 min-w-max">
                @foreach ([['general', 'fa-sliders', 'General'], ['security', 'fa-shield-halved', 'Security'], ['email', 'fa-envelope', 'Email'], ['payment', 'fa-credit-card', 'Payment'], ['oauth', 'fa-key', 'OAuth / SSO'], ['social', 'fa-share-nodes', 'Social'], ['maintenance', 'fa-wrench', 'Maintenance']] as [$id, $icon, $label])
                    <button onclick="switchTab('{{ $id }}')"
                        class="tab-btn px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all
                       {{ $id === 'general' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800' }}"
                        data-tab="{{ $id }}">
                        <i class="fa-solid {{ $icon }} mr-1.5"></i>{{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ══════════ GENERAL ══════════ --}}
        <div id="tab-general"
            class="tab-content bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
            <h3 class="font-bold text-stone-900 dark:text-white mb-6">General Settings</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <x-setting-field id="setting-site-name" label="Site Name" type="text" :value="$settings['site_name'] ?? 'Student-Freelancer Hub'" />
                    <x-setting-field id="setting-site-url" label="Site URL" type="url" :value="$settings['site_url'] ?? url('/')" />
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Timezone</label>
                        <select id="setting-timezone" class="setting-input">
                            @foreach (['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'] as $tz)
                                <option value="{{ $tz }}"
                                    {{ ($settings['timezone'] ?? 'Asia/Jakarta') === $tz ? 'selected' : '' }}>
                                    {{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Language</label>
                        <select id="setting-language" class="setting-input">
                            <option value="id" {{ ($settings['language'] ?? 'id') === 'id' ? 'selected' : '' }}>
                                Indonesian</option>
                            <option value="en" {{ ($settings['language'] ?? '') === 'en' ? 'selected' : '' }}>English
                            </option>
                        </select>
                    </div>
                </div>
                <div class="space-y-4">
                    <x-setting-field id="setting-contact-email" label="Contact Email" type="email" :value="$settings['contact_email'] ?? ''" />
                    <x-setting-field id="setting-contact-phone" label="Support Phone" type="text" :value="$settings['contact_phone'] ?? ''" />
                    <x-setting-field id="setting-copyright" label="Copyright Text" type="text" :value="$settings['copyright_text'] ?? '© 2025 SFHUB'" />
                    <x-setting-toggle id="setting-registration" label="Enable User Registration" :checked="$settings['user_registration'] ?? true" />
                </div>
            </div>
        </div>

        {{-- ══════════ SECURITY ══════════ --}}
        <div id="tab-security"
            class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
            <h3 class="font-bold text-stone-900 dark:text-white mb-6">Security Settings</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <x-setting-toggle id="setting-2fa" label="Force 2FA (all admins)"
                        subtext="Require 2FA for all admin accounts" :checked="$settings['force_2fa'] ?? false" />
                    <x-setting-toggle id="setting-email-verify" label="Email Verification"
                        subtext="Require email verification on register" :checked="$settings['email_verification'] ?? true" />
                </div>
                <div class="space-y-4">
                    <x-setting-field id="setting-login-attempts" label="Max Login Attempts" type="number" :value="$settings['max_login_attempts'] ?? 5"
                        help="Lockout after N failed attempts" />
                    <x-setting-field id="setting-session-timeout" label="Session Timeout (min)" type="number"
                        :value="$settings['session_timeout'] ?? 120" />
                    <x-setting-field id="setting-password-length" label="Password Min Length" type="number"
                        :value="$settings['password_min_length'] ?? 8" />
                </div>
            </div>
        </div>

        {{-- ══════════ EMAIL ══════════ --}}
        <div id="tab-email"
            class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
            <h3 class="font-bold text-stone-900 dark:text-white mb-6">Email / SMTP Settings</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <x-setting-field id="setting-smtp-host" label="SMTP Host" type="text" :value="$settings['mail_host'] ?? ''"
                        placeholder="smtp.gmail.com" />
                    <div class="grid grid-cols-2 gap-3">
                        <x-setting-field id="setting-smtp-port" label="Port" type="number" :value="$settings['mail_port'] ?? 587" />
                        <div>
                            <label
                                class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Encryption</label>
                            <select id="setting-smtp-encryption" class="setting-input">
                                @foreach (['tls' => 'TLS (587)', 'ssl' => 'SSL (465)', '' => 'None'] as $val => $lbl)
                                    <option value="{{ $val }}"
                                        {{ ($settings['mail_encryption'] ?? 'tls') === $val ? 'selected' : '' }}>
                                        {{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <x-setting-field id="setting-smtp-username" label="SMTP Username" type="text" :value="$settings['mail_username'] ?? ''" />
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">SMTP
                            Password</label>
                        <input type="password" id="setting-smtp-password" placeholder="••••••••" class="setting-input"
                            @if (!empty($settings['mail_password'])) value="{{ $settings['mail_password'] }}" @endif>
                    </div>
                </div>
                <div class="space-y-4">
                    <x-setting-field id="setting-from-name" label="From Name" type="text" :value="$settings['mail_from_name'] ?? 'SFHUB'" />
                    <x-setting-field id="setting-from-email" label="From Email" type="email" :value="$settings['mail_from_address'] ?? ''" />
                    <div class="pt-2 p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                        <p class="text-sm font-medium text-stone-700 dark:text-stone-300 mb-3">Test Email</p>
                        <div class="flex gap-2">
                            <input type="email" id="setting-test-email" class="setting-input flex-1"
                                value="{{ auth()->user()->email ?? ($settings['contact_email'] ?? '') }}"
                                placeholder="recipient@email.com">
                            <button onclick="testEmail()"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors whitespace-nowrap">
                                <i class="fa-solid fa-paper-plane mr-1"></i> Kirim Test
                            </button>
                        </div>
                        <p class="text-xs text-stone-500 mt-2">Pastikan kamu simpan settings SMTP dulu sebelum test.</p>
                    </div>

                    {{-- Quick guide --}}
                    <details class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                        <summary class="text-sm font-medium text-amber-800 dark:text-amber-400 cursor-pointer">
                            <i class="fa-solid fa-circle-info mr-1"></i> Panduan SMTP cepat
                        </summary>
                        <div class="mt-3 text-xs text-amber-700 dark:text-amber-300 space-y-2">
                            <p><strong>Gmail:</strong> smtp.gmail.com · Port 587 · TLS · Gunakan App Password (bukan
                                password biasa)</p>
                            <p><strong>Mailtrap (dev):</strong> smtp.mailtrap.io · Port 2525 · TLS</p>
                            <p><a href="https://myaccount.google.com/apppasswords" target="_blank" class="underline">Buat
                                    Google App Password →</a></p>
                        </div>
                    </details>
                </div>
            </div>
        </div>

        {{-- ══════════ PAYMENT ══════════ --}}
        <div id="tab-payment"
            class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
            <h3 class="font-bold text-stone-900 dark:text-white mb-2">Payment Settings</h3>
            <p class="text-stone-500 dark:text-stone-400 text-sm mb-6">
                Konfigurasi Midtrans untuk menerima pembayaran subscription.
            </p>

            {{-- ── Midtrans Status Card ── --}}
            @php
                $serverKey = $settings['midtrans_server_key'] ?? '';
                $clientKey = $settings['midtrans_client_key'] ?? '';
                $isSandbox = $settings['midtrans_sandbox'] ?? true;
                $isConnected = !empty($serverKey) && !empty($clientKey);
            @endphp
            <div
                class="mb-6 p-4 rounded-2xl border-2 {{ $isConnected ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20' : 'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20' }}">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center {{ $isConnected ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }}">
                        <i class="fa-solid {{ $isConnected ? 'fa-circle-check' : 'fa-circle-exclamation' }} text-lg"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-stone-800 dark:text-white">
                            {{ $isConnected ? 'Midtrans Terhubung' : 'Midtrans Belum Dikonfigurasi' }}
                        </p>
                        <p class="text-sm text-stone-500">
                            {{ $isConnected ? ($isSandbox ? 'Mode: Sandbox (testing)' : 'Mode: Production (live)') : 'Isi API key di bawah untuk mengaktifkan pembayaran.' }}
                        </p>
                    </div>
                    @if ($isConnected)
                        <span
                            class="ml-auto text-xs font-semibold px-2.5 py-1 rounded-full {{ $isSandbox ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ $isSandbox ? 'SANDBOX' : 'PRODUCTION' }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- ── How-to banner ── --}}
            <details
                class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl">
                <summary class="text-sm font-semibold text-blue-800 dark:text-blue-300 cursor-pointer">
                    <i class="fa-solid fa-rocket mr-1"></i> Cara menghubungkan Midtrans (klik untuk lihat)
                </summary>
                <ol class="mt-3 text-sm text-blue-700 dark:text-blue-300 space-y-2 pl-4 list-decimal">
                    <li>Daftar/login di <a href="https://dashboard.sandbox.midtrans.com" target="_blank"
                            class="underline font-medium">dashboard.sandbox.midtrans.com</a> (gratis untuk testing)</li>
                    <li>Pilih <strong>Settings → Access Keys</strong></li>
                    <li>Salin <strong>Server Key</strong> dan <strong>Client Key</strong>, tempel di field di bawah</li>
                    <li>Centang <strong>Sandbox Mode</strong> saat testing</li>
                    <li>Atur <strong>Payment Notification URL</strong> di Midtrans ke:<br>
                        <code class="px-2 py-0.5 bg-blue-100 dark:bg-blue-800 rounded text-xs font-mono"
                            id="webhook-url-text">{{ url('midtrans/webhook') }}</code>
                        <button onclick="copyWebhook()"
                            class="ml-2 text-xs px-2 py-0.5 bg-blue-200 dark:bg-blue-700 rounded hover:bg-blue-300 transition-colors">
                            <i class="fa-solid fa-copy mr-1"></i>Salin
                        </button>
                    </li>
                    <li>Klik <strong>Simpan Semua</strong> di halaman ini, lalu test pembayaran</li>
                </ol>
            </details>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    {{-- Enable subscriptions --}}
                    <x-setting-toggle id="setting-enable-subscriptions" label="Aktifkan Subscriptions"
                        subtext="Izinkan user berlangganan paket premium" :checked="$settings['enable_subscriptions'] ?? true" />

                    {{-- Currency --}}
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Default
                            Currency</label>
                        <select id="setting-currency" class="setting-input">
                            <option value="IDR" {{ ($settings['currency'] ?? 'IDR') === 'IDR' ? 'selected' : '' }}>IDR
                                — Indonesian Rupiah</option>
                            <option value="USD" {{ ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD —
                                US Dollar</option>
                        </select>
                    </div>

                    {{-- Sandbox toggle --}}
                    <x-setting-toggle id="setting-midtrans-sandbox" label="Midtrans Sandbox Mode"
                        subtext="Gunakan untuk testing. Matikan saat go-live ke production." :checked="$settings['midtrans_sandbox'] ?? true" />
                </div>

                <div class="space-y-4">
                    {{-- Server Key --}}
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Midtrans Server Key
                            <span class="text-xs text-stone-400 ml-1">(rahasia, jangan dibagikan)</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="setting-midtrans-server"
                                value="{{ $settings['midtrans_server_key'] ?? '' }}"
                                placeholder="SB-Mid-server-xxxxxxxxxxxx" class="setting-input pr-10">
                            <button type="button" onclick="toggleSecret('setting-midtrans-server', 'eye-server')"
                                class="absolute inset-y-0 right-0 px-3 text-stone-400 hover:text-stone-600 dark:hover:text-stone-300">
                                <i id="eye-server" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Client Key --}}
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Midtrans Client Key
                            <span class="text-xs text-stone-400 ml-1">(aman ditampilkan di frontend)</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="setting-midtrans-client"
                                value="{{ $settings['midtrans_client_key'] ?? '' }}"
                                placeholder="SB-Mid-client-xxxxxxxxxxxx" class="setting-input pr-10">
                            <button type="button" onclick="toggleSecret('setting-midtrans-client', 'eye-client')"
                                class="absolute inset-y-0 right-0 px-3 text-stone-400 hover:text-stone-600 dark:hover:text-stone-300">
                                <i id="eye-client" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Webhook URL --}}
                    <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                        <p class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Webhook URL (untuk
                            Midtrans)</p>
                        <div class="flex items-center gap-2">
                            <code class="text-xs font-mono text-stone-600 dark:text-stone-300 break-all flex-1">
                                {{ url('midtrans/webhook') }}
                            </code>
                            <button onclick="copyWebhook()"
                                class="flex-shrink-0 px-2 py-1.5 bg-stone-200 dark:bg-stone-700 rounded text-xs font-medium hover:bg-stone-300 transition-colors">
                                <i class="fa-solid fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════ OAUTH / SSO ══════════ --}}
        <div id="tab-oauth"
            class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
            <h3 class="font-bold text-stone-900 dark:text-white mb-2">OAuth & Social Login</h3>
            <p class="text-stone-500 dark:text-stone-400 text-sm mb-6">
                Konfigurasi login dengan akun Google (dan provider lainnya).
                Setelah isi dan simpan, tambahkan ke file <code
                    class="bg-stone-100 dark:bg-stone-800 px-1.5 py-0.5 rounded text-xs font-mono">.env</code> juga (lihat
                panduan).
            </p>

            {{-- Google OAuth setup guide --}}
            <details
                class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl">
                <summary class="text-sm font-semibold text-blue-800 dark:text-blue-300 cursor-pointer">
                    <i class="fa-brands fa-google mr-1"></i> Cara setup Google OAuth (klik untuk lihat)
                </summary>
                <ol class="mt-3 text-sm text-blue-700 dark:text-blue-300 space-y-2 pl-4 list-decimal">
                    <li>Buka <a href="https://console.cloud.google.com" target="_blank"
                            class="underline">console.cloud.google.com</a></li>
                    <li>Buat project baru → <strong>APIs & Services → Credentials</strong></li>
                    <li>Klik <strong>Create Credentials → OAuth Client ID</strong></li>
                    <li>Pilih <strong>Web Application</strong></li>
                    <li>Authorized redirect URI isi dengan:<br>
                        <code class="px-2 py-0.5 bg-blue-100 dark:bg-blue-800 rounded text-xs font-mono">
                            {{ url('auth/google/callback') }}
                        </code>
                        <button onclick="copyText('{{ url('auth/google/callback') }}')"
                            class="ml-2 text-xs px-2 py-0.5 bg-blue-200 dark:bg-blue-700 rounded hover:bg-blue-300 transition-colors">
                            <i class="fa-solid fa-copy mr-1"></i>Salin
                        </button>
                    </li>
                    <li>Salin <strong>Client ID</strong> dan <strong>Client Secret</strong> ke field di bawah</li>
                    <li>Juga tambahkan ke file <code>.env</code>:
                        <pre class="mt-1 text-xs bg-blue-100 dark:bg-blue-900 rounded p-2 font-mono">GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI={{ url('auth/google/callback') }}</pre>
                    </li>
                    <li>Jalankan <code>php artisan config:clear</code> setelah update .env</li>
                </ol>
            </details>

            {{-- Google --}}
            <div class="p-5 border border-stone-200 dark:border-stone-700 rounded-2xl mb-4">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-9 h-9 rounded-lg bg-white border border-stone-200 flex items-center justify-center shadow-sm">
                        <svg width="18" height="18" viewBox="0 0 24 24">
                            <path fill="#4285F4"
                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                            <path fill="#34A853"
                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                            <path fill="#FBBC05"
                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                            <path fill="#EA4335"
                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-stone-800 dark:text-white">Google Sign-In</p>
                        <p class="text-xs text-stone-500">Login & register dengan akun Google</p>
                    </div>
                    <x-setting-toggle id="setting-google-enabled" :checked="!empty($settings['google_client_id'] ?? '')" class="flex-shrink-0" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1.5">Client
                            ID</label>
                        <input type="text" id="setting-google-client-id"
                            value="{{ $settings['google_client_id'] ?? config('services.google.client_id', '') }}"
                            placeholder="1234567890-xxxx.apps.googleusercontent.com" class="setting-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1.5">Client
                            Secret</label>
                        <div class="relative">
                            <input type="password" id="setting-google-client-secret"
                                value="{{ $settings['google_client_secret'] ?? '' }}" placeholder="GOCSPX-xxxxxxxxxxxx"
                                class="setting-input pr-10">
                            <button type="button" onclick="toggleSecret('setting-google-client-secret','eye-g-secret')"
                                class="absolute inset-y-0 right-0 px-3 text-stone-400 hover:text-stone-600">
                                <i id="eye-g-secret" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1.5">Redirect URI</label>
                    <input type="url" id="setting-google-redirect-uri"
                        value="{{ $settings['google_redirect_uri'] ?? url('auth/google/callback') }}"
                        placeholder="{{ url('auth/google/callback') }}" class="setting-input">
                </div>
                <div class="mt-3 p-2.5 bg-stone-50 dark:bg-stone-800 rounded-lg text-xs text-stone-500">
                    Redirect URI yang harus didaftarkan di Google:
                    <code
                        class="font-mono ml-1 text-stone-700 dark:text-stone-300">{{ url('auth/google/callback') }}</code>
                </div>
            </div>

            {{-- Note: install socialite --}}
            <div
                class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-sm text-amber-700 dark:text-amber-300">
                <i class="fa-solid fa-circle-info mr-1"></i>
                <strong>Pastikan sudah install:</strong>
                <code class="bg-amber-100 dark:bg-amber-800 px-1.5 py-0.5 rounded font-mono text-xs ml-1">
                    composer require laravel/socialite
                </code>
                dan tambahkan ke <code class="font-mono text-xs">config/services.php</code>.
            </div>
        </div>

        {{-- ══════════ SOCIAL MEDIA ══════════ --}}
        <div id="tab-social"
            class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
            <h3 class="font-bold text-stone-900 dark:text-white mb-6">Social Media Links</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <x-setting-field id="setting-facebook" label="Facebook" type="url" :value="$settings['social_facebook'] ?? ''"
                        icon="fa-brands fa-facebook" placeholder="https://facebook.com/..." />
                    <x-setting-field id="setting-twitter" label="Twitter/X" type="url" :value="$settings['social_twitter'] ?? ''"
                        icon="fa-brands fa-x-twitter" placeholder="https://x.com/..." />
                    <x-setting-field id="setting-instagram" label="Instagram" type="url" :value="$settings['social_instagram'] ?? ''"
                        icon="fa-brands fa-instagram" placeholder="https://instagram.com/..." />
                </div>
                <div class="space-y-4">
                    <x-setting-field id="setting-linkedin" label="LinkedIn" type="url" :value="$settings['social_linkedin'] ?? ''"
                        icon="fa-brands fa-linkedin" placeholder="https://linkedin.com/..." />
                    <x-setting-field id="setting-youtube" label="YouTube" type="url" :value="$settings['social_youtube'] ?? ''"
                        icon="fa-brands fa-youtube" placeholder="https://youtube.com/..." />
                    <x-setting-field id="setting-whatsapp" label="WhatsApp" type="text" :value="$settings['social_whatsapp'] ?? ''"
                        icon="fa-brands fa-whatsapp" placeholder="+628..." />
                </div>
            </div>
        </div>

        {{-- ══════════ MAINTENANCE ══════════ --}}
        <div id="tab-maintenance"
            class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
            <h3 class="font-bold text-stone-900 dark:text-white mb-2">Maintenance Mode</h3>
            <p class="text-sm text-stone-500 dark:text-stone-400 mb-4">
                Saat maintenance aktif, semua user (kecuali admin) akan melihat halaman maintenance.
            </p>
            <div
                class="mb-5 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-sm text-amber-800 dark:text-amber-400">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                <strong>Perhatian:</strong> Mengaktifkan maintenance mode akan memblokir semua pengguna biasa.
            </div>
            <div class="space-y-4">
                <x-setting-toggle id="setting-maintenance" label="Aktifkan Maintenance Mode"
                    subtext="Site tidak bisa diakses user biasa" :checked="$settings['maintenance_mode'] ?? false" />
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Pesan
                        Maintenance</label>
                    <textarea id="setting-maintenance-message" rows="3" class="setting-input resize-none"
                        placeholder="Kami sedang melakukan pemeliharaan...">{{ $settings['maintenance_message'] ?? 'Kami sedang melakukan pemeliharaan. Silakan cek kembali nanti.' }}</textarea>
                </div>
                <x-setting-field id="setting-maintenance-bypass" label="Bypass Token (opsional)" type="text"
                    :value="$settings['maintenance_bypass_token'] ?? ''"
                    help="Tambahkan ?maintenance_bypass=TOKEN ke URL untuk bypass. Kosongkan jika tidak diperlukan."
                    placeholder="token-rahasia" />
            </div>
        </div>

    </div>
@endsection

@push('styles')
    <style>
        .setting-input {
            width: 100%;
            border: 1px solid rgb(214 211 209);
            border-radius: 12px;
            padding: .6rem 1rem;
            font-size: .9rem;
            background: white;
            color: #1c1917;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }

        .dark .setting-input {
            background: rgba(28, 26, 23, .8);
            border-color: rgba(255, 255, 255, .12);
            color: #f5f3ef;
        }

        .setting-input:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, .12);
        }
    </style>
@endpush

@push('scripts')
    <script>
        /* ── Tab switching ── */
        function switchTab(name) {
            document.querySelectorAll('.tab-btn').forEach(b => {
                const active = b.dataset.tab === name;
                b.classList.toggle('bg-emerald-100', active);
                b.classList.toggle('text-emerald-700', active);
                b.classList.toggle('dark:bg-emerald-900/30', active);
                b.classList.toggle('dark:text-emerald-400', active);
                b.classList.toggle('text-stone-600', !active);
                b.classList.toggle('dark:text-stone-400', !active);
            });
            document.querySelectorAll('.tab-content').forEach(c => {
                c.classList.toggle('hidden', !c.id.endsWith(name));
            });
        }

        /* ── Collect & save all ── */
        function saveSettings() {
            const get = id => document.getElementById(id);
            const val = id => get(id)?.value ?? null;
            const chk = id => get(id)?.checked ?? false;

            const settings = {
                // General
                site_name: val('setting-site-name'),
                site_url: val('setting-site-url'),
                timezone: val('setting-timezone'),
                language: val('setting-language'),
                contact_email: val('setting-contact-email'),
                contact_phone: val('setting-contact-phone'),
                copyright_text: val('setting-copyright'),
                user_registration: chk('setting-registration'),
                // Security
                force_2fa: chk('setting-2fa'),
                email_verification: chk('setting-email-verify'),
                max_login_attempts: val('setting-login-attempts'),
                session_timeout: val('setting-session-timeout'),
                password_min_length: val('setting-password-length'),
                // Email
                mail_host: val('setting-smtp-host'),
                mail_port: val('setting-smtp-port'),
                mail_encryption: val('setting-smtp-encryption'),
                mail_username: val('setting-smtp-username'),
                mail_password: val('setting-smtp-password') || null,
                mail_from_name: val('setting-from-name'),
                mail_from_address: val('setting-from-email'),
                // Payment
                enable_subscriptions: chk('setting-enable-subscriptions'),
                currency: val('setting-currency'),
                midtrans_server_key: val('setting-midtrans-server'),
                midtrans_client_key: val('setting-midtrans-client'),
                midtrans_sandbox: chk('setting-midtrans-sandbox'),
                // OAuth
                google_client_id: val('setting-google-client-id'),
                google_client_secret: val('setting-google-client-secret'),
                google_redirect_uri: val('setting-google-redirect-uri'),
                google_enabled: chk('setting-google-enabled'),
                // Social
                social_facebook: val('setting-facebook'),
                social_twitter: val('setting-twitter'),
                social_instagram: val('setting-instagram'),
                social_linkedin: val('setting-linkedin'),
                social_youtube: val('setting-youtube'),
                social_whatsapp: val('setting-whatsapp'),
                // Maintenance
                maintenance_mode: chk('setting-maintenance'),
                maintenance_message: val('setting-maintenance-message'),
                maintenance_bypass_token: val('setting-maintenance-bypass'),
            };

            // Remove null passwords (don't overwrite with empty)
            if (!settings.mail_password) delete settings.mail_password;

            const btn = event?.currentTarget ?? document.querySelector('button[onclick="saveSettings()"]');
            const origText = btn?.innerHTML;
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...';
                btn.disabled = true;
            }

            fetch('{{ route('admin.settings.save') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(settings),
                })
                .then(r => r.json())
                .then(d => {
                    showToast(d.success !== false ? d.message : (d.message || 'Gagal menyimpan'), d.success !== false ?
                        'success' : 'error');
                    if (d.success !== false) setTimeout(() => location.reload(), 1200);
                })
                .catch(() => showToast('Koneksi error. Coba lagi.', 'error'))
                .finally(() => {
                    if (btn) {
                        btn.innerHTML = origText;
                        btn.disabled = false;
                    }
                });
        }

        /* ── Test email ── */
        function testEmail() {
            const email = document.getElementById('setting-test-email')?.value;
            if (!email) return showToast('Masukkan email penerima dulu.', 'error');
            fetch('{{ route('admin.settings.test-email') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email
                }),
            }).then(r => r.json()).then(d => showToast(d.message, d.success !== false ? 'success' : 'error'));
        }

        /* ── Toggle secret field visibility ── */
        function toggleSecret(inputId, iconId) {
            const inp = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!inp) return;
            const showing = inp.type === 'text';
            inp.type = showing ? 'password' : 'text';
            icon?.classList.toggle('fa-eye', showing);
            icon?.classList.toggle('fa-eye-slash', !showing);
        }

        /* ── Copy webhook URL ── */
        function copyWebhook() {
            copyText('{{ url('midtrans/webhook') }}');
        }

        function copyText(text) {
            navigator.clipboard.writeText(text).then(() => showToast('Tersalin!', 'success'));
        }

        /* ── Toast notification ── */
        function showToast(msg, type = 'success') {
            const el = document.createElement('div');
            const colors = type === 'success' ?
                'bg-emerald-600 text-white' :
                type === 'error' ?
                'bg-red-500 text-white' :
                'bg-amber-500 text-white';
            el.className =
                `fixed bottom-5 right-5 px-5 py-3 rounded-2xl shadow-xl z-50 flex items-center gap-2 text-sm font-medium ${colors}`;
            el.innerHTML =
                `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>${msg}`;
            document.body.appendChild(el);
            el.style.animation = 'toastIn .35s cubic-bezier(.34,1.56,.64,1) both';
            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(8px)';
                el.style.transition = '.3s ease';
                setTimeout(() => el.remove(), 300);
            }, 3500);
        }
    </script>
    <style>
        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateY(12px) scale(.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>
@endpush
