<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\PklSchedule;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\ThesisMilestone;
use App\Models\Event;
use App\Models\PklInfo;
use App\Models\SubjectSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * FocusController - Smart Timeline Management
 *
 * Logika Timeline:
 * 1. FIXED BLOCKS (prioritas rendah ke tinggi):
 *    - PKL Schedule
 *    - Academic/Kuliah (Subject Sessions)
 *    - Routine Activities (Schedules)
 *    - One-off Events
 *
 * 2. TASKS IN GAPS:
 *    - Satu tugas per gap (tidak bertumpuk)
 *    - Prioritas: urgent-important > important-not-urgent > urgent-not-important > not-urgent-not-important
 *    - Hitung mundur untuk setiap tugas di slot
 *
 * 3. Eisenhower Matrix:
 *    - Mengambil semua tasks dari semua sumber (Task, Academic, Creative, dll)
 */
class FocusController extends Controller
{
    // ── Konstanta Timeline ──────────────────────────────────────────────────
    const TIMELINE_START = 0;   // 00:00
    const TIMELINE_END   = 24;  // 24:00
    const TIMELINE_HOURS = 24;  // 00–24

    // ── Prioritas Kategori (1 = tertinggi, lebih besar = lebih rendah) ─────
    const PRIORITY_EVENT = 1;      // Event one-off (rapat, deadline)
    const PRIORITY_ACADEMIC = 2;   // Kuliah (wajib)
    const PRIORITY_PKL = 3;        // PKL (wajib)
    const PRIORITY_ROUTINE = 4;    // Rutinitas

    // ── Warna & ikon per kategori ───────────────────────────────────────────
    const COLOR_MAP = [
        'pkl'       => '#10b981',  // emerald
        'work'      => '#059669',  // emerald-600
        'academic'  => '#3b82f6',  // blue
        'skripsi'   => '#f97316',  // orange
        'creative'  => '#8b5cf6',  // violet
        'freelance' => '#a855f7',  // purple
        'personal'  => '#6b7280',  // gray
        'health'    => '#ef4444',  // red
        'routine'   => '#14b8a6',  // teal
        'event'     => '#eab308',  // yellow
        'finance'   => '#f59e0b',  // amber
    ];

    const ICON_MAP = [
        'pkl'       => '💼',
        'work'      => '💼',
        'academic'  => '📚',
        'skripsi'   => '✍️',
        'creative'  => '🎬',
        'freelance' => '�',
        'personal'  => '🌅',
        'health'    => '💪',
        'routine'   => '🔁',
        'event'     => '📅',
        'finance'   => '💰',
    ];

    // ═══════════════════════════════════════════════════════════════════════
    // INDEX - MAIN METHOD (Ditulis ulang dengan logika yang benar)
    // ═══════════════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Redirect admin ke dashboard admin
        if ($user->role === 'admin') {
            return redirect()->route('admin.index');
        }

        $today = now()->toDateString();
        $todayDow = Schedule::getIndonesianDayName(now()->dayOfWeek);
        $now = now()->timezone('Asia/Jakarta');
        $currentHour = $now->hour + ($now->minute / 60);

        // ── 1. AMBIL SEMUA FIXED SCHEDULES ─────────────────────────────────
        $fixedBlocks = [];

        // A. PKL Schedules (hanya jika PKL aktif)
        $activePkl = PklInfo::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        if ($activePkl) {
            $pklSchedules = $user->pklSchedules()
                ->where('day', $todayDow)
                ->whereIn('type', ['full', 'half', 'split'])
                ->get();

            foreach ($pklSchedules as $pkl) {
                // Sesi 1
                $start = $this->timeToFloat($pkl->start_time);
                $end = $this->timeToFloat($pkl->end_time);
                if ($start !== null && $end !== null && $end > $start) {
                    $fixedBlocks[] = [
                        'start'     => $start,
                        'end'       => $end,
                        'label'     => 'PKL',
                        'type'      => 'pkl',
                        'color'     => self::COLOR_MAP['pkl'],
                        'icon'      => self::ICON_MAP['pkl'],
                        'source'    => 'pkl',
                        'is_fixed'  => true,
                        'priority'  => self::PRIORITY_PKL,
                        'id'        => 'pkl_' . $pkl->id . '_1',
                    ];
                }

                // Sesi 2 (split shift)
                if ($pkl->hasSplitShift()) {
                    $s2 = $this->timeToFloat($pkl->start_time_2);
                    $e2 = $this->timeToFloat($pkl->end_time_2);
                    if ($s2 !== null && $e2 !== null && $e2 > $s2) {
                        $fixedBlocks[] = [
                            'start'     => $s2,
                            'end'       => $e2,
                            'label'     => 'PKL Sesi 2',
                            'type'      => 'pkl',
                            'color'     => self::COLOR_MAP['pkl'],
                            'icon'      => self::ICON_MAP['pkl'],
                            'source'    => 'pkl',
                            'is_fixed'  => true,
                            'priority'  => self::PRIORITY_PKL,
                            'id'        => 'pkl_' . $pkl->id . '_2',
                        ];
                    }
                }
            }
        }

