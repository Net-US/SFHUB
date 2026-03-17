<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\Schedule;
use App\Models\CalendarEvent;
use App\Models\AcademicCourse;
use App\Models\ProjectStage;
use App\Models\PklLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FocusController extends Controller
{
    private function getTimeGreeting()
    {
        $hour = Carbon::now()->format('G');

        if ($hour < 12) return 'Pagi';
        if ($hour < 15) return 'Siang';
        if ($hour < 18) return 'Sore';
        return 'Malam';
    }

    /**
     * Display the focus dashboard
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Get current time
        $currentTime = Carbon::now();
        $currentHour = $currentTime->format('G');
        $currentDay = $this->getIndonesianDay($currentTime->format('w'));

        // Get today's schedule
        $todaySchedule = $this->getTodaySchedule($user, $currentDay);

        // Get current activity based on time
        $currentActivity = $this->getCurrentActivity($todaySchedule, $currentHour);

        // Get priority tasks for today
        $priorityTasks = $this->getTodayPriorityTasks($user);

        // Get upcoming deadlines (next 3 days)
        $upcomingDeadlines = $this->getUpcomingDeadlines($user);

        // Get exams/tests for this week
        $thisWeekExams = $this->getThisWeekExams($user);

        // Get routine tasks
        $routineTasks = $this->getRoutineTasks($user);

        $timeGreeting = $this->getTimeGreeting() . ', here are your routine tasks for today.';

        // Get productivity suggestion
        $productivitySuggestion = $this->getProductivitySuggestion(
            $currentActivity,
            $priorityTasks,
            $currentHour
        );

        return view('focus.index', compact(
            'currentTime',
            'currentDay',
            'todaySchedule',
            'currentActivity',
            'priorityTasks',
            'upcomingDeadlines',
            'thisWeekExams',
            'routineTasks',
            'productivitySuggestion',
            'timeGreeting'
        ));
    }

    /**
     * Get today's schedule based on day
     */
    private function getTodaySchedule($user, $currentDay)
    {
        // Get recurring schedule for today
        $recurringSchedule = Schedule::where('user_id', $user->id)
            ->where(function ($query) use ($currentDay) {
                $query->where('day', $currentDay)
                    ->orWhere('is_recurring', false);
            })
            ->whereDate('created_at', '<=', Carbon::today())
            ->orderBy('start_time')
            ->get()
            ->map(function ($schedule) {
                return [
                    'start' => (int) Carbon::parse($schedule->start_time)->format('G'),
                    'end' => (int) Carbon::parse($schedule->end_time)->format('G'),
                    'activity' => $schedule->activity,
                    'type' => $schedule->type,
                    'location' => $schedule->location,
                    'color' => $this->getTypeColor($schedule->type),
                ];
            })
            ->toArray();

        // Get today's calendar events
        $calendarEvents = CalendarEvent::where('user_id', $user->id)
            ->whereDate('start_time', Carbon::today())
            ->orderBy('start_time')
            ->get()
            ->map(function ($event) {
                $startHour = Carbon::parse($event->start_time)->format('G');
                $endHour = Carbon::parse($event->end_time)->format('G');

                return [
                    'start' => (int) $startHour,
                    'end' => (int) $endHour,
                    'activity' => $event->title,
                    'type' => $event->type,
                    'description' => $event->description,
                    'color' => $this->getEventColor($event->type),
                ];
            })
            ->toArray();

        return array_merge($recurringSchedule, $calendarEvents);
    }

    /**
     * Get current activity based on schedule and time
     */
    private function getCurrentActivity($schedule, $currentHour)
    {
        foreach ($schedule as $activity) {
            if ($currentHour >= $activity['start'] && $currentHour < $activity['end']) {
                return $activity;
            }
        }

        // Default activity if nothing scheduled
        return [
            'activity' => 'Free Time / Self Study',
            'type' => 'personal',
            'color' => 'bg-stone-100 border-stone-300 text-stone-800',
            'suggestion' => 'This is a good time to work on personal projects or catch up on tasks.'
        ];
    }

    /**
     * Get today's priority tasks using Eisenhower Matrix
     */
    private function getTodayPriorityTasks($user)
    {
        return Task::where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereDate('due_date', Carbon::today())
                    ->orWhere('priority', 'urgent-important');
            })
            ->whereNotIn('status', ['done', 'archived'])
            ->orderByRaw("
                CASE priority
                    WHEN 'urgent-important' THEN 1
                    WHEN 'important-not-urgent' THEN 2
                    WHEN 'urgent-not-important' THEN 3
                    WHEN 'not-urgent-not-important' THEN 4
                END
            ")
            ->orderBy('due_date')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'category' => $task->category,
                    'priority' => $task->priority,
                    'priority_color' => $this->getPriorityColor($task->priority),
                    'due_date' => $task->due_date ? Carbon::parse($task->due_date)->format('d M') : null,
                    'due_text' => $this->getDueText($task->due_date),
                    'status' => $task->status,
                    'estimated_time' => $task->estimated_time,
                ];
            });
    }

    /**
     * Get upcoming deadlines (3 days ahead)
     */
    private function getUpcomingDeadlines($user)
    {
        $deadlines = [];

        // Get task deadlines
        $taskDeadlines = Task::where('user_id', $user->id)
            ->whereDate('due_date', '>', Carbon::today())
            ->whereDate('due_date', '<=', Carbon::today()->addDays(3))
            ->whereNotIn('status', ['done', 'archived'])
            ->orderBy('due_date')
            ->get();

        foreach ($taskDeadlines as $task) {
            $deadlines[] = [
                'type' => 'task',
                'title' => $task->title,
                'deadline' => Carbon::parse($task->due_date),
                'category' => $task->category,
                'priority' => $task->priority,
            ];
        }

        // Get project deadlines
        $projectDeadlines = ProjectStage::where('user_id', $user->id)
            ->whereDate('deadline', '>', Carbon::today())
            ->whereDate('deadline', '<=', Carbon::today()->addDays(3))
            ->where('status', 'active')
            ->orderBy('deadline')
            ->get();

        foreach ($projectDeadlines as $project) {
            $deadlines[] = [
                'type' => 'project',
                'title' => $project->title . ' (' . $project->stage . ')',
                'deadline' => Carbon::parse($project->deadline),
                'category' => 'creative',
                'progress' => $project->progress,
            ];
        }

        // Sort by deadline
        usort($deadlines, function ($a, $b) {
            return $a['deadline'] <=> $b['deadline'];
        });

        return array_slice($deadlines, 0, 5);
    }

    /**
     * Get exams/tests for this week
     */
    private function getThisWeekExams($user)
    {
        return CalendarEvent::where('user_id', $user->id)
            ->where('type', 'exam')
            ->whereBetween('start_time', [Carbon::today(), Carbon::today()->addDays(7)])
            ->orderBy('start_time')
            ->get()
            ->map(function ($exam) {
                return [
                    'title' => $exam->title,
                    'date' => Carbon::parse($exam->start_time)->format('d M'),
                    'time' => Carbon::parse($exam->start_time)->format('H:i'),
                    'course' => $exam->description,
                    'location' => $exam->location,
                    'days_remaining' => Carbon::now()->diffInDays(Carbon::parse($exam->start_time), false),
                ];
            });
    }

    /**
     * Get routine/recurring tasks
     */
    private function getRoutineTasks($user)
    {
        return Task::where('user_id', $user->id)
            ->where('is_recurring', true)
            ->where(function ($query) {
                // Get routines that should be done today based on pattern
                $query->where('recurring_pattern', 'like', '%' . strtolower(Carbon::now()->format('l')) . '%')
                    ->orWhere('recurring_pattern', 'daily');
            })
            ->where('status', 'todo')
            ->orderBy('created_at')
            ->limit(5)
            ->get();
    }

    /**
     * Get productivity suggestion based on current context
     */
    private function getProductivitySuggestion($currentActivity, $priorityTasks, $currentHour)
    {
        $suggestions = [];

        // Check if we're in scheduled activity time
        if ($currentActivity['type'] !== 'personal') {
            $suggestions[] = "Fokus pada: " . $currentActivity['activity'];
            if (isset($currentActivity['location'])) {
                $suggestions[] = "Lokasi: " . $currentActivity['location'];
            }
        }

        // Check for urgent tasks
        $urgentTasks = $priorityTasks->where('priority', 'urgent-important')->take(2);
        if ($urgentTasks->count() > 0) {
            $suggestions[] = "Prioritas utama: " . $urgentTasks->first()['title'];
        }

        // Time-based suggestions
        if ($currentHour < 12) {
            $suggestions[] = "Waktu optimal untuk tugas yang membutuhkan fokus tinggi";
        } elseif ($currentHour >= 12 && $currentHour < 15) {
            $suggestions[] = "Sebaiknya kerjakan tugas ringan setelah makan siang";
        } elseif ($currentHour >= 15 && $currentHour < 18) {
            $suggestions[] = "Waktu baik untuk meeting atau kolaborasi";
        } else {
            $suggestions[] = "Malam hari cocok untuk review dan perencanaan besok";
        }

        return $suggestions;
    }

    /**
     * Helper: Get Indonesian day name
     */
    private function getIndonesianDay($dayNumber)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $days[$dayNumber] ?? 'Minggu';
    }

    /**
     * Helper: Get color based on activity type
     */
    private function getTypeColor($type)
    {
        return match ($type) {
            'academic' => 'bg-blue-100 border-blue-300 text-blue-800',
            'creative' => 'bg-orange-100 border-orange-300 text-orange-800',
            'pkl' => 'bg-emerald-100 border-emerald-300 text-emerald-800',
            'exam' => 'bg-red-100 border-red-300 text-red-800',
            'personal' => 'bg-purple-100 border-purple-300 text-purple-800',
            'routine' => 'bg-stone-100 border-stone-300 text-stone-800',
            default => 'bg-gray-100 border-gray-300 text-gray-800',
        };
    }

    /**
     * Helper: Get event color
     */
    private function getEventColor($type)
    {
        return match ($type) {
            'deadline' => 'bg-red-500',
            'project' => 'bg-orange-500',
            'academic' => 'bg-blue-500',
            'creative' => 'bg-emerald-500',
            'pkl' => 'bg-purple-500',
            default => 'bg-gray-500',
        };
    }

    /**
     * Helper: Get priority color
     */
    private function getPriorityColor($priority)
    {
        return match ($priority) {
            'urgent-important' => 'bg-red-100 text-red-800',
            'important-not-urgent' => 'bg-blue-100 text-blue-800',
            'urgent-not-important' => 'bg-orange-100 text-orange-800',
            'not-urgent-not-important' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Helper: Get due text
     */
    private function getDueText($dueDate)
    {
        if (!$dueDate) return 'Flexible';

        $due = Carbon::parse($dueDate);
        $today = Carbon::today();

        if ($due->isToday()) return 'Hari Ini';
        if ($due->isTomorrow()) return 'Besok';

        $diffDays = $today->diffInDays($due, false);

        if ($diffDays < 0) return 'Overdue';
        if ($diffDays == 0) return 'Hari Ini';
        if ($diffDays == 1) return 'Besok';
        if ($diffDays <= 7) return $diffDays . ' Hari Lagi';

        return $due->format('d M');
    }

    /**
     * AJAX: Update current time simulation
     */
    public function updateTime(Request $request)
    {
        $hour = $request->input('hour', Carbon::now()->format('G'));
        $user = Auth::user();

        // Recalculate based on simulated time
        $currentDay = $this->getIndonesianDay(Carbon::now()->format('w'));
        $todaySchedule = $this->getTodaySchedule($user, $currentDay);
        $currentActivity = $this->getCurrentActivity($todaySchedule, $hour);

        return response()->json([
            'hour' => $hour,
            'time_display' => str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00',
            'current_activity' => $currentActivity,
        ]);
    }

    /**
     * AJAX: Complete task
     */
    public function completeTask(Task $task, Request $request)
    {
        // Authorization check
        if ($task->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->update([
            'status' => 'done',
            'actual_time' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task marked as complete',
            'task_id' => $task->id,
        ]);
    }

    /**
     * AJAX: Quick add task
     */
    public function quickAddTask(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'required|in:urgent-important,important-not-urgent,urgent-not-important,not-urgent-not-important',
            'category' => 'nullable|string|max:100',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $task = $user->tasks()->create([
            'title' => $request->title,
            'priority' => $request->priority,
            'category' => $request->category ?? 'general',
            'status' => 'todo',
            'due_date' => $request->due_date ?? Carbon::today(),
        ]);

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'priority' => $task->priority,
                'priority_color' => $this->getPriorityColor($task->priority),
                'category' => $task->category,
            ],
        ]);
    }
}
