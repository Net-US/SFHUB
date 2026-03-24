# PANDUAN SETUP LENGKAP — SFHUB Settings & Integrations

## STATUS KODE SAAT INI

| Fitur               | Status           | Keterangan                                    |
|---------------------|------------------|-----------------------------------------------|
| General Settings    | ✅ Bisa dipakai  | Simpan ke DB, perlu AppServiceProvider        |
| Social Links        | ✅ Bisa dipakai  | Simpan ke DB, tampilkan di frontend           |
| Subscription Plans  | ✅ Bisa dipakai  | CRUD lengkap                                  |
| Security Toggles    | ⚠️ Setengah jalan | Tersimpan, tapi belum enforce 2FA/rate limit  |
| Maintenance Mode    | ⚠️ Setengah jalan | Perlu middleware (sudah disediakan)            |
| Email SMTP          | ⚠️ Setengah jalan | Perlu konfigurasi .env atau DB + ServiceProvider |
| Payment Midtrans    | ❌ Belum jalan   | Perlu SDK + webhook (sudah disediakan)        |
| Backup              | ❌ Simulasi saja | Perlu mysqldump + ZipArchive (sudah diperbaiki)|

---

## LANGKAH 1 — Install Package yang Dibutuhkan

```bash
# Wajib untuk Midtrans
composer require midtrans/midtrans-php

# Opsional tapi sangat disarankan untuk backup otomatis
composer require spatie/laravel-backup
```

---

## LANGKAH 2 — Ganti / Update File Ini

Salin file dari folder output ke project kamu:

```
app/Providers/AppServiceProvider.php       ← GANTI dengan file baru
app/Http/Controllers/Admin/SettingController.php  ← GANTI dengan file baru
app/Http/Controllers/MidtransController.php       ← FILE BARU, buat ini
app/Http/Middleware/CheckMaintenanceMode.php      ← FILE BARU, buat ini
```

---

## LANGKAH 3 — Daftarkan Middleware

### Laravel 11 (bootstrap/app.php):
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\CheckMaintenanceMode::class);
})
```

### Laravel 10 (app/Http/Kernel.php):
```php
protected $middlewareGroups = [
    'web' => [
        // ... yang sudah ada ...
        \App\Http\Middleware\CheckMaintenanceMode::class,
    ],
];
```

---

## LANGKAH 4 — Kecualikan Webhook dari CSRF

Di `app/Http/Middleware/VerifyCsrfToken.php`:
```php
protected $except = [
    'midtrans/webhook',
    'midtrans/*',
];
```

---

## LANGKAH 5 — Tambahkan Routes

Di `routes/web.php`, tambahkan semua route dari file `routes_setup.php`.

---

## LANGKAH 6 — Setup Midtrans (Gratis untuk Testing)

1. Daftar di https://dashboard.sandbox.midtrans.com (GRATIS)
2. Login → Settings → Access Keys
3. Salin **Server Key** dan **Client Key**
4. Di Admin Panel kamu → Settings → Tab Payment:
   - Isi Server Key
   - Isi Client Key
   - Centang "Sandbox Mode" (untuk testing)
   - Klik Save
5. Di dashboard Midtrans → Settings → Configuration:
   - **Payment Notification URL**: `https://domainmu.com/midtrans/webhook`
   - Jika masih local/belum deploy: gunakan ngrok untuk expose localhost
     ```bash
     ngrok http 8000
     # Lalu isi URL ngrok di Midtrans dashboard
     ```

---

## LANGKAH 7 — Setup Email (Opsional tapi Berguna)

Kamu bisa pakai layanan gratis:

### Opsi A — Mailtrap (untuk testing, gratis):
1. Daftar di https://mailtrap.io
2. Inbox → SMTP Settings → salin host, port, username, password
3. Di Admin Panel → Settings → Tab Email: isi semua field tersebut

### Opsi B — Gmail (gratis, untuk produksi kecil):
1. Aktifkan 2FA di Google Account
2. Buat App Password: Google Account → Security → App Passwords
3. Di Admin Panel → Settings → Tab Email:
   - Host: smtp.gmail.com
   - Port: 587
   - Username: emailkamu@gmail.com
   - Password: [app password yang dibuat]
   - Encryption: tls

### Opsi C — Langsung di .env (paling mudah, tidak perlu UI):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=emailkamu@gmail.com
MAIL_PASSWORD=app_password_disini
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=emailkamu@gmail.com
MAIL_FROM_NAME="SFHUB"
```

---

## LANGKAH 8 — Tentang Domain / URL di Hosting Gratis

**Pertanyaan kamu: apakah Site URL di settings berpengaruh?**

Jawaban: **Tidak langsung**. URL domainmu diatur di:
1. File `.env` → `APP_URL=https://domainmu.com`
2. Config hosting (cPanel, Nginx, Apache)
3. Field "Site URL" di settings hanya disimpan ke DB sebagai referensi/display

Yang perlu kamu lakukan saat deploy ke hosting:
- Set `APP_URL` di `.env` sesuai domain
- Jika pindah domain, update `.env` dan jalankan `php artisan config:clear`
- Field di Settings UI lebih untuk ditampilkan di footer, email, dll

---

## LANGKAH 9 — Tentang Security (2FA, Rate Limiting)

Setting security di UI menyimpan preferensi ke DB, tapi belum enforce otomatis.
Untuk Rate Limiting (max login attempts), tambahkan di `routes/web.php`:

```php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:' . SystemSetting::get('max_login_attempts', 5) . ',1');
```

Untuk 2FA yang sesungguhnya, gunakan package:
```bash
composer require pragmarx/google2fa-laravel
```

---

## LANGKAH 10 — Setelah Deploy

Jalankan berurutan setelah upload ke hosting:
```bash
php artisan migrate          # Buat tabel
php artisan config:clear     # Bersihkan cache config
php artisan cache:clear      # Bersihkan cache aplikasi
php artisan storage:link     # Link storage untuk file upload
php artisan queue:work       # (Jika pakai queue untuk email/backup)
```

---

## RINGKASAN: Mana yang Perlu Kamu Prioritaskan?

### Prioritas Tinggi (supaya fitur utama jalan):
1. ✅ Jalankan AppServiceProvider baru (settings teraplikasikan)
2. ✅ Pasang middleware maintenance
3. ✅ Setup Midtrans (kalau mau fitur berbayar)

### Prioritas Sedang:
4. Setup email (SMTP)
5. Perbaiki backup (mysqldump)

### Bisa Nanti:
6. 2FA yang sesungguhnya (butuh package tambahan)
7. Auto-backup terjadwal (butuh Laravel Scheduler)
