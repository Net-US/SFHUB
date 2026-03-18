<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\SubTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreativeStudioController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Ambil semua proyek kreatif dengan subtasks
        $allTasks = $user->tasks()
            ->where('category', 'Creative')
            ->whereNotIn('status', ['archived'])
            ->with('subtasks')
            ->latest()
            ->get();

        $stageColorBorder = [
            'script'     => 'border-l-4 border-slate-500',
            'production' => 'border-l-4 border-orange-500',
            'revision'   => 'border-l-4 border-amber-500',
            'done'       => 'border-l-4 border-emerald-500',
        ];

        $stageMap = ['todo' => 'script', 'doing' => 'production', 'done' => 'done'];

        // TAMBAHKAN KODE INI: Mapping stage detail ke kolom utama Kanban
        $kanbanColumnMap = [
            'planning'  => 'script',
            'script'    => 'script',
            'concept'   => 'script',
            'recording' => 'production',
            'design'    => 'production',
            'animation' => 'production',
            'editing'   => 'production',
            'doing'     => 'production',
            'review'    => 'revision',
            'revision'  => 'revision',
            'finalize'  => 'revision',
            'publish'   => 'done',
            'done'      => 'done',
        ];

        $projects = $allTasks->map(function ($t) use ($stageMap, $stageColorBorder, $kanbanColumnMap) {
            $rawStage = ($t->workflow_stage && $t->workflow_stage !== 'none')
                ? $t->workflow_stage
                : ($stageMap[$t->status] ?? 'script');

            // UBAH VARIABEL STAGE DI BAWAH INI: Gunakan mapping
            $stage = $kanbanColumnMap[$rawStage] ?? 'script'; // Default ke script jika tidak terdaftar

            $priority = match ($t->priority) {
                'urgent-important'         => 'high',
                'important-not-urgent'     => 'medium',
                'urgent-not-important'     => 'medium',
                'not-urgent-not-important' => 'low',
                default                    => $t->priority ?? 'medium',
            };

            $projectMode = $t->project_mode ?? ($t->total_subtasks > 0 ? 'sequential' : 'simple');

            return [
                'id'           => $t->id,
                'title'        => $t->title,
                'stage'        => $stage, // Sekarang stage akan selalu bernilai salah satu dari 4 kolom utama
                'type'         => $t->project_type ?? 'Personal',
                'project_mode' => $projectMode,
                'progress'     => $t->progress ?? 0,
                'deadline'     => $t->due_date ? $t->due_date->format('d M Y') : 'Flexible',
                'due_date_raw' => $t->due_date?->format('Y-m-d'),
                'tags'         => $t->tags ?? [],
                'color'        => $stageColorBorder[$stage] ?? 'border-l-4 border-stone-400',
                'priority'     => $priority,
                'description'  => $t->description ?? '',
                'drive_link'   => $t->drive_link ?? '',
                'total_subtasks'    => $t->total_subtasks ?? $t->subtasks->count(),
                'completed_subtasks' => $t->completed_subtasks ?? $t->subtasks->where('status', 'completed')->count(),
                'subtasks'     => $t->subtasks->map(fn($s) => [
                    'id'          => $s->id,
                    'title'       => $s->title,
                    'status'      => $s->status,
                    'progress'    => $s->progress ?? 0,
                    'stage_key'   => $s->stage_key ?? '',
                    'stage_label' => $s->stage_label ?? $s->title,
                    'order'       => $s->order ?? 0,
                ])->sortBy('order')->values()->toArray(),
            ];
        })->values()->toArray();

        // Stats
        $stats = [
            'total_projects'     => count($projects),
            'active_projects'    => collect($projects)->whereIn('stage', ['script', 'production', 'revision'])->count(),
            'completed_projects' => collect($projects)->where('stage', 'done')->count(),
            'overdue_projects'   => $user->tasks()
                ->where('category', 'Creative')
                ->whereDate('due_date', '<', today())
                ->where('status', '!=', 'done')
                ->count(),
        ];

        return view('dashboard.creative-studio', compact('projects', 'stats'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE PROJECT
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:2000',
            'project_type'   => 'nullable|string|max:100',
            'project_mode'   => 'required|in:sequential,simple',
            'priority'       => 'nullable|in:high,medium,low',
            'workflow_stage' => 'nullable|string',
            'due_date'       => 'nullable|date',
            'tags'           => 'nullable|array',
            'tags.*'         => 'string|max:50',
            'drive_link'     => 'nullable|url|max:500',
            'client'         => 'nullable|string|max:255',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $priorityMap = [
            'high'   => 'urgent-important',
            'medium' => 'important-not-urgent',
            'low'    => 'not-urgent-not-important',
        ];
        $stageStatusMap = ['script' => 'todo', 'production' => 'doing', 'revision' => 'doing', 'done' => 'done'];
        $stage    = $validated['workflow_stage'] ?? 'script';
        $priority = $priorityMap[$validated['priority'] ?? 'medium'] ?? 'important-not-urgent';

        $task = $user->tasks()->create([
            'title'          => $validated['title'],
            'description'    => $validated['description'] ?? null,
            'category'       => 'Creative',
            'project_type'   => $validated['project_type'] ?? 'other',
            'project_mode'   => $validated['project_mode'],
            'priority'       => $priority,
            'status'         => $stageStatusMap[$stage] ?? 'todo',
            'workflow_stage' => $stage,
            'due_date'       => $validated['due_date'] ? Carbon::parse($validated['due_date']) : null,
            'tags'           => $validated['tags'] ?? [],
            'drive_link'     => $validated['drive_link'] ?? null,
            'client'         => $validated['client'] ?? null,
            'progress'       => 0,
            'total_subtasks' => 0,
            'completed_subtasks' => 0,
        ]);

        // Tipe A (sequential): buat default subtasks otomatis
        if ($validated['project_mode'] === 'sequential') {
            $this->createDefaultSubtasksForType($task);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Proyek berhasil dibuat!',
                'task_id' => $task->id,
            ]);
        }
        return redirect()->route('dashboard.creative.index')->with('success', 'Proyek berhasil ditambahkan!');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPDATE PROJECT
    // ─────────────────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:2000',
            'project_type'   => 'nullable|string|max:100',
            'priority'       => 'nullable|in:high,medium,low,urgent-important,important-not-urgent,urgent-not-important,not-urgent-not-important',
            'due_date'       => 'nullable|date',
            'workflow_stage' => 'nullable|string',
            'drive_link'     => 'nullable|url|max:500',
            'client'         => 'nullable|string|max:255',
            'tags'           => 'nullable|array',
        ]);

        $stageStatusMap = ['script' => 'todo', 'production' => 'doing', 'revision' => 'doing', 'done' => 'done'];
        $newStage = $validated['workflow_stage'] ?? $task->workflow_stage;
        $newStatus = $stageStatusMap[$newStage] ?? $task->status;

        // Auto complete if stage = done
        if ($newStage === 'done' && $task->status !== 'done') {
            $validated['status']       = 'done';
            $validated['progress']     = 100;
            $validated['completed_at'] = now();
        } else {
            $validated['status'] = $newStatus;
        }
        $validated['workflow_stage'] = $newStage;

        $task->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Proyek diperbarui!', 'task' => $task->fresh()]);
        }
        return back()->with('success', 'Proyek berhasil diperbarui!');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPDATE STATUS (move card antar kolom Kanban)
    // ─────────────────────────────────────────────────────────────────────────
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['stage' => 'required|in:script,production,revision,done']);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);

        $stageStatusMap = ['script' => 'todo', 'production' => 'doing', 'revision' => 'doing', 'done' => 'done'];
        $newStage  = $request->stage;
        $newStatus = $stageStatusMap[$newStage];

        $updates = [
            'workflow_stage' => $newStage,
            'status'         => $newStatus,
        ];

        if ($newStage === 'done' && $task->status !== 'done') {
            $updates['progress']     = 100;
            $updates['completed_at'] = now();
            // Selesaikan semua subtask yang belum done
            $task->subtasks()->where('status', '!=', 'completed')->update([
                'status'       => 'completed',
                'progress'     => 100,
                'completed_at' => now(),
            ]);
            $task->update(['completed_subtasks' => $task->subtasks()->count()]);
        } elseif ($newStage !== 'done' && $task->status === 'done') {
            $updates['completed_at'] = null;
        }

        $task->update($updates);

        return response()->json([
            'success'  => true,
            'message'  => 'Status diperbarui!',
            'task'     => $task->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MARK AS DONE
    // ─────────────────────────────────────────────────────────────────────────
    public function markDone(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $task = $user->tasks()->with('subtasks')->findOrFail($id);

        $task->update([
            'status'         => 'done',
            'workflow_stage' => 'done',
            'progress'       => 100,
            'completed_at'   => now(),
        ]);

        // Selesaikan semua subtask
        $task->subtasks()->update([
            'status'       => 'completed',
            'progress'     => 100,
            'completed_at' => now(),
        ]);
        $task->update(['completed_subtasks' => $task->subtasks()->count()]);

        return response()->json([
            'success' => true,
            'message' => 'Proyek ditandai selesai! 🎉',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RESCHEDULE (Tipe B: simple/recurring — geser ke minggu depan)
    // ─────────────────────────────────────────────────────────────────────────
    public function reschedule(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);

        if ($task->project_mode !== 'simple') {
            return response()->json(['success' => false, 'message' => 'Hanya proyek mandiri (simple) yang bisa dijadwal ulang.'], 422);
        }

        $newDueDate = $task->due_date
            ? Carbon::parse($task->due_date)->addWeek()
            : Carbon::now()->addWeek();

        $task->update(['due_date' => $newDueDate]);

        return response()->json([
            'success'    => true,
            'message'    => 'Tugas dijadwalkan ulang ke ' . $newDueDate->isoFormat('D MMM YYYY') . '.',
            'new_due_date' => $newDueDate->format('Y-m-d'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DESTROY PROJECT (dengan konfirmasi sudah dari FE, BE hanya validasi owner)
    // ─────────────────────────────────────────────────────────────────────────
    public function destroy(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);

        // Hard delete subtasks dulu
        $task->subtasks()->forceDelete();
        $task->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Proyek berhasil dihapus.']);
        }
        return redirect()->route('dashboard.creative.index')->with('success', 'Proyek dihapus!');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SUBTASK CRUD
    // ─────────────────────────────────────────────────────────────────────────
    public function storeSubtask(Request $request, $taskId)
    {
        $request->validate([
            'title'             => 'required|string|max:255',
            'stage_key'         => 'nullable|string|max:50',
            'stage_label'       => 'nullable|string|max:100',
            'estimated_minutes' => 'nullable|integer|min:1|max:9999',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($taskId);

        $subtask = $task->subtasks()->create([
            'user_id'           => $user->id,
            'title'             => $request->title,
            'stage_key'         => $request->stage_key ?? 'custom',
            'stage_label'       => $request->stage_label ?? $request->title,
            'type'              => 'stage',
            'estimated_minutes' => $request->estimated_minutes ?? 60,
            'order'             => $task->subtasks()->count(),
            'status'            => 'pending',
            'progress'          => 0,
        ]);

        // Update parent counter
        $total = $task->subtasks()->count();
        $task->update(['total_subtasks' => $total]);

        return response()->json([
            'success' => true,
            'message' => 'Sub-tugas berhasil ditambahkan',
            'subtask' => $subtask,
        ]);
    }

    public function updateSubtask(Request $request, $taskId, $subtaskId)
    {
        $request->validate([
            'status'   => 'required|in:pending,in_progress,completed',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        /** @var \App\Models\User $user */
        $user    = Auth::user();
        $task    = $user->tasks()->findOrFail($taskId);
        $subtask = $task->subtasks()->findOrFail($subtaskId);

        if ($request->status === 'completed') {
            $subtask->update([
                'status'       => 'completed',
                'progress'     => 100,
                'completed_at' => now(),
                'started_at'   => $subtask->started_at ?? now(),
            ]);
        } else {
            $subtask->update([
                'status'     => $request->status,
                'progress'   => $request->progress ?? $subtask->progress,
                'started_at' => ($request->status === 'in_progress' && !$subtask->started_at) ? now() : $subtask->started_at,
            ]);
        }

        // ── Recalculate parent task progress (Tipe A: auto progress) ──────
        $totalSubs    = $task->subtasks()->count();
        $completedSubs = $task->subtasks()->where('status', 'completed')->count();
        $newProgress  = $totalSubs > 0 ? round(($completedSubs / $totalSubs) * 100) : 0;

        $parentUpdates = [
            'completed_subtasks' => $completedSubs,
            'progress'           => $newProgress,
        ];

        // Jika semua subtask selesai → parent task otomatis done
        if ($completedSubs === $totalSubs && $totalSubs > 0) {
            $parentUpdates['status']         = 'done';
            $parentUpdates['workflow_stage'] = 'done';
            $parentUpdates['completed_at']   = now();
        } elseif ($newProgress > 0 && $task->status === 'todo') {
            $parentUpdates['status']       = 'doing';
            $parentUpdates['started_at']   = $task->started_at ?? now();
            $parentUpdates['workflow_stage'] = 'production';
        }

        $task->update($parentUpdates);

        // ── Auto-advance workflow stage berdasarkan subtask yang selesai ───
        if ($request->status === 'completed') {
            $this->advanceWorkflowStageIfNeeded($task);
        }

        return response()->json([
            'success'       => true,
            'message'       => 'Sub-tugas diperbarui!',
            'task_progress' => $task->fresh()->progress,
            'task_status'   => $task->fresh()->status,
            'subtask'       => $subtask->fresh(),
        ]);
    }

    public function destroySubtask(Request $request, $taskId, $subtaskId)
    {
        /** @var \App\Models\User $user */
        $user    = Auth::user();
        $task    = $user->tasks()->findOrFail($taskId);
        $subtask = $task->subtasks()->findOrFail($subtaskId);
        $subtask->forceDelete();

        // Recalculate
        $total     = $task->subtasks()->count();
        $completed = $task->subtasks()->where('status', 'completed')->count();
        $progress  = $total > 0 ? round(($completed / $total) * 100) : 0;
        $task->update(['total_subtasks' => $total, 'completed_subtasks' => $completed, 'progress' => $progress]);

        return response()->json(['success' => true, 'message' => 'Sub-tugas dihapus.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SHOW TASK DETAIL (JSON untuk modal)
    // ─────────────────────────────────────────────────────────────────────────
    public function showTaskDetail($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $task = $user->tasks()->with('subtasks')->findOrFail($id);

        return response()->json([
            'success' => true,
            'task' => [
                'id'            => $task->id,
                'title'         => $task->title,
                'description'   => $task->description,
                'project_type'  => $task->project_type,
                'project_mode'  => $task->project_mode ?? 'simple',
                'priority'      => $task->priority,
                'status'        => $task->status,
                'workflow_stage' => $task->workflow_stage,
                'progress'      => $task->progress ?? 0,
                'due_date'      => $task->due_date?->format('Y-m-d'),
                'due_date_fmt'  => $task->due_date?->isoFormat('D MMM Y') ?? 'Flexible',
                'tags'          => $task->tags ?? [],
                'links'         => $task->links ?? [],
                'drive_link'    => $task->drive_link ?? '',
                'client'        => $task->client ?? '',
                'total_subtasks'     => $task->total_subtasks ?? 0,
                'completed_subtasks' => $task->completed_subtasks ?? 0,
                'subtasks'      => $task->subtasks->map(fn($s) => [
                    'id'          => $s->id,
                    'title'       => $s->title,
                    'status'      => $s->status,
                    'progress'    => $s->progress ?? 0,
                    'stage_key'   => $s->stage_key ?? '',
                    'stage_label' => $s->stage_label ?? $s->title,
                    'order'       => $s->order ?? 0,
                ])->sortBy('order')->values(),
                'workflow_stages' => $task->getWorkflowStages(),
            ],
        ]);
    }

    public function createDefaultSubtasks(Request $request, $taskId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($taskId);

        if ($task->subtasks()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Sub-tugas sudah ada.']);
        }

        $subtasks = $this->createDefaultSubtasksForType($task);

        return response()->json([
            'success'  => true,
            'message'  => 'Workflow stages dibuat!',
            'subtasks' => $subtasks,
        ]);
    }

    public function addLink(Request $request, $id)
    {
        $request->validate([
            'type'  => 'required|string|in:drive,canva,figma,adobe,notion,miro,github,dropbox,other',
            'url'   => 'required|url',
            'label' => 'nullable|string|max:50',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);
        $task->addLink($request->type, $request->url, $request->label);

        return response()->json(['success' => true, 'message' => 'Link ditambahkan', 'links' => $task->links]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Buat default subtasks untuk Tipe A (sequential) berdasarkan project_type
     */
    private function createDefaultSubtasksForType(Task $task): array
    {
        $workflowMap = [
            'video_editing'    => [
                ['key' => 'planning',  'label' => '📋 Perencanaan & Brief',     'mins' => 30],
                ['key' => 'script',    'label' => '✍️ Naskah & Script',         'mins' => 120],
                ['key' => 'recording', 'label' => '🎙️ Rekaman / Shooting',     'mins' => 180],
                ['key' => 'editing',   'label' => '✂️ Editing Video',           'mins' => 240],
                ['key' => 'review',    'label' => '👁️ Review & QC',            'mins' => 60],
                ['key' => 'publish',   'label' => '🚀 Publish / Deliver',       'mins' => 30],
            ],
            'animation'        => [
                ['key' => 'script',    'label' => '✍️ Script & Storyboard',     'mins' => 120],
                ['key' => 'design',    'label' => '🎨 Character & Asset Design', 'mins' => 180],
                ['key' => 'animation', 'label' => '🎬 Animasi',                 'mins' => 360],
                ['key' => 'editing',   'label' => '🎵 Sound & Editing',         'mins' => 120],
                ['key' => 'review',    'label' => '👁️ Review',                 'mins' => 60],
                ['key' => 'publish',   'label' => '📤 Render & Delivery',       'mins' => 60],
            ],
            'motion_graphics'  => [
                ['key' => 'concept',   'label' => '💡 Konsep & Moodboard',      'mins' => 60],
                ['key' => 'design',    'label' => '🎨 Desain Aset',             'mins' => 180],
                ['key' => 'animation', 'label' => '🎬 Motion & Animasi',        'mins' => 240],
                ['key' => 'review',    'label' => '👁️ Review',                 'mins' => 60],
                ['key' => 'publish',   'label' => '📤 Export & Deliver',        'mins' => 30],
            ],
            'graphic_design'   => [
                ['key' => 'planning',  'label' => '📋 Brief & Research',        'mins' => 45],
                ['key' => 'concept',   'label' => '💡 Konsep & Moodboard',      'mins' => 60],
                ['key' => 'design',    'label' => '🎨 Desain',                  'mins' => 180],
                ['key' => 'review',    'label' => '👁️ Review Client',          'mins' => 30],
                ['key' => 'revision',  'label' => '🔄 Revisi',                  'mins' => 60],
                ['key' => 'finalize',  'label' => '✅ Finalisasi & Deliver',    'mins' => 30],
            ],
            'social_media'     => [
                ['key' => 'concept',   'label' => '💡 Konsep Konten',           'mins' => 30],
                ['key' => 'design',    'label' => '🎨 Buat Visual / Caption',   'mins' => 60],
                ['key' => 'review',    'label' => '👁️ Review',                 'mins' => 15],
                ['key' => 'publish',   'label' => '📱 Publish ke Platform',     'mins' => 15],
            ],
        ];

        $stages = $workflowMap[$task->project_type] ?? [
            ['key' => 'planning',  'label' => '📋 Perencanaan',  'mins' => 60],
            ['key' => 'doing',     'label' => '🔨 Pengerjaan',   'mins' => 120],
            ['key' => 'review',    'label' => '👁️ Review',      'mins' => 30],
            ['key' => 'finalize',  'label' => '✅ Finalisasi',  'mins' => 30],
        ];

        $created = [];
        foreach ($stages as $idx => $stage) {
            $sub = $task->subtasks()->create([
                'user_id'           => $task->user_id,
                'title'             => $stage['label'],
                'type'              => 'stage',
                'stage_key'         => $stage['key'],
                'stage_label'       => $stage['label'],
                'order'             => $idx,
                'estimated_minutes' => $stage['mins'],
                'status'            => 'pending',
                'progress'          => 0,
            ]);
            $created[] = $sub->toArray();
        }

        $task->update([
            'total_subtasks'     => count($stages),
            'completed_subtasks' => 0,
            'workflow_stage'     => $stages[0]['key'],
        ]);

        return $created;
    }

    /**
     * Auto-advance workflow_stage saat subtask tertentu selesai
     */
    private function advanceWorkflowStageIfNeeded(Task $task): void
    {
        $task->refresh();
        if ($task->status === 'done') return;

        // Cari subtask pertama yang belum completed → itu stage aktif berikutnya
        $nextPending = $task->subtasks()
            ->whereNotIn('status', ['completed'])
            ->orderBy('order')
            ->first();

        if ($nextPending) {
            $task->update(['workflow_stage' => $nextPending->stage_key ?? $task->workflow_stage]);
        }
    }
}
