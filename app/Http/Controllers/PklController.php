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
    // ── INDEX ─────────────────────────────────────────────────────────────
    public function index()
    {
        /** @var \App\Models\User $user */
        $user    = Auth::user();
       // ✅ PERBAIKAN: Query langsung ke database agar bebas dari cache (langsung update di Blade)
        $pklInfo = \App\Models\PklInfo::where('user_id', $user->id)->where('is_active', true)->first();

        // Schedules
        $schedules = $user->pklSchedules()
            ->orderByRaw("FIELD(day,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->get();

        // Activities (last 50)
        $logsQuery = $user->pklLogs()->latest('log_date');
        $hoursDone = $user->pklLogs()->where('status', 'done')->sum('hours');

        $daysLeft = $pklInfo?->getDaysLeft() ?? 0;
        $pctDone  = $pklInfo?->getProgressPercentage((int)$hoursDone) ?? 0;
        $calPct   = $pklInfo?->getCalendarProgressPercentage() ?? 0;
        $start    = $pklInfo?->start_date ?? now();
        $end      = $pklInfo?->end_date   ?? now()->addMonths(6);

        // Build pklInfoArr for blade
        $pklInfoArr = [
            'id'             => $pklInfo?->id,
            'company'        => $pklInfo?->company        ?? '-',
            'department'     => $pklInfo?->department     ?? '-',
            'supervisor'     => $pklInfo?->supervisor     ?? '-',
            'supervisor_hp'  => $pklInfo?->supervisor_phone ?? '-',
            'address'        => $pklInfo?->address        ?? '-',
            'start_date'     => $pklInfo?->start_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'end_date'       => $pklInfo?->end_date?->format('Y-m-d')   ?? now()->addMonths(6)->format('Y-m-d'),
            'hours_required' => $pklInfo?->hours_required ?? 720,
            'hours_done'     => (float)$hoursDone,
            'allowance'      => $pklInfo?->allowance ?? 0,
        ];

       // Build schedule array — termasuk split_shift
        // ✅ PERBAIKAN: Gunakan strtotime agar kebal membaca format H:i:s dari MySQL
        $fmt = fn($t) => $t ? date('H:i', strtotime($t)) : '';

        $schedule = $schedules->map(fn($s) => [
            'id'          => $s->id,
            'day'         => $s->day,
            'type'        => $s->type ?? 'full',
            'start'       => $fmt($s->start_time) ?: '08:00',
            'end'         => $fmt($s->end_time)   ?: '17:00',
            'start2'      => $fmt($s->start_time_2),
            'end2'        => $fmt($s->end_time_2),
            'notes'       => $s->notes ?? '',
            'has_split'   => !empty($s->start_time_2) && !empty($s->end_time_2),
            'total_hours' => $s->getTotalHours(),
        ])->values()->all();

        // Default schedule jika kosong
        if (empty($schedule)) {
            $days    = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            $schedule = collect($days)->map(fn($d) => [
                'id'        => null,
                'day'       => $d,
                'type'      => in_array($d, ['Sabtu', 'Minggu']) ? 'off' : 'full',
                'start'     => '08:00',
                'end'       => '17:00',
                'start2'    => '',
                'end2'      => '',
                'notes'     => '',
                'has_split' => false,
                'total_hours' => in_array($d, ['Sabtu', 'Minggu']) ? 0 : 9.0,
            ])->all();
        }

        // Activities array for blade
        $activities = $logsQuery->take(50)->get()->map(fn($a) => [
            'id'       => $a->id,
            'date'     => $a->log_date->format('Y-m-d'),
            'task'     => $a->task,
            'hours'    => (float)$a->hours,
            'category' => $a->category,
            'notes'    => $a->notes ?? '',
            'status'   => $a->status,
        ])->all();

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

    // ── STORE / UPDATE PKL INFO ───────────────────────────────────────────
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

        $userId = Auth::id();

        // Cek apakah sudah ada — jika ada, update; kalau belum, create
        $existing = PklInfo::where('user_id', $userId)->where('is_active', true)->first();

        if ($existing) {
            $existing->update($data);
            $message = 'Info PKL berhasil diperbarui.';
        } else {
            PklInfo::where('user_id', $userId)->update(['is_active' => false]);
            PklInfo::create(array_merge($data, ['user_id' => $userId, 'is_active' => true]));
            $message = 'Info PKL berhasil disimpan.';
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return back()->with('success', $message);
    }

    public function updatePklInfo(Request $request, $id)
    {
        $pklInfo = PklInfo::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $data    = $request->validate([
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

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Info PKL diperbarui.']);
        }
        return back()->with('success', 'Info PKL berhasil diperbarui.');
    }

    // ── UPDATE SCHEDULE (SPLIT SHIFT SUPPORTED) ───────────────────────────
    public function updateSchedule(Request $request)
    {
        $validated = $request->validate([
            'schedules'                  => 'required|array',
            'schedules.*.day'            => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'schedules.*.type'           => 'required|in:full,half,off,split',
            // ✅ UBAH date_format:H:i MENJADI string
            'schedules.*.start_time'     => 'nullable|string',
            'schedules.*.end_time'       => 'nullable|string',
            'schedules.*.start_time_2'   => 'nullable|string',
            'schedules.*.end_time_2'     => 'nullable|string',
            'schedules.*.notes'          => 'nullable|string|max:200',
        ]);

        $userId = Auth::id();
        PklSchedule::where('user_id', $userId)->delete();

        foreach ($validated['schedules'] as $sched) {
            $type = $sched['type'];
            $isOff = ($type === 'off');

            // ✅ LOGIKA PENYELAMAT JAM (Cek string kosong agar tidak jadi null yang merusak tampilan)
            $st1 = !empty($sched['start_time']) ? $sched['start_time'] : '08:00';
            $en1 = !empty($sched['end_time']) ? $sched['end_time'] : '17:00';

            PklSchedule::create([
                'user_id'      => $userId,
                'day'          => $sched['day'],
                'type'         => $type,
                'start_time'   => !$isOff ? $st1 : null,
                'end_time'     => !$isOff ? $en1 : null,
                'start_time_2' => ($type === 'split') ? ($sched['start_time_2'] ?? null) : null,
                'end_time_2'   => ($type === 'split') ? ($sched['end_time_2'] ?? null) : null,
                'notes'        => $sched['notes'] ?? null,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Jadwal PKL berhasil disimpan.']);
        }
        return back()->with('success', 'Jadwal PKL berhasil disimpan.');
    }

    // ── STORE ACTIVITY ────────────────────────────────────────────────────
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

        $data['start_time'] = '00:00:00';
        $data['end_time']   = '00:00:00';
        $data['activity']   = $data['task'];

        PklLog::create($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Aktivitas PKL berhasil dicatat.']);
        }
        return back()->with('success', 'Aktivitas PKL berhasil dicatat.');
    }

    public function updateActivity(Request $request, $id)
    {
        $log  = PklLog::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $data = $request->validate([
            'task'     => 'required|string|max:500',
            'log_date' => 'required|date',
            'hours'    => 'required|numeric|min:0.5|max:24',
            'category' => 'required|string|max:100',
            'notes'    => 'nullable|string|max:1000',
            'status'   => 'nullable|in:done,todo,in_progress',
        ]);
        $log->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Aktivitas diperbarui.']);
        }
        return back()->with('success', 'Aktivitas PKL berhasil diperbarui.');
    }

    public function destroyActivity(Request $request, $id)
    {
        PklLog::where('id', $id)->where('user_id', Auth::id())->firstOrFail()->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Aktivitas dihapus.']);
        }
        return back()->with('success', 'Aktivitas PKL berhasil dihapus.');
    }
}
