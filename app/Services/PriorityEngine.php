<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\Schedule;
use App\Models\Event;
use App\Models\Subject;
use App\Models\PklLog;
use App\Models\ContentSchedule;
use Illuminate\Support\Facades\Log;

class PriorityEngine
{
    private $user;
    private $conflictResolver;
    private $contentTracker;

    public function __construct($user)
    {
        $this->user = $user;
        $this->conflictResolver = new ConflictResolver($user);
        $this->contentTracker = new ContentTrackerService($user);
    }

    /**
     * LOGIKA UTAMA: Dapatkan rekomendasi yang pintar berdasarkan waktu
     */
    public function getSmartRecommendations($now = null)
    {
        if ($now === null) {
            $now = Carbon::now();
        }

        try {
            $currentHour = $now->hour;
            $currentMinute = $now->minute;
            $currentDay = $now->dayOfWeek; // 0=Minggu, 1=Senin, dst

            // 1. Cek apakah ada jadwal aktif SAAT INI
            $currentSchedule = $this->getCurrentSchedule($now);

            // 2. Jika ada jadwal aktif: fokus ke jadwal
            if ($currentSchedule) {
                return $this->getScheduleBasedRecommendations($currentSchedule, $now);
            }

            // 3. Jika tidak ada jadwal: berikan rekomendasi berdasarkan waktu dan tugas
            return $this->getFreeTimeRecommendations($now);
        } catch (\Exception $e) {
            Log::error('PriorityEngine error: ' . $e->getMessage());
            return $this->getFallbackRecommendations($now);
        }
    }

    /**
     * LOGIKA 1: Jika ada jadwal aktif
     */
    private function getScheduleBasedRecommendations($schedule, $now)
    {
        $recommendations = [];
        $endTime = Carbon::parse($schedule->end_time);
        $remainingMinutes = $now->diffInMinutes($endTime, false);

        // Jika waktu tersisa sedikit (< 15 menit), beri rekomendasi singkat
        if ($remainingMinutes < 15 && $remainingMinutes > 0) {
            $quickTasks = $this->getQuickTasks($remainingMinutes);
            if (!empty($quickTasks)) {
                $recommendations[] = [
                    'type' => 'quick_before_schedule_end',
                    'title' => 'Tugas Cepat Sebelum Jadwal Berakhir',
                    'task' => $quickTasks[0],
                    'time_left' => $remainingMinutes . ' menit',
                    'message' => 'Kerjakan tugas singkat ini sebelum ' . $schedule->end_time
                ];
            }
        }

        // Rekomendasi untuk waktu setelah jadwal
        $afterScheduleTime = $endTime->copy()->addMinutes(15); // Beri waktu istirahat 15 menit
        $nextSchedule = $this->getNextSchedule($now);

        if ($nextSchedule) {
            $timeBetween = $afterScheduleTime->diffInMinutes(Carbon::parse($nextSchedule->start_time));

            if ($timeBetween >= 30) { // Minimal 30 menit
                $recommendations[] = [
                    'type' => 'between_schedules',
                    'title' => 'Waktu Antara Jadwal',
                    'duration' => $timeBetween . ' menit',
                    'next_schedule' => $nextSchedule->activity . ' (' . $nextSchedule->start_time . ')',
                    'recommendations' => $this->getTasksForTimeSlot($timeBetween, 'medium')
                ];
            }
        } else {
            // Tidak ada jadwal selanjutnya, rekomendasi untuk sisa hari
            $recommendations = array_merge($recommendations, $this->getEveningRecommendations($afterScheduleTime));
        }

        return [
            'current_schedule' => $schedule,
            'recommendations' => $recommendations,
            'message' => 'Anda sedang dalam jadwal: ' . $schedule->activity
        ];
    }

