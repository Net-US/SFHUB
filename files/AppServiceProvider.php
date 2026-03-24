<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Baca settings dari DB dan terapkan ke konfigurasi Laravel
        // Dibungkus try-catch agar tidak crash saat pertama kali migrate
        try {
            $this->applySystemSettings();
        } catch (\Exception $e) {
            // Tabel belum ada (belum migrate), abaikan
        }
    }

    protected function applySystemSettings(): void
    {
        // Cache selama 60 menit agar tidak query DB setiap request
        $settings = Cache::remember('system_settings_all', 3600, function () {
            return SystemSetting::all()->pluck('value', 'key')->toArray();
        });

        // Terapkan timezone
        if (!empty($settings['timezone'])) {
            config(['app.timezone' => $settings['timezone']]);
            date_default_timezone_set($settings['timezone']);
        }

        // Terapkan locale/language
        if (!empty($settings['language'])) {
            config(['app.locale' => $settings['language']]);
            app()->setLocale($settings['language']);
        }

        // Terapkan nama aplikasi
        if (!empty($settings['site_name'])) {
            config(['app.name' => $settings['site_name']]);
        }

        // Terapkan email konfigurasi (dari tab Email di settings)
        if (!empty($settings['mail_host'])) {
            config([
                'mail.mailers.smtp.host'       => $settings['mail_host'],
                'mail.mailers.smtp.port'       => $settings['mail_port'] ?? 587,
                'mail.mailers.smtp.username'   => $settings['mail_username'] ?? null,
                'mail.mailers.smtp.password'   => $settings['mail_password'] ?? null,
                'mail.mailers.smtp.encryption' => $settings['mail_encryption'] ?? 'tls',
                'mail.from.address'            => $settings['contact_email'] ?? config('mail.from.address'),
                'mail.from.name'               => $settings['site_name'] ?? config('mail.from.name'),
            ]);
        }

        // Terapkan Midtrans keys (dari tab Payment)
        if (!empty($settings['midtrans_server_key'])) {
            config([
                'midtrans.server_key'   => $settings['midtrans_server_key'],
                'midtrans.client_key'   => $settings['midtrans_client_key'] ?? null,
                'midtrans.is_production' => !($settings['midtrans_sandbox'] ?? true),
            ]);
        }
    }
}