        // B. Academic/Subject Sessions hari ini
        $sessionsToday = SubjectSession::with('subject')
            ->whereHas('subject', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('is_active', true);
            })
            ->whereDate('date', $today)
            ->whereIn('status', ['scheduled', 'completed'])
            ->get();

        foreach ($sessionsToday as $session) {
            $sub = $session->subject;
            if (!$sub) continue;

            $start = $this->timeToFloat($sub->start_time);
            $end = $this->timeToFloat($sub->end_time);

            if ($start !== null && $end !== null && $end > $start) {
                $fixedBlocks[] = [
                    'start'     => $start,
                    'end'       => $end,
                    'label'     => $sub->name . ' (' . $session->title . ')',
                    'type'      => 'academic',
                    'color'     => self::COLOR_MAP['academic'],
                    'icon'      => self::ICON_MAP['academic'],
                    'source'    => 'academic',
                    'is_fixed'  => true,
                    'priority'  => self::PRIORITY_ACADEMIC,
                    'room'      => $sub->room ?? '',
                    'id'        => 'academic_' . $session->id,
                ];
            }
        }

        // C. Routine Schedules (kegiatan rutin)
        $schedules = Schedule::where('user_id', $user->id)
            ->activeToday()
            ->get();

        foreach ($schedules as $schedule) {
            $start = $this->timeToFloat($schedule->start_time);
            $end = $this->timeToFloat($schedule->end_time);

            if ($start !== null && $end !== null && $end > $start) {
                $cat = $schedule->getNormalizedCategory();

                $fixedBlocks[] = [
                    'start'     => $start,
                    'end'       => $end,
                    'label'     => $schedule->activity ?? $schedule->title,
                    'type'      => $cat,
                    'color'     => $schedule->getCategoryColor(),
                    'icon'      => self::ICON_MAP[$cat] ?? self::ICON_MAP['routine'],
                    'source'    => 'routine',
                    'is_fixed'  => true,
                    'priority'  => self::PRIORITY_ROUTINE,
                    'id'        => 'routine_' . $schedule->id,
                ];
            }
        }

        // D. One-off Events hari ini
        $events = Event::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->get();

        foreach ($events as $event) {
            $start = $this->timeToFloat($event->start_time);
            $end = $this->timeToFloat($event->end_time);

            if ($start !== null && $end !== null && $end > $start) {
                $fixedBlocks[] = [
                    'start'     => $start,
                    'end'       => $end,
                    'label'     => $event->title,
                    'type'      => 'event',
                    'color'     => self::COLOR_MAP['event'],
                    'icon'      => self::ICON_MAP['event'],
                    'source'    => 'event',
                    'is_fixed'  => true,
                    'priority'  => self::PRIORITY_EVENT,
                    'id'        => 'event_' . $event->id,
                ];
            }
        }

        // Sort fixed blocks by priority (ascending), then by start time
        usort($fixedBlocks, function ($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] <=> $b['priority'];
            }
            return $a['start'] <=> $b['start'];
        });

        // ── 2. CARI GAPS (WAKTU KOSONG) ────────────────────────────────────
        $gaps = $this->findGaps($fixedBlocks, self::TIMELINE_START, self::TIMELINE_END);

        // ── 3. AMBIL SEMUA TASKS PENDING ───────────────────────────────────
        $pendingTasks = Task::where('user_id', $user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->whereNotNull('due_date')
            ->orderByRaw("CASE
                WHEN priority = 'urgent-important' THEN 1
                WHEN priority = 'important-not-urgent' THEN 2
                WHEN priority = 'urgent-not-important' THEN 3
                WHEN priority = 'not-urgent-not-important' THEN 4
                ELSE 5
            END")
            ->orderBy('due_date')
            ->get();

        // ── 4. SISIPKAN TASKS KE GAPS ─────────────────────────────────────
        $suggestedBlocks = $this->insertTasksIntoGaps($pendingTasks, $gaps, $currentHour);

        // ── 5. BUILD GANTT ROWS ───────────────────────────────────────────
        $ganttRows = $this->buildGanttRows($fixedBlocks, $suggestedBlocks);

        // ── 6. EISENHOWER MATRIX ───────────────────────────────────────────
        $eisenhowerTasks = $this->buildEisenhowerMatrix($user);

        // ── 7. COUNTDOWNS ──────────────────────────────────────────────────
        $countdowns = $this->buildCountdowns($user);

        // ── 8. STATISTICS ──────────────────────────────────────────────────
        $todayDoneCount = Task::where('user_id', $user->id)
            ->where('status', 'done')
            ->whereDate('completed_at', $today)
            ->count();

        $todayTotalCount = Task::where('user_id', $user->id)
            ->whereDate('due_date', $today)
            ->count();

        // ── 9. RECOMMENDATIONS ────────────────────────────────────────────
        $recommendations = $this->buildRecommendations($user, $pendingTasks, $gaps, $currentHour);

        // ── 10. AKTIVITAS KINI ────────────────────────────────────────────
        $currentActivity = $this->getCurrentActivity($fixedBlocks, $suggestedBlocks, $currentHour);

        return view('dashboard.focus', compact(
            'ganttRows',
            'eisenhowerTasks',
            'recommendations',
            'countdowns',
            'todayDoneCount',
            'todayTotalCount',
            'gaps',
            'fixedBlocks',
            'suggestedBlocks',
            'currentActivity',
            'currentHour',
        ));
    }

    // ═══════════════════════════════════════════════════════════════════════
    // TASK STORAGE
    // ═══════════════════════════════════════════════════════════════════════
    public function storeTask(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'priority' => ['required', 'in:urgent-important,important-not-urgent,urgent-not-important,not-urgent-not-important'],
            'due_date' => ['nullable', 'date'],
            'estimated_time' => ['nullable', 'string'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $task = $user->tasks()->create([
            'title' => $request->title,
            'category' => $this->normalizeCategory($request->category),
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'estimated_time' => $request->estimated_time,
            'status' => 'todo',
        ]);

        return redirect()->route('dashboard.focus')
            ->with('success', 'Tugas "' . $task->title . '" berhasil ditambahkan');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // HELPER METHODS
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Convert time string (HH:MM or HH:MM:SS) to float hours
     */
    private function timeToFloat($time): ?float
    {
        if (empty($time)) return null;

        if ($time instanceof \Carbon\Carbon) {
            return $time->hour + ($time->minute / 60);
        }

        if (preg_match('/(\d{2}):(\d{2})/', (string) $time, $matches)) {
            return (int)$matches[1] + ((int)$matches[2] / 60);
        }

        return null;
    }

    /**
     * Find gaps between fixed blocks
     */
    private function findGaps(array $fixedBlocks, float $dayStart, float $dayEnd): array
    {
        $gaps = [];
        $cursor = $dayStart;
        $minGap = 0.5; // minimum 30 minutes

        // Sort blocks by start time
        usort($fixedBlocks, fn($a, $b) => $a['start'] <=> $b['start']);

        foreach ($fixedBlocks as $block) {
            if ($block['start'] > $cursor + $minGap) {
                $gaps[] = [
                    'start'          => $cursor,
                    'end'            => $block['start'],
                    'duration_hours' => $block['start'] - $cursor,
                ];
            }
            $cursor = max($cursor, $block['end']);
        }

        // Gap after last block
        if ($cursor < $dayEnd - $minGap) {
            $gaps[] = [
                'start'          => $cursor,
                'end'            => $dayEnd,
                'duration_hours' => $dayEnd - $cursor,
            ];
        }

        return $gaps;
    }

    /**
     * Insert tasks into gaps - ONE task per gap
     */
    private function insertTasksIntoGaps($pendingTasks, array $gaps, float $currentHour): array
    {
        $suggested = [];
        $usedGaps = []; // Track which gaps have been used

        foreach ($pendingTasks as $task) {
            // Skip jika sudah 5 task yang di-assign
            if (count($suggested) >= 5) break;

            $cat = $this->normalizeCategory($task->category ?? 'personal');

            // Estimate duration (default 1 hour, max 3 hours)
            $estimatedHours = $task->estimated_hours ?? 1.0;
            $estimatedHours = min(3.0, max(0.5, $estimatedHours));

            // Cari gap yang masih kosong dan cukup besar
            foreach ($gaps as $gapIndex => $gap) {
                // Skip gap yang sudah digunakan
                if (isset($usedGaps[$gapIndex])) continue;

                $gapDuration = $gap['end'] - $gap['start'];

                // Jika gap cukup besar untuk task ini
                if ($gapDuration >= $estimatedHours) {
                    // Assign task ke gap ini
                    $start = max($gap['start'], $currentHour); // Jangan mulai sebelum sekarang
                    $end = min($start + $estimatedHours, $gap['end']);

                    // Hitung countdown untuk task ini
                    $countdown = $this->calculateCountdown($task->due_date);

                    $suggested[] = [
                        'start'         => $start,
                        'end'           => $end,
                        'label'         => $task->title,
                        'type'          => $cat,
                        'color'         => self::COLOR_MAP[$cat] ?? '#6b7280',
                        'icon'          => self::ICON_MAP[$cat] ?? '📌',
                        'source'        => 'task',
                        'is_fixed'      => false,
                        'is_suggested'  => true,
                        'task_id'       => $task->id,
                        'priority'      => $task->priority,
                        'countdown'     => $countdown,
                        'gap_index'     => $gapIndex,
                    ];

                    // Tandai gap ini sudah digunakan
                    $usedGaps[$gapIndex] = true;
                    break; // Pindah ke task berikutnya
                }
            }
        }

        return $suggested;
    }

    /**
     * Build Gantt rows untuk tampilan timeline
     */
    private function buildGanttRows(array $fixedBlocks, array $suggestedBlocks): array
    {
        $rowDefs = [
            'academic'  => ['label' => '📚 Akademik',       'types' => ['academic']],
            'pkl'       => ['label' => '💼 PKL',            'types' => ['pkl', 'work']],
            'skripsi'   => ['label' => '✍️ Skripsi',        'types' => ['skripsi']],
            'creative'  => ['label' => '🎬 Kreatif',        'types' => ['creative', 'freelance']],
            'routine'   => ['label' => '🔁 Rutinitas',      'types' => ['routine']],
            'event'     => ['label' => '📅 Event',          'types' => ['event']],
            'personal'  => ['label' => '📝 Personal',       'types' => ['personal', 'health', 'finance']],
        ];

        $rows = [];

        // Row untuk suggested tasks (dikelompokkan sendiri)
        $taskRowBlocks = [];
        foreach ($suggestedBlocks as $block) {
            $taskRowBlocks[] = $block;
        }

        if (!empty($taskRowBlocks)) {
            $rows[] = [
                'label' => '⏰ Slot Tugas',
                'blocks' => $taskRowBlocks,
            ];
        }

        // Rows untuk fixed blocks
        foreach ($rowDefs as $key => $def) {
            $blocks = [];

            foreach ($fixedBlocks as $block) {
                if (in_array($block['type'], $def['types'])) {
                    $blocks[] = $block;
                }
            }

            // Selalu tampilkan row meski kosong (untuk konsistensi UI)
            $rows[] = [
                'label' => $def['label'],
                'blocks' => $blocks,
            ];
        }

        return $rows;
    }

    /**
     * Build Eisenhower Matrix dari semua tasks
     */
    private function buildEisenhowerMatrix($user): array
    {
        // Ambil semua tasks pending dari user
        $allPendingTasks = Task::where('user_id', $user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->orderBy('due_date')
            ->get()
            ->map(function ($task) {
                // Tambahkan data countdown
                $task->countdown = $this->calculateCountdown($task->due_date);
                $task->countdown_label = $this->formatCountdownLabel($task->countdown);

                // Set default priority jika kosong
                if (!$task->priority) {
                    $task->priority = 'not-urgent-not-important';
                }

                return $task;
            });

        return [
            'q1' => $allPendingTasks->where('priority', 'urgent-important')->take(4),
            'q2' => $allPendingTasks->where('priority', 'important-not-urgent')->take(4),
            'q3' => $allPendingTasks->where('priority', 'urgent-not-important')->take(3),
            'q4' => $allPendingTasks->where('priority', 'not-urgent-not-important')->take(3),
        ];
    }

    /**
     * Build multiple countdown targets
     */
    private function buildCountdowns($user): array
    {
        $countdowns = [];
        $now = now();

        // 1. Thesis Milestone terdekat
        $thesisMilestone = ThesisMilestone::where('user_id', $user->id)
            ->where('done', false)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();

        if ($thesisMilestone && !empty($thesisMilestone->target_date)) {
            $deadline = Carbon::parse($thesisMilestone->target_date)->endOfDay();
            $countdown = $this->calculateCountdown($deadline);

            $countdowns[] = [
                'type'      => 'thesis',
                'label'     => $thesisMilestone->label ?? 'Skripsi',
                'deadline'  => $deadline,
                'countdown' => $countdown,
                'color'     => '#f97316',
                'icon'      => 'fa-graduation-cap',
            ];
        }

        // 2. Urgent Task terdekat
        $urgentTask = Task::where('user_id', $user->id)
            ->where('priority', 'urgent-important')
            ->whereNotIn('status', ['done', 'archived'])
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->first();

        if ($urgentTask) {
            $countdown = $this->calculateCountdown($urgentTask->due_date);

            $countdowns[] = [
                'type'      => 'urgent',
                'label'     => $urgentTask->title,
                'deadline'  => $urgentTask->due_date,
                'countdown' => $countdown,
                'color'     => '#ef4444',
                'icon'      => 'fa-fire',
            ];
        }

        // 3. Academic deadline terdekat
        $academicTask = Task::where('user_id', $user->id)
            ->where('category', 'academic')
            ->whereNotIn('status', ['done', 'archived'])
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->first();

        if ($academicTask && (!$urgentTask || $academicTask->id !== $urgentTask->id)) {
            $countdown = $this->calculateCountdown($academicTask->due_date);

            $countdowns[] = [
                'type'      => 'academic',
                'label'     => $academicTask->title,
                'deadline'  => $academicTask->due_date,
                'countdown' => $countdown,
                'color'     => '#3b82f6',
                'icon'      => 'fa-book',
            ];
        }

        // 4. Creative deadline terdekat
        $creativeTask = Task::where('user_id', $user->id)
            ->whereIn('category', ['creative', 'freelance'])
            ->whereNotIn('status', ['done', 'archived'])
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->first();

        if ($creativeTask) {
            $countdown = $this->calculateCountdown($creativeTask->due_date);

            $countdowns[] = [
                'type'      => 'creative',
                'label'     => $creativeTask->title,
                'deadline'  => $creativeTask->due_date,
                'countdown' => $countdown,
                'color'     => '#8b5cf6',
                'icon'      => 'fa-video',
            ];
        }

        return $countdowns;
    }

    /**
     * Calculate countdown array dari deadline
     */
    private function calculateCountdown($deadline): array
    {
        if (!$deadline) {
            return ['days' => 0, 'hours' => 0, 'minutes' => 0, 'total_minutes' => 0, 'is_overdue' => false];
        }

        $deadline = $deadline instanceof Carbon ? $deadline : Carbon::parse($deadline);
        $now = now();
        $isOverdue = $now->gt($deadline);
        $diff = $now->diff($deadline);

        return [
            'days'          => $isOverdue ? -$diff->d : $diff->d,
            'hours'         => $diff->h,
            'minutes'       => $diff->i,
            'total_minutes' => $isOverdue ? -$now->diffInMinutes($deadline, false) : $now->diffInMinutes($deadline, false),
            'is_overdue'    => $isOverdue,
        ];
    }

    /**
     * Format countdown untuk display
     */
    private function formatCountdownLabel(array $countdown): string
    {
        if ($countdown['is_overdue']) {
            $days = abs($countdown['days']);
            if ($days > 0) {
                return "Terlambat {$days} hari";
            }
            return "Terlambat {$countdown['hours']}j {$countdown['minutes']}m";
        }

        if ($countdown['days'] > 0) {
            return "{$countdown['days']} hari lagi";
        }
        if ($countdown['hours'] > 0) {
            return "{$countdown['hours']}j {$countdown['minutes']}m lagi";
        }
        return "{$countdown['minutes']} menit lagi";
    }

    /**
     * Build recommendations dinamis
     */
    private function buildRecommendations($user, $pendingTasks, array $gaps, float $currentHour): array
    {
        $recs = [];

        // 1. Cek task urgent dengan deadline dekat
        $urgentDueSoon = $pendingTasks
            ->where('priority', 'urgent-important')
            ->filter(fn($t) => $t->due_date && $t->due_date->diffInDays(now(), false) <= 1)
            ->first();

        if ($urgentDueSoon) {
            $recs[] = [
                'icon'  => 'fa-fire',
                'cls'   => 'text-red-500 bg-red-50 dark:bg-red-900/20',
                'title' => 'Prioritas Mendesak',
                'desc'  => "\"{$urgentDueSoon->title}\" - deadline " . $urgentDueSoon->due_date->isoFormat('D MMM'),
            ];
        }

        // 2. Rekomendasi waktu kosong terbaik
        if (!empty($gaps)) {
            $validGaps = array_filter($gaps, fn($g) => $g['start'] >= $currentHour);
            if (!empty($validGaps)) {
                $bestGap = collect($validGaps)->sortByDesc('duration_hours')->first();
                if ($bestGap && $bestGap['duration_hours'] >= 1) {
                    $startH = sprintf('%02d:%02d', (int)$bestGap['start'], (int)(($bestGap['start'] % 1) * 60));
                    $endH = sprintf('%02d:%02d', (int)$bestGap['end'], (int)(($bestGap['end'] % 1) * 60));

                    $recs[] = [
                        'icon'  => 'fa-bolt',
                        'cls'   => 'text-blue-500 bg-blue-50 dark:bg-blue-900/20',
                        'title' => 'Waktu Fokus Tersedia',
                        "desc"  => "Slot {$startH}-{$endH} ({$bestGap['duration_hours']} jam) - ideal untuk deep work",
                    ];
                }
            }
        }

        // 3. Task due today
        $dueToday = $pendingTasks->filter(fn($t) => $t->due_date && $t->due_date->isToday())->count();
        if ($dueToday > 0) {
            $recs[] = [
                'icon'  => 'fa-calendar-check',
                'cls'   => 'text-orange-500 bg-orange-50 dark:bg-orange-900/20',
                'title' => "{$dueToday} Deadline Hari Ini",
                'desc'  => 'Selesaikan tugas dengan deadline hari ini terlebih dahulu',
            ];
        }

        // 4. Default jika tidak ada rekomendasi
        if (empty($recs)) {
            $recs[] = [
                'icon'  => 'fa-check-circle',
                'cls'   => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20',
                'title' => 'Jadwal Bersih',
                'desc'  => 'Tidak ada tugas mendesak. Manfaatkan waktu untuk pengembangan diri!',
            ];
        }

        return $recs;
    }

    /**
     * Get current activity information
     */
    private function getCurrentActivity(array $fixedBlocks, array $suggestedBlocks, float $currentHour): ?array
    {
        // Cek di fixed blocks
        foreach ($fixedBlocks as $block) {
            if ($currentHour >= $block['start'] && $currentHour < $block['end']) {
                $remaining = $block['end'] - $currentHour;
                return [
                    'label'     => $block['label'],
                    'type'      => $block['type'],
                    'color'     => $block['color'],
                    'icon'      => $block['icon'],
                    'start'     => $block['start'],
                    'end'       => $block['end'],
                    'remaining' => round($remaining, 1),
                    'is_fixed'  => true,
                ];
            }
        }

        // Cek di suggested blocks
        foreach ($suggestedBlocks as $block) {
            if ($currentHour >= $block['start'] && $currentHour < $block['end']) {
                $remaining = $block['end'] - $currentHour;
                return [
                    'label'     => $block['label'],
                    'type'      => $block['type'],
                    'color'     => $block['color'],
                    'icon'      => $block['icon'],
                    'start'     => $block['start'],
                    'end'       => $block['end'],
                    'remaining' => round($remaining, 1),
                    'is_fixed'  => false,
                    'countdown' => $block['countdown'] ?? null,
                ];
            }
        }

        return null;
    }

    /**
     * Normalisasi kategori task
     */
    private function normalizeCategory(string $category): string
    {
        $cat = strtolower(trim($category));

        return match ($cat) {
            'pkl', 'pkk', 'kerja', 'work', 'internship' => 'pkl',
            'academic', 'kuliah', 'academy', 'college', 'university', 'course' => 'academic',
            'skripsi', 'thesis', 'tugas_akhir', 'final_project', 'ta' => 'skripsi',
            'creative', 'kreatif', 'content', 'konten', 'video', 'design' => 'creative',
            'freelance', 'freelancer', 'project', 'proyek', 'client' => 'freelance',
            'health', 'kesehatan', 'workout', 'olahraga', 'fitness' => 'health',
            'finance', 'keuangan', 'money', 'uang', 'budget' => 'finance',
            'personal', 'organisasi', 'org', 'self', 'family', 'keluarga' => 'personal',
            'routine', 'rutin', 'rutinitas', 'habit', 'kebiasaan', 'daily' => 'routine',
            default => 'personal',
        };
    }
}
