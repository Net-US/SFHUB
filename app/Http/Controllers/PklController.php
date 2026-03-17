<?php

namespace App\Http\Controllers;

use App\Models\PklInfo;
use App\Models\PklSchedule;
use App\Models\PklLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PklController extends Controller
{
    // ── INDEX ──────────────────────────────────────────────────────────────
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $pklInfo = $user->activePklInfo;

        $schedules = $user->pklSchedules()
            ->orderByRaw("FIELD(day, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->get();

        $activities = $user->pklLogs()
            ->latest('log_date')
            ->take(50)
            ->get();

        $hoursDone = $activities->where('status', 'done')->sum('hours');

        // Compute derived data
        $daysLeft = 0;
        $pctDone  = 0;
        $calPct   = 0;
        $start    = null;
        $end      = null;

        if ($pklInfo) {
            $daysLeft = $pklInfo->getDaysLeft();
            $pctDone  = $pklInfo->getProgressPercentage($hoursDone);
            $calPct   = $pklInfo->getCalendarProgressPercentage();
            $start    = $pklInfo->start_date;
            $end      = $pklInfo->end_date;
        }

        // Build pklInfo array (compatible with blade expectations)
        $pklInfoArr = [
            'company'        => $pklInfo?->company ?? '-',
            'department'     => $pklInfo?->department ?? '-',
            'supervisor'     => $pklInfo?->supervisor ?? '-',
            'supervisor_hp'  => $pklInfo?->supervisor_phone ?? '-',
            'address'        => $pklInfo?->address ?? '-',
            'start_date'     => $pklInfo?->start_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'end_date'       => $pklInfo?->end_date?->format('Y-m-d') ?? now()->addMonths(6)->format('Y-m-d'),
            'hours_required' => $pklInfo?->hours_required ?? 720,
            'hours_done'     => $hoursDone,
            'allowance'      => $pklInfo?->allowance ?? 0,
        ];

        // Schedule data for blade
        $schedule = collect($schedules)->map(fn($s) => [
            'day'   => $s->day,
            'type'  => $s->type,
            'start' => $s->start_time ? \Carbon\Carbon::parse($s->start_time)->format('H:i') : '08:00',
            'end'   => $s->end_time ? \Carbon\Carbon::parse($s->end_time)->format('H:i') : '17:00',
            'notes' => $s->notes,
        ])->values()->all();

        // If no schedules, provide default
        if (empty($schedule)) {
            $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            $schedule = collect($days)->map(fn($d) => [
                'day'   => $d,
                'type'  => in_array($d, ['Sabtu', 'Minggu']) ? 'off' : 'full',
                'start' => '08:00',
                'end'   => '17:00',
                'notes' => '',
            ])->all();
        }

        // Activities for blade
        $activities = $user->pklLogs()
            ->latest('log_date')
            ->take(50)
            ->get()
            ->map(fn($a) => [
                'id'     => $a->id,
                'date'   => $a->log_date->format('Y-m-d'),
                'task'   => $a->task,
                'hours'  => $a->hours,
                'category' => $a->category,
                'notes'  => $a->notes,
                'status' => $a->status,
            ])
            ->all();

        // Default dates for view
        $start = $pklInfo?->start_date ?? now();
        $end   = $pklInfo?->end_date ?? now()->addMonths(6);

        return view('dashboard.pkl', compact(
            'pklInfoArr',
            'schedule',
            'activities',
            'hoursDone',
            'daysLeft',
            'pctDone',
            'calPct',
            'start',
            'end'
        ));
    }

    // ── PKL INFO CRUD ──────────────────────────────────────────────────────
    public function storePklInfo(Request $request)
    {
        $data = $request->validate([
            'company'          => 'required|string|max:255',
            'department'       => 'nullable|string|max:255',
            'supervisor'       => 'nullable|string|max:255',
            'supervisor_phone' => 'nullable|string|max:30',
            'address'          => 'nullable|string|max:500',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date',
            'hours_required'   => 'nullable|integer|min:1',
            'allowance'        => 'nullable|numeric|min:0',
        ]);

        $data['user_id']   = Auth::id();
        $data['is_active'] = true;

        // Deactivate previous active info
        PklInfo::where('user_id', Auth::id())->where('is_active', true)->update(['is_active' => false]);

        PklInfo::create($data);

        return back()->with('success', 'Info PKL berhasil disimpan.');
    }

    public function updatePklInfo(Request $request, $id)
    {
        $pklInfo = PklInfo::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $data = $request->validate([
            'company'          => 'required|string|max:255',
            'department'       => 'nullable|string|max:255',
            'supervisor'       => 'nullable|string|max:255',
            'supervisor_phone' => 'nullable|string|max:30',
            'address'          => 'nullable|string|max:500',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date',
            'hours_required'   => 'nullable|integer|min:1',
            'allowance'        => 'nullable|numeric|min:0',
        ]);

        $pklInfo->update($data);

        return back()->with('success', 'Info PKL berhasil diperbarui.');
    }

    // ── PKL SCHEDULE CRUD ─────────────────────────────────────────────────
    public function updateSchedule(Request $request)
    {
        $scheduleData = $request->validate([
            'schedules'              => 'required|array',
            'schedules.*.day'        => 'required|string',
            'schedules.*.type'       => 'required|in:full,half,off',
            'schedules.*.start_time' => 'nullable|string',
            'schedules.*.end_time'   => 'nullable|string',
            'schedules.*.notes'      => 'nullable|string|max:200',
        ]);

        $userId = Auth::id();

        // Delete existing schedules
        PklSchedule::where('user_id', $userId)->delete();

        // Create new schedules
        foreach ($scheduleData['schedules'] as $sched) {
            PklSchedule::create([
                'user_id'    => $userId,
                'day'        => $sched['day'],
                'type'       => $sched['type'],
                'start_time' => $sched['start_time'] ?? null,
                'end_time'   => $sched['end_time'] ?? null,
                'notes'      => $sched['notes'] ?? null,
            ]);
        }

        return back()->with('success', 'Jadwal PKL berhasil disimpan.');
    }

    // ── PKL ACTIVITY LOG CRUD ─────────────────────────────────────────────
    public function storeActivity(Request $request)
    {
        $data = $request->validate([
            'task'     => 'required|string|max:500',
            'log_date' => 'required|date',
            'hours'    => 'required|numeric|min:0.5|max:24',
            'category' => 'required|string|max:100',
            'notes'    => 'nullable|string|max:1000',
        ]);

        $data['user_id'] = Auth::id();
        $data['status']  = 'done';

        PklLog::create($data);

        return back()->with('success', 'Aktivitas PKL berhasil dicatat.');
    }

    public function updateActivity(Request $request, $id)
    {
        $log = PklLog::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $data = $request->validate([
            'task'     => 'required|string|max:500',
            'log_date' => 'required|date',
            'hours'    => 'required|numeric|min:0.5|max:24',
            'category' => 'required|string|max:100',
            'notes'    => 'nullable|string|max:1000',
            'status'   => 'nullable|in:done,todo,in_progress',
        ]);

        $log->update($data);

        return back()->with('success', 'Aktivitas PKL berhasil diperbarui.');
    }

    public function destroyActivity($id)
    {
        PklLog::where('id', $id)->where('user_id', Auth::id())->firstOrFail()->delete();
        return back()->with('success', 'Aktivitas PKL berhasil dihapus.');
    }
}
