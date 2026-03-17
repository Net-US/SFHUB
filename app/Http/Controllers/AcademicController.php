<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Task;
use App\Models\ThesisMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AcademicController extends Controller
{
    // ── INDEX ──────────────────────────────────────────────────────────────
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $courses = $user->subjects()->active()->orderBy('day_of_week')->get();

        $tasks = $user->tasks()
            ->whereIn('category', ['academic', 'skripsi'])
            ->whereNotIn('status', ['archived'])
            ->with('subject')
            ->orderBy('due_date')
            ->get();

        $milestones = $user->thesisMilestones()->orderBy('sort_order')->get();

        // Thesis progress from tasks
        $thesisCourse = $user->subjects()->where('code', 'LIKE', '%499%')
            ->orWhere('name', 'LIKE', '%Skripsi%')
            ->orWhere('name', 'LIKE', '%Tugas Akhir%')
            ->first();

        $thesisProgress = $thesisCourse ? $thesisCourse->progress : 0;

        $tasksByCourse = $tasks->groupBy('linked_subject_id');

        return view('dashboard.academic', compact(
            'courses',
            'tasks',
            'milestones',
            'thesisProgress',
            'tasksByCourse'
        ));
    }

    // ── COURSES (SUBJECTS) CRUD ────────────────────────────────────────────
    public function storeCourse(Request $request)
    {
        $data = $request->validate([
            'code'        => 'nullable|string|max:20',
            'name'        => 'required|string|max:255',
            'sks'         => 'required|integer|min:1|max:6',
            'lecturer'    => 'nullable|string|max:255',
            'day_of_week' => 'required|string',
            'start_time'  => 'nullable|string',
            'end_time'    => 'nullable|string',
            'room'        => 'nullable|string|max:100',
            'drive_link'  => 'nullable|url|max:500',
            'notes'       => 'nullable|string|max:1000',
            'semester'    => 'nullable|integer|min:1|max:14',
        ]);

        $data['user_id'] = Auth::id();
        $data['is_active'] = true;
        $data['progress'] = 0;

        Subject::create($data);

        return back()->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function updateCourse(Request $request, $id)
    {
        $subject = Subject::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $data = $request->validate([
            'code'        => 'nullable|string|max:20',
            'name'        => 'required|string|max:255',
            'sks'         => 'required|integer|min:1|max:6',
            'lecturer'    => 'nullable|string|max:255',
            'day_of_week' => 'required|string',
            'start_time'  => 'nullable|string',
            'end_time'    => 'nullable|string',
            'room'        => 'nullable|string|max:100',
            'drive_link'  => 'nullable|url|max:500',
            'notes'       => 'nullable|string|max:1000',
            'progress'    => 'nullable|integer|min:0|max:100',
            'semester'    => 'nullable|integer|min:1|max:14',
        ]);

        $subject->update($data);

        return back()->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroyCourse($id)
    {
        Subject::where('id', $id)->where('user_id', Auth::id())->firstOrFail()->delete();
        return back()->with('success', 'Mata kuliah berhasil dihapus.');
    }

    // ── TASKS CRUD ─────────────────────────────────────────────────────────
    public function storeTask(Request $request)
    {
        $data = $request->validate([
            'title'              => 'required|string|max:255',
            'linked_subject_id'  => 'nullable|exists:subjects,id',
            'task_type'          => 'nullable|string|max:50',
            'due_date'           => 'nullable|date',
            'priority'           => 'nullable|string',
            'notes'              => 'nullable|string|max:1000',
            'drive_link'         => 'nullable|url|max:500',
        ]);

        $data['user_id'] = Auth::id();
        $data['category'] = 'academic';
        $data['status'] = 'todo';

        Task::create($data);

        return back()->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function updateTask(Request $request, $id)
    {
        $task = Task::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $data = $request->validate([
            'title'             => 'required|string|max:255',
            'linked_subject_id' => 'nullable|exists:subjects,id',
            'task_type'         => 'nullable|string|max:50',
            'due_date'          => 'nullable|date',
            'priority'          => 'nullable|string',
            'status'            => 'nullable|string',
            'notes'             => 'nullable|string|max:1000',
            'drive_link'        => 'nullable|url|max:500',
        ]);

        $task->update($data);

        return back()->with('success', 'Tugas berhasil diperbarui.');
    }

    public function updateTaskStatus(Request $request, $id)
    {
        $task = Task::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $newStatus = $task->status === 'done' ? 'todo' : 'done';
        $task->update(['status' => $newStatus]);

        return response()->json(['status' => $newStatus, 'message' => 'Status diperbarui.']);
    }

    public function destroyTask($id)
    {
        Task::where('id', $id)->where('user_id', Auth::id())->firstOrFail()->delete();
        return back()->with('success', 'Tugas berhasil dihapus.');
    }

    // ── THESIS MILESTONES CRUD ─────────────────────────────────────────────
    public function storeMilestone(Request $request)
    {
        $data = $request->validate([
            'label'       => 'required|string|max:255',
            'target_date' => 'nullable|string|max:50',
            'sort_order'  => 'nullable|integer',
        ]);

        $data['user_id'] = Auth::id();
        $data['done'] = false;
        $data['is_active'] = false;

        ThesisMilestone::create($data);

        return back()->with('success', 'Milestone skripsi berhasil ditambahkan.');
    }

    public function updateMilestone(Request $request, $id)
    {
        $ms = ThesisMilestone::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $data = $request->validate([
            'label'       => 'required|string|max:255',
            'target_date' => 'nullable|string|max:50',
            'done'        => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $ms->update($data);

        return back()->with('success', 'Milestone berhasil diperbarui.');
    }

    public function destroyMilestone($id)
    {
        ThesisMilestone::where('id', $id)->where('user_id', Auth::id())->firstOrFail()->delete();
        return back()->with('success', 'Milestone berhasil dihapus.');
    }
}
