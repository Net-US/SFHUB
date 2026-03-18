<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Task;
use App\Models\SubjectSession;
use App\Models\ThesisMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AcademicController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────────
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Ambil data Mata Kuliah beserta relasi 16 Sesi-nya
        $subjectsRaw = $user->subjects()->with('sessions')->active()->orderBy('day_of_week')->get();

        // Format subjects untuk blade
        $courses = $subjectsRaw->map(function ($s) {
            $st = $s->start_time ? date('H:i', strtotime($s->start_time)) : '';
            $et = $s->end_time ? date('H:i', strtotime($s->end_time)) : '';

            return [
                'id'         => $s->id,
                'code'       => $s->code ?? '',
                'name'       => $s->name,
                'sks'        => $s->sks ?? 2,
                'lecturer'   => $s->lecturer ?? '',
                'day'        => $s->day_of_week ?? '-',
                'time'       => ($st && $et) ? "{$st}–{$et}" : ($st ?: $et),
                'start_time' => $st,
                'end_time'   => $et,
                'room'       => $s->room ?? '',
                'progress'   => $s->progress ?? 0,
                'drive_link' => $s->drive_link ?? '',
                'notes'      => $s->notes ?? '',
                'is_active'  => $s->is_active,
                'semester'   => $s->semester ?? 1,
                // TAMBAHKAN BARIS INI: Bawa data sesi ke frontend
                'sessions'   => $s->sessions->toArray(),
            ];
        })->values();

        // Tasks akademik + skripsi — dengan linked_subject_id
        $tasksRaw = $user->tasks()
            ->whereIn('category', ['academic', 'skripsi', 'Academic'])
            ->whereNotIn('status', ['archived'])
            ->orderBy('due_date')
            ->get();

        $tasks = $tasksRaw->map(fn($t) => [
            'id'               => $t->id,
            'title'            => $t->title,
            'course_id'        => $t->linked_subject_id ?? $t->subject_id ?? null,
            'linked_subject_id' => $t->linked_subject_id ?? $t->subject_id ?? null,
            'type'             => $t->task_type ?? $t->project_type ?? 'assignment',
            'deadline'         => $t->due_date?->format('Y-m-d') ?? $t->deadline?->format('Y-m-d'),
            'status'           => $t->status,
            'priority'         => $this->mapPriority($t->priority),
            'drive_link'       => $t->drive_link ?? '',
            'notes'            => $t->notes ?? $t->description ?? '',
        ])->values();

        // Milestones skripsi
        // Milestones skripsi
        $milestones = $user->thesisMilestones()
            ->orderBy('sort_order')
            ->get()
            ->map(function ($m) {
                $formattedDate = $m->target_date;
                if ($m->target_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $m->target_date)) {
                    $formattedDate = \Carbon\Carbon::parse($m->target_date)->isoFormat('D MMM YYYY');
                }

                return [
                    'id'              => $m->id,
                    'label'           => $m->label,
                    'target_date'     => $formattedDate,         // Tanggal cantik untuk ditampilkan di layar
                    'target_date_raw' => $m->target_date,        // Tanggal mentah Y-m-d untuk edit modal
                    'done'            => (bool)$m->done,
                    'is_active'       => (bool)$m->is_active,
                    'sort_order'      => $m->sort_order,
                ];
            })->values();

        // Thesis progress
        $thesisCourse = $subjectsRaw
            ->filter(fn($s) => str_contains(strtolower($s->name), 'skripsi') ||
                str_contains(strtolower($s->name), 'tugas akhir') ||
                str_contains(strtolower($s->code ?? ''), '499'))
            ->first();
        $thesisProgress = $thesisCourse?->progress ?? 0;

        $tasksByCourse = $tasks->groupBy('course_id');

        return view('dashboard.academic', compact(
            'courses',
            'tasks',
            'milestones',
            'thesisProgress',
            'tasksByCourse'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // COURSES CRUD
    // ─────────────────────────────────────────────────────────────────────────
    public function storeCourse(Request $request)
    {
        $data = $request->validate([
            'code'        => 'nullable|string|max:20',
            'name'        => 'required|string|max:255',
            'sks'         => 'required|integer|min:1|max:6',
            'lecturer'    => 'nullable|string|max:255',
            'day_of_week' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
            'room'        => 'nullable|string|max:100',
            'drive_link'  => 'nullable|url|max:500',
            'notes'       => 'nullable|string|max:1000',
            'semester'    => 'nullable|integer|min:1|max:14',
            'start_date'  => 'required|date', // <--- TAMBAHAN BARU
        ]);

        $data['user_id']  = Auth::id();
        $data['is_active'] = true;
        $data['progress'] = 0;

        // Simpan Mata Kuliah
        $subject = Subject::create($data);

        // --- AUTOMATISASI 16 SESI KULIAH ---
        $currentDate = Carbon::parse($request->start_date);

        for ($i = 1; $i <= 16; $i++) {
            $type = 'regular';
            $title = "Pertemuan $i";

            if ($i == 8) {
                $type = 'uts';
                $title = "Ujian Tengah Semester (UTS)";
            } elseif ($i == 16) {
                $type = 'uas';
                $title = "Ujian Akhir Semester (UAS)";
            }

            SubjectSession::create([
                'subject_id'     => $subject->id,
                'session_number' => $i,
                'date'           => $currentDate->format('Y-m-d'),
                'type'           => $type,
                'status'         => 'scheduled',
                'title'          => $title,
            ]);

            // Tambah 1 minggu untuk sesi berikutnya
            $currentDate->addWeek();
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Mata kuliah & 16 Jadwal Sesi berhasil di-generate.']);
        }
        return back()->with('success', 'Mata kuliah & Jadwal Sesi berhasil ditambahkan.');
    }

    public function updateCourse(Request $request, $id)
    {
        $subject = Subject::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $data = $request->validate([
            'code'        => 'nullable|string|max:20',
            'name'        => 'required|string|max:255',
            'sks'         => 'required|integer|min:1|max:6',
            'lecturer'    => 'nullable|string|max:255',
            'day_of_week' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i',
            'room'        => 'nullable|string|max:100',
            'drive_link'  => 'nullable|url|max:500',
            'notes'       => 'nullable|string|max:1000',
            'progress'    => 'nullable|integer|min:0|max:100',
            'semester'    => 'nullable|integer|min:1|max:14',
        ]);

        $subject->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Mata kuliah berhasil diperbarui.']);
        }
        return back()->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    // Fungsi untuk mengubah status sesi menjadi Selesai
    public function completeSession($id)
    {
        $session = SubjectSession::findOrFail($id);

        $newStatus = $session->status === 'completed' ? 'scheduled' : 'completed';
        $session->update(['status' => $newStatus]);

        // Update progress mata kuliah otomatis
        $subject = $session->subject;
        $completedCount = $subject->sessions()->where('status', 'completed')->count();
        $progress = round(($completedCount / 16) * 100);
        $subject->update(['progress' => $progress]);

        return response()->json([
            'success' => true,
            'message' => $newStatus === 'completed' ? 'Sesi Selesai!' : 'Sesi di-reset',
            'progress' => $progress
        ]);
    }

    // Fungsi untuk memproses Atur Ulang Jadwal (Reschedule/Libur)
    public function updateSessionSchedule(Request $request, $id)
    {
        $session = SubjectSession::findOrFail($id);
        $action = $request->action; // 'reschedule', 'tba', 'holiday', 'revert'

        // Bersihkan title dari label status sebelumnya agar tidak menumpuk
        $cleanTitle = str_replace([' (Libur)', ' (Menunggu Jadwal)', ' (Kelas Pengganti)'], '', $session->title);

        if ($action === 'reschedule') {
            $request->validate(['new_date' => 'required|date']);
            $session->update([
                'date' => $request->new_date,
                'status' => 'scheduled',
                'title' => $cleanTitle . ' (Kelas Pengganti)'
            ]);
            $msg = 'Sesi berhasil dijadwalkan ulang ke tanggal baru.';
        } elseif ($action === 'tba') {
            $session->update([
                'status' => 'holiday', // Kita pakai 'holiday' agar di UI menjadi abu-abu
                'title' => $cleanTitle . ' (Menunggu Jadwal)'
            ]);
            $msg = 'Sesi ditandai diundur (menunggu konfirmasi jadwal).';
        } elseif ($action === 'holiday') {
            $session->update([
                'status' => 'holiday',
                'title' => $cleanTitle . ' (Libur)'
            ]);
            $msg = 'Sesi ditandai libur tetap tanpa pengganti.';
        } elseif ($action === 'revert') {
            $session->update([
                'status' => 'scheduled',
                'title' => $cleanTitle
            ]);
            $msg = 'Status sesi dikembalikan menjadi normal.';
        }

        return response()->json([
            'success' => true,
            'message' => $msg
        ]);
    }
    public function destroyCourse(Request $request, $id)
    {
        $subject = Subject::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // Soft-nullify linked tasks so they still exist
        Task::where('linked_subject_id', $id)->update(['linked_subject_id' => null]);

        $subject->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Mata kuliah berhasil dihapus.']);
        }
        return back()->with('success', 'Mata kuliah berhasil dihapus.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TASKS CRUD
    // ─────────────────────────────────────────────────────────────────────────
    public function storeTask(Request $request)
    {
        $data = $request->validate([
            'title'             => 'required|string|max:255',
            'linked_subject_id' => 'nullable|integer',
            'task_type'         => 'nullable|string|max:50',
            'due_date'          => 'nullable|date',
            'priority'          => 'nullable|string',
            'notes'             => 'nullable|string|max:1000',
            'drive_link'        => 'nullable|url|max:500',
        ]);

        // Validasi linked_subject_id belongs to user
        if (!empty($data['linked_subject_id'])) {
            $exists = Subject::where('id', $data['linked_subject_id'])
                ->where('user_id', Auth::id())->exists();
            if (!$exists) $data['linked_subject_id'] = null;
        }

        Task::create([
            'user_id'           => Auth::id(),
            'title'             => $data['title'],
            'category'          => 'academic',
            'task_type'         => $data['task_type'] ?? 'assignment',
            'linked_subject_id' => $data['linked_subject_id'] ?? null,
            'due_date'          => $data['due_date'] ? Carbon::parse($data['due_date']) : null,
            'priority'          => $data['priority'] ?? 'medium',
            'status'            => 'todo',
            'notes'             => $data['notes'] ?? null,
            'drive_link'        => $data['drive_link'] ?? null,
            'progress'          => 0,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Tugas berhasil ditambahkan.']);
        }
        return back()->with('success', 'Tugas berhasil ditambahkan.');
    }
    public function updateTask(Request $request, $id)
    {
        $task = Task::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $data = $request->validate([
            'title'             => 'required|string|max:255',
            'linked_subject_id' => 'nullable|integer',
            'task_type'         => 'nullable|string|max:50',
            'due_date'          => 'nullable|date',
            'priority'          => 'nullable|string',
            'status'            => 'nullable|in:todo,doing,done',
            'notes'             => 'nullable|string|max:1000',
            'drive_link'        => 'nullable|url|max:500',
        ]);

        

        $task->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Tugas berhasil diperbarui.']);
        }
        return back()->with('success', 'Tugas berhasil diperbarui.');
    }

    public function updateTaskStatus(Request $request, $id)
    {
        $task = Task::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $newStatus = $task->status === 'done' ? 'todo' : 'done';
        $task->update([
            'status'       => $newStatus,
            'progress'     => $newStatus === 'done' ? 100 : $task->progress,
            'completed_at' => $newStatus === 'done' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
            'message' => $newStatus === 'done' ? 'Tugas ditandai selesai!' : 'Status direset.',
        ]);
    }

    public function destroyTask(Request $request, $id)
    {
        Task::where('id', $id)->where('user_id', Auth::id())->firstOrFail()->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Tugas berhasil dihapus.']);
        }
        return back()->with('success', 'Tugas berhasil dihapus.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // THESIS MILESTONES CRUD
    // ─────────────────────────────────────────────────────────────────────────
    public function storeMilestone(Request $request)
    {
        $data = $request->validate([
            'label'       => 'required|string|max:255',
            'target_date' => 'nullable|date',
            'sort_order'  => 'nullable|integer',
        ]);

        ThesisMilestone::create([
            'user_id'    => Auth::id(),
            'label'      => $data['label'],
            'target_date' => $data['target_date'] ?? null,
            'sort_order' => $data['sort_order'] ?? 999,
            'done'       => false,
            'is_active'  => false,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Milestone berhasil ditambahkan.']);
        }
        return back()->with('success', 'Milestone berhasil ditambahkan.');
    }

    public function updateMilestone(Request $request, $id)
    {
        $ms = ThesisMilestone::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $data = $request->validate([
            'label'       => 'required|string|max:255',
            'target_date' => 'nullable|date',
            'done'        => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $ms->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Milestone diperbarui.']);
        }
        return back()->with('success', 'Milestone berhasil diperbarui.');
    }

    public function toggleMilestone(Request $request, $id)
    {
        $ms = ThesisMilestone::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $ms->update(['done' => !$ms->done]);

        return response()->json([
            'success' => true,
            'done'    => $ms->fresh()->done,
            'message' => $ms->fresh()->done ? 'Milestone selesai!' : 'Milestone di-reset.',
        ]);
    }

    public function destroyMilestone(Request $request, $id)
    {
        ThesisMilestone::where('id', $id)->where('user_id', Auth::id())->firstOrFail()->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Milestone dihapus.']);
        }
        return back()->with('success', 'Milestone berhasil dihapus.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────
    private function mapPriority(?string $raw): string
    {
        return match ($raw) {
            'urgent-important'         => 'high',
            'important-not-urgent'     => 'medium',
            'urgent-not-important'     => 'medium',
            'not-urgent-not-important' => 'low',
            'high', 'medium', 'low'    => $raw,
            default                    => 'medium',
        };
    }
}
