<?php

namespace App\Livewire\Tasks;

use Livewire\Component;
use App\Models\Task;
use App\Models\Subject;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Carbon\Carbon;

class TaskManager extends Component
{
    use WithPagination;

    #[Validate('required|string|max:255')]
    public $title = '';

    #[Validate('nullable|string|max:1000')]
    public $description = '';

    #[Validate('required|in:Tugas Kuliah,Project,Konten,PKL,Personal')]
    public $category = 'Personal';

    #[Validate('required|in:High,Medium,Low')]
    public $priority = 'Medium';

    #[Validate('required|in:Todo,In Progress,Done')]
    public $status = 'Todo';

    #[Validate('nullable|date')]
    public $deadline = '';

    #[Validate('nullable|string|max:1000')]
    public $notes = '';

    #[Validate('nullable|exists:subjects,id')]
    public $subject_id = '';

    public $editingId = null;
    public $showModal = false;
    public $search = '';
    public $filterStatus = '';
    public $filterPriority = '';
    public $filterCategory = '';

    public function mount()
    {
        $this->deadline = Carbon::today()->addDays(7)->format('Y-m-d');
    }

    public function render()
    {
        $query = Task::where('user_id', auth()->id());

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        $tasks = $query->orderByRaw($this->getTaskOrder())->paginate(10);
        $subjects = Subject::where('user_id', auth()->id())->where('is_active', true)->get();

        return view('livewire.tasks.task-manager', compact('tasks', 'subjects'));
    }

    public function create()
    {
        $this->reset(['editingId', 'title', 'description', 'category', 'priority', 'status', 'deadline', 'notes', 'subject_id']);
        $this->deadline = Carbon::today()->addDays(7)->format('Y-m-d');
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        Task::create([
            'user_id' => auth()->id(),
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'priority' => $this->priority,
            'status' => $this->status,
            'deadline' => $this->deadline ?: null,
            'notes' => $this->notes,
            'subject_id' => $this->subject_id ?: null,
        ]);

        $this->showModal = false;
        $this->dispatch('flash-message', type: 'success', message: 'Tugas berhasil ditambahkan!');
        $this->reset(['editingId', 'title', 'description', 'category', 'priority', 'status', 'deadline', 'notes', 'subject_id']);
        $this->deadline = Carbon::today()->addDays(7)->format('Y-m-d');
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        $this->editingId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->category = $task->category;
        $this->priority = $task->priority;
        $this->status = $task->status;
        $this->deadline = $task->deadline?->format('Y-m-d');
        $this->notes = $task->notes;
        $this->subject_id = $task->subject_id;
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate();

        $task = Task::findOrFail($this->editingId);
        $this->authorize('update', $task);

        $task->update([
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'priority' => $this->priority,
            'status' => $this->status,
            'deadline' => $this->deadline ?: null,
            'notes' => $this->notes,
            'subject_id' => $this->subject_id ?: null,
        ]);

        $this->showModal = false;
        $this->dispatch('flash-message', type: 'success', message: 'Tugas berhasil diperbarui!');
        $this->reset(['editingId', 'title', 'description', 'category', 'priority', 'status', 'deadline', 'notes', 'subject_id']);
        $this->deadline = Carbon::today()->addDays(7)->format('Y-m-d');
    }

    public function deleteConfirm($id)
    {
        $this->editingId = $id;
        $this->dispatch(
            'confirm-delete',
            title: 'Hapus Tugas?',
            message: 'Apakah Anda yakin ingin menghapus tugas ini?',
            id: $id
        );
    }

    public function delete()
    {
        $task = Task::findOrFail($this->editingId);
        $this->authorize('delete', $task);

        $task->delete();

        $this->dispatch('flash-message', type: 'success', message: 'Tugas berhasil dihapus!');
        $this->reset();
    }

    public function toggleStatus(Task $task)
    {
        $this->authorize('update', $task);

        $newStatus = match ($task->status) {
            'Todo' => 'In Progress',
            'In Progress' => 'Done',
            'Done' => 'Todo',
            default => 'Todo'
        };

        $task->update(['status' => $newStatus]);

        $this->dispatch('flash-message', type: 'success', message: 'Status tugas diperbarui!');
    }

    public function cancel()
    {
        $this->showModal = false;
        $this->reset(['editingId', 'title', 'description', 'category', 'priority', 'status', 'deadline', 'notes', 'subject_id']);
        $this->deadline = Carbon::today()->addDays(7)->format('Y-m-d');
    }

    private function getTaskOrder()
    {
        return "
            CASE
                WHEN status = 'Done' THEN 3
                WHEN status = 'In Progress' THEN 2
                WHEN status = 'Todo' THEN 1
                ELSE 4
            END,
            CASE
                WHEN priority = 'High' THEN 1
                WHEN priority = 'Medium' THEN 2
                WHEN priority = 'Low' THEN 3
                ELSE 4
            END,
            deadline ASC NULLS LAST,
            created_at DESC
        ";
    }

    public function getPriorityColor($priority)
    {
        return match ($priority) {
            'High' => 'red',
            'Medium' => 'yellow',
            'Low' => 'green',
            default => 'gray',
        };
    }

    public function getStatusColor($status)
    {
        return match ($status) {
            'Todo' => 'gray',
            'In Progress' => 'blue',
            'Done' => 'green',
            default => 'gray',
        };
    }

    public function getCategoryIcon($category)
    {
        return match ($category) {
            'Tugas Kuliah' => '📚',
            'Project' => '💼',
            'Konten' => '📱',
            'PKL' => '🏢',
            'Personal' => '👤',
            default => '📝',
        };
    }

    public function getDaysUntilDeadline($deadline)
    {
        if (!$deadline) return null;

        $days = Carbon::now()->diffInDays(Carbon::parse($deadline), false);

        if ($days < 0) return ['text' => 'Terlambat ' . abs($days) . ' hari', 'color' => 'red'];
        if ($days === 0) return ['text' => 'Hari ini', 'color' => 'yellow'];
        if ($days === 1) return ['text' => 'Besok', 'color' => 'yellow'];
        if ($days <= 3) return ['text' => $days . ' hari lagi', 'color' => 'orange'];
        return ['text' => $days . ' hari lagi', 'color' => 'green'];
    }

    public function getCategoryColor($category)
    {
        return match ($category) {
            'Tugas Kuliah' => 'blue',
            'Project' => 'purple',
            'Konten' => 'green',
            'PKL' => 'orange',
            'Personal' => 'gray',
            default => 'gray',
        };
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatus', 'filterPriority', 'filterCategory']);
    }
}
