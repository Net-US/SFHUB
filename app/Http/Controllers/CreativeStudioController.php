<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CreativeStudioController extends Controller
{
    /**
     * Display Creative Studio Kanban Board
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Filter parameters
        $status = $request->get('status', 'all');
        $projectType = $request->get('project_type', 'all');
        $priority = $request->get('priority', 'all');

        // Base query for creative projects
        $query = $user->tasks()
            ->where('category', 'Creative')
            ->with('workspace');

        // Apply filters
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($projectType !== 'all') {
            $query->where('project_type', $projectType);
        }

        if ($priority !== 'all') {
            $query->where('priority', $priority);
        }

        // Get creative projects grouped by status
        $todoTasks = (clone $query)->where('status', 'todo')->get();
        $doingTasks = (clone $query)->where('status', 'doing')->get();
        $doneTasks = (clone $query)->where('status', 'done')->get();

        // Get statistics
        $stats = [
            'total_projects' => $user->tasks()->where('category', 'Creative')->count(),
            'active_projects' => $user->tasks()->where('category', 'Creative')
                ->whereIn('status', ['todo', 'doing'])
                ->count(),
            'completed_projects' => $user->tasks()->where('category', 'Creative')
                ->where('status', 'done')
                ->count(),
            'overdue_projects' => $user->tasks()->where('category', 'Creative')
                ->whereDate('due_date', '<', today())
                ->where('status', '!=', 'done')
                ->count(),
        ];

        // Get project type distribution
        $projectTypes = [
            'video_editing' => 'Video Editing',
            'graphic_design' => 'Graphic Design',
            'motion_graphics' => 'Motion Graphics',
            'audio_production' => 'Audio Production',
            'script_writing' => 'Script Writing',
            'ui_ux_design' => 'UI/UX Design',
            'animation' => 'Animation',
            'photography' => 'Photography',
            'illustration' => 'Illustration',
            'branding' => 'Branding',
            'social_media' => 'Social Media',
        ];

        // Get project type counts
        $projectTypeCounts = [];
        foreach ($projectTypes as $key => $label) {
            $projectTypeCounts[$key] = [
                'label' => $label,
                'count' => $user->tasks()
                    ->where('category', 'Creative')
                    ->where('project_type', $key)
                    ->count(),
                'active' => $user->tasks()
                    ->where('category', 'Creative')
                    ->where('project_type', $key)
                    ->whereIn('status', ['todo', 'doing'])
                    ->count(),
            ];
        }

        // Build $projects array in the format the blade Kanban expects
        $stageColorBorder = [
            'script'     => 'border-l-4 border-slate-500',
            'production' => 'border-l-4 border-orange-500',
            'revision'   => 'border-l-4 border-amber-500',
            'done'       => 'border-l-4 border-emerald-500',
        ];
        $allProjects = $user->tasks()
            ->where('category', 'Creative')
            ->whereNotIn('status', ['archived'])
            ->get();

        $stageMap = ['todo' => 'script', 'doing' => 'production', 'done' => 'done'];

        $projects = $allProjects->map(function ($t) use ($stageMap, $stageColorBorder) {
            $stage    = $t->workflow_stage && $t->workflow_stage !== 'none' ? $t->workflow_stage : ($stageMap[$t->status] ?? 'script');
            $priority = match ($t->priority) {
                'urgent-important'         => 'high',
                'important-not-urgent'     => 'medium',
                'urgent-not-important'     => 'medium',
                'not-urgent-not-important' => 'low',
                default                    => $t->priority ?? 'medium',
            };
            return [
                'id'       => $t->id,
                'title'    => $t->title,
                'stage'    => $stage,
                'type'     => $t->project_type ?? 'Personal',
                'progress' => $t->progress ?? 0,
                'deadline' => $t->due_date ? $t->due_date->format('d M') : 'Flexible',
                'tags'     => $t->tags ?? [],
                'color'    => $stageColorBorder[$stage] ?? 'border-l-4 border-stone-400',
                'priority' => $priority,
            ];
        })->toArray();

        return view('dashboard.creative-studio', compact(
            'projects',
            'stats',
            'status',
            'projectType',
            'priority'
        ));
    }

    /**
     * Store a new creative project
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'project_type'   => 'nullable|string|max:100',
            'priority'       => 'nullable|string',
            'workflow_stage' => 'nullable|string',
            'due_date'       => 'nullable|date',
            'tags'           => 'nullable|array',
            'tags.*'         => 'string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Map simple priority to internal format
        $priorityMap = ['high' => 'urgent-important', 'medium' => 'important-not-urgent', 'low' => 'not-urgent-not-important'];
        $stageStatusMap = ['script' => 'todo', 'production' => 'doing', 'revision' => 'doing', 'done' => 'done'];
        $stage    = $request->input('workflow_stage', 'script');
        $priority = $priorityMap[$request->input('priority', 'medium')] ?? $request->input('priority', 'important-not-urgent');

        $task = $user->tasks()->create([
            'title'          => $request->title,
            'description'    => $request->description,
            'category'       => 'Creative',
            'project_type'   => $request->project_type,
            'priority'       => $priority,
            'status'         => $stageStatusMap[$stage] ?? 'todo',
            'workflow_stage' => $stage,
            'due_date'       => $request->due_date,
            'tags'           => $request->tags,
            'progress'       => 0,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Proyek berhasil dibuat!', 'task' => $task]);
        }

        return redirect()->route('dashboard.creative')
            ->with('success', 'Project kreatif berhasil ditambahkan!');
    }

    /**
     * Update creative project
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_type' => 'required|in:video_editing,graphic_design,motion_graphics,audio_production,script_writing,ui_ux_design,animation,photography,illustration,branding,social_media',
            'priority' => 'required|in:urgent-important,important-not-urgent,urgent-not-important,not-urgent-not-important',
            'due_date' => 'required|date',
            'estimated_time' => 'nullable|string',
            'status' => 'required|in:todo,doing,done',
            'progress' => 'required|integer|min:0|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'links' => 'nullable|array',
            'links.*.type' => 'required|string',
            'links.*.url' => 'required|url',
            'links.*.label' => 'nullable|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $task = $user->tasks()->findOrFail($id);

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'project_type' => $request->project_type,
            'priority' => $request->priority,
            'status' => $request->status,
            'due_date' => $request->due_date,
            'estimated_time' => $request->estimated_time,
            'progress' => $request->progress,
            'tags' => $request->tags,
            'links' => $request->links,
        ]);

        // Update actual time if task is completed
        if ($request->status === 'done' && $task->actual_time === null) {
            $task->update(['actual_time' => now()->format('H:i:s')]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Project berhasil diperbarui!',
        ]);
    }

    /**
     * Update task status via drag & drop
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:todo,doing,done',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $task = $user->tasks()->findOrFail($id);

        $oldStatus = $task->status;
        $task->status = $request->status;

        // Update progress based on status
        if ($request->status === 'done') {
            $task->progress = 100;
            $task->actual_time = now()->format('H:i:s');
        } elseif ($request->status === 'doing') {
            $task->progress = $request->progress ?? max(50, $task->progress);
        } elseif ($request->status === 'todo') {
            $task->progress = $request->progress ?? 0;
        }

        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Status project berhasil diperbarui',
            'task' => $task->fresh(),
        ]);
    }

    /**
     * Add link to creative project
     */
    public function addLink(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string|in:drive,canva,figma,adobe,notion,miro,milanote,github,dropbox,other',
            'url' => 'required|url',
            'label' => 'nullable|string|max:50',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $task = $user->tasks()->findOrFail($id);

        $task->addLink($request->type, $request->url, $request->label);

        return response()->json([
            'success' => true,
            'message' => 'Link berhasil ditambahkan',
            'links' => $task->links,
        ]);
    }

    /**
     * Delete creative project
     */
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $task = $user->tasks()->findOrFail($id);
        $task->delete();

        return redirect()->route('dashboard.creative')
            ->with('success', 'Project berhasil dihapus!');
    }

    /**
     * Get link icon based on type
     */
    private function getLinkIcon($type)
    {
        $icons = [
            'drive' => 'fa-google-drive',
            'canva' => 'fa-palette',
            'figma' => 'fa-figma',
            'adobe' => 'fa-adobe',
            'notion' => 'fa-book',
            'miro' => 'fa-chalkboard',
            'milanote' => 'fa-sticky-note',
            'github' => 'fa-github',
            'dropbox' => 'fa-dropbox',
            'other' => 'fa-link',
        ];

        return $icons[$type] ?? 'fa-link';
    }

    /**
     * Get link color based on type
     */
    private function getLinkColor($type)
    {
        $colors = [
            'drive' => 'text-blue-500 bg-blue-50 dark:bg-blue-900/20',
            'canva' => 'text-pink-500 bg-pink-50 dark:bg-pink-900/20',
            'figma' => 'text-purple-500 bg-purple-50 dark:bg-purple-900/20',
            'adobe' => 'text-red-500 bg-red-50 dark:bg-red-900/20',
            'notion' => 'text-stone-700 bg-stone-100 dark:bg-stone-800',
            'miro' => 'text-amber-500 bg-amber-50 dark:bg-amber-900/20',
            'milanote' => 'text-rose-500 bg-rose-50 dark:bg-rose-900/20',
            'github' => 'text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-800',
            'dropbox' => 'text-blue-600 bg-blue-50 dark:bg-blue-900/20',
            'other' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20',
        ];

        return $colors[$type] ?? 'text-gray-500 bg-gray-100 dark:bg-gray-800';
    }
    // Tambahkan method ini ke CreativeStudioController
    public function showTaskDetail($id)
    {
        $user = Auth::user();
        $task = $user->tasks()->with('subtasks')->findOrFail($id);

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'project_type' => $task->project_type,
                'priority' => $task->priority,
                'status' => $task->status,
                'workflow_stage' => $task->workflow_stage,
                'progress' => $task->progress,
                'due_date' => $task->due_date?->format('Y-m-d'),
                'tags' => $task->tags ?? [],
                'links' => $task->links ?? [],
                'subtasks' => $task->subtasks->map(fn($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'status' => $s->status,
                    'progress' => $s->progress,
                    'stage_key' => $s->stage_key,
                    'stage_label' => $s->stage_label,
                ]),
                'workflow_stages' => $task->getWorkflowStages(),
            ]
        ]);
    }

    public function createDefaultSubtasks(Request $request, $taskId)
    {
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($taskId);

        // Hanya buat jika belum ada subtasks
        if ($task->subtasks()->count() === 0) {
            $subtasks = $task->createDefaultSubtasks();
            return response()->json([
                'success' => true,
                'message' => 'Workflow stages created!',
                'subtasks' => $subtasks
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Subtasks already exist'
        ]);
    }

    public function updateSubtask(Request $request, $taskId, $subtaskId)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $user = Auth::user();
        $task = $user->tasks()->findOrFail($taskId);
        $subtask = $task->subtasks()->findOrFail($subtaskId);

        if ($request->status === 'completed') {
            $subtask->markAsComplete();
        } else {
            $subtask->update([
                'status' => $request->status,
                'progress' => $request->progress ?? $subtask->progress
            ]);

            if ($request->status === 'in_progress' && !$subtask->started_at) {
                $subtask->start();
            }
        }

        // Update parent task progress
        $task->updateProgressFromSubtasks();

        return response()->json([
            'success' => true,
            'message' => 'Subtask berhasil diperbarui',
            'task_progress' => $task->fresh()->progress,
            'subtask' => $subtask->fresh()
        ]);
    }

    public function storeSubtask(Request $request, $taskId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'estimated_minutes' => 'nullable|integer|min:1',
        ]);

        $user = Auth::user();
        $task = $user->tasks()->findOrFail($taskId);

        $subtask = $task->subtasks()->create([
            'user_id' => $user->id,
            'title' => $request->title,
            'estimated_minutes' => $request->estimated_minutes ?? 60,
            'order' => $task->subtasks()->count()
        ]);

        // Update parent task counters
        $task->update([
            'total_subtasks' => $task->subtasks()->count(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subtask berhasil ditambahkan',
            'subtask' => $subtask
        ]);
    }

    public function destroySubtask($taskId, $subtaskId)
    {
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($taskId);
        $subtask = $task->subtasks()->findOrFail($subtaskId);
        $subtask->delete();

        // Update parent task counters
        $completed = $task->subtasks()->where('status', 'completed')->count();
        $task->update([
            'total_subtasks' => $task->subtasks()->count(),
            'completed_subtasks' => $completed,
        ]);
        $task->updateProgressFromSubtasks();

        return response()->json(['success' => true, 'message' => 'Subtask dihapus']);
    }
}
