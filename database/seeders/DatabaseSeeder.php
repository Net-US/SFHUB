<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Task;
use App\Models\SubTask;
use App\Models\Subject;
use App\Models\PklInfo;
use App\Models\PklSchedule;
use App\Models\ThesisMilestone;
use App\Models\FinanceAccount;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Debt;
use App\Models\Asset;
use App\Models\InvestmentInstrument;
use App\Models\CalendarEvent;
use App\Models\Schedule;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Memulai seeding database SFHUB...');

        // ── 1. CREATE USERS ────────────────────────────────────────────────
        $this->command->info('📝 Membuat users...');

        // Admin User
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@sfhub.test',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'plan' => 'premium',
            'occupation' => 'Administrator',
            'phone' => '+628123456789',
            'location' => 'Jakarta, Indonesia',
            'bio' => 'System administrator',
            'email_verified_at' => now(),
        ]);

        // Regular User - Student
        $user = User::create([
            'name' => 'Ahmad',
            'username' => 'ahmad',
            'email' => 'ahmad@student.ac.id',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'is_active' => true,
            'plan' => 'free',
            'occupation' => 'Mahasiswa',
            'phone' => '+628987654321',
            'location' => 'Bandung, Indonesia',
            'bio' => 'Student',
            'email_verified_at' => now(),
        ]);

        // Usep Account
        $usep = User::create([
            'name' => 'Usep',
            'username' => 'usep',
            'email' => 'usep@sfhub.test',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'is_active' => true,
            'plan' => 'free',
            'occupation' => 'Professional',
            'phone' => '+628123456789',
            'location' => 'Jakarta, Indonesia',
            'bio' => 'Personal account for financial management',
            'email_verified_at' => now(),
        ]);

        // Freelancer User
        $freelancer = User::create([
            'name' => 'Sarah',
            'username' => 'sarah',
            'email' => 'sarah@sfhub.test',
            'password' => bcrypt('password123'),
            'role' => 'freelancer',
            'is_active' => true,
            'plan' => 'premium',
            'occupation' => 'Freelance Designer',
            'phone' => '+628112233445',
            'location' => 'Surabaya, Indonesia',
            'bio' => 'Creative professional',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Users created successfully');

        // ── 2. CREATE WORKSPACES ────────────────────────────────────────────
        $this->command->info('🏢 Membuat workspaces...');

        // Admin workspace
        $adminWorkspace = Workspace::create([
            'user_id' => $admin->id,
            'name' => 'System Administration',
            'slug' => 'system-administration',
            'description' => 'Workspace for managing system administration tasks',
            'color' => '#ef4444',
            'icon' => 'shield-alt',
            'type' => 'work',
            'is_default' => true,
            'is_private' => true,
            'settings' => [
                'task_view' => 'kanban',
                'time_tracking' => true,
                'auto_archive' => true
            ]
        ]);

        // Student workspace
        $studentWorkspace = Workspace::create([
            'user_id' => $user->id,
            'name' => 'Kuliah Semester 5',
            'slug' => 'kuliah-semester-5',
            'description' => 'Workspace for managing academic tasks and projects',
            'color' => '#3b82f6',
            'icon' => 'graduation-cap',
            'type' => 'academic',
            'is_default' => true,
            'is_private' => false,
            'settings' => [
                'task_view' => 'list',
                'time_tracking' => false,
                'auto_archive' => false
            ]
        ]);

        // Student personal workspace
        $personalWorkspace = Workspace::create([
            'user_id' => $user->id,
            'name' => 'Personal Development',
            'slug' => 'personal-development',
            'description' => 'Personal goals and self-improvement tasks',
            'color' => '#10b981',
            'icon' => 'user',
            'type' => 'personal',
            'is_default' => false,
            'is_private' => true,
            'settings' => [
                'task_view' => 'calendar',
                'time_tracking' => true,
                'auto_archive' => false
            ]
        ]);

        // Usep's Personal Workspace
        $usepWorkspace = Workspace::create([
            'user_id' => $usep->id,
            'name' => 'Personal Finance',
            'slug' => 'personal-finance',
            'description' => 'Personal finance and task management',
            'color' => '#10b981',
            'icon' => 'wallet',
            'type' => 'personal',
            'is_default' => true,
            'is_private' => true,
            'settings' => [
                'task_view' => 'list',
                'time_tracking' => false,
                'auto_archive' => false
            ]
        ]);

        // Freelancer workspace
        $freelanceWorkspace = Workspace::create([
            'user_id' => $freelancer->id,
            'name' => 'Creative Studio',
            'slug' => 'creative-studio',
            'description' => 'Freelance projects and client work',
            'color' => '#f97316',
            'icon' => 'palette',
            'type' => 'creative',
            'is_default' => true,
            'is_private' => false,
            'settings' => [
                'task_view' => 'kanban',
                'time_tracking' => true,
                'auto_archive' => true,
                'client_tracking' => true
            ]
        ]);

        $this->command->info('✅ Workspaces created successfully');

        // ── 3. CREATE ACADEMIC DATA (For Student) ─────────────────────────────
        $this->command->info('📚 Membuat data akademik...');

        // Subjects
        $subjects = [
            [
                'name' => 'Pemrograman Web Lanjutan',
                'code' => 'IF404',
                'sks' => 3,
                'semester' => 5,
                'day_of_week' => 'Senin',
                'start_time' => '08:00',
                'end_time' => '10:30',
                'room' => 'Lab Komputer A',
                'lecturer' => 'Dr. Budi Santoso, M.Kom',
                'is_active' => true,
                'progress' => 65
            ],
            [
                'name' => 'Kecerdasan Buatan',
                'code' => 'IF405',
                'sks' => 4,
                'semester' => 5,
                'day_of_week' => 'Selasa',
                'start_time' => '13:00',
                'end_time' => '16:30',
                'room' => 'Kelas 301',
                'lecturer' => 'Prof. Siti Nurhaliza, M.T',
                'is_active' => true,
                'progress' => 45
            ],
            [
                'name' => 'Jaringan Komputer',
                'code' => 'IF403',
                'sks' => 3,
                'semester' => 5,
                'day_of_week' => 'Rabu',
                'start_time' => '10:00',
                'end_time' => '12:30',
                'room' => 'Lab Jaringan',
                'lecturer' => 'Ir. Ahmad Fauzi, M.Kom',
                'is_active' => true,
                'progress' => 80
            ]
        ];

        foreach ($subjects as $subject) {
            Subject::create(array_merge(['user_id' => $user->id], $subject));
        }

        // PKL Info
        $pklInfo = PklInfo::create([
            'user_id' => $user->id,
            'company' => 'PT Teknologi Indonesia Maju',
            'department' => 'IT Development',
            'supervisor' => 'Budi Hermawan',
            'supervisor_phone' => '+628123456789',
            'address' => 'Jakarta Selatan, Indonesia',
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->addMonths(4),
            'hours_required' => 720,
            'allowance' => 1500000,
            'is_active' => true
        ]);

        // PKL Schedule
        $pklSchedules = [
            ['day' => 'Senin', 'type' => 'full', 'start_time' => '09:00', 'end_time' => '17:00', 'notes' => 'Regular office day'],
            ['day' => 'Selasa', 'type' => 'full', 'start_time' => '09:00', 'end_time' => '17:00', 'notes' => 'Team meeting at 10:00'],
            ['day' => 'Rabu', 'type' => 'half', 'start_time' => '09:00', 'end_time' => '13:00', 'notes' => 'Work from home afternoon'],
            ['day' => 'Kamis', 'type' => 'split', 'start_time' => '09:00', 'end_time' => '12:00', 'start_time_2' => '13:30', 'end_time_2' => '17:00', 'notes' => 'Split shift schedule'],
            ['day' => 'Jumat', 'type' => 'full', 'start_time' => '09:00', 'end_time' => '16:00', 'notes' => 'Earlier finish on Friday'],
            ['day' => 'Sabtu', 'type' => 'off', 'notes' => 'Weekend'],
            ['day' => 'Minggu', 'type' => 'off', 'notes' => 'Weekend']
        ];

        foreach ($pklSchedules as $schedule) {
            PklSchedule::create(array_merge(['user_id' => $user->id], $schedule));
        }

        // Thesis Milestones
        $thesisMilestones = [
            ['label' => 'Proposal Seminar', 'target_date' => Carbon::now()->addMonths(2), 'sort_order' => 1, 'done' => false, 'is_active' => true],
            ['label' => 'Literature Review', 'target_date' => Carbon::now()->addMonths(3), 'sort_order' => 2, 'done' => false, 'is_active' => true],
            ['label' => 'Data Collection', 'target_date' => Carbon::now()->addMonths(5), 'sort_order' => 3, 'done' => false, 'is_active' => true],
            ['label' => 'Implementation', 'target_date' => Carbon::now()->addMonths(7), 'sort_order' => 4, 'done' => false, 'is_active' => true],
            ['label' => 'Thesis Defense', 'target_date' => Carbon::now()->addMonths(8), 'sort_order' => 5, 'done' => false, 'is_active' => true]
        ];

        foreach ($thesisMilestones as $milestone) {
            ThesisMilestone::create(array_merge(['user_id' => $user->id], $milestone));
        }

        $this->command->info('✅ Academic data created successfully');

        // ── 4. CREATE TASKS ───────────────────────────────────────────────────
        $this->command->info('📋 Membuat tasks...');

        // Student Tasks
        $studentTasks = [
            // Academic Tasks
            [
                'title' => 'Tugas Besar Pemrograman Web',
                'description' => 'Membuat aplikasi e-commerce dengan Laravel dan Vue.js',
                'category' => 'academic',
                'priority' => 'urgent-important',
                'status' => 'doing',
                'due_date' => Carbon::now()->addDays(3),
                'estimated_time' => '20:00',
                'actual_time' => '08:30',
                'progress' => 40,
                'project_type' => 'web_application',
                'linked_subject_id' => 1, // Pemrograman Web Lanjutan
                'workspace_id' => $studentWorkspace->id,
                'tags' => ['laravel', 'vuejs', 'ecommerce'],
                'notes' => 'Need to complete payment integration module'
            ],
            [
                'title' => 'AI Project Report',
                'description' => 'Laporan analisis machine learning untuk prediksi data',
                'category' => 'academic',
                'priority' => 'important-not-urgent',
                'status' => 'todo',
                'due_date' => Carbon::now()->addWeeks(2),
                'estimated_time' => '15:00',
                'progress' => 0,
                'project_type' => 'research',
                'linked_subject_id' => 2, // Kecerdasan Buatan
                'workspace_id' => $studentWorkspace->id,
                'tags' => ['machine-learning', 'python', 'research']
            ],
            [
                'title' => 'Jaringan Lab Assignment',
                'description' => 'Konfigurasi jaringan Cisco Packet Tracer',
                'category' => 'academic',
                'priority' => 'urgent-not-important',
                'status' => 'todo',
                'due_date' => Carbon::now()->addDays(5),
                'estimated_time' => '08:00',
                'progress' => 0,
                'project_type' => 'lab_assignment',
                'linked_subject_id' => 3, // Jaringan Komputer
                'workspace_id' => $studentWorkspace->id,
                'tags' => ['networking', 'cisco', 'lab']
            ],
            // Creative Tasks
            [
                'title' => 'Logo Design for Client',
                'description' => 'Design company logo for startup tech company',
                'category' => 'creative',
                'priority' => 'urgent-important',
                'status' => 'doing',
                'due_date' => Carbon::now()->addDays(2),
                'estimated_time' => '12:00',
                'actual_time' => '06:00',
                'progress' => 50,
                'project_type' => 'graphic_design',
                'client' => 'TechStart Indonesia',
                'budget' => 1500000,
                'workspace_id' => $personalWorkspace->id,
                'tags' => ['logo', 'branding', 'illustrator']
            ],
            [
                'title' => 'Video Editing Project',
                'description' => 'Edit promotional video for product launch',
                'category' => 'creative',
                'priority' => 'important-not-urgent',
                'status' => 'todo',
                'due_date' => Carbon::now()->addWeeks(1),
                'estimated_time' => '16:00',
                'progress' => 0,
                'project_type' => 'video_editing',
                'client' => 'Creative Agency',
                'budget' => 2500000,
                'workspace_id' => $personalWorkspace->id,
                'tags' => ['video-editing', 'after-effects', 'premiere-pro']
            ],
            // Personal Tasks
            [
                'title' => 'Workout Routine',
                'description' => 'Exercise 3 times per week for fitness goals',
                'category' => 'health',
                'priority' => 'not-urgent-not-important',
                'status' => 'doing',
                'due_date' => Carbon::now()->addMonths(3),
                'estimated_time' => '03:00',
                'actual_time' => '01:30',
                'progress' => 60,
                'project_type' => 'personal',
                'workspace_id' => $personalWorkspace->id,
                'tags' => ['fitness', 'health', 'routine']
            ],
            [
                'title' => 'Read Technical Books',
                'description' => 'Complete reading 2 technical books this month',
                'category' => 'personal',
                'priority' => 'important-not-urgent',
                'status' => 'todo',
                'due_date' => Carbon::now()->addMonth(),
                'estimated_time' => '20:00',
                'progress' => 25,
                'project_type' => 'self_development',
                'workspace_id' => $personalWorkspace->id,
                'tags' => ['reading', 'learning', 'technical']
            ]
        ];

        foreach ($studentTasks as $taskData) {
            $task = Task::create(array_merge(['user_id' => $user->id], $taskData));

            // Add subtasks for some tasks
            if ($task->title === 'Tugas Besar Pemrograman Web') {
                SubTask::create([
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'title' => 'Setup Laravel Project',
                    'description' => 'Initialize Laravel with Vue.js frontend',
                    'status' => 'completed',
                    'order' => 1
                ]);
                SubTask::create([
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'title' => 'Create Database Schema',
                    'description' => 'Design and migrate database tables',
                    'status' => 'completed',
                    'order' => 2
                ]);
                SubTask::create([
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'title' => 'Implement User Authentication',
                    'description' => 'Login, register, and user management',
                    'status' => 'in_progress',
                    'order' => 3
                ]);
                SubTask::create([
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'title' => 'Build Product Catalog',
                    'description' => 'Product listing and detail pages',
                    'status' => 'pending',
                    'order' => 4
                ]);
            }
        }

        // Freelancer Tasks
        $freelanceTasks = [
            [
                'title' => 'Brand Identity Package',
                'description' => 'Complete brand identity for new restaurant',
                'category' => 'creative',
                'priority' => 'urgent-important',
                'status' => 'doing',
                'due_date' => Carbon::now()->addDays(4),
                'estimated_time' => '25:00',
                'actual_time' => '15:00',
                'progress' => 60,
                'project_type' => 'branding',
                'client' => 'Warung Nusantara',
                'budget' => 5000000,
                'workspace_id' => $freelanceWorkspace->id,
                'tags' => ['branding', 'logo', 'menu-design']
            ],
            [
                'title' => 'Social Media Content',
                'description' => 'Create 30 social media posts for campaign',
                'category' => 'creative',
                'priority' => 'important-not-urgent',
                'status' => 'todo',
                'due_date' => Carbon::now()->addWeeks(2),
                'estimated_time' => '20:00',
                'progress' => 10,
                'project_type' => 'social_media',
                'client' => 'Fashion Boutique',
                'budget' => 3000000,
                'workspace_id' => $freelanceWorkspace->id,
                'tags' => ['social-media', 'content', 'instagram']
            ],
            [
                'title' => 'Motion Graphics Intro',
                'description' => '15-second intro for YouTube channel',
                'category' => 'creative',
                'priority' => 'urgent-not-important',
                'status' => 'todo',
                'due_date' => Carbon::now()->addDays(7),
                'estimated_time' => '12:00',
                'progress' => 0,
                'project_type' => 'motion_graphics',
                'client' => 'Tech YouTuber',
                'budget' => 2000000,
                'workspace_id' => $freelanceWorkspace->id,
                'tags' => ['motion-graphics', 'after-effects', 'youtube']
            ]
        ];

        foreach ($freelanceTasks as $taskData) {
            Task::create(array_merge(['user_id' => $freelancer->id], $taskData));
        }

        $this->command->info('✅ Tasks created successfully');

        // ── 5. CREATE FINANCE DATA ───────────────────────────────────────────
        $this->command->info('💰 Membuat data keuangan...');

        // Finance Accounts
        $accounts = [
            [
                'name' => 'Bank BCA',
                'type' => 'bank',
                'balance' => 5000000,
                'currency' => 'IDR',
                'account_number' => '1234567890'
            ],
            [
                'name' => 'E-Wallet Dana',
                'type' => 'ewallet',
                'balance' => 1500000,
                'currency' => 'IDR'
            ],
            [
                'name' => 'Business Account',
                'type' => 'business',
                'balance' => 12000000,
                'currency' => 'IDR'
            ]
        ];

        foreach ($accounts as $account) {
            FinanceAccount::create(array_merge(['user_id' => $user->id], $account));
        }

        // Usep's Finance Accounts (Realistic Data)
        $usepAccounts = [
            [
                'name' => 'Cash',
                'type' => 'cash',
                'balance' => 250000,
                'currency' => 'IDR',
                'color' => '#10b981',
                'icon' => 'banknote'
            ],
            [
                'name' => 'Bank BRI',
                'type' => 'bank',
                'balance' => 5000000,
                'currency' => 'IDR',
                'account_number' => '1234567890',
                'color' => '#1e40af',
                'icon' => 'bank'
            ],
            [
                'name' => 'Bank JAGO',
                'type' => 'bank',
                'balance' => 3000000,
                'currency' => 'IDR',
                'account_number' => '0987654321',
                'color' => '#06b6d4',
                'icon' => 'bank'
            ],
            [
                'name' => 'GoPay',
                'type' => 'ewallet',
                'balance' => 1500000,
                'currency' => 'IDR',
                'color' => '#10b981',
                'icon' => 'wallet'
            ],
            [
                'name' => 'DANA',
                'type' => 'ewallet',
                'balance' => 2000000,
                'currency' => 'IDR',
                'color' => '#0891b2',
                'icon' => 'credit-card'
            ],
            [
                'name' => 'ShopeePay',
                'type' => 'ewallet',
                'balance' => 1200000,
                'currency' => 'IDR',
                'color' => '#dc2626',
                'icon' => 'shopping-bag'
            ],
            [
                'name' => 'Indodax',
                'type' => 'investment',
                'balance' => 7500000,
                'currency' => 'IDR',
                'color' => '#f59e0b',
                'icon' => 'trending-up'
            ],
            [
                'name' => 'Ajaib Kripto',
                'type' => 'investment',
                'balance' => 215000000,
                'currency' => 'IDR',
                'color' => '#8b5cf6',
                'icon' => 'coins'
            ]
        ];

        foreach ($usepAccounts as $account) {
            FinanceAccount::create(array_merge(['user_id' => $usep->id], $account));
        }

        // Recent Transactions
        $transactions = [
            [
                'finance_account_id' => 1,
                'type' => 'expense',
                'amount' => 150000,
                'category' => 'food',
                'description' => 'Makan siang di kampus',
                'transaction_date' => Carbon::now()->subDays(1)
            ],
            [
                'finance_account_id' => 1,
                'type' => 'income',
                'amount' => 2000000,
                'category' => 'freelance',
                'description' => 'Payment logo design project',
                'transaction_date' => Carbon::now()->subDays(3)
            ],
            [
                'finance_account_id' => 2,
                'type' => 'expense',
                'amount' => 50000,
                'category' => 'transportation',
                'description' => 'Gojek to campus',
                'transaction_date' => Carbon::now()->subDays(2)
            ]
        ];

        foreach ($transactions as $transaction) {
            Transaction::create(array_merge(['user_id' => $user->id], $transaction));
        }

        // Usep's Recent Transactions (Realistic Data)
        $usepTransactions = [
            [
                'finance_account_id' => 4, // Bank Mandiri
                'type' => 'income',
                'amount' => 5000000,
                'category' => 'salary',
                'description' => 'Gaji Bulanan',
                'transaction_date' => Carbon::now()->subDays(5)
            ],
            [
                'finance_account_id' => 4, // Bank Mandiri
                'type' => 'expense',
                'amount' => 1200000,
                'category' => 'rent',
                'description' => 'Bayar Kos Bulanan',
                'transaction_date' => Carbon::now()->subDays(3)
            ],
            [
                'finance_account_id' => 5, // Gopay
                'type' => 'expense',
                'amount' => 85000,
                'category' => 'food',
                'description' => 'Makan siang',
                'transaction_date' => Carbon::now()->subDays(1)
            ],
            [
                'finance_account_id' => 6, // OVO
                'type' => 'expense',
                'amount' => 45000,
                'category' => 'transportation',
                'description' => 'Gojek ke kantor',
                'transaction_date' => Carbon::now()->subDays(1)
            ],
            [
                'finance_account_id' => 4, // Bank Mandiri
                'type' => 'expense',
                'amount' => 350000,
                'category' => 'utilities',
                'description' => 'Listrik & Air',
                'transaction_date' => Carbon::now()->subDays(7)
            ],
            [
                'finance_account_id' => 7, // Tabungan Emas
                'type' => 'expense',
                'amount' => 500000,
                'category' => 'investment',
                'description' => 'Beli Tabungan Emas',
                'transaction_date' => Carbon::now()->subDays(10)
            ]
        ];

        foreach ($usepTransactions as $transaction) {
            Transaction::create(array_merge(['user_id' => $usep->id], $transaction));
        }

        // Budgets
        $budgets = [
            [
                'category' => 'Food',
                'amount' => 1500000,
                'spent_amount' => 850000,
                'period' => 'monthly',
                'alert_threshold' => 80
            ],
            [
                'category' => 'Transportation',
                'amount' => 500000,
                'spent_amount' => 320000,
                'period' => 'monthly',
                'alert_threshold' => 80
            ]
        ];

        foreach ($budgets as $budget) {
            Budget::create(array_merge(['user_id' => $user->id], $budget));
        }

        // Usep's Budgets (Realistic Data)
        $usepBudgets = [
            [
                'category' => 'Makanan',
                'amount' => 2000000,
                'spent_amount' => 850000,
                'period' => 'monthly',
                'alert_threshold' => 80
            ],
            [
                'category' => 'Transportasi',
                'amount' => 500000,
                'spent_amount' => 320000,
                'period' => 'monthly',
                'alert_threshold' => 85
            ],
            [
                'category' => 'Listrik & Air',
                'amount' => 400000,
                'spent_amount' => 350000,
                'period' => 'monthly',
                'alert_threshold' => 90
            ],
            [
                'category' => 'Hiburan',
                'amount' => 800000,
                'spent_amount' => 450000,
                'period' => 'monthly',
                'alert_threshold' => 75
            ],
            [
                'category' => 'Investasi',
                'amount' => 1000000,
                'spent_amount' => 500000,
                'period' => 'monthly',
                'alert_threshold' => 100
            ]
        ];

        foreach ($usepBudgets as $budget) {
            Budget::create(array_merge(['user_id' => $usep->id], $budget));
        }

        $this->command->info('✅ Finance data created successfully');

        // ── 6. CREATE SCHEDULES AND EVENTS ───────────────────────────────────
        $this->command->info('📅 Membuat jadwal dan events...');

        // Daily Routines
        $routines = [
            [
                'day' => 'Senin',
                'start_time' => '06:00',
                'end_time' => '06:30',
                'activity' => 'Morning Exercise',
                'type' => 'routine',
                'location' => 'Home Gym',
                'frequency' => 'daily',
                'days_of_week' => 'Senin,Selasa,Rabu,Kamis,Jumat',
                'notes' => '30 minutes cardio and strength training',
                'color' => '#10b981'
            ],
            [
                'day' => 'Senin',
                'start_time' => '19:00',
                'end_time' => '21:00',
                'activity' => 'Study Time',
                'type' => 'study',
                'location' => 'Home Office',
                'frequency' => 'daily',
                'days_of_week' => 'Senin,Selasa,Rabu,Kamis',
                'notes' => 'Focus on academic assignments',
                'color' => '#3b82f6'
            ],
            [
                'day' => 'Sabtu',
                'start_time' => '09:00',
                'end_time' => '15:00',
                'activity' => 'Freelance Work',
                'type' => 'work',
                'location' => 'Home Office',
                'frequency' => 'weekly',
                'days_of_week' => 'Sabtu,Minggu',
                'notes' => 'Client project development',
                'color' => '#f97316'
            ]
        ];

        foreach ($routines as $routine) {
            Schedule::create(array_merge(['user_id' => $user->id], $routine));
        }

        // Calendar Events
        $events = [
            [
                'title' => 'Team Meeting',
                'description' => 'Weekly sync with development team',
                'start_time' => Carbon::now()->next(Carbon::TUESDAY)->setTime(10, 0),
                'end_time' => Carbon::now()->next(Carbon::TUESDAY)->setTime(11, 0),
                'location' => 'Meeting Room A',
                'type' => 'meeting',
                'color' => '#3b82f6',
                'is_recurring' => true,
                'recurring_rule' => 'weekly'
            ],
            [
                'title' => 'Client Presentation',
                'description' => 'Present logo design concepts',
                'start_time' => Carbon::now()->addDays(2)->setTime(14, 0),
                'end_time' => Carbon::now()->addDays(2)->setTime(15, 30),
                'location' => 'Client Office',
                'type' => 'meeting',
                'color' => '#f97316'
            ],
            [
                'title' => 'Mid Exam Pemrograman Web',
                'description' => 'UTS Pemrograman Web Lanjutan',
                'start_time' => Carbon::now()->addWeeks(2)->setTime(8, 0),
                'end_time' => Carbon::now()->addWeeks(2)->setTime(10, 0),
                'location' => 'Lab Komputer A',
                'type' => 'exam',
                'color' => '#ef4444'
            ]
        ];

        foreach ($events as $event) {
            CalendarEvent::create(array_merge(['user_id' => $user->id], $event));
        }

        $this->command->info('✅ Schedules and events created successfully');

        // ── 7. CREATE LANDING PAGE DATA ───────────────────────────────────────
        $this->command->info('🏠 Membuat data landing page...');

        $this->call(LandingPageSeeder::class);

        $this->command->info('✅ Landing page data created successfully');

        // ── 7. CREATE BLOG POSTS ─────────────────────────────────────────────
        $this->command->info('📝 Membuat blog posts...');
        $this->call(BlogPostSeeder::class);

        // ── SUMMARY ────────────────────────────────────────────────────────
        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📊 Created Data Summary:');
        $this->command->info('├── Users: 4 (1 admin, 2 students, 1 freelancer)');
        $this->command->info('├── Workspaces: 5');
        $this->command->info('├── Tasks: 12+ with various categories');
        $this->command->info('├── Subjects: 3');
        $this->command->info('├── PKL Schedule: 7 days');
        $this->command->info('├── Thesis Milestones: 5');
        $this->command->info('├── Finance Accounts: 7');
        $this->command->info('├── Transactions: 9');
        $this->command->info('├── Budgets: 7');
        $this->command->info('├── Schedules: 3');
        $this->command->info('└── Calendar Events: 3');
        $this->command->info('');
        $this->command->info('🔑 Login Credentials:');
        $this->command->info('├── Admin: admin@sfhub.test / admin123');
        $this->command->info('├── Student: ahmad@student.ac.id / password123');
        $this->command->info('├── Freelancer: sarah@sfhub.test / password123');
        $this->command->info('└── Usep (Your Account): usep@sfhub.test / password123');
        $this->command->info('');
        $this->command->info('💰 Usep Finance Data:');
        $this->command->info('├── Cash: Rp 250.000');
        $this->command->info('├── Bank BRI: Rp 5.000.000');
        $this->command->info('├── Bank JAGO: Rp 3.000.000');
        $this->command->info('├── GoPay: Rp 1.500.000');
        $this->command->info('├── DANA: Rp 2.000.000');
        $this->command->info('├── ShopeePay: Rp 1.200.000');
        $this->command->info('├── Indodax: Rp 7.500.000 (BTC)');
        $this->command->info('├── Ajaib Kripto: Rp 215.000.000 (BTC, ETH, SOL, ADA)');
        $this->command->info('├── Total Assets: Rp 235.450.000');
        $this->command->info('├── Recent Transactions: 6');
        $this->command->info('└── Monthly Budgets: 5');
        $this->command->info('');
        $this->command->info('🚀 You can now login and explore the dashboard!');
    }
}
