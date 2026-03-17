<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Task;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SmartCalendarController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // INDEX — Halaman utama Smart Calendar
    // ─────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $month = (int) $request->get('month', date('m'));
        $year  = (int) $request->get('year', date('Y'));

        $currentDate = Carbon::create($year, $month, 1);
        $prevMonth   = $currentDate->copy()->subMonth();
        $nextMonth   = $currentDate->copy()->addMonth();

        // ── One-off events bulan ini ──────────────────────────────────────
        $events = $user->calendarEvents()
            ->whereMonth('start_time', $month)
            ->whereYear('start_time', $year)
            ->get()
            ->map(fn($e) => [
                'id'          => $e->id,
                'title'       => $e->title,
                'date'        => $e->start_time->format('Y-m-d'),
                'type'        => $e->type,
                'color'       => $e->color ?? $this->getEventColor($e->type),
                'description' => $e->description,
                'start_time'  => $e->start_time->format('H:i'),
                'end_time'    => optional($e->end_time)->format('H:i') ?? '23:59',
            ]);

        // ── Upcoming deadlines dari tasks ─────────────────────────────────
        $upcomingDeadlines = $user->tasks()
            ->where('due_date', '>=', today())
            ->where('status', '!=', 'done')
            ->orderBy('due_date')
            ->take(5)
            ->get()
            ->map(fn($t) => [
                'id'              => $t->id,
                'title'           => $t->title,
                'date'            => $t->due_date->format('Y-m-d'),
                'formatted_date'  => $t->due_date->format('d M'),
                'days_remaining'  => now()->diffInDays($t->due_date, false),
                'category'        => $t->category,
                'type'            => 'deadline',
                'color'           => $this->getTaskColor($t->category),
                'is_overdue'      => $t->due_date->isPast(),
            ]);

        // ── Recurring activities (Schedule model) ─────────────────────────
        $categoryIconMap = [
            'pkl'      => '💼',
            'academic' => '📚',
            'creative' => '🎬',
            'finance'  => '💰',
            'personal' => '🌅',
            'health'   => '🏃',
            'work'     => '💼',
            'routine'  => '🔁',
        ];
        $colorMap = [
            'pkl'      => '#10b981',
            'academic' => '#3b82f6',
            'creative' => '#f97316',
            'finance'  => '#f59e0b',
            'personal' => '#94a3b8',
            'health'   => '#ef4444',
            'work'     => '#059669',
            'routine'  => '#6b7280',
        ];
        $dayMap = ['Minggu' => 0, 'Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6];

        $recurringActivities = $user->schedules()
            ->where('is_recurring', true)
            ->get()
            ->map(function ($s) use ($categoryIconMap, $colorMap, $dayMap) {
                $type = $s->type ?? 'personal';
                $freq = $s->frequency ?? 'weekly';

                // Parse hari dari days_of_week (comma-separated) atau kolom day
                $days = [];
                if (!empty($s->days_of_week)) {
                    $days = collect(explode(',', $s->days_of_week))
                        ->map(fn($d) => $dayMap[trim($d)] ?? 0)
                        ->toArray();
                } elseif (!empty($s->day)) {
                    $days = [$dayMap[trim($s->day)] ?? 1];
                }

                // Format waktu: HH:MM dari kolom start_time / end_time
                $startT = $s->start_time
                    ? (strlen($s->start_time) > 5 ? substr($s->start_time, 11, 5) : substr($s->start_time, 0, 5))
                    : '';
                $endT = $s->end_time
                    ? (strlen($s->end_time) > 5 ? substr($s->end_time, 11, 5) : substr($s->end_time, 0, 5))
                    : '';
                $timeLabel = $startT ? ($startT . ($endT ? '–' . $endT : '')) : '';

                return [
                    'id'           => $s->id,
                    'title'        => $s->activity ?? $s->title ?? 'Jadwal',
                    'category'     => $type,
                    'color'        => $s->color ?? ($colorMap[$type] ?? '#94a3b8'),
                    'frequency'    => $freq,
                    'days'         => $days,
                    'day_of_month' => $s->day_of_month ?? null,
                    'time'         => $timeLabel,
                    'start_time'   => $startT,
                    'end_time'     => $endT,
                    'start_date'   => $s->start_date   ?? now()->format('Y-m-d'),
                    'end_date'     => $s->end_date     ?? now()->addYear()->format('Y-m-d'),
                    'notes'        => $s->notes        ?? '',
                    'icon'         => $categoryIconMap[$type] ?? '📌',
                ];
            })->toArray();

        // ── Rekap recurring per tanggal di bulan ini (untuk overlay kalender)
        $recEventsByDate = [];
        $daysInMonth     = $currentDate->daysInMonth;
        foreach ($recurringActivities as $rec) {
            $startRec = Carbon::parse($rec['start_date']);
            $endRec   = Carbon::parse($rec['end_date']);
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateObj = Carbon::create($year, $month, $d);
                if ($dateObj->lt($startRec) || $dateObj->gt($endRec)) continue;
                $match = match ($rec['frequency']) {
                    'daily'   => true,
                    'weekly'  => in_array($dateObj->dayOfWeek, $rec['days']),
                    'monthly' => $dateObj->day == $rec['day_of_month'],
                    default   => false,
                };
                if ($match) {
                    $recEventsByDate[$dateObj->format('Y-m-d')][] = $rec;
                }
            }
        }

        // ── Jadwal mingguan ringkasan ─────────────────────────────────────
        $dayNames2  = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $colorDays  = ['bg-emerald-400', 'bg-emerald-400', 'bg-emerald-400', 'bg-blue-400', 'bg-blue-400', 'bg-indigo-400', 'bg-stone-400'];
        $weeklySchedule = [];
        foreach ($dayNames2 as $i => $dayName) {
            $dayScheds = $user->schedules()->where('day', $dayName)->get();
            $items = $dayScheds->map(
                fn($s) => ($s->activity ?? 'Jadwal') .
                    ($s->start_time ? ' ' . substr($s->start_time, 0, 5) : '') .
                    ($s->end_time   ? '–' . substr($s->end_time, 0, 5)   : '')
            )->toArray();
            $weeklySchedule[] = ['day' => $dayName, 'color' => $colorDays[$i], 'items' => $items ?: ['Tidak ada jadwal']];
        }

        return view('dashboard.smartcalendar', compact(
            'currentDate',
            'prevMonth',
            'nextMonth',
            'events',
            'recurringActivities',
            'recEventsByDate',
            'weeklySchedule',
            'upcomingDeadlines',
            'month',
            'year'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // STORE EVENT (one-off) — POST /calendar/events
    // ─────────────────────────────────────────────────────────────────────
    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type'        => 'required|in:deadline,academic,creative,pkl,personal,routine,finance',
            'date'        => 'required|date',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
            'is_all_day'  => 'boolean',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $startDT = Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
        $endDT   = Carbon::parse($validated['date'] . ' ' . $validated['end_time']);

        // Jika end <= start, tambah 1 hari (kegiatan melewati tengah malam)
        if ($endDT->lte($startDT)) {
            $endDT->addDay();
        }

        $user->calendarEvents()->create([
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'type'        => $validated['type'],
            'color'       => $this->getEventColor($validated['type']),
            'start_time'  => $startDT,
            'end_time'    => $endDT,
            'is_all_day'  => $request->boolean('is_all_day'),
            'is_recurring' => false,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Event berhasil ditambahkan!']);
        }
        return redirect()->back()->with('success', 'Event berhasil ditambahkan!');
    }

    // ─────────────────────────────────────────────────────────────────────
    // STORE SCHEDULE (recurring) — POST /calendar/schedules
    // ─────────────────────────────────────────────────────────────────────
    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'activity'    => 'required|string|max:255',
            'type'        => 'required|in:academic,creative,pkl,personal,routine,health,finance',
            'frequency'   => 'required|in:daily,weekly,monthly',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'notes'       => 'nullable|string|max:500',
            'color'       => 'nullable|string|max:20',
            // weekly
            'days_of_week'  => 'required_if:frequency,weekly|nullable|string',
            // monthly
            'day_of_month'  => 'required_if:frequency,monthly|nullable|integer|min:1|max:31',
            // single-day weekly (lama)
            'day'           => 'nullable|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $colorMap = [
            'pkl'      => '#10b981',
            'academic' => '#3b82f6',
            'creative' => '#f97316',
            'finance'  => '#f59e0b',
            'personal' => '#94a3b8',
            'health'   => '#ef4444',
            'routine'  => '#6b7280',
        ];

        // Untuk backward-compat: jika weekly & days_of_week berisi 1 hari, set kolom day juga
        $day = null;
        if ($validated['frequency'] === 'weekly' && !empty($validated['days_of_week'])) {
            $dayParts = explode(',', $validated['days_of_week']);
            if (count($dayParts) === 1) {
                $day = trim($dayParts[0]);
            }
        } elseif ($validated['frequency'] !== 'weekly' && !empty($validated['day'])) {
            $day = $validated['day'];
        }

        $user->schedules()->create([
            'activity'     => $validated['activity'],
            'type'         => $validated['type'],
            'frequency'    => $validated['frequency'],
            'day'          => $day,
            'days_of_week' => $validated['days_of_week'] ?? null,
            'day_of_month' => $validated['day_of_month'] ?? null,
            'start_time'   => $validated['start_time'],
            'end_time'     => $validated['end_time'],
            'start_date'   => $validated['start_date'],
            'end_date'     => $validated['end_date'],
            'notes'        => $validated['notes'] ?? null,
            'color'        => $validated['color'] ?? ($colorMap[$validated['type']] ?? '#94a3b8'),
            'is_recurring' => true,
            'location'     => $request->input('location'),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Jadwal rutin berhasil ditambahkan!']);
        }
        return redirect()->back()->with('success', 'Jadwal rutin berhasil ditambahkan!');
    }

    // ─────────────────────────────────────────────────────────────────────
    // UPDATE SCHEDULE — PUT /calendar/schedules/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function updateSchedule(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $schedule = $user->schedules()->findOrFail($id);

        $validated = $request->validate([
            'activity'    => 'required|string|max:255',
            'type'        => 'required|in:academic,creative,pkl,personal,routine,health,finance',
            'frequency'   => 'required|in:daily,weekly,monthly',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'notes'       => 'nullable|string|max:500',
            'color'       => 'nullable|string|max:20',
            'days_of_week' => 'nullable|string',
            'day_of_month' => 'nullable|integer|min:1|max:31',
        ]);

        $day = null;
        if ($validated['frequency'] === 'weekly' && !empty($validated['days_of_week'])) {
            $parts = explode(',', $validated['days_of_week']);
            if (count($parts) === 1) $day = trim($parts[0]);
        }

        $schedule->update([
            'activity'     => $validated['activity'],
            'type'         => $validated['type'],
            'frequency'    => $validated['frequency'],
            'day'          => $day,
            'days_of_week' => $validated['days_of_week'] ?? null,
            'day_of_month' => $validated['day_of_month'] ?? null,
            'start_time'   => $validated['start_time'],
            'end_time'     => $validated['end_time'],
            'start_date'   => $validated['start_date'],
            'end_date'     => $validated['end_date'],
            'notes'        => $validated['notes'] ?? null,
            'color'        => $validated['color'] ?? $schedule->color,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Jadwal berhasil diperbarui!']);
        }
        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui!');
    }

    // ─────────────────────────────────────────────────────────────────────
    // DELETE EVENT
    // ─────────────────────────────────────────────────────────────────────
    public function destroyEvent(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $event = $user->calendarEvents()->findOrFail($id);
        $event->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Event berhasil dihapus!']);
        }
        return redirect()->back()->with('success', 'Event berhasil dihapus!');
    }

    // ─────────────────────────────────────────────────────────────────────
    // DELETE SCHEDULE
    // ─────────────────────────────────────────────────────────────────────
    public function destroySchedule(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user     = Auth::user();
        $schedule = $user->schedules()->findOrFail($id);
        $schedule->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Jadwal berhasil dihapus!']);
        }
        return redirect()->back()->with('success', 'Jadwal berhasil dihapus!');
    }

    // ─────────────────────────────────────────────────────────────────────
    // SHOW DAY
    // ─────────────────────────────────────────────────────────────────────
    public function showDay(Request $request, $date)
    {
        /** @var \App\Models\User $user */
        $user       = Auth::user();
        $carbonDate = Carbon::parse($date);
        $dayEvents  = $user->calendarEvents()->whereDate('start_time', $carbonDate)->get();
        $dayTasks   = $user->tasks()->whereDate('due_date', $carbonDate)->get();

        return view('dashboard.calendar-day', compact('carbonDate', 'dayEvents', 'dayTasks'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // MARK TASK COMPLETE
    // ─────────────────────────────────────────────────────────────────────
    public function markTaskComplete(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);
        $task->update(['status' => 'done', 'progress' => 100]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Task diselesaikan!');
    }

    // ─────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────
    private function getEventColor(string $type): string
    {
        return match ($type) {
            'deadline' => '#ef4444',
            'academic' => '#3b82f6',
            'creative' => '#f97316',
            'pkl'      => '#10b981',
            'personal' => '#8b5cf6',
            'finance'  => '#f59e0b',
            'routine'  => '#6b7280',
            default    => '#6b7280',
        };
    }

    private function getTaskColor(string $category): string
    {
        return match ($category) {
            'Skripsi'           => '#8b5cf6',
            'Creative'          => '#f97316',
            'PKL'               => '#10b981',
            'Akademik'          => '#3b82f6',
            'Personal'          => '#64748b',
            'Kesehatan'         => '#ef4444',
            'Pengembangan Diri' => '#06b6d4',
            'Organisasi'        => '#f59e0b',
            'Perawatan'         => '#8b5cf6',
            default             => '#6b7280',
        };
    }
}
