<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\ProductivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProductivityController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $weekStart = now()->startOfWeek();
        $weekEnd   = now()->endOfWeek();

        // Weekly productivity: tasks done per day
        $weekly = [];
        $days   = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $done = $user->tasks()
                ->whereDate('updated_at', $date)
                ->where('status', 'done')
                ->count();
            $planned = $user->tasks()
                ->whereDate('due_date', $date)
                ->count();

            // Focus from productivity_logs if available
            $log = $user->productivityLogs()
                ->whereDate('log_date', $date)
                ->first();
            $focus = $log ? $log->focus_score : ($done > 0 ? min(95, 60 + ($done * 5)) : 55);

            $weekly[] = [
                'day'     => $days[$i],
                'done'    => $done,
                'planned' => max($done, $planned),
                'focus'   => $focus,
            ];
        }

        $totalDone    = collect($weekly)->sum('done');
        $totalPlanned = collect($weekly)->sum('planned');
        $avgFocus     = round(collect($weekly)->avg('focus'));
        $bestDay      = collect($weekly)->sortByDesc('focus')->first();
        $completionPct = $totalPlanned > 0 ? round(($totalDone / $totalPlanned) * 100) : 0;

        // Categories
        $categories = [
            ['name' => 'Akademik',        'done' => $this->countDone($user, 'academic'),   'total' => $this->countTotal($user, 'academic'),   'color' => '#3b82f6'],
            ['name' => 'Proyek Kreatif',  'done' => $this->countDone($user, 'creative'),   'total' => $this->countTotal($user, 'creative'),   'color' => '#f97316'],
            ['name' => 'PKL',             'done' => $this->countDone($user, 'pkl'),         'total' => $this->countTotal($user, 'pkl'),         'color' => '#10b981'],
            ['name' => 'Skripsi',         'done' => $this->countDone($user, 'skripsi'),     'total' => $this->countTotal($user, 'skripsi'),     'color' => '#8b5cf6'],
            ['name' => 'Personal',        'done' => $this->countDone($user, 'personal'),    'total' => $this->countTotal($user, 'personal'),    'color' => '#64748b'],
        ];

        return view('dashboard.productivity', compact(
            'weekly',
            'categories',
            'totalDone',
            'totalPlanned',
            'avgFocus',
            'bestDay',
            'completionPct'
        ));
    }

    private function countDone($user, string $category): int
    {
        return $user->tasks()->where('category', $category)->where('status', 'done')->count();
    }

    private function countTotal($user, string $category): int
    {
        return max(1, $user->tasks()->where('category', $category)->count());
    }

    // Store productivity log entry
    public function store(Request $request)
    {
        $data = $request->validate([
            'date'        => 'required|date',
            'focus_score' => 'required|integer|min:0|max:100',
            'mood'        => 'nullable|string|max:50',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $data['user_id'] = Auth::id();

        ProductivityLog::updateOrCreate(
            ['user_id' => $data['user_id'], 'log_date' => $data['date']],
            array_merge($data, ['log_date' => $data['date']])
        );

        return back()->with('success', 'Log produktivitas disimpan.');
    }
}
