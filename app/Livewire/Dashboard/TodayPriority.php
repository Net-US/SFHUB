<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\PriorityEngine;
use App\Models\Task;
use Carbon\Carbon;

class TodayPriority extends Component
{
    public array $whatToDoNow = [];
    public string $currentTime = '';
    public bool $hasConflict = false;
    public array $contentAlerts = [];

    protected PriorityEngine $priorityEngine;

    public function boot(PriorityEngine $priorityEngine)
    {
        $this->priorityEngine = $priorityEngine;
    }

    public function mount()
    {
        $this->refreshData();
    }

    #[On('refresh-priority')]
    public function refreshData()
    {
        $engine = new PriorityEngine(auth()->user());
        $this->whatToDoNow = $engine->getWhatToDoNow();
        $this->currentTime = now()->format('H:i');
        $this->hasConflict = $this->whatToDoNow['has_conflict'] ?? false;
        $this->contentAlerts = $this->whatToDoNow['content_alerts'] ?? [];
    }

    public function markTaskComplete($taskId)
    {
        $task = Task::where('user_id', auth()->id())->find($taskId);

        if ($task) {
            $task->markAsComplete();
            $this->dispatch('task-completed', taskId: $taskId);
            $this->refreshData();
        }
    }

    public function resolveConflict($scheduleId, $eventId)
    {
        $engine = new PriorityEngine(auth()->user());
        $engine->getConflictResolver()->resolveConflict($scheduleId, $eventId, today()->toDateString());
        $this->refreshData();
    }

    public function getListeners()
    {
        return [
            'echo:priority-updates' => 'refreshData',
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.today-priority');
    }
}
