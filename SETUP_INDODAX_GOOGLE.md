# Setup Guide: Google OAuth & Indodax Integration

## 📋 Overview

Implementasi ini menambahkan 2 fitur utama:
1. **Google OAuth Login** - User bisa login/register dengan akun Google
2. **Indodax Integration** - User bisa connect akun Indodax mereka untuk auto-sync crypto balances

---

## 🔐 Google OAuth Setup

### 1. Install Laravel Socialite

```bash
composer require laravel/socialite
```

### 2. Konfigurasi Google Cloud Console

1. Buka [Google Cloud Console](https://console.cloud.google.com)
2. Buat project baru atau pilih project existing
3. Navigasi ke **APIs & Services → Credentials**
4. Klik **Create Credentials → OAuth Client ID**
5. Pilih **Web Application**
6. Isi **Authorized redirect URIs**:
   ```
   http://localhost:8000/auth/google/callback
   https://yourdomain.com/auth/google/callback
   ```
7. Salin **Client ID** dan **Client Secret**

### 3. Update .env

Tambahkan ke file `.env`:

```env
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-your-secret-key
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### 4. Run Migration

```bash
php artisan migrate
```

Migration yang akan dijalankan:
- `2026_03_24_000001_add_google_id_to_users_table.php` - Menambah kolom `google_id` ke tabel users

### 5. Clear Config Cache

```bash
php artisan config:clear
php artisan cache:clear
```

### 6. Test Google Login

1. Buka halaman login: `http://localhost:8000/login`
2. Klik tombol **Masuk dengan Google**
3. Login dengan akun Google
4. User akan otomatis dibuat jika belum ada, atau login jika sudah terdaftar

---

## 💰 Indodax Integration Setup

### 1. Run Migration

Migration sudah dibuat, tinggal jalankan:

```bash
php artisan migrate
```

Migration yang akan dijalankan:
- `2026_03_24_000002_create_indodax_connections_table.php` - Tabel untuk menyimpan API credentials per user

### 2. Tambahkan UI Component ke Profile/Settings

Edit file `resources/views/profile/edit.blade.php` atau halaman settings user, tambahkan:

```blade
{{-- Indodax Connection Section --}}
<div class="mb-6">
    <h3 class="text-lg font-semibold text-stone-900 dark:text-white mb-4">
        Indodax Integration
    </h3>
    @include('components.indodax-connection')
</div>
```

### 3. Cara User Menghubungkan Indodax

**Untuk User:**

1. Login ke [indodax.com](https://indodax.com)
2. Buka halaman [Trade API](https://indodax.com/trade_api)
3. Buat API Key baru dengan permission **view** (minimal)
4. Salin **API Key** dan **Secret Key**
5. Di SFHUB, buka halaman **Profile/Settings**
6. Scroll ke section **Indodax Integration**
7. Paste API Key & Secret Key
8. Klik **Hubungkan**
9. Klik **Sync Balances** untuk import data crypto

### 4. Fitur yang Tersedia

- ✅ **Connect/Disconnect** - Hubungkan/putuskan koneksi Indodax
- ✅ **Test Connection** - Cek apakah API Key valid
- ✅ **Sync Balances** - Import semua crypto balances dari Indodax
- ✅ **Auto-create FinanceAccount** - Otomatis buat akun "Indodax" di Finance
- ✅ **Auto-create InvestmentInstruments** - Otomatis buat instrumen untuk setiap crypto
- ✅ **Real-time Price Update** - Fetch harga terkini dari Indodax public API
- ✅ **Encrypted Secret Key** - Secret Key dienkripsi sebelum disimpan ke database

### 5. Keamanan

- API Secret Key **dienkripsi** dengan Laravel Crypt sebelum disimpan
- API Key hanya ditampilkan sebagian (preview) di UI
- Hanya user yang login bisa akses endpoint Indodax
- Setiap user hanya bisa manage koneksi Indodax mereka sendiri

---

## 📊 Integrasi dengan Investment Dashboard

Setelah sync berhasil, data akan otomatis muncul di:

- **Investment Dashboard** (`/dashboard/investments`)
- **Finance Accounts** (akun "Indodax" akan muncul)
- **Investment Instruments** (setiap crypto jadi instrumen terpisah)

Data yang di-sync:
- Symbol crypto (BTC, ETH, dll)
- Balance (jumlah koin yang dimiliki)
- Current price (harga terkini dalam IDR)
- Total value (balance × price)

---

## 🔧 Troubleshooting

### Google OAuth Error: "redirect_uri_mismatch"

**Solusi:** Pastikan redirect URI di Google Cloud Console sama persis dengan yang di `.env`

### Indodax Error: "Invalid signature"

**Solusi:** 
- Pastikan API Key & Secret Key benar
- Cek apakah server time sudah sinkron (Indodax pakai timestamp)
- Pastikan tidak ada spasi di awal/akhir API Key/Secret

### Indodax Error: "Insufficient permission"

**Solusi:** API Key harus punya permission minimal **view**. Buat ulang API Key di Indodax dengan permission yang benar.

### Data tidak muncul setelah sync

**Solusi:**
- Cek apakah sync berhasil (lihat pesan sukses)
- Refresh halaman Investment Dashboard
- Cek di Network tab browser apakah ada error API

---

## 📁 File-file yang Dibuat/Dimodifikasi

### Google OAuth
- ✅ `database/migrations/2026_03_24_000001_add_google_id_to_users_table.php`
- ✅ `app/Http/Controllers/GoogleController.php`
- ✅ `app/Models/User.php` (tambah `google_id` ke fillable)
- ✅ `config/services.php` (tambah Google config)
- ✅ `routes/web.php` (tambah Google OAuth routes)

### Indodax Integration
- ✅ `database/migrations/2026_03_24_000002_create_indodax_connections_table.php`
- ✅ `app/Models/IndodaxConnection.php`
- ✅ `app/Services/IndodaxService.php`
- ✅ `app/Http/Controllers/IndodaxController.php`
- ✅ `resources/views/components/indodax-connection.blade.php`
- ✅ `routes/web.php` (tambah Indodax routes)

---

## 🚀 Next Steps (Opsional)

### 1. Auto-sync Scheduler

Tambahkan di `app/Console/Kernel.php` untuk auto-sync setiap hari:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        $connections = \App\Models\IndodaxConnection::where('is_active', true)->get();
        foreach ($connections as $conn) {
            $service = new \App\Services\IndodaxService($conn);
            $service->syncBalances();
        }
    })->daily();
}
```

### 2. Webhook untuk Real-time Updates

Indodax tidak support webhook, tapi bisa polling dengan cron job.

### 3. Trade History Import

Extend `IndodaxService` untuk import trade history:

```php
public function syncTradeHistory(string $pair = 'btc_idr')
{
    $trades = $this->getTradeHistory($pair);
    // Process & save to InvestmentPurchase
}
```

---

## 📞 Support

Jika ada masalah, cek:
1. Laravel log: `storage/logs/laravel.log`
2. Browser console untuk error JS
3. Network tab untuk error API response

---

**Selesai!** 🎉

Google OAuth dan Indodax integration sudah siap digunakan.
