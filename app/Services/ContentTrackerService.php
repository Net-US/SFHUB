<?php

namespace App\Services;

use App\Models\ContentSchedule;
use Carbon\Carbon;

class ContentTrackerService
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Dapatkan semua target konten untuk periode tertentu
     */
    public function getTargets(string $period = 'weekly'): array
    {
        $schedules = ContentSchedule::where('user_id', $this->user->id)
            ->where('frequency', $period)
            ->active()
            ->get();

        return [
            'total_targets' => $schedules->sum('target_per_period'),
            'completed' => $schedules->sum('completed_count'),
            'remaining' => $schedules->sum(fn($s) => $s->getRemainingCount()),
            'progress_percentage' => $schedules->avg(fn($s) => $s->getProgressPercentage()),
            'by_platform' => $this->groupByPlatform($schedules),
            'overdue' => $schedules->filter(fn($s) => $s->isOverdue())->values(),
            'not_on_track' => $schedules->filter(fn($s) => !$s->isComplete() && !$s->isOverdue())->values(),
        ];
    }

    /**
     * Kelompokkan target berdasarkan platform
     */
    private function groupByPlatform($schedules): array
    {
        $grouped = $schedules->groupBy('platform');
        $result = [];

        foreach ($grouped as $platform => $items) {
            $result[$platform] = [
                'platform' => $platform,
                'target' => $items->sum('target_per_period'),
                'completed' => $items->sum('completed_count'),
                'remaining' => $items->sum(fn($s) => $s->getRemainingCount()),
                'progress_percentage' => $items->avg(fn($s) => $s->getProgressPercentage()),
                'items' => $items,
                'icon' => $this->getPlatformIcon($platform),
                'color' => $this->getPlatformColor($platform),
            ];
        }

        return $result;
    }

    /**
     * Dapatkan alert untuk content deadline minggu ini
     */
    public function getWeeklyAlerts(): array
    {
        $dueThisWeek = ContentSchedule::where('user_id', $this->user->id)
            ->dueThisWeek()
            ->where('status', 'active')
            ->get();

        $alerts = [];

        foreach ($dueThisWeek as $schedule) {
            $remaining = $schedule->getRemainingCount();
            $daysRemaining = now()->diffInDays($schedule->due_date, false);

            if ($remaining > 0) {
                if ($daysRemaining <= 0) {
                    $alerts[] = [
                        'type' => 'urgent',
                        'message' => "Deadline hari ini: {$schedule->platform} masih kurang {$remaining} konten",
                        'schedule' => $schedule,
                        'days_remaining' => $daysRemaining,
                    ];
                } elseif ($daysRemaining <= 2) {
                    $alerts[] = [
                        'type' => 'warning',
                        'message' => "Deadline {$daysRemaining} hari lagi: {$schedule->platform} masih kurang {$remaining} konten",
                        'schedule' => $schedule,
                        'days_remaining' => $daysRemaining,
                    ];
                }
            }
        }

        return $alerts;
    }

    /**
     * Dapatkan summary untuk dashboard
     */
    public function getDashboardSummary(): array
    {
        $weekly = $this->getTargets('weekly');
        $monthly = $this->getTargets('monthly');
        $alerts = $this->getWeeklyAlerts();

        $hasOverdue = !empty($weekly['overdue']) || !empty($monthly['overdue']);

        return [
            'weekly' => $weekly,
            'monthly' => $monthly,
            'alerts' => $alerts,
            'has_critical_alert' => !empty($alerts) || $hasOverdue,
            'total_platforms_active' => count($weekly['by_platform']),
            'summary_message' => $this->generateSummaryMessage($weekly, $alerts),
        ];
    }

    /**
     * Generate pesan summary
     */
    private function generateSummaryMessage(array $weekly, array $alerts): string
    {
        if (!empty($alerts)) {
            $urgent = collect($alerts)->where('type', 'urgent')->count();
            if ($urgent > 0) {
                return "⚠️ Ada {$urgent} deadline konten yang perlu segera diselesaikan hari ini!";
            }
            return "📢 Ada " . count($alerts) . " konten yang mendekati deadline.";
        }

        $progress = $weekly['progress_percentage'] ?? 0;

        if ($progress >= 100) {
            return "🎉 Target konten minggu ini sudah tercapai!";
        } elseif ($progress >= 75) {
            return "👍 Target konten hampir tercapai ({$progress}%). Tinggal sedikit lagi!";
        } elseif ($progress >= 50) {
            return "📊 Progress konten {$progress}%. Ayo lanjutkan!";
        } elseif ($progress > 0) {
            return "📝 Progress konten {$progress}%. Masih banyak yang perlu dikerjakan.";
        }

        return "🎯 Belum ada konten yang diselesaikan minggu ini. Yuk mulai!";
    }

    /**
     * Update progress konten
     */
    public function incrementProgress(int $scheduleId, int $count = 1): ContentSchedule
    {
        $schedule = ContentSchedule::where('user_id', $this->user->id)
            ->findOrFail($scheduleId);

        $schedule->incrementCompleted($count);

        return $schedule;
    }

    /**
     * Buat target konten baru
     */
    public function createTarget(array $data): ContentSchedule
    {
        return ContentSchedule::create([
            'user_id' => $this->user->id,
            'platform' => $data['platform'],
            'content_type' => $data['content_type'] ?? null,
            'frequency' => $data['frequency'] ?? 'weekly',
            'target_per_period' => $data['target_per_period'] ?? 1,
            'completed_count' => 0,
            'due_date' => $data['due_date'] ?? now()->endOfWeek(),
            'status' => 'active',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Reset counter untuk periode baru
     */
    public function resetCounters(string $frequency): void
    {
        ContentSchedule::where('user_id', $this->user->id)
            ->where('frequency', $frequency)
            ->where('status', 'completed')
            ->update([
                'completed_count' => 0,
                'status' => 'active',
            ]);
    }

    /**
     * Get platform icon
     */
    private function getPlatformIcon(string $platform): string
    {
        return match ($platform) {
            'instagram' => 'fa-instagram',
            'youtube' => 'fa-youtube',
            'tiktok' => 'fa-tiktok',
            'twitter' => 'fa-twitter',
            'linkedin' => 'fa-linkedin',
            'shutterstock' => 'fa-camera',
            'behance' => 'fa-behance',
            'dribbble' => 'fa-dribbble',
            default => 'fa-share-nodes',
        };
    }

    /**
     * Get platform color
     */
    private function getPlatformColor(string $platform): string
    {
        return match ($platform) {
            'instagram' => 'text-pink-600',
            'youtube' => 'text-red-600',
            'tiktok' => 'text-gray-900',
            'twitter' => 'text-blue-400',
            'linkedin' => 'text-blue-700',
            'shutterstock' => 'text-amber-600',
            'behance' => 'text-blue-600',
            'dribbble' => 'text-pink-500',
            default => 'text-gray-600',
        };
    }

    /**
     * Dapatkan rekomendasi platform yang perlu difokuskan
     */
    public function getPriorityPlatforms(): array
    {
        $schedules = ContentSchedule::where('user_id', $this->user->id)
            ->active()
            ->orderBy('due_date')
            ->get();

        return $schedules
            ->filter(fn($s) => !$s->isComplete())
            ->sortByDesc(fn($s) => $s->getRemainingCount() / max(1, $s->getDaysRemaining()))
            ->take(3)
            ->map(fn($s) => [
                'schedule' => $s,
                'priority_score' => $s->getRemainingCount() / max(1, $s->getDaysRemaining()),
            ])
            ->values()
            ->toArray();
    }
}
