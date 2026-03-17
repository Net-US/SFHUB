<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Services\PriorityEngine;
use App\Models\ProductivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{

    private $priorityEngine;

    public static function middleware(): array
    {
        return [
            'auth',
        ];
    }

    /**
     * Display the dashboard with SMART recommendations
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // --- Cek apakah ada simulasi waktu dari request ---
        $simulatedHour = $request->input('simulated_hour', null);

        if ($simulatedHour !== null) {
            $simulatedHour = intval($simulatedHour);
            // Pastikan jam valid (0-23)
            $simulatedHour = max(0, min(23, $simulatedHour));
            $now = Carbon::now()->setHour($simulatedHour)->setMinute(0);
            $isSimulation = true;
        } else {
            $now = Carbon::now();
            $isSimulation = false;
        }

        // --- Tentukan waktu berdasarkan waktu (nyata atau simulasi) ---
        $hour = $now->hour;
        if ($hour >= 0 && $hour < 5) {
            $timeOfDay = 'Dini Hari';
        } elseif ($hour >= 5 && $hour < 11) {
            $timeOfDay = 'Pagi';
        } elseif ($hour >= 11 && $hour < 15) {
            $timeOfDay = 'Siang';
        } elseif ($hour >= 15 && $hour < 18) {
            $timeOfDay = 'Sore';
        } elseif ($hour >= 18 && $hour < 24) {
            $timeOfDay = 'Malam';
        } else {
            $timeOfDay = 'Hari';
        }

        // --- Dapatkan rekomendasi PINTAR berdasarkan waktu ---
        $priorityEngine = new PriorityEngine($user);
        $smartRecommendations = $priorityEngine->getSmartRecommendations($now);

        // --- Dapatkan jadwal saat ini ---
        $currentSchedule = $this->getCurrentScheduleFromDatabase($user, $now);
        $currentActivity = $this->getCurrentActivity($currentSchedule, $hour, $isSimulation);

        $stats = $this->calculateDashboardStats($user);
        $todaySchedule = $this->getTodaySchedule($user, $now);

        // Ambil data tugas
        $todayTasks = $user->tasks()
            ->where(function ($query) use ($now) {
                $query->whereDate('due_date', '<=', $now)
                    ->orWhereDate('due_date', '=', $now->copy()->addDay());
            })
            ->whereNotIn('status', ['done', 'archived'])
            ->orderByRaw($this->getTaskPriorityOrder())
            ->get();

        $upcomingDeadlines = $user->tasks()
            ->whereDate('due_date', '>', $now)
            ->whereNotIn('status', ['done', 'archived'])
            ->orderByRaw($this->getTaskPriorityOrder())
            ->limit(5)
            ->get();

        $activeProjects = $user->projectStages()
            ->where('status', 'active')
            ->orderBy('deadline', 'asc')
            ->get();

        $timeBlockingData = $this->getTimeBlockingData($user, $now);
        $prioritySummary = $this->getPrioritySummary($user, $now);

        return view('dashboard.index', compact(
            'stats',
            'todayTasks',
            'todaySchedule',
            'upcomingDeadlines',
            'activeProjects',
            'smartRecommendations',
            'timeOfDay',
            'currentSchedule',
            'currentActivity',
            'now',
            'hour',
            'isSimulation',
            'timeBlockingData',    // Data baru
            'prioritySummary'      // Data baru
        ));
    }

    // Tambahkan method-method untuk navigasi sidebar
    public function focus()
    {
        $title = 'Focus - Smart Timeline';
        $user = Auth::user();

        // Get today's schedules
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $today = $days[now()->dayOfWeek];
        $schedules = $user->schedules()
            ->where('day', $today)
            ->orderBy('start_time')
            ->get();

        // Get tasks for today
        $tasks = $user->tasks()
            ->whereNotIn('status', ['done', 'archived'])
            ->orderBy('priority', 'desc')
            ->get();

        return view('dashboard.focus', compact('title', 'schedules', 'tasks'));
    }

    public function creative()
    {
        $title = 'Creative Studio';
        return view('dashboard.creative', compact('title'));
    }

    public function academic()
    {
        $title = 'Academic Hub';
        $user = Auth::user();

        // Get all subjects for the user
        $subjects = $user->subjects()->where('is_active', true)->orderBy('day_of_week')->orderBy('start_time')->get();

        // Get today's subjects
        $todayIndex = now()->dayOfWeek == 0 ? 6 : now()->dayOfWeek - 1;
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $todayName = $days[$todayIndex];
        $todaySubjects = $subjects->where('day_of_week', $todayName);

        // Calculate stats
        $totalSks = $subjects->sum('sks');
        $completedSks = $subjects->where('semester', '<', 5)->sum('sks'); // Assuming current semester is 5
        $gpa = 3.5; // Placeholder - would come from actual grades
        $currentSemester = 5; // Placeholder

        return view('dashboard.academic', compact(
            'title',
            'subjects',
            'todaySubjects',
            'todayName',
            'totalSks',
            'completedSks',
            'gpa',
            'currentSemester'
        ));
    }

    public function pkl()
    {
        $title = 'PKL / Work Log';
        $user = Auth::user();

        // Get PKL logs for the user
        $pklLogs = $user->pklLogs()->latest()->get();

        // Calculate totals
        $totalDays = $pklLogs->count();
        $totalHours = $pklLogs->sum('hours');
        $targetDays = 120; // Default target
        $progressPercentage = $targetDays > 0 ? ($totalDays / $targetDays) * 100 : 0;

        return view('dashboard.pkl', compact(
            'title',
            'pklLogs',
            'totalDays',
            'totalHours',
            'targetDays',
            'progressPercentage'
        ));
    }

    public function productivity()
    {
        $user = Auth::user();

        // Get productivity logs
        $productivityLogs = $user->productivityLogs()->latest()->take(30)->get();

        // Calculate stats
        $totalHours = $productivityLogs->sum('hours');
        $completedTasks = $user->tasks()->where('status', 'done')->count();
        $currentStreak = 0; // Calculate streak logic
        $productivityScore = $productivityLogs->avg('focus_level') * 10 ?? 0;

        return view('dashboard.productivity', compact(
            'title',
            'productivityLogs',
            'totalHours',
            'completedTasks',
            'currentStreak',
            'productivityScore'
        ));
    }


    public function finance()
    {
        $title = 'Finance Manager';
        $user = Auth::user();

        // Get finance accounts
        $accounts = $user->financeAccounts()->get();

        // Calculate totals
        $totalBalance = $accounts->sum('balance');
        $bankTotal = $accounts->where('type', 'bank')->sum('balance');
        $walletTotal = $accounts->where('type', 'e-wallet')->sum('balance');

        // Get recent transactions
        $transactions = $user->transactions()
            ->with('account')
            ->latest()
            ->take(10)
            ->get();

        // Calculate monthly stats
        $monthlyIncome = $user->transactions()
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        $monthlyExpense = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        // Get budgets
        $budgets = $user->budgets()->where('is_active', true)->get();

        return view('dashboard.finance', compact(
            'title',
            'accounts',
            'totalBalance',
            'bankTotal',
            'walletTotal',
            'transactions',
            'monthlyIncome',
            'monthlyExpense',
            'budgets'
        ));
    }

    public function assets()
    {
        $title = 'Asset Management';
        $user = Auth::user();

        // Get all assets for the user
        $assets = $user->assets()->latest()->get();

        // Calculate totals
        $totalAssets = $assets->sum('current_value');
        $totalPurchase = $assets->sum('purchase_value');
        $depreciation = $totalPurchase - $totalAssets;

        // Asset categories breakdown
        $assetsByCategory = $assets->groupBy('category')->map(function ($items) {
            return [
                'count' => $items->count(),
                'value' => $items->sum('current_value')
            ];
        });

        return view('dashboard.assets', compact(
            'title',
            'assets',
            'totalAssets',
            'totalPurchase',
            'depreciation',
            'assetsByCategory'
        ));
    }

    public function debts()
    {
        $title = 'Debt Tracker';
        $user = Auth::user();

        // Get all debts for the user
        $debts = $user->debts()->with('payments')->latest()->get();

        // Calculate totals
        $totalDebt = $debts->sum('remaining_amount');
        $originalDebt = $debts->sum('amount');
        $paidDebt = $debts->sum('paid_amount');

        // Debt status breakdown
        $activeDebts = $debts->where('status', 'active')->count();
        $paidOffDebts = $debts->where('status', 'paid_off')->count();
        $overdueDebts = $debts->where('status', 'overdue')->count();

        // Recent payments
        $recentPayments = $user->debtPayments()->latest()->take(10)->get();

        return view('dashboard.debts', compact(
            'title',
            'debts',
            'totalDebt',
            'originalDebt',
            'paidDebt',
            'activeDebts',
            'paidOffDebts',
            'overdueDebts',
            'recentPayments'
        ));
    }

    public function investments()
    {
        $title = 'Investment Portfolio';
        $user = Auth::user();

        // Get all investment purchases for the user
        $purchases = $user->investmentPurchases()->with('instrument')->latest()->get();

        // Calculate totals
        $totalInvested = $purchases->sum('purchase_amount');
        $currentValue = $purchases->sum(function ($purchase) {
            $currentPrice = $purchase->instrument->current_price ?? $purchase->purchase_price;
            return $purchase->quantity * $currentPrice;
        });
        $profitLoss = $currentValue - $totalInvested;
        $profitLossPercentage = $totalInvested > 0 ? ($profitLoss / $totalInvested) * 100 : 0;

        // Group by instrument type
        $investmentsByType = $purchases->groupBy(function ($purchase) {
            return $purchase->instrument->type ?? 'unknown';
        })->map(function ($items) {
            return [
                'count' => $items->count(),
                'invested' => $items->sum('purchase_amount'),
                'quantity' => $items->sum('quantity')
            ];
        });

        // Available instruments
        $instruments = \App\Models\InvestmentInstrument::all();

        return view('dashboard.investments', compact(
            'title',
            'purchases',
            'totalInvested',
            'currentValue',
            'profitLoss',
            'profitLossPercentage',
            'investmentsByType',
            'instruments'
        ));
    }

    /**
     * Dapatkan jadwal saat ini dari database
     */
    private function getCurrentScheduleFromDatabase($user, $now)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $currentDay = $days[$now->dayOfWeek];
        $currentTime = $now->format('H:i:s');

        $schedule = $user->schedules()
            ->where('day', $currentDay)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->first();

        return $schedule;
    }

    /**
     * Dapatkan aktivitas saat ini
     */
    private function getCurrentActivity($currentSchedule, $currentHour, $isSimulation = false)
    {
        if ($currentSchedule) {
            return [
                'title' => $currentSchedule->activity,
                'type' => $currentSchedule->type,
                'start_time' => $currentSchedule->start_time->format('H:i'),
                'end_time' => $currentSchedule->end_time->format('H:i'),
                'time_remaining' => $this->calculateTimeRemaining($currentSchedule->end_time),
                'is_scheduled' => true,
                'location' => $currentSchedule->location,
                'instructor' => $currentSchedule->instructor,
            ];
        }

        // Jika tidak ada jadwal, tentukan berdasarkan waktu
        $activity = $this->determineActivityByTime($currentHour);

        return [
            'title' => $activity['title'],
            'type' => $activity['type'],
            'time_remaining' => $isSimulation ? 'Waktu simulasi' : 'Waktu bebas',
            'is_scheduled' => false,
            'recommendation' => $activity['recommendation'],
        ];
    }

    /**
     * Tentukan aktivitas berdasarkan jam - LOGIKA YANG LEBIH BAIK
     */
    private function determineActivityByTime($hour)
    {
        if ($hour >= 0 && $hour < 3) {
            return [
                'title' => 'Larut Malam / Tidur',
                'type' => 'rest',
                'recommendation' => 'Waktu istirahat - sebaiknya tidur',
            ];
        } elseif ($hour >= 3 && $hour < 5) {
            return [
                'title' => 'Dini Hari',
                'type' => 'early_morning',
                'recommendation' => 'Bangun awal? Waktu yang baik untuk fokus tinggi',
            ];
        } elseif ($hour >= 5 && $hour < 8) {
            return [
                'title' => 'Persiapan Pagi',
                'type' => 'morning',
                'recommendation' => 'Waktu ideal untuk persiapan dan perencanaan hari',
            ];
        } elseif ($hour >= 8 && $hour < 12) {
            return [
                'title' => 'Waktu Produktif Pagi',
                'type' => 'productive',
                'recommendation' => 'Energi masih fresh - kerjakan tugas berat',
            ];
        } elseif ($hour >= 12 && $hour < 13) {
            return [
                'title' => 'Istirahat Siang',
                'type' => 'break',
                'recommendation' => 'Waktu makan siang dan istirahat sejenak',
            ];
        } elseif ($hour >= 13 && $hour < 17) {
            return [
                'title' => 'Waktu Belajar/Tugas',
                'type' => 'academic',
                'recommendation' => 'Fokus pada tugas akademik dan belajar',
            ];
        } elseif ($hour >= 17 && $hour < 19) {
            return [
                'title' => 'Waktu Bebas Sore',
                'type' => 'free',
                'recommendation' => 'Waktu untuk olahraga, hobi, atau tugas ringan',
            ];
        } elseif ($hour >= 19 && $hour < 22) {
            return [
                'title' => 'Waktu Produktif Malam',
                'type' => 'productive_night',
                'recommendation' => 'Waktu terbaik untuk proyek besar dan fokus tinggi',
            ];
        } else {
            return [
                'title' => 'Persiapan Tidur',
                'type' => 'wind_down',
                'recommendation' => 'Waktu untuk menenangkan pikiran dan persiapan tidur',
            ];
        }
    }

    /**
     * Hitung waktu tersisa dari jadwal
     */
    private function calculateTimeRemaining($endTime)
    {
        $end = Carbon::createFromFormat('H:i:s', $endTime);
        $now = Carbon::now();

        if ($now > $end) {
            return 'Sudah selesai';
        }

        $diff = $now->diff($end);

        if ($diff->h > 0) {
            return $diff->h . ' jam ' . $diff->i . ' menit lagi';
        }

        return $diff->i . ' menit lagi';
    }

    /**
     * Logika prioritas tugas yang lebih cerdas
     */
    private function getTaskPriorityOrder()
    {
        return "
            CASE
                -- Tugas yang sudah overdue (paling prioritas)
                WHEN due_date < CURDATE() THEN 1

                -- Deadline hari ini
                WHEN due_date = CURDATE() THEN 2

                -- Deadline besok
                WHEN due_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 3

                -- Tugas menyenangkan dengan deadline dekat
                WHEN due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                     AND (tags LIKE '%fun%' OR tags LIKE '%enjoy%') THEN 4

                -- Tugas mudah (< 30 menit)
                WHEN (estimated_time LIKE '%15%' OR estimated_time LIKE '%30%') THEN 5

                -- Deadline minggu ini
                WHEN due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 6

                -- Sisanya
                ELSE 7
            END,
            due_date ASC,

            -- Prioritas berdasarkan kategori
            CASE
                WHEN category IN ('Skripsi', 'Project', 'Freelance') THEN 1
                WHEN category LIKE '%akademik%' THEN 2
                WHEN category LIKE '%kerja%' THEN 3
                WHEN category LIKE '%creative%' THEN 4
                ELSE 5
            END
        ";
    }

    /**
     * Get today's schedule
     */
    private function getTodaySchedule($user, $now)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $today = $days[$now->dayOfWeek];

        return $user->schedules()
            ->where('day', $today)
            ->orderBy('start_time')
            ->get()
            ->map(function ($schedule) use ($now, $today) {
                $currentTime = $now->format('H:i');
                $startTime = $schedule->start_time->format('H:i');
                $endTime = $schedule->end_time->format('H:i');

                $isNow = $currentTime >= $startTime && $currentTime <= $endTime;
                $isUpcoming = $currentTime < $startTime;
                $isPast = $currentTime > $endTime;

                return [
                    'id' => $schedule->id,
                    'activity' => $schedule->activity,
                    'type' => $schedule->type,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'location' => $schedule->location,
                    'instructor' => $schedule->instructor,
                    'color' => $schedule->getTypeColor(),
                    'icon' => $schedule->getIcon(),
                    'is_now' => $isNow,
                    'is_upcoming' => $isUpcoming,
                    'is_past' => $isPast,
                    'status' => $isNow ? 'sedang berlangsung' : ($isUpcoming ? 'akan datang' : 'selesai'),
                ];
            });
    }

    /**
     * Calculate dashboard statistics
     */
    private function calculateDashboardStats($user)
    {
        return [
            'tasks' => [
                'total' => $user->tasks()->count(),
                'pending' => $user->tasks()->where('status', 'todo')->count(),
                'in_progress' => $user->tasks()->where('status', 'doing')->count(),
                'completed' => $user->tasks()->where('status', 'done')->count(),
                'overdue' => $user->tasks()
                    ->whereDate('due_date', '<', today())
                    ->whereNotIn('status', ['done', 'archived'])
                    ->count(),
            ],
            'events' => [
                'today' => $user->calendarEvents()
                    ->whereDate('start_time', today())
                    ->count(),
                'upcoming' => $user->calendarEvents()
                    ->where('start_time', '>', now())
                    ->where('start_time', '<=', now()->addDays(7))
                    ->count(),
            ],
            'finance' => [
                'total_assets' => $user->getTotalAssets(),
                'total_investments' => $user->getTotalInvestments(),
                'total_debts' => $user->getTotalDebts(),
                'net_worth' => $user->getNetWorth(),
            ],
            'academic' => [
                'courses' => $user->academicCourses()->count(),
                'average_progress' => $user->academicCourses()->avg('progress') ?? 0,
            ],
            'creative' => [
                'active_projects' => $user->projectStages()
                    ->where('status', 'active')
                    ->count(),
                'total_projects' => $user->projectStages()->count(),
            ]
        ];
    }





    /**
     * Check if schedule is currently happening
     */
    private function isScheduleNow($schedule)
    {
        $currentTime = date('H:i');
        $startTime = $schedule->start_time->format('H:i');
        $endTime = $schedule->end_time->format('H:i');

        return $currentTime >= $startTime && $currentTime <= $endTime;
    }

    /**
     * Check if schedule is upcoming today
     */
    private function isScheduleUpcoming($schedule)
    {
        $currentTime = date('H:i');
        $startTime = $schedule->start_time->format('H:i');

        return $currentTime < $startTime;
    }

    /**
     * Add new task with smart priority calculation
     */
    public function addTask(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string'],
            'due_date' => ['required', 'date'],
            'estimated_time' => ['nullable', 'string'],
            'is_recurring' => ['boolean'],
            'recurring_pattern' => ['nullable', 'string'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Calculate priority automatically
        $priority = $this->calculatePriority(
            $request->due_date,
            $request->category,
            $request->title
        );

        $task = $user->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $priority,
            'status' => 'todo',
            'due_date' => $request->due_date,
            'estimated_time' => $request->estimated_time,
            'is_recurring' => $request->boolean('is_recurring'),
            'recurring_pattern' => $request->recurring_pattern,
            'tags' => json_encode(explode(',', $request->tags ?? '')),
        ]);

        return response()->json([
            'success' => true,
            'task' => $task,
            'message' => 'Task berhasil ditambahkan dengan prioritas: ' . $priority
        ]);
    }

    /**
     * Calculate task priority based on due date and category
     */
    private function calculatePriority($dueDate, $category, $title)
    {
        $today = Carbon::today();
        $due = Carbon::parse($dueDate);
        $daysUntilDue = $today->diffInDays($due, false);

        // Check for academic/exam keywords
        $isAcademic = str_contains(strtolower($title), 'ujian') ||
            str_contains(strtolower($title), 'exam') ||
            str_contains(strtolower($title), 'tugas') ||
            str_contains(strtolower($title), 'kuliah');

        // Check for content creation
        $isContent = str_contains(strtolower($title), 'video') ||
            str_contains(strtolower($title), 'edit') ||
            str_contains(strtolower($title), 'content') ||
            str_contains(strtolower($title), 'upload');

        // Priority logic
        if ($daysUntilDue < 0) {
            // Overdue - urgent and important
            return 'urgent-important';
        } elseif ($daysUntilDue == 0) {
            // Due today
            if ($isAcademic) {
                return 'urgent-important';
            } else {
                return 'urgent-not-important';
            }
        } elseif ($daysUntilDue <= 2) {
            // Due in 2 days
            if ($isAcademic) {
                return 'urgent-important';
            } elseif ($isContent) {
                return 'important-not-urgent';
            } else {
                return 'urgent-not-important';
            }
        } elseif ($daysUntilDue <= 7) {
            // Due this week
            if ($isAcademic || $isContent) {
                return 'important-not-urgent';
            } else {
                return 'not-urgent-not-important';
            }
        } else {
            // Due later
            return 'not-urgent-not-important';
        }
    }

    /**
     * Update task progress
     */
    public function updateTaskProgress(Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $task->update([
            'progress' => $request->progress,
        ]);

        // Update status based on progress
        if ($request->progress >= 100) {
            $task->update(['status' => 'done']);
        } elseif ($request->progress > 0) {
            $task->update(['status' => 'doing']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Progress task diperbarui'
        ]);
    }

    /**
     * Get task suggestions based on available time
     */
    public function getTaskSuggestions(Request $request)
    {
        $request->validate([
            'available_minutes' => ['required', 'integer', 'min:15'],
            'energy_level' => ['required', 'in:low,medium,high'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $priorityEngine = new PriorityEngine($user);
        $recommendations = $priorityEngine->getTodayRecommendations();

        // Filter recommendations based on available time and energy
        $suggestions = array_filter($recommendations['recommendations'], function ($rec) use ($request) {
            return $rec['estimated_minutes'] <= $request->available_minutes;
        });

        // Sort by urgency level
        usort($suggestions, function ($a, $b) {
            $urgencyOrder = ['overdue' => 1, 'critical' => 2, 'high' => 3, 'medium' => 4, 'low' => 5];
            return $urgencyOrder[$a['urgency_level']] <=> $urgencyOrder[$b['urgency_level']];
        });

        // Limit to 3 suggestions
        $suggestions = array_slice($suggestions, 0, 3);

        return response()->json([
            'suggestions' => $suggestions,
            'message' => count($suggestions) . ' tugas ditemukan yang sesuai dengan waktu Anda'
        ]);
    }

    /**
     * Get weekly schedule
     */
    public function getWeeklySchedule()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabru', 'Minggu'];
        $schedule = [];

        foreach ($days as $day) {
            $daySchedules = $user->schedules()
                ->where('day', $day)
                ->orderBy('start_time')
                ->get()
                ->map(function ($item) {
                    return [
                        'activity' => $item->activity,
                        'type' => $item->type,
                        'time' => $item->start_time->format('H:i') . ' - ' . $item->end_time->format('H:i'),
                        'color' => $item->getTypeColor(),
                    ];
                });

            $schedule[$day] = $daySchedules;
        }

        return response()->json($schedule);
    }

    /**
     * Quick add task from dashboard
     */
    public function quickAddTask(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'priority' => ['nullable', 'in:urgent-important,important-not-urgent,urgent-not-important,not-urgent-not-important'],
            'due_date' => ['nullable', 'date'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Auto-calculate priority if not provided
        $priority = $request->priority ?? $this->calculatePriority(
            $request->due_date ?? today()->addDays(7),
            'personal',
            $request->title
        );

        $task = $user->tasks()->create([
            'title' => $request->title,
            'priority' => $priority,
            'status' => 'todo',
            'category' => $request->category ?? 'personal',
            'due_date' => $request->due_date ?? today()->addDays(7),
        ]);

        return response()->json([
            'success' => true,
            'task' => $task,
            'priority_explanation' => $this->getPriorityExplanation($priority)
        ]);
    }

    /**
     * Get explanation for priority level
     */
    private function getPriorityExplanation($priority)
    {
        return match ($priority) {
            'urgent-important' => 'Prioritas 1: Kerjakan sekarang, deadline mendesak dan penting',
            'important-not-urgent' => 'Prioritas 2: Jadwalkan untuk minggu ini, penting tapi tidak mendesak',
            'urgent-not-important' => 'Prioritas 3: Kerjakan setelah prioritas 1 & 2, mendesak tapi tidak kritis',
            'not-urgent-not-important' => 'Prioritas 4: Kerjakan saat ada waktu luang, bisa didelegasikan',
            default => 'Prioritas sedang'
        };
    }

    // Tambahkan method di DashboardController
    public function getRecommendations()
    {
        $user = Auth::user();
        $priorityEngine = new PriorityEngine($user);
        $recommendations = $priorityEngine->getTodayRecommendations();

        return response()->json([
            'success' => true,
            'recommendations' => $recommendations['recommendations'] ?? [],
            'current_schedule' => $recommendations['current_schedule'] ?? null
        ]);
    }

    public function startTask(Task $task)
    {
        Gate::authorize('update', $task);

        $task->update(['status' => 'doing']);

        // Log activity
        ProductivityLog::create([
            'user_id' => Auth::id(),
            'log_date' => today(),
            'tasks_completed' => 0,
            'tasks_planned' => 1,
            'notes' => 'Started task: ' . $task->title,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task berhasil dimulai'
        ]);
    }
    /**
     * Generate time blocking dari jadwal database
     */
    private function getTimeBlockingData($user, $now)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $today = $days[$now->dayOfWeek];

        // Ambil semua jadwal hari ini
        $schedules = $user->schedules()
            ->where('day', $today)
            ->orderBy('start_time')
            ->get();

        // Jika tidak ada jadwal, return default
        if ($schedules->isEmpty()) {
            return $this->getDefaultTimeBlocking($now->hour);
        }

        return $this->generateTimeBlocksFromSchedules($schedules, $now);
    }

    /**
     * Generate time blocks dari jadwal database
     */
    private function generateTimeBlocksFromSchedules($schedules, $now)
    {
        $timeBlocks = [];
        $previousEnd = '00:00';
        $dayStart = '06:00'; // Mulai dari jam 6 pagi
        $dayEnd = '23:00';   // Sampai jam 11 malam

        // Urutkan jadwal berdasarkan waktu mulai
        $sortedSchedules = $schedules->sortBy('start_time');

        foreach ($sortedSchedules as $schedule) {
            $start = $schedule->start_time->format('H:i');
            $end = $schedule->end_time->format('H:i');

            // Tambahkan waktu kosong sebelum jadwal pertama
            if ($previousEnd === '00:00' && $start > $dayStart) {
                $timeBlocks[] = $this->createTimeBlock(
                    'Free Time',
                    $dayStart,
                    $start,
                    'gray',
                    'Waktu bebas'
                );
            }

            // Tambahkan waktu kosong antara jadwal
            if ($previousEnd !== '00:00' && $start > $previousEnd) {
                $timeBlocks[] = $this->createTimeBlock(
                    'Break',
                    $previousEnd,
                    $start,
                    'stone',
                    'Istirahat'
                );
            }

            // Tambahkan jadwal aktual
            $timeBlocks[] = $this->createTimeBlock(
                $schedule->activity,
                $start,
                $end,
                $this->getScheduleColor($schedule->type),
                $schedule->location ?? $schedule->type,
                true,
                $schedule->type
            );

            $previousEnd = $end;
        }

        // Tambahkan waktu setelah jadwal terakhir
        if ($previousEnd < $dayEnd) {
            $timeBlocks[] = $this->createTimeBlock(
                'Personal Time',
                $previousEnd,
                $dayEnd,
                'orange',
                'Waktu pribadi'
            );
        }

        // Hitung persentase untuk setiap time block
        $totalMinutes = (strtotime($dayEnd) - strtotime($dayStart)) / 60;

        foreach ($timeBlocks as &$block) {
            $block['percentage'] = $this->calculateTimePercentage(
                $block['start_time'],
                $block['end_time'],
                $dayStart,
                $dayEnd
            );
            $block['is_now'] = $this->isTimeNow($block['start_time'], $block['end_time'], $now);
        }

        return [
            'blocks' => $timeBlocks,
            'day_start' => $dayStart,
            'day_end' => $dayEnd,
            'total_hours' => count($timeBlocks)
        ];
    }

    /**
     * Buat default time blocking jika tidak ada jadwal
     */
    private function getDefaultTimeBlocking($currentHour)
    {
        // Default schedule untuk mahasiswa
        $defaultBlocks = [
            [
                'title' => 'Morning Routine',
                'start_time' => '06:00',
                'end_time' => '08:00',
                'color' => 'blue',
                'description' => 'Persiapan pagi',
                'is_scheduled' => false,
                'type' => 'routine'
            ],
            [
                'title' => 'PKL / Work',
                'start_time' => '08:00',
                'end_time' => '12:00',
                'color' => 'emerald',
                'description' => 'Waktu kerja',
                'is_scheduled' => false,
                'type' => 'work'
            ],
            [
                'title' => 'Lunch Break',
                'start_time' => '12:00',
                'end_time' => '13:00',
                'color' => 'stone',
                'description' => 'Istirahat makan siang',
                'is_scheduled' => false,
                'type' => 'break'
            ],
            [
                'title' => 'Study Time',
                'start_time' => '13:00',
                'end_time' => '17:00',
                'color' => 'blue',
                'description' => 'Waktu belajar',
                'is_scheduled' => false,
                'type' => 'study'
            ],
            [
                'title' => 'Free Time',
                'start_time' => '17:00',
                'end_time' => '19:00',
                'color' => 'stone',
                'description' => 'Waktu bebas',
                'is_scheduled' => false,
                'type' => 'free'
            ],
            [
                'title' => 'Deep Work',
                'start_time' => '19:00',
                'end_time' => '23:00',
                'color' => 'orange',
                'description' => 'Fokus pada proyek besar',
                'is_scheduled' => false,
                'type' => 'deep_work'
            ],
        ];

        // Hitung persentase
        foreach ($defaultBlocks as &$block) {
            $block['percentage'] = $this->calculateTimePercentage(
                $block['start_time'],
                $block['end_time'],
                '06:00',
                '23:00'
            );
            $block['is_now'] = $this->isTimeNow($block['start_time'], $block['end_time'], now()->setHour($currentHour));
        }

        return [
            'blocks' => $defaultBlocks,
            'day_start' => '06:00',
            'day_end' => '23:00',
            'total_hours' => count($defaultBlocks)
        ];
    }

    /**
     * Helper methods
     */
    private function createTimeBlock($title, $start, $end, $color, $description, $isScheduled = false, $type = null)
    {
        return [
            'title' => $title,
            'start_time' => $start,
            'end_time' => $end,
            'color' => $color,
            'description' => $description,
            'is_scheduled' => $isScheduled,
            'type' => $type
        ];
    }

    private function getScheduleColor($type)
    {
        return match ($type) {
            'academic' => 'blue',
            'pkl' => 'emerald',
            'creative' => 'orange',
            'personal' => 'purple',
            'routine' => 'stone',
            'break' => 'stone',
            default => 'gray'
        };
    }

    private function calculateTimePercentage($start, $end, $dayStart, $dayEnd)
    {
        $totalMinutes = (strtotime($dayEnd) - strtotime($dayStart)) / 60;
        $blockMinutes = (strtotime($end) - strtotime($start)) / 60;

        return ($blockMinutes / $totalMinutes) * 100;
    }

    private function isTimeNow($start, $end, $now)
    {
        $currentTime = $now->format('H:i');
        return $currentTime >= $start && $currentTime <= $end;
    }


    /**
     * Get priority summary dari database dengan algoritma Eisenhower Matrix
     */
    private function getPrioritySummary($user, $now)
    {
        $tasks = $user->tasks()
            ->whereNotIn('status', ['done', 'archived'])
            ->orderByRaw($this->getTaskPriorityOrder())
            ->take(10) // Ambil lebih banyak untuk analisis
            ->get();

        $summary = [
            'urgent_important' => [],
            'important_not_urgent' => [],
            'urgent_not_important' => [],
            'not_urgent_not_important' => [],
            'routine' => [],
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $user->tasks()->where('status', 'done')->count(),
            'overdue_tasks' => $user->tasks()
                ->whereDate('due_date', '<', $now)
                ->whereNotIn('status', ['done', 'archived'])
                ->count()
        ];

        foreach ($tasks as $task) {
            // Tentukan kategori berdasarkan algoritma
            $dueDate = $task->due_date ? Carbon::parse($task->due_date) : null;
            $isUrgent = false;
            $isImportant = false;

            // Logika Urgency
            if ($dueDate) {
                $daysUntilDue = $now->diffInDays($dueDate, false);
                $isUrgent = $daysUntilDue <= 2 || $daysUntilDue < 0; // <= 2 hari atau overdue
            }

            // Logika Importance (bisa disesuaikan)
            $isImportant = in_array($task->category, ['Skripsi', 'Project', 'Freelance', 'Academic'])
                || $task->priority === 'high'
                || $task->priority === 'urgent-important';

            // Klasifikasikan berdasarkan Eisenhower Matrix
            if ($isUrgent && $isImportant) {
                $summary['urgent_important'][] = $task;
            } elseif (!$isUrgent && $isImportant) {
                $summary['important_not_urgent'][] = $task;
            } elseif ($isUrgent && !$isImportant) {
                $summary['urgent_not_important'][] = $task;
            } elseif (!$isUrgent && !$isImportant) {
                $summary['not_urgent_not_important'][] = $task;
            }

            // Tugas rutin
            if ($task->is_recurring || $task->category === 'routine') {
                $summary['routine'][] = $task;
            }
        }

        return $summary;
    }



    /**
     * Get today's tasks for API
     */
    public function getTodayTasks()
    {
        $user = Auth::user();
        $now = now();

        $tasks = $user->tasks()
            ->where(function ($query) use ($now) {
                $query->whereDate('due_date', '<=', $now)
                    ->orWhereDate('due_date', '=', $now->copy()->addDay());
            })
            ->whereNotIn('status', ['done', 'archived'])
            ->orderByRaw($this->getTaskPriorityOrder())
            ->get();

        return response()->json($tasks);
    }

    /**
     * Get today's schedule for API
     */
    public function getTodayScheduleApi()
    {
        $user = Auth::user();
        $now = now();

        return response()->json($this->getTodaySchedule($user, $now));
    }

    /**
     * Get priority order for queries
     */
    private function getPriorityOrder()
    {
        return "
            CASE
                WHEN due_date < CURDATE() THEN 1
                WHEN due_date = CURDATE() THEN 2
                WHEN due_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 3
                WHEN due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN 4
                WHEN due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 5
                ELSE 6
            END,
            due_date ASC
        ";
    }
}
