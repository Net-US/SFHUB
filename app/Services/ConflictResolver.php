<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Event;
use App\Models\ScheduleOverride;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ConflictResolver
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Deteksi semua konflik jadwal pada tanggal tertentu
     */
    public function detectConflicts(?Carbon $date = null): array
    {
        $checkDate = $date ?? now();
        $dayOfWeek = $this->getDayName($checkDate->dayOfWeek);

        // Ambil jadwal rutin hari ini
        $schedules = Schedule::where('user_id', $this->user->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->where('is_recurring', true)
            ->get();

        // Ambil event pada tanggal ini
        $events = Event::where('user_id', $this->user->id)
            ->whereDate('date', $checkDate->toDateString())
            ->get();

        // Ambil override yang sudah ada
        $overrides = ScheduleOverride::where('user_id', $this->user->id)
            ->whereDate('date', $checkDate->toDateString())
            ->with('event')
            ->get();

        $conflicts = [];

        foreach ($events as $event) {
            foreach ($schedules as $schedule) {
                if ($this->hasTimeConflict($event, $schedule) && !$this->isOverridden($schedule, $checkDate, $overrides)) {
                    $conflicts[] = [
                        'event' => $event,
                        'schedule' => $schedule,
                        'date' => $checkDate->toDateString(),
                        'type' => 'event_schedule_conflict',
                        'message' => "Event '{$event->title}' ({$event->getTimeRangeAttribute()}) bentrok dengan jadwal '{$schedule->activity}' ({$schedule->start_time->format('H:i')} - {$schedule->end_time->format('H:i')})"
                    ];
                }
            }
        }

        // Cek konflik antar event
        foreach ($events as $i => $event1) {
            foreach ($events as $j => $event2) {
                if ($i >= $j) continue;

                if ($this->eventsConflict($event1, $event2)) {
                    $conflicts[] = [
                        'event1' => $event1,
                        'event2' => $event2,
                        'date' => $checkDate->toDateString(),
                        'type' => 'event_event_conflict',
                        'message' => "Event '{$event1->title}' bentrok dengan event '{$event2->title}'"
                    ];
                }
            }
        }

        return $conflicts;
    }

    /**
     * Cek apakah ada konflik waktu antara event dan schedule
     */
    private function hasTimeConflict(Event $event, Schedule $schedule): bool
    {
        if (!$event->start_time || !$event->end_time) {
            return false;
        }

        $eventStart = Carbon::parse($event->start_time);
        $eventEnd = Carbon::parse($event->end_time);
        $scheduleStart = Carbon::parse($schedule->start_time);
        $scheduleEnd = Carbon::parse($schedule->end_time);

        return $eventStart < $scheduleEnd && $eventEnd > $scheduleStart;
    }

    /**
     * Cek apakah dua event bentrok
     */
    private function eventsConflict(Event $event1, Event $event2): bool
    {
        if (!$event1->start_time || !$event1->end_time || !$event2->start_time || !$event2->end_time) {
            return false;
        }

        $start1 = Carbon::parse($event1->start_time);
        $end1 = Carbon::parse($event1->end_time);
        $start2 = Carbon::parse($event2->start_time);
        $end2 = Carbon::parse($event2->end_time);

        return $start1 < $end2 && $end1 > $start2;
    }

    /**
     * Cek apakah schedule sudah di-override untuk tanggal ini
     */
    private function isOverridden(Schedule $schedule, Carbon $date, Collection $overrides): bool
    {
        return $overrides->contains(function ($override) use ($schedule, $date) {
            return $override->schedule_id == $schedule->id &&
                $override->date->isSameDay($date) &&
                !$override->is_cancelled;
        });
    }

    /**
     * Resolve konflik dengan membuat override
     */
    public function resolveConflict(int $scheduleId, int $eventId, string $date, ?string $reason = null): ScheduleOverride
    {
        $override = ScheduleOverride::create([
            'user_id' => $this->user->id,
            'schedule_id' => $scheduleId,
            'date' => $date,
            'reason' => $reason ?? 'Diganti karena ada event',
            'replaced_by_event_id' => $eventId,
            'is_cancelled' => false,
        ]);

        return $override;
    }

    /**
     * Batalkan override (kembalikan jadwal normal)
     */
    public function cancelOverride(int $overrideId): bool
    {
        $override = ScheduleOverride::where('user_id', $this->user->id)
            ->findOrFail($overrideId);

        $override->update(['is_cancelled' => true]);

        return true;
    }

    /**
     * Dapatkan jadwal aktif untuk hari ini (setelah resolusi konflik)
     */
    public function getActiveSchedules(?Carbon $date = null): Collection
    {
        $checkDate = $date ?? now();
        $dayOfWeek = $this->getDayName($checkDate->dayOfWeek);

        // Ambil semua schedule rutin hari ini
        $schedules = Schedule::where('user_id', $this->user->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->where('is_recurring', true)
            ->get();

        // Ambil override untuk hari ini
        $overrides = ScheduleOverride::where('user_id', $this->user->id)
            ->whereDate('date', $checkDate->toDateString())
            ->where('is_cancelled', false)
            ->pluck('schedule_id')
            ->toArray();

        // Filter schedule yang tidak di-override
        return $schedules->reject(function ($schedule) use ($overrides) {
            return in_array($schedule->id, $overrides);
        })->values();
    }

    /**
     * Dapatkan semua aktivitas untuk hari ini (schedule + event yang tersisa)
     */
    public function getTodaysActivities(?Carbon $date = null): array
    {
        $checkDate = $date ?? now();

        $schedules = $this->getActiveSchedules($checkDate);
        $events = Event::where('user_id', $this->user->id)
            ->whereDate('date', $checkDate->toDateString())
            ->get();

        $activities = [];

        foreach ($schedules as $schedule) {
            $activities[] = [
                'type' => 'schedule',
                'id' => $schedule->id,
                'title' => $schedule->title ?? $schedule->activity,
                'start_time' => $schedule->start_time->format('H:i'),
                'end_time' => $schedule->end_time->format('H:i'),
                'category' => $schedule->type,
                'color' => $schedule->color ?? $this->getDefaultColor($schedule->type),
            ];
        }

        foreach ($events as $event) {
            $activities[] = [
                'type' => 'event',
                'id' => $event->id,
                'title' => $event->title,
                'start_time' => $event->start_time ? $event->start_time->format('H:i') : null,
                'end_time' => $event->end_time ? $event->end_time->format('H:i') : null,
                'category' => $event->type,
                'color' => $event->getTypeColor(),
            ];
        }

        // Sort by start time
        usort($activities, function ($a, $b) {
            if (!$a['start_time']) return 1;
            if (!$b['start_time']) return -1;
            return strcmp($a['start_time'], $b['start_time']);
        });

        return $activities;
    }

    /**
     * Hitung gap waktu yang tersedia untuk task
     */
    public function getTimeGaps(?Carbon $date = null, array $fixedBlocks = []): array
    {
        $checkDate = $date ?? now();
        $activities = $this->getTodaysActivities($checkDate);

        // Waktu mulai dan akhir hari (default 06:00 - 23:00)
        $dayStart = Carbon::parse($checkDate->toDateString() . ' 06:00');
        $dayEnd = Carbon::parse($checkDate->toDateString() . ' 23:00');

        // Tambahkan blok tetap (tidur & sholat)
        $allBlocks = array_merge($activities, $fixedBlocks);

        // Sort by start time
        usort($allBlocks, function ($a, $b) {
            return strcmp($a['start_time'] ?? '00:00', $b['start_time'] ?? '00:00');
        });

        $gaps = [];
        $currentTime = $dayStart;

        foreach ($allBlocks as $block) {
            if (!$block['start_time'] || !$block['end_time']) continue;

            $blockStart = Carbon::parse($checkDate->toDateString() . ' ' . $block['start_time']);
            $blockEnd = Carbon::parse($checkDate->toDateString() . ' ' . $block['end_time']);

            if ($currentTime < $blockStart) {
                $gapMinutes = $currentTime->diffInMinutes($blockStart);

                if ($gapMinutes >= 30) { // Minimum 30 menit
                    $gaps[] = [
                        'start' => $currentTime->format('H:i'),
                        'end' => $blockStart->format('H:i'),
                        'duration_minutes' => $gapMinutes,
                        'duration_hours' => round($gapMinutes / 60, 1),
                    ];
                }
            }

            $currentTime = max($currentTime, $blockEnd);
        }

        // Cek gap setelah blok terakhir
        if ($currentTime < $dayEnd) {
            $gapMinutes = $currentTime->diffInMinutes($dayEnd);

            if ($gapMinutes >= 30) {
                $gaps[] = [
                    'start' => $currentTime->format('H:i'),
                    'end' => $dayEnd->format('H:i'),
                    'duration_minutes' => $gapMinutes,
                    'duration_hours' => round($gapMinutes / 60, 1),
                ];
            }
        }

        return $gaps;
    }

    /**
     * Get default color untuk schedule type
     */
    private function getDefaultColor(string $type): string
    {
        return match ($type) {
            'academic' => 'bg-blue-100 text-blue-800',
            'pkl' => 'bg-emerald-100 text-emerald-800',
            'creative' => 'bg-orange-100 text-orange-800',
            'personal' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get Indonesian day name
     */
    private function getDayName(int $dayOfWeek): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $days[$dayOfWeek];
    }
}