    /**
     * LOGIKA 2: Jika waktu bebas (tidak ada jadwal)
     */
    private function getFreeTimeRecommendations($now)
    {
        $currentHour = $now->hour;
        $recommendations = [];

        // LOGIKA BERDASARKAN WAKTU HARI
        if ($currentHour >= 0 && $currentHour < 5) {
            // DINI HARI (00:00 - 05:00)
            if ($currentHour < 3) {
                // Masih larut malam
                $recommendations[] = [
                    'type' => 'late_night',
                    'title' => '💤 Waktu Istirahat',
                    'message' => 'Sebaiknya istirahat. Tubuh butuh waktu tidur yang cukup.',
                    'suggestion' => 'Jika ingin bekerja, pilih tugas yang ringan dan tidak terlalu memerlukan konsentrasi tinggi.',
                    'ideal_tasks' => $this->getLateNightTasks()
                ];
            } else {
                // Sudah mendekati pagi
                $recommendations[] = [
                    'type' => 'early_morning',
                    'title' => '🌅 Persiapan Pagi',
                    'message' => 'Bangun lebih awal? Ini waktu yang baik untuk persiapan.',
                    'suggestion' => 'Siapkan rencana hari ini, olahraga ringan, atau kerjakan tugas yang membutuhkan fokus tinggi.',
                    'ideal_tasks' => $this->getEarlyMorningTasks()
                ];
            }
        } elseif ($currentHour >= 5 && $currentHour < 8) {
            // PAGI (05:00 - 08:00)
            $recommendations[] = [
                'type' => 'morning_preparation',
                'title' => '☕ Persiapan Hari',
                'message' => 'Waktu yang ideal untuk persiapan dan perencanaan.',
                'suggestion' => 'Review tugas hari ini, buat timeline, sarapan, dan kerjakan tugas yang butuh fokus tinggi.',
                'ideal_tasks' => $this->getMorningTasks()
            ];
        } elseif ($currentHour >= 8 && $currentHour < 12) {
            // PAGI-PKAL (08:00 - 12:00)
            $nextSchedule = $this->getNextSchedule($now);

            if ($nextSchedule && Carbon::parse($nextSchedule->start_time)->hour >= 13) {
                // Ada waktu sebelum PKL/Kuliah
                $timeAvailable = $now->diffInMinutes(Carbon::parse('13:00'));
                $recommendations[] = [
                    'type' => 'pre_work_time',
                    'title' => '⏰ Sebelum Aktivitas Utama',
                    'message' => 'Waktu produktif sebelum jadwal utama dimulai.',
                    'time_available' => $timeAvailable . ' menit',
                    'suggestion' => 'Kerjakan tugas yang butuh konsentrasi tinggi, karena energi masih fresh.',
                    'ideal_tasks' => $this->getTasksForTimeSlot($timeAvailable, 'deep_work')
                ];
            }
        } elseif ($currentHour >= 12 && $currentHour < 13) {
            // SIANG (12:00 - 13:00) - ISTIRAHAT MAKAN
            $recommendations[] = [
                'type' => 'lunch_break',
                'title' => '🍽️ Waktu Istirahat Siang',
                'message' => 'Saatnya istirahat dan makan siang.',
                'suggestion' => 'Istirahatkan pikiran 20-30 menit. Bisa kerjakan tugas ringan sambil makan.',
                'quick_tasks' => $this->getQuickTasks(30)
            ];
        } elseif ($currentHour >= 13 && $currentHour < 17) {
            // SIANG-SORE (13:00 - 17:00)
            $nextSchedule = $this->getNextSchedule($now);

            if ($nextSchedule) {
                $timeAvailable = $now->diffInMinutes(Carbon::parse($nextSchedule->start_time));
                $recommendations[] = [
                    'type' => 'between_activities',
                    'title' => '📚 Waktu Belajar/Tugas',
                    'message' => 'Waktu yang baik untuk mengerjakan tugas akademik.',
                    'time_available' => $timeAvailable . ' menit',
                    'suggestion' => 'Fokus pada tugas kuliah atau akademik.',
                    'ideal_tasks' => $this->getAcademicTasks($timeAvailable)
                ];
            }
        } elseif ($currentHour >= 17 && $currentHour < 19) {
            // SORE (17:00 - 19:00) - WAKTU BEBAS
            $recommendations[] = [
                'type' => 'evening_free_time',
                'title' => '🏃 Waktu Bebas Sore',
                'message' => 'Waktu yang baik untuk olahraga, hobi, atau tugas ringan.',
                'suggestion' => 'Kerjakan tugas yang menyenangkan atau rutinitas.',
                'ideal_tasks' => $this->getEveningTasks()
            ];
        } elseif ($currentHour >= 19 && $currentHour < 22) {
            // MALAM (19:00 - 22:00) - WAKTU PRODUKTIF
            $recommendations[] = [
                'type' => 'productive_night',
                'title' => '🌙 Waktu Produktif Malam',
                'message' => 'Waktu terbaik untuk fokus pada proyek besar.',
                'suggestion' => 'Kerjakan skripsi, proyek freelance, atau tugas yang butuh waktu panjang.',
                'ideal_tasks' => $this->getNightDeepWorkTasks()
            ];
        } else {
            // MALAM JELANG TIDUR (22:00 - 00:00)
            $recommendations[] = [
                'type' => 'wind_down',
                'title' => '😴 Persiapan Tidur',
                'message' => 'Waktu untuk menenangkan pikiran.',
                'suggestion' => 'Review pencapaian hari ini, rencanakan besok, atau kerjakan tugas ringan.',
                'ideal_tasks' => $this->getWindDownTasks()
            ];
        }

        // Tambahkan rekomendasi berdasarkan deadline
        $deadlineRecommendations = $this->getDeadlineBasedRecommendations($now);
        if (!empty($deadlineRecommendations)) {
            $recommendations = array_merge($deadlineRecommendations, $recommendations);
        }

        return [
            'current_schedule' => null,
            'recommendations' => $recommendations,
            'message' => 'Waktu bebas - Pilih aktivitas yang sesuai'
        ];
    }

