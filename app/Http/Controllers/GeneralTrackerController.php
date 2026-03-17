<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class GeneralTrackerController extends Controller
{
    /**
     * Display the General Task Tracker page
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Filter parameters
        $filter = $request->get('filter', 'all');
        $category = $request->get('category');

        // Base query
        $query = $user->tasks();

        // Apply filters
        if ($filter === 'completed') {
            $query->where('status', 'done');
        } elseif ($filter === 'pending') {
            $query->where('status', 'todo');
        } elseif ($filter === 'today') {
            $query->whereDate('due_date', today());
        } elseif ($filter === 'overdue') {
            $query->whereDate('due_date', '<', today())
                ->whereNotIn('status', ['done']);
        }

        if ($category) {
            $query->where('category', $category);
        }

        // Get tasks
        $tasks = $query->orderByRaw("
            CASE
                WHEN status = 'done' THEN 3
                WHEN due_date < CURDATE() THEN 1
                WHEN due_date = CURDATE() THEN 2
                ELSE 3
            END,
            CASE priority
                WHEN 'urgent-important' THEN 1
                WHEN 'important-not-urgent' THEN 2
                WHEN 'urgent-not-important' THEN 3
                WHEN 'not-urgent-not-important' THEN 4
                ELSE 5
            END,
            due_date ASC
        ")->paginate(15);

        // Get task statistics
        $stats = [
            'total_tasks' => $user->tasks()->count(),
            'completed_tasks' => $user->tasks()->where('status', 'done')->count(),
            'pending_tasks' => $user->tasks()->where('status', 'todo')->count(),
            'in_progress_tasks' => $user->tasks()->where('status', 'doing')->count(),
            'overdue_tasks' => $user->tasks()
                ->whereDate('due_date', '<', today())
                ->where('status', '!=', 'done')
                ->count(),
            'completion_rate' => $user->tasks()->count() > 0
                ? round(($user->tasks()->where('status', 'done')->count() / $user->tasks()->count()) * 100)
                : 0,
            'categories_count' => $user->tasks()->distinct('category')->count('category'),
        ];

        // Get categories
        $categories = $user->tasks()
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        // Get category breakdown
        $categoryBreakdown = $user->tasks()
            ->selectRaw('category, COUNT(*) as total,
                        SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as completed')
            ->groupBy('category')
            ->get()
            ->map(function ($item) {
                $completionRate = $item->total > 0 ? round(($item->completed / $item->total) * 100) : 0;
                return [
                    'name' => $item->category,
                    'total' => $item->total,
                    'completed' => $item->completed,
                    'completion_rate' => $completionRate,
                    'color' => $this->getCategoryColor($item->category),
                ];
            })->sortByDesc('total')->values();

        // Get recently completed tasks
        $recentlyCompleted = $user->tasks()
            ->where('status', 'done')
            ->orderBy('updated_at', 'desc')
            ->take(3)
            ->get();

        // Transform tasks to blade-expected $allTasks format
        $allTasksRaw = $user->tasks()
            ->whereNotIn('category', ['academic', 'skripsi', 'Creative'])
            ->orderByRaw("CASE WHEN status='done' THEN 1 ELSE 0 END, due_date ASC")
            ->get();

        $allTasks = $allTasksRaw->map(function ($t) {
            return [
                'id'       => $t->id,
                'title'    => $t->title,
                'category' => $t->category ?? 'Personal',
                'done'     => $t->status === 'done',
                'date'     => $t->due_date ? $t->due_date->format('Y-m-d') : now()->format('Y-m-d'),
                'time'     => $t->estimated_time ? $t->estimated_time : '-',
            ];
        })->toArray();

        $completedCount = collect($allTasks)->where('done', true)->count();
        $totalCount     = count($allTasks);
        $catCounts      = collect($allTasks)->groupBy('category')->map->count();
        $doneByCat      = collect($allTasks)->where('done', true)->groupBy('category')->map->count();

        return view('dashboard.general-tracker', compact(
            'allTasks',
            'completedCount',
            'totalCount',
            'catCounts',
            'doneByCat',
            'stats',
            'filter',
            'category'
        ));
    }

    /**
     * Store a new task (normal form submission)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'category'       => 'nullable|string',
            'priority'       => 'nullable|string',
            'due_date'       => 'nullable|date',
            'estimated_time' => 'nullable|string',
            'description'    => 'nullable|string',
            'status'         => 'nullable|in:todo,doing,done',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $task = $user->tasks()->create([
            'title'          => $request->title,
            'description'    => $request->description,
            'category'       => $request->category ?? 'Personal',
            'priority'       => $request->priority ?? 'not-urgent-not-important',
            'status'         => $request->status ?? 'todo',
            'due_date'       => $request->due_date,
            'estimated_time' => $request->estimated_time,
            'progress'       => 0,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Tugas berhasil ditambahkan!', 'task' => $task]);
        }

        return redirect()->route('dashboard.tracker')
            ->with('success', 'Task berhasil ditambahkan!');
    }

    /**
     * Update task status (toggle complete/incomplete) - FIXED VERSION
     */
    public function updateStatus(Request $request, $id)
    {
        // Validasi request
        $validated = $request->validate([
            'status' => 'required|in:todo,doing,done'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cari task milik user ini
        $task = $user->tasks()->findOrFail($id);

        // Simpan status lama untuk logging
        $oldStatus = $task->status;

        // Update status dan progress
        $task->status = $validated['status'];

        // Update progress berdasarkan status
        if ($validated['status'] === 'done') {
            $task->progress = 100;
            $task->actual_time = now()->format('H:i:s'); // Set waktu selesai
        } elseif ($validated['status'] === 'doing') {
            $task->progress = max(50, $task->progress); // Minimal 50% jika sedang dikerjakan
        } else {
            $task->progress = 0; // Reset jika kembali ke todo
        }

        // Simpan perubahan
        $saved = $task->save();

        if ($saved) {
            // Log productivity jika task selesai
            if ($oldStatus !== 'done' && $validated['status'] === 'done') {
                $this->logProductivity($user, $task);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status task berhasil diperbarui',
                'new_status' => $task->status,
                'progress' => $task->progress
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status task'
            ], 500);
        }
    }

    /**
     * Log productivity when task is completed
     */
    private function logProductivity($user, $task)
    {
        try {
            $today = now()->toDateString();

            // Cari log untuk hari ini
            $log = $user->productivityLogs()
                ->whereDate('log_date', $today)
                ->first();

            if ($log) {
                // Update existing log
                $log->increment('tasks_completed');
                $log->increment('tasks_planned'); // Anggap task yang diselesaikan sudah direncanakan
                $log->focus_score = min(100, $log->focus_score + 5); // Tambah 5 poin focus score
                $log->save();
            } else {
                // Buat log baru
                $categoryBreakdown = [$task->category => 1];

                $user->productivityLogs()->create([
                    'log_date' => $today,
                    'tasks_completed' => 1,
                    'tasks_planned' => 1,
                    'focus_score' => 80,
                    'total_work_hours' => '01:00:00', // Asumsi 1 jam
                    'category_breakdown' => json_encode($categoryBreakdown),
                    'notes' => 'Menyelesaikan task: ' . $task->title
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to log productivity: ' . $e->getMessage());
        }
    }

    /**
     * Delete task
     */
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $task = $user->tasks()->findOrFail($id);
        $task->delete();

        return redirect()->route('dashboard.tracker')
            ->with('success', 'Task berhasil dihapus!');
    }

    /**
     * Quick add task (simple version)
     */
    public function quickAdd(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->tasks()->create([
            'title' => $request->title,
            'category' => $request->category,
            'priority' => 'not-urgent-not-important',
            'status' => 'todo',
            'due_date' => today()->addDays(7),
            'estimated_time' => '30m',
            'progress' => 0,
        ]);

        return redirect()->route('dashboard.tracker')
            ->with('success', 'Task berhasil ditambahkan!');
    }

    /**
     * Get color for category
     */
    private function getCategoryColor($category)
    {
        $colors = [
            'Kesehatan' => '#ef4444',
            'Pengembangan Diri' => '#06b6d4',
            'Personal' => '#64748b',
            'Organisasi' => '#f59e0b',
            'Perawatan' => '#8b5cf6',
            'Shuttertoct' => '#ec4899',
            'Skripsi' => '#8b5cf6',
            'Creative' => '#f97316',
            'PKL' => '#10b981',
            'Akademik' => '#3b82f6',
        ];

        return $colors[$category] ?? '#6b7280';
    }
}
