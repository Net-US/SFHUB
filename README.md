# SFHUB - Student-Freelancer Hub

> 🚀 **Platform Manajemen Terintegrasi untuk Mahasiswa & Freelancer**

SFHUB adalah aplikasi web berbasis Laravel yang dirancang khusus untuk membantu mahasiswa dan freelancer mengelola berbagai aspek kehidupan mereka secara efisien. Platform ini menggabungkan manajemen akademik, keuangan, produktivitas, dan karir dalam satu sistem yang terintegrasi.

---

## 📋 **Tabel Konten**

- [🌟 Fitur Utama](#-fitur-utama)
- [🛠️ Teknologi & Stack](#️-teknologi--stack)
- [📊 Struktur Database](#-struktur-database)
- [🚀 Instalasi & Setup](#-instalasi--setup)
- [📖 Panduan Penggunaan](#-panduan-penggunaan)
- [👥 User Roles & Akses](#-user-roles--akses)
- [🔧 Integrasi & API](#-integrasi--api)
- [📈 Pengembangan Masa Depan](#-pengembangan-masa-depan)
- [🤝 Kontribusi](#-kontribusi)
- [📄 Lisensi](#-lisensi)

---

## 🌟 **Fitur Utama**

### 📚 **Manajemen Akademik**
- **Course Management**: Kelola mata kuliah, jadwal, dan sesi belajar
- **Task Tracking**: Pantau tugas-tugas akademik dengan deadline
- **Thesis Milestones**: Tracking progress skripsi/tugas akhir
- **Study Sessions**: Log sesi belajar dengan durasi dan progress

### 💰 **Manajemen Keuangan**
- **Multi-Account Support**: Kelola rekening bank, e-wallet, dan akun investasi
- **Transaction Tracking**: Catat pemasukan & pengeluaran otomatis
- **Budget Planning**: Buat dan pantau anggaran bulanan
- **Investment Portfolio**: Kelola aset kripto dan investasi lainnya
- **Savings Goals**: Tetapkan dan tracking target tabungan
- **Debt Management**: Kelola utang dan cicilan

### 📈 **Produktivitas & Task Management**
- **General Task Tracker**: Kelola tugas harian dengan kategori
- **Creative Studio**: Manajemen proyek kreatif & freelance
- **PKL Manager**: Tracking aktivitas magang/internship
- **Productivity Log**: Analisis produktivitas harian

### 📅 **Smart Calendar**
- **Event Management**: Kelola event one-off dan recurring
- **Schedule Integration**: Sinkronisasi jadwal dari semua modul
- **Conflict Detection**: Deteksi bentrokan jadwal otomatis
- **Day Overview**: Tampilan harian terintegrasi

### 🎯 **Dashboard Terintegrasi**
- **Unified View**: Satu dashboard untuk semua modul
- **Real-time Updates**: Data real-time dengan WebSocket
- **Analytics & Insights**: Grafik dan statistik performa
- **Personalized Recommendations**: Saran berdasarkan data pengguna

---

## 🛠️ **Teknologi & Stack**

### **Backend**
- **Framework**: Laravel 12.0
- **PHP**: ^8.2
- **Database**: MySQL/PostgreSQL
- **Queue System**: Redis Queue
- **Authentication**: Laravel Sanctum

### **Frontend**
- **CSS Framework**: TailwindCSS 4.0
- **JavaScript**: Vanilla JS + Axios
- **Build Tool**: Vite 7.0
- **Real-time**: Livewire 4.1

### **DevOps & Tools**
- **Package Manager**: Composer + NPM
- **Testing**: PHPUnit
- **Code Style**: Laravel Pint
- **Version Control**: Git

---

## 📊 **Struktur Database**

### **Core Models**
```php
User                    // Data pengguna & profil
Profile                 // Profil lengkap pengguna
Workspace              // Ruang kerja virtual
```

### **Academic Module**
```php
Subject                // Mata kuliah
SubjectSession         // Sesi belajar
Task                   // Tugas akademik
ThesisMilestone        // Milestone skripsi
```

### **Finance Module**
```php
FinanceAccount         // Rekening (bank, e-wallet, investasi)
Transaction            // Transaksi keuangan
Budget                // Anggaran bulanan
SavingsGoal           // Target tabungan
Debt                  // Data utang
DebtPayment           // Pembayaran utang
```

### **Productivity Module**
```php
Task                   // Tugas umum
SubTask               // Sub-tugas
ProductivityLog       // Log produktivitas
PklInfo              // Info PKL/internship
PklSchedule          // Jadwal PKL
PklLog               // Log aktivitas PKL
```

### **Calendar Module**
```php
CalendarEvent         // Event one-off
Schedule              // Jadwal recurring
ContentSchedule       // Jadwal konten
```

---

## 🚀 **Instalasi & Setup**

### **Prerequisites**
- PHP ^8.2
- Composer
- Node.js ^18
- MySQL/PostgreSQL
- Redis (optional)

### **Quick Setup**
```bash
# 1. Clone repository
git clone <repository-url> sfhub
cd sfhub

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Database setup
php artisan migrate
php artisan db:seed

# 5. Build assets
npm run build

# 6. Start development server
php artisan serve
```

### **Development Commands**
```bash
# Development mode (all services)
composer run dev

# Production build
npm run build
composer run prod

# Testing
composer run test

# Database operations
php artisan migrate:fresh --seed
php artisan db:seed --class=DatabaseSeeder
```

---

## 📖 **Panduan Penggunaan**

### **🔑 Login Akun Demo**

#### **Admin Access**
- **Email**: admin@sfhub.test
- **Password**: admin123
- **Role**: Full system administrator

#### **Student Access**
- **Email**: ahmad@student.ac.id
- **Password**: password123
- **Role**: Mahasiswa dengan data akademik lengkap

#### **Freelancer Access**
- **Email**: sarah@sfhub.test
- **Password**: password123
- **Role**: Freelancer dengan proyek aktif

#### **Personal Finance Access**
- **Email**: usep@sfhub.test
- **Password**: password123
- **Role**: User dengan data keuangan lengkap

### **📱 Navigasi Utama**

#### **1. Dashboard Overview**
- Akses: `/dashboard`
- Fitur: Overview semua modul, task hari ini, jadwal, statistik

#### **2. Academic Module**
- Akses: `/dashboard/academic`
- Fitur: Kelola mata kuliah, tugas, sesi belajar, progress skripsi

#### **3. Finance Module**
- Akses: `/dashboard/finance`
- Fitur: Kelola rekening, transaksi, budget, investasi, utang

#### **4. Productivity Module**
- Akses: `/dashboard/tracker`
- Fitur: Task management, creative studio, PKL tracking

#### **5. Calendar Module**
- Akses: `/dashboard/smart-calendar`
- Fitur: Event management, jadwal recurring, conflict detection

### **💡 Tips Penggunaan**

#### **Untuk Mahasiswa**
1. **Setup Academic**: Tambahkan mata kuliah dan jadwal kuliah
2. **Task Planning**: Buat tugas untuk setiap mata kuliah dengan deadline
3. **Study Sessions**: Log sesi belajar untuk tracking produktivitas
4. **Calendar Integration**: Sinkronkan jadwal kuliah dengan personal calendar

#### **Untuk Freelancer**
1. **Creative Studio**: Kelola proyek freelance dengan sub-tasks
2. **Finance Tracking**: Monitor income dari berbagai sumber
3. **Productivity Log**: Track waktu kerja dan produktivitas
4. **Client Management**: Kelola jadwal meeting dengan klien

#### **Untuk Finance Management**
1. **Account Setup**: Tambahkan semua rekening bank dan e-wallet
2. **Transaction Recording**: Catat semua transaksi harian
3. **Budget Planning**: Buat anggaran bulanan per kategori
4. **Investment Tracking**: Monitor portfolio investasi dan kripto

---

## 👥 **User Roles & Akses**

### **🔧 Admin**
- **Access**: Full system access
- **Features**: User management, system settings, analytics
- **Permissions**: CRUD semua data, system configuration

### **👨‍🎓 Student**
- **Access**: Academic + personal finance + productivity
- **Features**: Course management, task tracking, basic finance
- **Permissions**: CRUD data pribadi, read-only analytics

### **💼 Freelancer**
- **Access**: Creative studio + finance + productivity
- **Features**: Project management, advanced finance, client tracking
- **Permissions**: CRUD data proyek, advanced finance features

### **👤 Regular User**
- **Access**: Basic productivity + personal finance
- **Features**: Task management, basic finance tracking
- **Permissions**: CRUD data pribadi, limited analytics

---

## 🔧 **Integrasi & API**

### **Internal API Endpoints**

#### **Authentication**
```http
POST /login          # User login
POST /register       # User registration
POST /logout         # User logout
```

#### **Finance API**
```http
GET  /finance/summary           # Finance overview
POST /finance/accounts          # Create account
GET  /finance/transactions      # Get transactions
POST /finance/transactions      # Add transaction
```

#### **Task API**
```http
GET  /tasks                     # Get user tasks
POST /tasks                     # Create task
PUT  /tasks/{id}                # Update task
DELETE /tasks/{id}              # Delete task
```

#### **Calendar API**
```http
GET  /calendar/day/{date}      # Get day schedule
POST /calendar/events           # Create event
POST /calendar/schedules        # Create recurring schedule
```

### **External Integrations (Future)**

#### **Payment Gateways**
- **Midtrans**: Payment processing
- **Xendit**: Disbursement & payments
- **Stripe**: International payments

#### **Banking APIs**
- **BRI API**: Account verification
- **Mandiri API**: Transaction history
- **BCA API**: Balance checking

#### **Investment Platforms**
- **Indodax API**: Crypto portfolio sync
- **Ajaib API**: Investment data
- **Tokopedia API**: Mutual fund data

---

## 📈 **Pengembangan Masa Depan**

### **🚀 Phase 1 - Core Enhancement (Q2 2026)**
- [ ] **Mobile App**: React Native iOS/Android
- [ ] **Real-time Sync**: WebSocket implementation
- [ ] **Advanced Analytics**: ML-based insights
- [ ] **API Documentation**: OpenAPI/Swagger

### **🎯 Phase 2 - Ecosystem Integration (Q3 2026)**
- [ ] **Banking Integration**: Direct bank API connections
- [ ] **Payment Gateway**: Automated payment processing
- [ ] **Investment API**: Real-time portfolio sync
- [ ] **Calendar Sync**: Google Calendar/Outlook integration

### **🌟 Phase 3 - AI & Automation (Q4 2026)**
- [ ] **AI Assistant**: Personal finance advisor
- [ ] **Smart Recommendations**: Predictive analytics
- [ ] **Automated Categorization**: ML-based transaction categorization
- [ ] **Voice Commands**: Speech-to-text task management

### **💎 Phase 4 - Enterprise Features (Q1 2027)**
- [ ] **Multi-tenant**: Organization accounts
- [ ] **Advanced Reporting**: Custom reports & exports
- [ ] **API Marketplace**: Third-party integrations
- [ ] **White-label**: Custom branding options

---

## 🤝 **Kontribusi**

### **Development Guidelines**
1. **Code Style**: Follow Laravel conventions
2. **Testing**: Write unit tests for new features
3. **Documentation**: Update README and API docs
4. **Git Flow**: Use feature branches and PRs

### **Contribution Steps**
```bash
# 1. Fork repository
# 2. Create feature branch
git checkout -b feature/new-feature

# 3. Make changes and test
php artisan test
npm run build

# 4. Commit and push
git commit -m "Add new feature"
git push origin feature/new-feature

# 5. Create Pull Request
```

### **Issue Reporting**
- **Bug Reports**: Use GitHub Issues with detailed description
- **Feature Requests**: Submit with use case and implementation ideas
- **Security Issues**: Email to security@sfhub.test

---

## 📄 **Lisensi**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 📞 **Support & Contact**

- **Documentation**: [docs.sfhub.test](https://docs.sfhub.test)
- **Support Email**: support@sfhub.test
- **Discord Community**: [discord.gg/sfhub](https://discord.gg/sfhub)
- **Twitter**: [@sfhub_platform](https://twitter.com/sfhub_platform)

---

## 🙏 **Acknowledgments**

- **Laravel Team**: Excellent PHP framework
- **TailwindCSS**: Utility-first CSS framework
- **Livewire Team**: Dynamic components without JavaScript complexity
- **Open Source Community**: All packages and libraries used

---

> **Built with ❤️ for Indonesian Students & Freelancers**

*Last Updated: March 2026*