    /**
     * LOGIKA 3: Rekomendasi berdasarkan deadline
     */
    private function getDeadlineBasedRecommendations($now)
    {
        $tasks = Task::where('user_id', $this->user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->take(5)
            ->get()
            ->map(function ($task) use ($now) {
                $dueDate = Carbon::parse($task->due_date);
                $daysUntilDue = $now->diffInDays($dueDate, false);

                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'due_date' => $task->due_date,
                    'days_until_due' => $daysUntilDue,
                    'priority' => $task->priority,
                    'estimated_time' => $task->estimated_time,
                    'urgency_level' => $this->getUrgencyLevel($daysUntilDue),
                    'category' => $task->category
                ];
            })
            ->toArray();

        $recommendations = [];

        // Tugas yang sudah overdue
        $overdueTasks = array_filter($tasks, function ($task) {
            return $task['days_until_due'] < 0;
        });

        if (!empty($overdueTasks)) {
            $recommendations[] = [
                'type' => 'overdue_alert',
                'title' => '🚨 TUGAS TERLAMBAT!',
                'message' => 'Ada ' . count($overdueTasks) . ' tugas yang sudah melewati deadline.',
                'tasks' => $overdueTasks,
                'priority' => 'critical'
            ];
        }

        // Tugas deadline hari ini
        $todayTasks = array_filter($tasks, function ($task) {
            return $task['days_until_due'] === 0;
        });

        if (!empty($todayTasks)) {
            $recommendations[] = [
                'type' => 'deadline_today',
                'title' => '🔥 DEADLINE HARI INI',
                'message' => 'Segera selesaikan ' . count($todayTasks) . ' tugas yang deadline-nya hari ini.',
                'tasks' => $todayTasks,
                'priority' => 'high'
            ];
        }

        // Tugas deadline besok
        $tomorrowTasks = array_filter($tasks, function ($task) {
            return $task['days_until_due'] === 1;
        });

        if (!empty($tomorrowTasks)) {
            $recommendations[] = [
                'type' => 'deadline_tomorrow',
                'title' => '⏰ Deadline Besok',
                'message' => 'Ada ' . count($tomorrowTasks) . ' tugas yang deadline-nya besok.',
                'tasks' => $tomorrowTasks,
                'priority' => 'medium'
            ];
        }

        return $recommendations;
    }

    /**
     * Helper Methods
     */
    private function getCurrentSchedule($now)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $currentDay = $days[$now->dayOfWeek];
        $currentTime = $now->format('H:i:s');

