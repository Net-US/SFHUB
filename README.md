# SFHUB - Student Finance Hub

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="200" alt="Laravel Logo">
  <h3 align="center">Student Finance Hub</h3>
  <p align="center">Sistem Manajemen Keuangan Mahasiswa Indonesia</p>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css" alt="TailwindCSS">
</p>

## 📖 Tentang Project

SFHUB adalah aplikasi manajemen keuangan yang dirancang khusus untuk mahasiswa Indonesia. Aplikasi ini membantu pengguna untuk mengelola keuangan pribadi, melacak pengeluaran, menetapkan target tabungan, dan membuat perencanaan keuangan yang lebih baik.

### 🎯 Fitur Utama

#### 💰 Manajemen Keuangan
- **Multi-Akun**: Kelola berbagai jenis akun (Cash, Bank, E-Wallet, Investasi, Piutang)
- **Transaksi**: Catat pemasukan, pengeluaran, dan transfer antar akun
- **Kategori**: Organisir transaksi berdasarkan kategori (Makanan, Transport, dll)
- **Budget**: Tetapkan batas pengeluaran per kategori dengan alert otomatis

#### 📊 Dashboard Analitik
- **Chart Interaktif**: Visualisasi data keuangan dengan Chart.js
- **Trend 6 Bulan**: Analisis pemasukan dan pengeluaran historis
- **Distribusi Aset**: Lihat sebaran kekayaan di berbagai akun
- **Insight Keuangan**: Tips dan rekomendasi personal

#### 🎯 Target & Perencanaan
- **Savings Goals**: Tetapkan target tabungan dengan tracking progress
- **Pending Needs**: Catat kebutuhan masa depan dan alokasi dana
- **Debt Management**: Kelola hutang dengan tracking pembayaran

#### 🏠 Manajemen Aset
- **Aset Fisik**: Catat barang berharga (elektronik, kendaraan, dll)
- **Depresiasi**: Tracking nilai aset dari waktu ke waktu
- **Garansi & Asuransi**: Monitoring expiry dates
- **Lokasi & Kondisi**: Detail lengkap setiap aset

#### 💼 Manajemen Investasi
- **Portfolio**: Kelola berbagai instrumen investasi
- **Performance Tracking**: Monitor profit/loss investasi
- **Purchase History**: Catat riwayat pembelian investasi

#### 🌟 Fitur Lokal Indonesia
- **Tips Finansial**: Rekomendasi yang disesuaikan dengan konteks Indonesia
- **Kalender Finansial**: Tracking hari gajian dan tanggal penting
- **Format Rupiah**: Tampilan mata uang yang familiar
- **Bahasa Indonesia**: Interface yang user-friendly untuk pengguna lokal

## 🏗️ Teknologi

### Backend
- **Laravel 10.x** - PHP Framework
- **MySQL 8.0** - Database
- **Eloquent ORM** - Database Abstraction
- **Laravel Sanctum** - Authentication

### Frontend
- **Blade Templates** - Laravel Templating
- **TailwindCSS 3.x** - CSS Framework
- **Alpine.js** - JavaScript Interactivity
- **Chart.js** - Data Visualization
- **Font Awesome** - Icons

### Testing
- **PHPUnit** - Unit & Feature Testing
- **Laravel Dusk** - Browser Testing (Optional)

## 🚀 Instalasi

### Prerequisites
- PHP 8.2+
- MySQL 8.0+ atau MariaDB 10.3+
- Composer
- Node.js & NPM (untuk asset compilation)
- Git

### Step-by-Step Installation

1. **Clone Repository**
   ```bash
   git clone https://github.com/username/SFHUB.git
   cd SFHUB
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Configuration**
   ```bash
   # Edit .env file
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sfhub
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Database Migration**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Asset Compilation**
   ```bash
   npm run build
   ```

7. **Start Development Server**
   ```bash
   php artisan serve
   ```

8. **Access Application**
   - URL: http://localhost:8000
   - Default Admin: admin@example.com / password
   - Default User: user@example.com / password

## 📁 Struktur Project

