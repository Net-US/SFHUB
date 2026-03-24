# SFHUB Setup Guide (Real Integrations)

Dokumen ini fokus pada hal yang **belum selesai / perlu setup eksternal** berdasarkan audit kode project saat ini.

## 1) Ringkasan Hasil Audit

### Sudah ada di kode
- `midtrans/midtrans-php` sudah terpasang di `composer.json`.
- `MidtransController` sudah ada (`createTransaction`, `webhook`, `finish`).
- Route Midtrans sudah ada di `routes/web.php`:
  - `POST /midtrans/webhook`
  - `GET /midtrans/finish`
  - `POST /subscribe`
- Settings panel sudah bisa simpan key Midtrans + SMTP.
- `AppServiceProvider` sudah apply settings dari DB ke config runtime.
- Maintenance middleware sudah aktif + CSRF webhook Midtrans sudah dikecualikan (Laravel 11 `bootstrap/app.php`).

### Belum selesai / gap penting
- Alur pembayaran yang dipakai user saat ini masih `donation` manual (`DonationController` + `resources/views/donation/show.blade.php`), **belum memanggil Midtrans Snap**.
- Belum ada halaman user-facing pricing/subscription yang memanggil endpoint `POST /subscribe`.
- Modul admin subscription masih ada ketidaksinkronan field (UI pakai `price`/`billing_period`, model/migration pakai `price_monthly`/`price_yearly` + `billing_cycle`).
- `routes/web.php` punya route `POST /admin/subscriptions` ke `SubscriptionController@store`, tapi method `store()` tidak ada.
- 2FA masih sebatas toggle settings (`force_2fa`), enforcement nyata belum diimplementasikan.
- Rate limit login dari setting `max_login_attempts` belum diterapkan ke route login.
- Belum ada runbook deploy production khusus project ini (queue worker, scheduler, webhook URL verification, dsb).

---

## 2) Cara Menghubungkan Midtrans ke Pembayaran yang Ada Sekarang

Saat ini ada 2 jalur pembayaran:
1. Jalur lama: donasi manual (`/donation`) 
2. Jalur baru: subscription Midtrans (`/subscribe` + webhook)

Agar Midtrans benar-benar dipakai user, pilih salah satu strategi di bawah.

## Opsi A (disarankan): migrasi dari Donasi ke Subscription Midtrans

### Langkah teknis
1. Buat halaman pricing untuk user (mis. `/pricing`) yang menampilkan `subscription_plans` aktif.
2. Tombol “Subscribe” kirim request ke `POST /subscribe` dengan payload:
   - `plan_id`
   - `billing_cycle` (`monthly` / `yearly`)
3. Endpoint ini mengembalikan `snap_token` dan `client_key`.
4. Di frontend panggil Snap:

```html
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="MIDTRANS_CLIENT_KEY"></script>
```

```js
fetch('/subscribe', {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': csrfToken,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({ plan_id, billing_cycle })
})
  .then(r => r.json())
  .then(data => {
    if (!data.snap_token) throw new Error(data.message || 'Snap token tidak tersedia');

    window.snap.pay(data.snap_token, {
      onSuccess: () => window.location.href = '/dashboard?payment=success',
      onPending: () => window.location.href = '/dashboard?payment=pending',
      onError: () => window.location.href = '/dashboard?payment=error',
      onClose: () => console.log('Popup ditutup user')
    });
  });
```

5. Set `Payment Notification URL` di Midtrans ke:
   - `https://domainmu.com/midtrans/webhook`
6. Pastikan webhook bisa diakses publik (gunakan ngrok saat local).
7. Verifikasi di DB `user_subscriptions` bahwa status berubah `pending -> active` setelah settlement.

### Catatan penting
- Endpoint `webhook` adalah source of truth status pembayaran (bukan callback frontend).
- Pastikan clock server benar (NTP) untuk menghindari issue signature/expiry.
- Simpan key hanya lewat Settings admin / env, jangan hardcode di blade.

## Opsi B: tetap pakai halaman Donasi, tapi backend dipindah ke Midtrans

Jika tetap pakai UI `donation.show`, ubah submit form agar:
- Bukan langsung activate plan.
- Buat transaksi Midtrans dulu.
- Aktivasi plan hanya saat webhook status sukses.

---

## 3) Setup Midtrans (Dashboard)

1. Login ke `https://dashboard.sandbox.midtrans.com`.
2. Ambil:
   - `Server Key`
   - `Client Key`
3. Isi key di Admin > Settings > Tab Payment.
4. Centang `Sandbox Mode` saat testing.
5. Isi Notification URL:
   - `https://domainmu.com/midtrans/webhook`
6. Untuk local:
   - Jalankan app (contoh `php artisan serve`)
   - Expose pakai `ngrok http 8000`
   - Isi URL ngrok di Midtrans.