        return Schedule::where('user_id', $this->user->id)
            ->where('day', $currentDay)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->first();
    }

    private function getNextSchedule($now)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $currentDay = $days[$now->dayOfWeek];
        $currentTime = $now->format('H:i:s');

        return Schedule::where('user_id', $this->user->id)
            ->where('day', $currentDay)
            ->where('start_time', '>', $currentTime)
            ->orderBy('start_time')
            ->first();
    }

    private function getQuickTasks($maxMinutes = 30)
    {
        return Task::where('user_id', $this->user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->where(function ($query) use ($maxMinutes) {
                $query->where('estimated_time', 'LIKE', '%15%')
                    ->orWhere('estimated_time', 'LIKE', '%30%')
                    ->orWhere('category', 'routine');
            })
            ->orderBy('due_date')
            ->take(3)
            ->get()
            ->toArray();
    }

    private function getTasksForTimeSlot($minutesAvailable, $type = 'general')
    {
        $query = Task::where('user_id', $this->user->id)
            ->whereNotIn('status', ['done', 'archived']);

        // Filter berdasarkan jenis tugas
        switch ($type) {
            case 'deep_work':
                $query->whereIn('category', ['Skripsi', 'Project', 'Freelance', 'Creative'])
                    ->orWhere('priority', 'urgent-important');
                break;

            case 'academic':
                $query->where('category', 'LIKE', '%akademik%')
                    ->orWhere('category', 'LIKE', '%kuliah%')
                    ->orWhere('category', 'LIKE', '%tugas%');
                break;

            case 'medium':
                $query->where('estimated_time', 'LIKE', '%1 hour%')
                    ->orWhere('estimated_time', 'LIKE', '%2 hours%');
                break;
        }

        return $query->orderBy('due_date')
            ->take(3)
            ->get()
            ->toArray();
    }

    private function getUrgencyLevel($daysUntilDue)
    {
        if ($daysUntilDue < 0) return 'critical';
        if ($daysUntilDue === 0) return 'high';
        if ($daysUntilDue <= 2) return 'medium';
        if ($daysUntilDue <= 7) return 'low';
        return 'very_low';
    }

    private function getLateNightTasks()
    {
        return Task::where('user_id', $this->user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->where(function ($query) {
                $query->where('estimated_time', 'LIKE', '%15%')
                    ->orWhere('estimated_time', 'LIKE', '%30%')
                    ->orWhere('category', 'routine')
                    ->orWhere('tags', 'LIKE', '%light%');
            })
            ->orderBy('due_date')
            ->take(2)
            ->get()
            ->toArray();
    }

    private function getEarlyMorningTasks()
    {
        return Task::where('user_id', $this->user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->where(function ($query) {
                $query->where('priority', 'urgent-important')
                    ->orWhere('category', 'planning')
                    ->orWhere('tags', 'LIKE', '%morning%');
            })
            ->orderBy('due_date')
            ->take(2)
            ->get()
            ->toArray();
    }

    private function getMorningTasks()
    {
        return Task::where('user_id', $this->user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->where(function ($query) {
                $query->where('priority', 'urgent-important')
                    ->orWhere('estimated_time', 'LIKE', '%2 hours%')
                    ->orWhere('estimated_time', 'LIKE', '%3 hours%');
            })
            ->orderBy('due_date')
            ->take(3)
            ->get()
            ->toArray();
    }

    private function getEveningTasks()
    {
        return Task::where('user_id', $this->user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->where(function ($query) {
                $query->where('tags', 'LIKE', '%fun%')
                    ->orWhere('tags', 'LIKE', '%hobby%')
                    ->orWhere('category', 'routine')
                    ->orWhere('category', 'personal');
            })
            ->orderBy('due_date')
            ->take(3)
            ->get()
            ->toArray();
    }

    private function getNightDeepWorkTasks()
    {
        return Task::where('user_id', $this->user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->where(function ($query) {
                $query->whereIn('category', ['Skripsi', 'Project', 'Freelance', 'Creative'])
                    ->orWhere('estimated_time', 'LIKE', '%2 hours%')
                    ->orWhere('estimated_time', 'LIKE', '%3 hours%');
            })
            ->orderBy('due_date')
            ->take(3)
            ->get()
            ->toArray();
    }

    private function getWindDownTasks()
    {
        return Task::where('user_id', $this->user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->where(function ($query) {
                $query->where('estimated_time', 'LIKE', '%15%')
                    ->orWhere('estimated_time', 'LIKE', '%30%')
                    ->orWhere('category', 'review')
                    ->orWhere('tags', 'LIKE', '%light%');
            })
            ->orderBy('due_date')
            ->take(2)
            ->get()
            ->toArray();
    }

    /**
     * Get "What to do NOW" - output utama sistem
     */
    public function getWhatToDoNow($now = null): array
    {
        if ($now === null) {
            $now = Carbon::now();
        }

        $currentSchedule = $this->getCurrentSchedule($now);
        $activities = $this->conflictResolver->getTodaysActivities($now);
        $gaps = $this->conflictResolver->getTimeGaps($now, $this->getFixedBlocks());
        $contentAlerts = $this->contentTracker->getWeeklyAlerts();
        $contentSummary = $this->contentTracker->getDashboardSummary();

        // Temukan current activity
        $currentActivity = null;
        $nextActivity = null;
        $gapInfo = null;

        if ($currentSchedule) {
            $currentActivity = [
                'type' => 'schedule',
                'title' => $currentSchedule->title ?? $currentSchedule->activity,
                'time' => $currentSchedule->start_time->format('H:i') . ' - ' . $currentSchedule->end_time->format('H:i'),
            ];
        } else {
            // Cek apakah ada event yang sedang berjalan
            foreach ($activities as $activity) {
                $activityStart = Carbon::parse($now->toDateString() . ' ' . ($activity['start_time'] ?? '00:00'));
                $activityEnd = Carbon::parse($now->toDateString() . ' ' . ($activity['end_time'] ?? '23:59'));

                if ($now->between($activityStart, $activityEnd)) {
                    $currentActivity = [
                        'type' => $activity['type'],
                        'title' => $activity['title'],
                        'time' => $activity['start_time'] . ' - ' . $activity['end_time'],
                    ];
                    break;
                }
            }
        }

        // Temukan aktivitas berikutnya
        foreach ($activities as $activity) {
            $activityStart = Carbon::parse($now->toDateString() . ' ' . ($activity['start_time'] ?? '00:00'));
            if ($activityStart > $now) {
                $nextActivity = [
                    'type' => $activity['type'],
                    'title' => $activity['title'],
                    'time' => $activity['start_time'] . ' - ' . $activity['end_time'],
                    'minutes_until' => $now->diffInMinutes($activityStart),
                ];
                break;
            }
        }

        // Temukan gap berikutnya
        foreach ($gaps as $gap) {
            $gapStart = Carbon::parse($now->toDateString() . ' ' . $gap['start']);
            $gapEnd = Carbon::parse($now->toDateString() . ' ' . $gap['end']);

            if ($now->between($gapStart, $gapEnd) || $gapStart > $now) {
                $gapInfo = $gap;
                break;
            }
        }

        // Rekomendasi task untuk gap saat ini
        $recommendedTasks = [];
        if ($gapInfo) {
            $recommendedTasks = $this->getTasksForGap($gapInfo['duration_minutes']);
        }

        return [
            'current_time' => $now->format('H:i'),
            'current_day' => $this->getDayName($now->dayOfWeek),
            'current_activity' => $currentActivity,
            'next_activity' => $nextActivity,
            'current_gap' => $gapInfo,
            'recommended_tasks' => $recommendedTasks,
            'content_alerts' => $contentAlerts,
            'content_summary' => $contentSummary['summary_message'] ?? null,
            'has_conflict' => !empty($this->conflictResolver->detectConflicts($now)),
            'message' => $this->generateWhatToDoMessage($currentActivity, $nextActivity, $gapInfo, $recommendedTasks),
        ];
    }

    /**
     * Generate pesan "What to do now"
     */
    private function generateWhatToDoMessage($currentActivity, $nextActivity, $gapInfo, $recommendedTasks): string
    {
        if ($currentActivity) {
            return "Sedang berlangsung: {$currentActivity['title']}";
        }

        if ($gapInfo && !empty($recommendedTasks)) {
            $task = $recommendedTasks[0];
            return "Waktu kosong {$gapInfo['duration_minutes']} menit. Rekomendasi: {$task['title']}";
        }

        if ($nextActivity) {
            return "Selesaikan ini lalu: {$nextActivity['title']} ({$nextActivity['minutes_until']} menit lagi)";
        }

        return "Tidak ada jadwal khusus. Fokus pada tugas prioritas!";
    }

    /**
     * Get tasks untuk gap waktu tertentu
     */
    private function getTasksForGap(int $durationMinutes): array
    {
        // Prioritas 1: Deadline hari ini atau besok (URGENT)
        $urgentTasks = Task::where('user_id', $this->user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->where(function ($q) {
                $q->whereDate('due_date', today())
                    ->orWhereDate('due_date', today()->addDay());
            })
            ->orderBy('due_date')
            ->take(2)
            ->get(['id', 'title', 'due_date', 'priority', 'estimated_time'])
            ->toArray();

        if (!empty($urgentTasks)) {
            return array_map(fn($t) => array_merge($t, ['priority_level' => 1, 'reason' => 'Deadline mendesak']), $urgentTasks);
        }

        // Prioritas 2: PKL atau tugas kuliah yang hampir deadline
        $academicTasks = Task::where('user_id', $this->user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->where(function ($q) {
                $q->where('category', 'like', '%pkl%')
                    ->orWhere('category', 'like', '%academic%')
                    ->orWhere('category', 'like', '%kuliah%')
                    ->orWhere('linked_subject_id', '!=', null);
            })
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', now()->addDays(3))
            ->orderBy('due_date')
            ->take(2)
            ->get(['id', 'title', 'due_date', 'priority', 'estimated_time', 'category'])
            ->toArray();

        if (!empty($academicTasks)) {
            return array_map(fn($t) => array_merge($t, ['priority_level' => 2, 'reason' => 'PKL/Kuliah mendekati deadline']), $academicTasks);
        }

        // Prioritas 3: Content creator schedule
        $contentAlerts = $this->contentTracker->getWeeklyAlerts();
        if (!empty($contentAlerts)) {
            return [
                [
                    'type' => 'content_alert',
                    'title' => 'Deadline Konten',
                    'message' => $contentAlerts[0]['message'],
                    'priority_level' => 3,
                    'reason' => 'Target konten belum tercapai',
                ]
            ];
        }

        // Prioritas 4: Project dengan priority tinggi
        $highPriorityTasks = Task::where('user_id', $this->user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->where('priority', 'urgent-important')
            ->orderBy('due_date')
            ->take(2)
            ->get(['id', 'title', 'due_date', 'priority', 'estimated_time'])
            ->toArray();

        if (!empty($highPriorityTasks)) {
            return array_map(fn($t) => array_merge($t, ['priority_level' => 4, 'reason' => 'Prioritas tinggi']), $highPriorityTasks);
        }

        // Prioritas 5: Task personal
        $personalTasks = Task::where('user_id', $this->user->id)
            ->whereNotIn('status', ['done', 'archived'])
            ->where(function ($q) {
                $q->where('category', 'personal')
                    ->orWhere('priority', 'important-not-urgent');
            })
            ->orderBy('due_date')
            ->take(2)
            ->get(['id', 'title', 'due_date', 'priority', 'estimated_time'])
            ->toArray();

        return array_map(fn($t) => array_merge($t, ['priority_level' => 5, 'reason' => 'Task personal']), $personalTasks);
    }

    /**
     * Get blok waktu tetap (tidur & sholat)
     */
    private function getFixedBlocks(): array
    {
        return [
            // Waktu tidur
            ['title' => 'Tidur', 'start_time' => '22:00', 'end_time' => '06:00', 'type' => 'sleep', 'color' => 'bg-indigo-900 text-white'],
            // Sholat 5 waktu (perkiraan)
            ['title' => 'Sholat Subuh', 'start_time' => '04:30', 'end_time' => '05:00', 'type' => 'prayer', 'color' => 'bg-teal-100 text-teal-800'],
            ['title' => 'Sholat Dzuhur', 'start_time' => '11:30', 'end_time' => '12:00', 'type' => 'prayer', 'color' => 'bg-teal-100 text-teal-800'],
            ['title' => 'Sholat Asar', 'start_time' => '15:00', 'end_time' => '15:30', 'type' => 'prayer', 'color' => 'bg-teal-100 text-teal-800'],
            ['title' => 'Sholat Maghrib', 'start_time' => '17:45', 'end_time' => '18:15', 'type' => 'prayer', 'color' => 'bg-teal-100 text-teal-800'],
            ['title' => 'Sholat Isya', 'start_time' => '19:00', 'end_time' => '19:30', 'type' => 'prayer', 'color' => 'bg-teal-100 text-teal-800'],
        ];
    }

    /**
     * Get day name
     */
    private function getDayName(int $dayOfWeek): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $days[$dayOfWeek];
    }

    /**
     * Get conflict resolver instance
     */
    public function getConflictResolver(): ConflictResolver
    {
        return $this->conflictResolver;
    }

    /**
     * Get content tracker instance
     */
    public function getContentTracker(): ContentTrackerService
    {
        return $this->contentTracker;
    }
}
