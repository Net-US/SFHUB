# SFHUB - Sistem Manajemen Mahasiswa & Konten Kreator

Dashboard personal all-in-one untuk mahasiswa, freelancer, dan content creator. Dibangun dengan Laravel 12 + Livewire 4.

## Fitur Utama

### 1. AI Priority System ("What to do NOW")
Sistem cerdas yang merekomendasikan task prioritas berdasarkan:
- **Deadline mendesak** (hari ini/besok)
- **PKL/Kuliah** yang mendekati deadline
- **Target konten** yang belum tercapai
- **Gap waktu kosong** antar jadwal
- **Blok waktu tetap**: tidur & sholat

### 2. Sistem Konflik & Resolusi
- Deteksi otomatis konflik jadwal
- Resolusi: ganti jadwal, batalkan, atau skip
- Penanganan konflik event vs schedule

### 3. Content Creator Tracker
Tracking target konten per platform:
- Instagram, YouTube, TikTok, Twitter, LinkedIn
- Shutterstock, Behance, Dribbble
- Progress mingguan/bulanan
- Alert deadline approaching

### 4. Dashboard Sections
- **Today Priority**: "What to do NOW" recommendations
- **Productivity**: Task stats, PKL streak, content progress
- **Finance**: Saldo, income/expense, budget tracking
- **Assets**: Daftar aset dengan depresiasi/appresiasi
- **Debts**: Hutang & piutang dengan tracking
- **Investments**: Portfolio saham/reksadana/ crypto
- **Schedule Manager**: CRUD jadwal dengan conflict detection
- **Task Manager**: CRUD task dengan timer & subject linking
- **Settings**: Profile, sleep/prayer times, content platforms, notifikasi

## Tech Stack

- **Framework**: Laravel 12
- **Live Components**: Livewire 4
- **Styling**: Tailwind CSS
- **Database**: MySQL/PostgreSQL
- **Charts**: Chart.js (via CDN)
- **Icons**: Font Awesome

## Installation

```bash
# Clone repository
git clone <repository-url>
cd SFHUB

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Run server
php artisan serve
```

## Struktur Project

```
SFHUB/
├── app/
│   ├── Livewire/
│   │   ├── Dashboard/
│   │   │   ├── TodayPriority.php
│   │   │   ├── ProductivityDashboard.php
│   │   │   ├── FinanceDashboard.php
│   │   │   ├── AssetsDashboard.php
│   │   │   ├── DebtsDashboard.php
│   │   │   └── InvestmentsDashboard.php
│   │   ├── Schedule/
│   │   │   └── ScheduleManager.php
│   │   ├── Tasks/
│   │   │   └── TaskManager.php
│   │   └── Settings/
│   │       └── UserSettings.php
│   ├── Services/
│   │   ├── PriorityEngine.php
│   │   ├── ConflictResolver.php
│   │   └── ContentTrackerService.php
│   └── Models/
│       ├── Event.php
│       ├── ContentSchedule.php
│       ├── Budget.php
│       ├── Subject.php
│       ├── ScheduleOverride.php
│       └── ... (existing models)
├── database/
│   └── migrations/
│       ├── 2026_03_16_000001_create_events_table.php
│       ├── 2026_03_16_000002_create_content_schedules_table.php
│       ├── 2026_03_16_000003_create_budgets_table.php
│       ├── 2026_03_16_000004_create_subjects_table.php
│       └── 2026_03_16_000005_create_schedule_overrides_table.php
└── resources/
    └── views/
        └── livewire/
            ├── dashboard/
            ├── schedule/
            ├── tasks/
            └── settings/
```

## Priority Engine Logic

### Hierarchy Task Prioritas

1. **URGENT - Deadline hari ini/besok**
   - Task yang `due_date` hari ini atau besok
   - Status bukan "done"

2. **HIGH - PKL/Kuliah mendekati deadline**
   - Task dengan kategori "pkl" atau "academic"
   - Due date <= 3 hari
   - Atau `linked_subject_id` tidak null

3. **MEDIUM - Content deadline approaching**
   - Content schedule dengan due date mendekati
   - Target belum tercapai

4. **LOW - High priority tasks**
   - Priority = "urgent-important"

5. **VERY LOW - Personal tasks**
   - Kategori "personal" atau "important-not-urgent"

### Gap Detection

Sistem mendeteksi gap waktu kosong antara:
- Jadwal rutin (schedule)
- Event satu kali (events)
- Blok waktu tetap: tidur (22:00-06:00) & sholat

### Conflict Resolution

Ketika ada konflik event vs schedule:
1. Tampilkan notifikasi konflik
2. User bisa:
   - Skip jadwal rutin (buat override)
   - Reschedule event
   - Batalkan event

## API Endpoints

### Livewire Components
- `GET /dashboard/livewire/today-priority` - TodayPriority component
- `GET /dashboard/livewire/productivity` - ProductivityDashboard component
- `GET /dashboard/livewire/finance` - FinanceDashboard component
- `GET /dashboard/livewire/assets` - AssetsDashboard component
- `GET /dashboard/livewire/debts` - DebtsDashboard component
- `GET /dashboard/livewire/investments` - InvestmentsDashboard component
- `GET /dashboard/livewire/schedule` - ScheduleManager component
- `GET /dashboard/livewire/tasks` - TaskManager component
- `GET /dashboard/livewire/settings` - UserSettings component

### AJAX API
- `GET /dashboard/recommendations` - Get AI recommendations
- `GET /dashboard/today-tasks` - Get today's tasks
- `GET /dashboard/today-schedule` - Get today's schedule

## Models & Relationships

### New Models

**Event**
- `user_id`, `title`, `date`, `start_time`, `end_time`
- `type` (seminar/deadline/acara/lainnya)
- `location`, `notes`

**ContentSchedule**
- `user_id`, `platform`, `content_type`
- `frequency` (weekly/monthly)
- `target_per_period`, `completed_count`
- `due_date`, `status`

**Budget**
- `user_id`, `category`, `amount`
- `spent_amount`, `period` (monthly/weekly)
- `alert_threshold`, `is_active`

**Subject**
- `user_id`, `name`, `code`, `sks`
- `day_of_week`, `start_time`, `end_time`
- `room`, `lecturer`, `is_active`

**ScheduleOverride**
- `user_id`, `schedule_id`, `date`
- `reason`, `replaced_by_event_id`, `is_cancelled`

## Seeding Data

```bash
# Seed all data including dummy data
php artisan db:seed

# Or specific seeder
php artisan db:seed --class=NewEntitiesSeeder
```

## Testing

```bash
# Run PHPUnit tests
php artisan test

# Run specific test
php artisan test --filter=PriorityEngineTest
```

## License

MIT License

## Author

StudentHub Team
