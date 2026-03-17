<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Task;
use App\Models\PklLog;
use App\Models\ProductivityLog;
use App\Services\ContentTrackerService;
use Carbon\Carbon;

class ProductivityDashboard extends Component
{
    public array $stats = [];
    public array $contentProgress = [];
    public array $weeklyChart = [];
    public int $pklStreak = 0;

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $user = auth()->user();

        // Task stats
        $this->stats = [
            'today' => [
                'completed' => Task::where('user_id', $user->id)->completed()->today()->count(),
                'total' => Task::where('user_id', $user->id)->today()->count(),
            ],
            'week' => [
                'completed' => Task::where('user_id', $user->id)->completed()->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'total' => Task::where('user_id', $user->id)->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            ],
            'month' => [
                'completed' => Task::where('user_id', $user->id)->completed()->whereMonth('completed_at', now()->month)->count(),
                'total' => Task::where('user_id', $user->id)->whereMonth('due_date', now()->month)->count(),
            ],
        ];

        // PKL streak
        $this->pklStreak = $this->calculatePklStreak($user->id);

        // Content progress
        $contentTracker = new ContentTrackerService($user);
        $contentSummary = $contentTracker->getDashboardSummary();
        $this->contentProgress = $contentSummary['weekly']['by_platform'] ?? [];

        // Weekly chart data
        $this->weeklyChart = $this->getWeeklyProductivityData($user->id);
    }

    private function calculatePklStreak($userId): int
    {
        $logs = PklLog::where('user_id', $userId)
            ->where('is_approved', true)
            ->orderBy('log_date', 'desc')
            ->pluck('log_date')
            ->toArray();

        if (empty($logs)) {
            return 0;
        }

        $streak = 0;
        $currentDate = now();

        foreach ($logs as $logDate) {
            if ($currentDate->isSameDay($logDate)) {
                $streak++;
                $currentDate = $currentDate->subDay();
            } elseif ($currentDate->diffInDays($logDate) == 1) {
                $streak++;
                $currentDate = Carbon::parse($logDate);
            } else {
                break;
            }
        }

        return $streak;
    }

    private function getWeeklyProductivityData($userId): array
    {
        $days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $completed = Task::where('user_id', $userId)
                ->completed()
                ->whereDate('completed_at', $date)
                ->count();

            $data[] = [
                'day' => $days[$date->dayOfWeek == 0 ? 6 : $date->dayOfWeek - 1],
                'completed' => $completed,
            ];
        }

        return $data;
    }

    public function render()
    {
        return view('livewire.dashboard.productivity-dashboard');
    }
}
