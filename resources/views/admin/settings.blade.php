@extends('layouts.app')

@section('title', 'System Settings | SFHUB Admin')

@section('page-title', 'System Settings')

@section('content')
<div class="animate-fade-in-up space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-stone-900 dark:text-white">System Settings</h2>
            <p class="text-stone-500 dark:text-stone-400 text-sm">Kelola pengaturan sistem platform</p>
        </div>
        <button onclick="saveSettings()" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors">
            <i class="fa-solid fa-save"></i> Save All Changes
        </button>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl p-2 border border-stone-200 dark:border-stone-800">
        <div class="flex gap-2 overflow-x-auto">
            <button onclick="switchTab('general')" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400" data-tab="general">
                <i class="fa-solid fa-sliders mr-1"></i> General
            </button>
            <button onclick="switchTab('security')" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800" data-tab="security">
                <i class="fa-solid fa-shield-halved mr-1"></i> Security
            </button>
            <button onclick="switchTab('email')" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800" data-tab="email">
                <i class="fa-solid fa-envelope mr-1"></i> Email
            </button>
            <button onclick="switchTab('payment')" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800" data-tab="payment">
                <i class="fa-solid fa-credit-card mr-1"></i> Payment
            </button>
            <button onclick="switchTab('social')" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800" data-tab="social">
                <i class="fa-solid fa-share-nodes mr-1"></i> Social
            </button>
            <button onclick="switchTab('maintenance')" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800" data-tab="maintenance">
                <i class="fa-solid fa-wrench mr-1"></i> Maintenance
            </button>
        </div>
    </div>

    <!-- General Settings -->
    <div id="tab-general" class="tab-content bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
        <h3 class="font-bold text-stone-900 dark:text-white mb-6">General Settings</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Site Name</label>
                    <input type="text" id="setting-site-name" value="{{ $settings['site_name'] ?? 'Student-Freelancer Hub' }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Site URL</label>
                    <input type="url" id="setting-site-url" value="{{ $settings['site_url'] ?? 'https://sfhub.id' }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Default Timezone</label>
                    <select id="setting-timezone" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        @foreach(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'] as $tz)
                        <option value="{{ $tz }}" {{ ($settings['timezone'] ?? 'Asia/Jakarta') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Default Language</label>
                    <select id="setting-language" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        <option value="id" {{ ($settings['language'] ?? 'id') === 'id' ? 'selected' : '' }}>Indonesian</option>
                        <option value="en" {{ ($settings['language'] ?? '') === 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Contact Email</label>
                    <input type="email" id="setting-contact-email" value="{{ $settings['contact_email'] ?? 'support@sfhub.id' }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Support Phone</label>
                    <input type="text" id="setting-contact-phone" value="{{ $settings['contact_phone'] ?? '' }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Copyright Text</label>
                    <input type="text" id="setting-copyright" value="{{ $settings['copyright_text'] ?? '© 2025 SFHUB. All rights reserved.' }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div class="flex items-center gap-2 pt-4">
                    <input type="checkbox" id="setting-registration" {{ ($settings['user_registration'] ?? true) ? 'checked' : '' }} class="rounded border-stone-300 text-primary-600 focus:ring-primary-500">
                    <label for="setting-registration" class="text-sm text-stone-700 dark:text-stone-300">Enable User Registration</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div id="tab-security" class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
        <h3 class="font-bold text-stone-900 dark:text-white mb-6">Security Settings</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                    <div>
                        <p class="font-medium text-stone-800 dark:text-white">Two Factor Authentication (2FA)</p>
                        <p class="text-sm text-stone-500">Require 2FA for all admin users</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="setting-2fa" {{ ($settings['force_2fa'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-stone-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-stone-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                    <div>
                        <p class="font-medium text-stone-800 dark:text-white">Email Verification</p>
                        <p class="text-sm text-stone-500">Require email verification for new users</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="setting-email-verify" {{ ($settings['email_verification'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-stone-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-stone-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Max Login Attempts</label>
                    <input type="number" id="setting-login-attempts" value="{{ $settings['max_login_attempts'] ?? 5 }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Session Timeout (minutes)</label>
                    <input type="number" id="setting-session-timeout" value="{{ $settings['session_timeout'] ?? 120 }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Password Min Length</label>
                    <input type="number" id="setting-password-length" value="{{ $settings['password_min_length'] ?? 8 }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
            </div>
        </div>
    </div>

    <!-- Email Settings -->
    <div id="tab-email" class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
        <h3 class="font-bold text-stone-900 dark:text-white mb-6">Email Settings</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">SMTP Host</label>
                    <input type="text" id="setting-smtp-host" value="{{ $settings['smtp_host'] ?? '' }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Port</label>
                        <input type="number" id="setting-smtp-port" value="{{ $settings['smtp_port'] ?? 587 }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Encryption</label>
                        <select id="setting-smtp-encryption" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                            <option value="tls" {{ ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="" {{ ($settings['smtp_encryption'] ?? '') === '' ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Username</label>
                    <input type="text" id="setting-smtp-username" value="{{ $settings['smtp_username'] ?? '' }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Password</label>
                    <input type="password" id="setting-smtp-password" placeholder="••••••••" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">From Name</label>
                    <input type="text" id="setting-from-name" value="{{ $settings['mail_from_name'] ?? 'SFHUB Team' }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">From Email</label>
                    <input type="email" id="setting-from-email" value="{{ $settings['mail_from_address'] ?? 'noreply@sfhub.id' }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div class="pt-2">
                    <button onclick="testEmail()" class="px-4 py-2 border border-primary-600 text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg">
                        <i class="fa-solid fa-paper-plane mr-2"></i>Send Test Email
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Settings -->
    <div id="tab-payment" class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
        <h3 class="font-bold text-stone-900 dark:text-white mb-6">Payment Settings</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                    <div>
                        <p class="font-medium text-stone-800 dark:text-white">Enable Subscriptions</p>
                        <p class="text-sm text-stone-500">Allow users to subscribe to plans</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="setting-enable-subscriptions" {{ ($settings['enable_subscriptions'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-stone-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-stone-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Default Currency</label>
                    <select id="setting-currency" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        <option value="IDR" {{ ($settings['currency'] ?? 'IDR') === 'IDR' ? 'selected' : '' }}>IDR - Indonesian Rupiah</option>
                        <option value="USD" {{ ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                    </select>
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Midtrans Server Key</label>
                    <input type="password" id="setting-midtrans-server" placeholder="••••••••" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Midtrans Client Key</label>
                    <input type="password" id="setting-midtrans-client" placeholder="••••••••" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="setting-midtrans-sandbox" {{ ($settings['midtrans_sandbox'] ?? true) ? 'checked' : '' }} class="rounded border-stone-300 text-primary-600 focus:ring-primary-500">
                    <label for="setting-midtrans-sandbox" class="text-sm text-stone-700 dark:text-stone-300">Use Sandbox Mode</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Social Settings -->
    <div id="tab-social" class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
        <h3 class="font-bold text-stone-900 dark:text-white mb-6">Social Media Links</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2"><i class="fa-brands fa-facebook mr-2"></i>Facebook</label>
                    <input type="url" id="setting-facebook" value="{{ $settings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/..." class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2"><i class="fa-brands fa-twitter mr-2"></i>Twitter</label>
                    <input type="url" id="setting-twitter" value="{{ $settings['social_twitter'] ?? '' }}" placeholder="https://twitter.com/..." class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2"><i class="fa-brands fa-instagram mr-2"></i>Instagram</label>
                    <input type="url" id="setting-instagram" value="{{ $settings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/..." class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2"><i class="fa-brands fa-linkedin mr-2"></i>LinkedIn</label>
                    <input type="url" id="setting-linkedin" value="{{ $settings['social_linkedin'] ?? '' }}" placeholder="https://linkedin.com/..." class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2"><i class="fa-brands fa-youtube mr-2"></i>YouTube</label>
                    <input type="url" id="setting-youtube" value="{{ $settings['social_youtube'] ?? '' }}" placeholder="https://youtube.com/..." class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2"><i class="fa-brands fa-whatsapp mr-2"></i>WhatsApp</label>
                    <input type="text" id="setting-whatsapp" value="{{ $settings['social_whatsapp'] ?? '' }}" placeholder="+62..." class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Settings -->
    <div id="tab-maintenance" class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
        <h3 class="font-bold text-stone-900 dark:text-white mb-6">Maintenance Mode</h3>
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 mb-6">
            <p class="text-amber-800 dark:text-amber-400 text-sm"><i class="fa-solid fa-exclamation-triangle mr-2"></i><strong>Warning:</strong> Enabling maintenance mode will make the site inaccessible to regular users.</p>
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                <div>
                    <p class="font-medium text-stone-800 dark:text-white">Maintenance Mode</p>
                    <p class="text-sm text-stone-500">Put the site in maintenance mode</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="setting-maintenance" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 dark:peer-focus:ring-amber-800 rounded-full peer dark:bg-stone-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-stone-600 peer-checked:bg-amber-500"></div>
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Maintenance Message</label>
                <textarea id="setting-maintenance-message" rows="3" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">{{ $settings['maintenance_message'] ?? 'We are currently performing maintenance. Please check back later.' }}</textarea>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tab switching
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-primary-100', 'text-primary-700', 'dark:bg-primary-900/30', 'dark:text-primary-400');
            btn.classList.add('text-stone-600', 'dark:text-stone-400');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('bg-primary-100', 'text-primary-700', 'dark:bg-primary-900/30', 'dark:text-primary-400');
        document.querySelector(`[data-tab="${tabName}"]`).classList.remove('text-stone-600', 'dark:text-stone-400');
        
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById(`tab-${tabName}`).classList.remove('hidden');
    }

    // Save Settings
    function saveSettings() {
        const settings = {
            site_name: document.getElementById('setting-site-name').value,
            site_url: document.getElementById('setting-site-url').value,
            timezone: document.getElementById('setting-timezone').value,
            language: document.getElementById('setting-language').value,
            contact_email: document.getElementById('setting-contact-email').value,
            contact_phone: document.getElementById('setting-contact-phone').value,
            copyright_text: document.getElementById('setting-copyright').value,
            user_registration: document.getElementById('setting-registration').checked,
            force_2fa: document.getElementById('setting-2fa').checked,
            email_verification: document.getElementById('setting-email-verify').checked,
            max_login_attempts: document.getElementById('setting-login-attempts').value,
            session_timeout: document.getElementById('setting-session-timeout').value,
            password_min_length: document.getElementById('setting-password-length').value,
            enable_subscriptions: document.getElementById('setting-enable-subscriptions').checked,
            currency: document.getElementById('setting-currency').value,
            midtrans_sandbox: document.getElementById('setting-midtrans-sandbox').checked,
            social_facebook: document.getElementById('setting-facebook').value,
            social_twitter: document.getElementById('setting-twitter').value,
            social_instagram: document.getElementById('setting-instagram').value,
            social_linkedin: document.getElementById('setting-linkedin').value,
            social_youtube: document.getElementById('setting-youtube').value,
            social_whatsapp: document.getElementById('setting-whatsapp').value,
            maintenance_mode: document.getElementById('setting-maintenance').checked,
            maintenance_message: document.getElementById('setting-maintenance-message').value,
        };

        fetch('{{ route("admin.settings.save") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(settings)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification('Settings saved successfully');
            } else {
                showNotification(data.message, 'error');
            }
        });
    }

    // Test Email
    function testEmail() {
        fetch('{{ route("admin.settings.test-email") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            showNotification(data.message, data.success ? 'success' : 'error');
        });
    }

    // Notification helper
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'} text-white px-4 py-2 rounded-lg shadow-lg z-50`;
        notification.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>${message}`;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
</script>
@endpush