Checklist verifikasi:
- Bisa create transaction (mendapat `snap_token`).
- Webhook masuk (log tidak invalid signature).
- `user_subscriptions.status` berubah sesuai status Midtrans.

---

## 4) Setup Email Production

## Yang sudah ada
- Settings SMTP tersimpan ke DB.
- `AppServiceProvider` apply config mail runtime.
- Tombol test email ada di Admin Settings.

## Opsi provider
- Development: Mailtrap.
- Production kecil: Gmail App Password.
- Production serius: Brevo / Mailgun / Postmark / SES.

## Contoh Gmail (small production)
1. Aktifkan 2FA akun Google.
2. Buat App Password.
3. Isi di Settings Email:
   - Host: `smtp.gmail.com`
   - Port: `587`
   - Username: `email@gmail.com`
   - Password: app password
   - Encryption: `tls`
   - From Address/Name sesuai domain/brand
4. Save, lalu `Send Test Email`.

## Rekomendasi production
- Gunakan email domain sendiri untuk deliverability.
- Tambahkan SPF, DKIM, DMARC di DNS domain.
- Pindahkan mailer dari `log` ke `smtp` di env production bila diperlukan.

---

## 5) Setup 2FA (belum ada implementasi nyata)

Toggle `force_2fa` di settings **baru menyimpan preferensi**, belum enforce saat login.

## Implementasi yang direkomendasikan
1. Install package:
```bash
composer require pragmarx/google2fa-laravel
```
2. Publish config package (ikuti dokumentasi package).
3. Tambah kolom user untuk secret/recovery code jika diperlukan.
4. Buat flow:
   - Enroll 2FA (QR code)
   - Verify OTP
   - Challenge OTP setelah password benar
5. Enforce ke admin dulu, lalu role lain bertahap.

---

## 6) Rate Limiting Login (belum dipasang)

`max_login_attempts` sudah ada di settings, tapi route login belum throttle dinamis.

Contoh minimal:
```php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');
```

Contoh dinamis dari setting:
```php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:' . \App\Models\SystemSetting::get('max_login_attempts', 5) . ',1');
```

---

## 7) Setup Deploy Production

## Prasyarat server
- PHP 8.2+
- Composer
- Node.js (untuk build awal asset)
- MySQL/PostgreSQL
- Web server (Nginx/Apache)
- SSL (HTTPS wajib untuk webhook/payment)

## Langkah deploy standar
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan key:generate
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan storage:link
```

Set env production minimal:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://domainmu.com`
- DB credentials
- `MAIL_*` jika pakai env

## Queue worker (wajib jika nanti email/backup dipindah ke queue)
Contoh supervisor command:
```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

## Scheduler
Tambahkan cron:
```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

Saat ini `routes/console.php` belum berisi schedule khusus; ini perlu ditambah jika nanti ada auto-backup atau task terjadwal lain.

---

## 8) Maintenance Mode Operasional

Sudah ada middleware custom + bypass auth/admin.

Emergency command jika terkunci:
```bash
php artisan up
php artisan cache:clear
php artisan config:clear
```

---

## 9) To-Do Prioritas Implementasi

## Prioritas tinggi
- [ ] Buat halaman pricing user + integrasi Snap (`/subscribe`).
- [ ] Putuskan: migrasi donasi ke Midtrans atau tetap manual.
- [ ] Rapikan modul admin subscription agar sinkron dengan schema (`price_monthly`, `price_yearly`, `billing_cycle`).
- [ ] Perbaiki route `POST /admin/subscriptions` (hapus atau implement `store`).

## Prioritas menengah
- [ ] Implementasi 2FA end-to-end.
- [ ] Pasang throttle login dinamis dari settings.
- [ ] Setup SMTP production + DNS email authentication.

## Prioritas deploy
- [ ] Buat SOP deploy final (server-specific: shared hosting / VPS).
- [ ] Setup queue process manager (Supervisor/systemd).
- [ ] Setup monitoring webhook Midtrans + log alert.

---

## 10) Quick Test Matrix Setelah Setup

1. Save key Midtrans di settings -> reload -> nilai tetap tersimpan.
2. Create transaksi subscribe -> dapat `snap_token`.
3. Bayar di sandbox -> webhook hit.
4. `user_subscriptions.status` berubah ke `active`.
5. Login/register tetap normal saat maintenance mode aktif.
6. Test email sukses kirim ke inbox real.
7. Deploy URL `APP_URL` benar dan HTTPS aktif.

---

Jika ingin, langkah berikutnya saya bisa langsung lanjutkan implementasi teknisnya:
1) buat halaman pricing + tombol subscribe Snap,
2) rapikan modul admin subscription yang belum sinkron,
3) pasang throttle login dinamis dari settings.
