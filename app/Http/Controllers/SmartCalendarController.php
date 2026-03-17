<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Task;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class SmartCalendarController extends Controller
{
    /**
     * Display Smart Calendar page (Bulanan)
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        $currentDate = Carbon::create($year, $month, 1);
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        // Get events for this month
        $events = $user->calendarEvents()
            ->whereMonth('start_time', $month)
            ->whereYear('start_time', $year)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->start_time->format('Y-m-d'),
                    'type' => $event->type,
                    'color' => $event->color,
                    'description' => $event->description,
                    'start_time' => $event->start_time->format('H:i'),
                    'end_time' => $event->end_time->format('H:i'),
                ];
            });

        // Get recurring schedules
        $recurringSchedules = $user->schedules()
            ->where('is_recurring', true)
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'day' => $schedule->day,
                    'activity' => $schedule->activity,
                    'time' => $schedule->start_time . ' - ' . $schedule->end_time,
                    'type' => $schedule->type,
                    'color' => $this->getScheduleColor($schedule->type),
                ];
            });

        // Get upcoming deadlines
        $upcomingDeadlines = $user->tasks()
            ->where('due_date', '>=', today())
            ->where('status', '!=', 'done')
            ->orderBy('due_date')
            ->take(5)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'date' => $task->due_date->format('Y-m-d'),
                    'formatted_date' => $task->due_date->format('d M'),
                    'days_remaining' => now()->diffInDays($task->due_date, false),
                    'category' => $task->category,
                    'type' => 'deadline',
                    'color' => $this->getTaskColor($task->category),
                    'description' => $task->description,
                    'progress' => $task->progress,
                    'is_overdue' => $task->is_overdue,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'priority_label' => $task->priority_label,
                ];
            });

        // Calendar data
        $calendarData = $this->generateCalendarData($currentDate, $events);

        // Month names in Indonesian
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        // Day names in Indonesian
        $dayNames = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu'
        ];

        return view('dashboard.smartcalendar', compact(
            'currentDate',
            'prevMonth',
            'nextMonth',
            'events',
            'recurringSchedules',
            'upcomingDeadlines',
            'calendarData',
            'monthNames',
            'dayNames',
            'month',
            'year'
        ));
    }

    /**
     * Show day schedule details
     */
    public function showDay(Request $request, $date)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $carbonDate = Carbon::parse($date);

        // Get events for this day
        $dayEvents = $user->calendarEvents()
            ->whereDate('start_time', $carbonDate)
            ->get();

        // Get tasks for this day
        $dayTasks = $user->tasks()
            ->whereDate('due_date', $carbonDate)
            ->get();

        // Generate hourly schedule
        $hourlySchedule = $this->generateHourlySchedule($carbonDate, $dayEvents, $dayTasks);

        // Get weekly schedule
        $weekStart = $carbonDate->copy()->startOfWeek();
        $weekEnd = $carbonDate->copy()->endOfWeek();
        $weeklyEvents = $user->calendarEvents()
            ->whereBetween('start_time', [$weekStart, $weekEnd])
            ->get();

        // Calculate productive hours
        $productiveHours = $this->calculateProductiveHours($dayEvents, $dayTasks);

        // Month names in Indonesian
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        // Day names in Indonesian
        $dayNames = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];

        return view('dashboard.calendar-day', compact(
            'carbonDate',
            'dayEvents',
            'dayTasks',
            'hourlySchedule',
            'weeklyEvents',
            'weekStart',
            'weekEnd',
            'productiveHours',
            'monthNames',
            'dayNames'
        ));
    }

    /**
     * Store new event
     */
    public function storeEvent(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:deadline,academic,creative,pkl,personal,routine',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|in:daily,weekly,monthly',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Combine date and time
        $startTime = Carbon::parse($request->date . ' ' . $request->start_time);
        $endTime = Carbon::parse($request->date . ' ' . $request->end_time);

        $event = $user->calendarEvents()->create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'color' => $this->getEventColor($request->type),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_recurring' => $request->boolean('is_recurring'),
            'recurring_frequency' => $request->recurring_frequency,
        ]);

        // If recurring, create future events
        if ($request->boolean('is_recurring') && $request->recurring_frequency) {
            $this->createRecurringEvents($event, $request->recurring_frequency);
        }

        return redirect()->back()
            ->with('success', 'Event berhasil ditambahkan!');
    }

    /**
     * Store new schedule
     */
    public function storeSchedule(Request $request)
    {
        $request->validate([
            'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'activity' => 'required|string|max:255',
            'type' => 'required|in:academic,creative,pkl,personal,routine',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'nullable|string',
            'instructor' => 'nullable|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->schedules()->create([
            'day' => $request->day,
            'activity' => $request->activity,
            'type' => $request->type,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'instructor' => $request->instructor,
            'is_recurring' => true,
        ]);

        return redirect()->back()
            ->with('success', 'Jadwal rutin berhasil ditambahkan!');
    }

    /**
     * Store new task
     */
    public function storeTask(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'priority' => 'nullable|in:urgent-important,important-not-urgent,urgent-not-important,not-urgent-not-important',
            'due_date' => 'required|date',
            'estimated_time' => 'nullable|numeric|min:0.5',
            'progress' => 'nullable|integer|min:0|max:100',
            'is_recurring' => 'boolean',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $task = $user->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => $request->progress >= 100 ? 'done' : ($request->progress > 0 ? 'doing' : 'todo'),
            'due_date' => Carbon::parse($request->due_date),
            'estimated_time' => $request->estimated_time,
            'progress' => $request->progress ?? 0,
            'is_recurring' => $request->boolean('is_recurring'),
        ]);

        return redirect()->back()
            ->with('success', 'Task berhasil ditambahkan!');
    }

    /**
     * Delete event
     */
    public function destroyEvent($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $event = $user->calendarEvents()->findOrFail($id);
        $event->delete();

        return redirect()->back()
            ->with('success', 'Event berhasil dihapus!');
    }

    /**
     * Delete schedule
     */
    public function destroySchedule($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $schedule = $user->schedules()->findOrFail($id);
        $schedule->delete();

        return redirect()->back()
            ->with('success', 'Jadwal berhasil dihapus!');
    }

    /**
     * Mark task as complete
     */
    public function markTaskComplete($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $task = $user->tasks()->findOrFail($id);
        $task->update([
            'status' => 'done',
            'progress' => 100,
        ]);

        return redirect()->back()
            ->with('success', 'Task berhasil diselesaikan!');
    }

    /**
     * Generate calendar data for the month
     */
    private function generateCalendarData($currentDate, $events)
    {
        $firstDayOfMonth = $currentDate->copy()->startOfMonth();
        $lastDayOfMonth = $currentDate->copy()->endOfMonth();

        // Get days of the month
        $daysInMonth = $currentDate->daysInMonth;
        $firstDayOfWeek = $firstDayOfMonth->dayOfWeek;

        // Adjust for Sunday as first day (Carbon uses Monday as first day)
        $firstDayOfWeek = $firstDayOfWeek === 0 ? 6 : $firstDayOfWeek - 1;

        // Create calendar array
        $calendar = [];

        // Fill empty days before the first day of month
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $calendar[] = [
                'day' => null,
                'date' => null,
                'events' => [],
                'is_today' => false,
                'is_weekend' => false,
            ];
        }

        // Fill days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $currentDate->copy()->setDay($day);
            $dateString = $date->format('Y-m-d');

            // Get events for this day
            $dayEvents = $events->filter(function ($event) use ($dateString) {
                return $event['date'] === $dateString;
            })->values();

            $calendar[] = [
                'day' => $day,
                'date' => $dateString,
                'formatted_date' => $date->format('d M'),
                'events' => $dayEvents,
                'is_today' => $date->isToday(),
                'is_weekend' => $date->isWeekend(),
                'day_name' => $this->getIndonesianDay($date->dayOfWeek),
            ];
        }

        // Fill empty days to complete the grid (6 rows x 7 columns = 42 cells)
        $remainingDays = 42 - count($calendar);
        for ($i = 0; $i < $remainingDays; $i++) {
            $calendar[] = [
                'day' => null,
                'date' => null,
                'events' => [],
                'is_today' => false,
                'is_weekend' => false,
            ];
        }

        return $calendar;
    }

    /**
     * Generate hourly schedule for a day
     */
    private function generateHourlySchedule($date, $dayEvents, $dayTasks)
    {
        $schedule = [];

        // Combine events and tasks
        $allItems = collect();

        foreach ($dayEvents as $event) {
            $allItems->push([
                'id' => $event->id,
                'type' => 'event',
                'title' => $event->title,
                'start' => $event->start_time->format('H:i'),
                'end' => $event->end_time->format('H:i'),
                'color' => $event->color,
                'description' => $event->description,
            ]);
        }

        foreach ($dayTasks as $task) {
            $allItems->push([
                'id' => $task->id,
                'type' => 'task',
                'title' => $task->title,
                'start' => '09:00',
                'end' => '12:00',
                'color' => $this->getTaskColor($task->category),
                'description' => $task->description,
                'priority' => $task->priority,
            ]);
        }

        // Sort by start time
        $allItems = $allItems->sortBy('start');

        // Create hourly slots
        for ($hour = 0; $hour < 24; $hour++) {
            $timeSlot = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
            $nextSlot = str_pad($hour + 1, 2, '0', STR_PAD_LEFT) . ':00';

            // Find items for this time slot
            $slotItems = $allItems->filter(function ($item) use ($timeSlot, $nextSlot) {
                return $item['start'] >= $timeSlot && $item['start'] < $nextSlot;
            });

            $schedule[] = [
                'time' => $timeSlot,
                'time_display' => $timeSlot,
                'items' => $slotItems->values(),
                'is_current' => $date->isToday() && $hour == date('H'),
                'recommendation' => $this->getTimeRecommendation($hour),
            ];
        }

        return $schedule;
    }

    /**
     * Create recurring events
     */
    private function createRecurringEvents($originalEvent, $frequency)
    {
        $user = $originalEvent->user;
        $startDate = $originalEvent->start_time->copy();

        // Create events for next 3 months
        $endDate = $startDate->copy()->addMonths(3);

        switch ($frequency) {
            case 'daily':
                $interval = 1;
                break;
            case 'weekly':
                $interval = 7;
                break;
            case 'monthly':
                $interval = 30;
                break;
            default:
                $interval = 7;
        }

        $currentDate = $startDate->copy()->addDays($interval);

        while ($currentDate <= $endDate) {
            $user->calendarEvents()->create([
                'title' => $originalEvent->title,
                'description' => $originalEvent->description,
                'type' => $originalEvent->type,
                'color' => $originalEvent->color,
                'start_time' => $currentDate->copy()->setTime(
                    $originalEvent->start_time->hour,
                    $originalEvent->start_time->minute
                ),
                'end_time' => $currentDate->copy()->setTime(
                    $originalEvent->end_time->hour,
                    $originalEvent->end_time->minute
                ),
                'is_recurring' => true,
                'recurring_frequency' => $frequency,
                'parent_event_id' => $originalEvent->id,
            ]);

            $currentDate->addDays($interval);
        }
    }

    /**
     * Get time-based recommendation
     */
    private function getTimeRecommendation($hour)
    {
        if ($hour >= 0 && $hour < 5) {
            return '💤 Waktu tidur optimal';
        } elseif ($hour >= 5 && $hour < 8) {
            return '🌅 Bangun dan persiapan pagi';
        } elseif ($hour >= 8 && $hour < 12) {
            return '🧠 Waktu fokus tinggi - kerjakan tugas penting';
        } elseif ($hour >= 12 && $hour < 14) {
            return '🍽️ Istirahat siang dan makan';
        } elseif ($hour >= 14 && $hour < 18) {
            return '📚 Waktu belajar dan meeting';
        } elseif ($hour >= 18 && $hour < 21) {
            return '🎨 Waktu kreatif dan proyek pribadi';
        } else {
            return '📖 Review dan persiapan besok';
        }
    }

    /**
     * Calculate productive hours for the day
     */
    private function calculateProductiveHours($dayEvents, $dayTasks)
    {
        $totalHours = 0;

        foreach ($dayEvents as $event) {
            $start = Carbon::parse($event->start_time);
            $end = Carbon::parse($event->end_time);
            $totalHours += $start->diffInHours($end, true);
        }

        foreach ($dayTasks as $task) {
            $totalHours += $task->estimated_time ?? 1;
        }

        return round($totalHours, 1);
    }

    /**
     * Get Indonesian day name
     */
    private function getIndonesianDay($dayOfWeek)
    {
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu'
        ];

        return $days[$dayOfWeek] ?? 'Unknown';
    }

    /**
     * Get event color by type
     */
    private function getEventColor($type)
    {
        $colors = [
            'deadline' => '#ef4444',
            'academic' => '#3b82f6',
            'creative' => '#f97316',
            'pkl' => '#10b981',
            'personal' => '#8b5cf6',
            'routine' => '#6b7280',
        ];

        return $colors[$type] ?? '#6b7280';
    }

    /**
     * Get schedule color by type
     */
    private function getScheduleColor($type)
    {
        $colors = [
            'academic' => '#3b82f6',
            'creative' => '#f97316',
            'pkl' => '#10b981',
            'personal' => '#8b5cf6',
            'routine' => '#6b7280',
        ];

        return $colors[$type] ?? '#6b7280';
    }

    /**
     * Get task color by category
     */
    private function getTaskColor($category)
    {
        $colors = [
            'Skripsi' => '#8b5cf6',
            'Creative' => '#f97316',
            'PKL' => '#10b981',
            'Akademik' => '#3b82f6',
            'Personal' => '#64748b',
            'Kesehatan' => '#ef4444',
            'Pengembangan Diri' => '#06b6d4',
            'Organisasi' => '#f59e0b',
            'Perawatan' => '#8b5cf6',
            'Shuttertoct' => '#ec4899',
        ];

        return $colors[$category] ?? '#6b7280';
    }
}