```
SFHUB/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── FinanceController.php
│   │   │   ├── AssetController.php
│   │   │   ├── DebtController.php
│   │   │   ├── InvestmentController.php
│   │   │   └── DashboardController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── FinanceAccount.php
│   │   ├── Transaction.php
│   │   ├── Asset.php
│   │   ├── Debt.php
│   │   └── InvestmentInstrument.php
│   └── Services/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── views/
│   │   └── dashboard/
│   │       ├── finance.blade.php
│   │       ├── assets.blade.php
│   │       ├── debts.blade.php
│   │       └── investments.blade.php
│   └── js/
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
│   ├── Feature/
│   │   ├── FinanceCRUDTest.php
│   │   ├── AssetCRUDTest.php
│   │   ├── DebtCRUDTest.php
│   │   └── InvestmentCRUDTest.php
│   └── Unit/
└── public/
```

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/FinanceCRUDTest.php

# Run with coverage
php artisan test --coverage
```

### Test Coverage
- ✅ Finance CRUD Operations
- ✅ Asset Management
- ✅ Debt Management  
- ✅ Investment Portfolio
- ✅ Authentication & Authorization
- ✅ Input Validation
- ✅ Business Logic

## 📊 API Endpoints

### Finance Management
- `GET /finance` - Dashboard keuangan
- `POST /finance/accounts` - Tambah akun
- `PUT /finance/accounts/{id}` - Update akun
- `DELETE /finance/accounts/{id}` - Hapus akun
- `PATCH /finance/accounts/{id}/balance` - Update saldo
- `POST /finance/transactions` - Tambah transaksi
- `DELETE /finance/transactions/{id}` - Hapus transaksi

### Asset Management
- `GET /assets` - Dashboard aset
- `POST /assets` - Tambah aset fisik
- `PUT /assets/{id}` - Update aset
- `DELETE /assets/{id}` - Hapus aset
- `GET /assets/summary` - Ringkasan aset

### Debt Management
- `GET /debts` - Dashboard hutang
- `POST /debts` - Tambah hutang
- `PUT /debts/{id}` - Update hutang
- `POST /debts/{id}/payments` - Tambah pembayaran
- `POST /debts/{id}/mark-paid` - Tandai lunas

### Investment Management
- `GET /investments` - Dashboard investasi
- `POST /investments` - Tambah instrumen
- `POST /investments/{id}/purchases` - Tambah pembelian
- `PATCH /investments/{id}/price` - Update harga
- `GET /investments/summary` - Ringkasan portfolio

## 🔧 Konfigurasi

### Environment Variables
```env
APP_NAME=SFHUB
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sfhub
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Custom Configuration
- **Currency Settings**: Default IDR (Rupiah)
- **Date Format**: Indonesian locale (id_ID)
- **Payday**: Default tanggal 25 setiap bulan
- **Budget Alert**: 80% threshold

## 🎨 UI/UX Features

### Responsive Design
- Mobile-first approach
- Dark mode support
- Touch-friendly interface
- Progressive Web App ready

### Accessibility
- Semantic HTML5
- ARIA labels
- Keyboard navigation
- Screen reader support

### Indonesian Localization
- Bahasa Indonesia interface
- Rupiah currency format
- Local date/time formats
- Indonesian financial tips

## 📈 Performance

### Optimization
- Database indexing
- Eager loading relationships
- Asset minification
- Image optimization
- Caching strategies

### Security
- CSRF protection
- XSS prevention
- SQL injection protection
- Input validation
- Rate limiting

## 🤝 Kontribusi

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Use semantic versioning

## 📝 Changelog

### v1.0.0 (2024-01-XX)
- ✅ Initial release
- ✅ Finance management system
- ✅ Asset tracking
- ✅ Debt management
- ✅ Investment portfolio
- ✅ Dashboard with charts
- ✅ Indonesian localization
- ✅ Comprehensive test suite

## 🐛 Troubleshooting

### Common Issues

**1. Migration Error**
```bash
php artisan migrate:fresh --seed
```

**2. Asset Compilation**
```bash
npm run build
# atau
npm run dev
```

**3. Permission Issues**
```bash
chmod -R 775 storage bootstrap/cache
```

**4. Cache Clear**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework For Web Artisans
- [TailwindCSS](https://tailwindcss.com) - Utility-first CSS framework
- [Chart.js](https://www.chartjs.org) - Simple yet flexible JavaScript charting
- [Font Awesome](https://fontawesome.com) - The internet's icon library

## 📞 Support

- 📧 Email: support@sfhub.com
- 📱 WhatsApp: +62 812-3456-7890
- 🐛 Issues: [GitHub Issues](https://github.com/username/SFHUB/issues)
- 📖 Documentation: [Wiki](https://github.com/username/SFHUB/wiki)

---

<p align="center">
  Made with ❤️ for Indonesian Students
</p>
