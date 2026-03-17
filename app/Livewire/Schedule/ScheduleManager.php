<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Schedule;
use App\Services\ConflictResolver;

class ScheduleManager extends Component
{
    use WithPagination;

    public $schedules = [];
    public $showModal = false;
    public $editingSchedule = null;

    // Form fields
    public $day_of_week = '';
    public $start_time = '';
    public $end_time = '';
    public $title = '';
    public $activity = '';
    public $type = 'academic';
    public $location = '';
    public $instructor = '';
    public $is_recurring = true;
    public $color = '';
    public $priority = 'medium';

    protected $rules = [
        'day_of_week' => 'required|string',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'title' => 'required|string|max:255',
        'activity' => 'required|string|max:255',
        'type' => 'required|in:academic,pkl,creative,personal,routine',
        'location' => 'nullable|string|max:255',
        'instructor' => 'nullable|string|max:255',
        'is_recurring' => 'boolean',
        'color' => 'nullable|string|max:50',
        'priority' => 'required|in:low,medium,high',
    ];

    public function mount()
    {
        $this->loadSchedules();
    }

    public function loadSchedules()
    {
        $this->schedules = Schedule::where('user_id', auth()->id())
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->toArray();
    }

    public function checkConflict()
    {
        $resolver = new ConflictResolver(auth()->user());
        $conflicts = $resolver->detectConflicts();

        if (!empty($conflicts)) {
            $this->dispatch('show-conflicts', conflicts: $conflicts);
        }
    }

    public function save()
    {
        $this->validate();

        // Check for conflicts before saving
        $existing = Schedule::where('user_id', auth()->id())
            ->where('day_of_week', $this->day_of_week)
            ->where(function ($q) {
                $q->whereBetween('start_time', [$this->start_time, $this->end_time])
                    ->orWhereBetween('end_time', [$this->start_time, $this->end_time]);
            })
            ->when($this->editingSchedule, fn($q) => $q->where('id', '!=', $this->editingSchedule))
            ->first();

        if ($existing) {
            $this->dispatch('conflict-detected', existing: $existing);
            return;
        }

        $data = [
            'user_id' => auth()->id(),
            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'title' => $this->title,
            'activity' => $this->activity,
            'type' => $this->type,
            'location' => $this->location,
            'instructor' => $this->instructor,
            'is_recurring' => $this->is_recurring,
            'color' => $this->color,
            'priority' => $this->priority,
            'is_active' => true,
        ];

        if ($this->editingSchedule) {
            Schedule::find($this->editingSchedule)->update($data);
        } else {
            Schedule::create($data);
        }

        $this->resetForm();
        $this->loadSchedules();
        $this->showModal = false;
        $this->dispatch('schedule-saved');
    }

    public function edit($id)
    {
        $schedule = Schedule::where('user_id', auth()->id())->findOrFail($id);

        $this->editingSchedule = $id;
        $this->day_of_week = $schedule->day_of_week;
        $this->start_time = $schedule->start_time->format('H:i');
        $this->end_time = $schedule->end_time->format('H:i');
        $this->title = $schedule->title;
        $this->activity = $schedule->activity;
        $this->type = $schedule->type;
        $this->location = $schedule->location;
        $this->instructor = $schedule->instructor;
        $this->is_recurring = $schedule->is_recurring;
        $this->color = $schedule->color;
        $this->priority = $schedule->priority;

        $this->showModal = true;
    }

    public function delete($id)
    {
        Schedule::where('user_id', auth()->id())->findOrFail($id)->delete();
        $this->loadSchedules();
        $this->dispatch('schedule-deleted');
    }

    public function resetForm()
    {
        $this->reset(['day_of_week', 'start_time', 'end_time', 'title', 'activity', 'type', 'location', 'instructor', 'is_recurring', 'color', 'priority', 'editingSchedule']);
        $this->is_recurring = true;
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function render()
    {
        return view('livewire.schedule.schedule-manager');
    }
}
