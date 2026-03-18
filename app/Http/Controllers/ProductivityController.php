<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\PklLog;
use App\Models\ProductivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ProductivityController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $range = $request->get('range', 'week');

        [$startDate, $endDate] = $this->getDateRange($range);

        // ── Stats utama ─────────────────────────────────────────────────────
        $totalDone    = $user->tasks()
            ->where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $totalPlanned = $user->tasks()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $completionPct = $totalPlanned > 0
            ? min(100, round(($totalDone / $totalPlanned) * 100))
            : 0;

        // PKL jam terselesaikan
        $pklHours = (float) ($user->pklLogs()
            ->where('status', 'done')
            ->whereBetween('log_date', [$startDate, $endDate])
            ->sum('hours') ?? 0);

        // Tugas akademik dikumpulkan
        $academicDone = $user->tasks()
            ->whereIn('category', ['academic', 'skripsi', 'Academic'])
            ->where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        // ── Weekly data (7 hari untuk grafik) ───────────────────────────────
        $weekly = $this->buildWeeklyData($user, $startDate, $endDate, $range);

        // ── Avg focus score & best day ───────────────────────────────────────
        $avgFocus = collect($weekly)->avg('focus') ?? 0;
        $bestDayArr = collect($weekly)->sortByDesc('focus')->first()
            ?? ['day' => '-', 'focus' => 0];

        // ── Categories breakdown ─────────────────────────────────────────────
        $categories = $this->buildCategoryBreakdown($user, $startDate, $endDate);

        // ── History: ProductivityLog + recent done tasks ─────────────────────
        $history = $user->tasks()
            ->where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->orderByDesc('updated_at')
            ->select(['id', 'title', 'category', 'priority', 'status', 'updated_at', 'due_date'])
            ->paginate(10)
            ->withQueryString();

        return view('dashboard.productivity', compact(
            'weekly',
            'categories',
            'totalDone',
            'totalPlanned',
            'completionPct',
            'avgFocus',
            'bestDayArr',
            'pklHours',
            'academicDone',
            'history',
            'range',
            'startDate',
            'endDate'
        ));
    }

    // ── Private Helpers ──────────────────────────────────────────────────────

    private function getDateRange(string $range): array
    {
        $end   = now()->endOfDay();
        $start = match($range) {
            'month' => now()->startOfMonth(),
            'year'  => now()->startOfYear(),
            default => now()->startOfWeek(), // 'week'
        };
        return [$start, $end];
    }

    private function buildWeeklyData($user, Carbon $start, Carbon $end, string $range): array
    {
        $dayNames = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
        $data     = [];

        if ($range === 'week') {
            // 7 hari (Senin–Minggu minggu ini)
            $period = CarbonPeriod::create(
                now()->startOfWeek(),
                now()->endOfWeek()
            );
            foreach ($period as $date) {
                $done    = $user->tasks()
                    ->where('status', 'done')
                    ->whereDate('updated_at', $date)
                    ->count();
                $planned = $user->tasks()
                    ->whereDate('created_at', $date)
                    ->count();

                // Focus score: dari ProductivityLog jika ada, kalau tidak hitung dari completion
                $log = $user->productivityLogs()
                    ->whereDate('log_date', $date)
                    ->first();
                $focus = $log?->focus_score
                    ?? ($planned > 0 ? min(100, round(($done / max(1,$planned)) * 100)) : 0);

                $data[] = [
                    'day'     => $date->isoFormat('ddd'),
                    'date'    => $date->format('Y-m-d'),
                    'done'    => $done,
                    'planned' => max($done, $planned),
                    'focus'   => (int)$focus,
                ];
            }
        } elseif ($range === 'month') {
            // Minggu ke-1 sampai ke-4 bulan ini
            for ($w = 1; $w <= 4; $w++) {
                $wStart = now()->startOfMonth()->addWeeks($w - 1);
                $wEnd   = (clone $wStart)->addDays(6)->endOfDay();
                $done    = $user->tasks()->where('status','done')->whereBetween('updated_at', [$wStart,$wEnd])->count();
                $planned = $user->tasks()->whereBetween('created_at', [$wStart,$wEnd])->count();
                $focus   = $planned > 0 ? min(100, round(($done / max(1,$planned)) * 80)) : 0;
                $data[]  = [
                    'day'     => "Minggu {$w}",
                    'date'    => $wStart->format('Y-m-d'),
                    'done'    => $done,
                    'planned' => max($done, $planned),
                    'focus'   => $focus,
                ];
            }
        } else {
            // Year: per bulan
            $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
            for ($m = 1; $m <= 12; $m++) {
                $mStart = Carbon::create(now()->year, $m, 1)->startOfMonth();
                $mEnd   = $mStart->copy()->endOfMonth();
                $done    = $user->tasks()->where('status','done')->whereBetween('updated_at',[$mStart,$mEnd])->count();
                $planned = $user->tasks()->whereBetween('created_at',[$mStart,$mEnd])->count();
                $focus   = $planned > 0 ? min(100, round(($done / max(1,$planned)) * 85)) : 0;
                $data[]  = [
                    'day'     => $months[$m - 1],
                    'date'    => $mStart->format('Y-m-d'),
                    'done'    => $done,
                    'planned' => max($done, $planned),
                    'focus'   => $focus,
                ];
            }
        }

        return $data;
    }

    private function buildCategoryBreakdown($user, Carbon $start, Carbon $end): array
    {
        $colorMap = [
            'Skripsi'          => '#8b5cf6',
            'skripsi'          => '#8b5cf6',
            'Creative'         => '#f97316',
            'creative'         => '#f97316',
            'PKL'              => '#10b981',
            'Academic'         => '#3b82f6',
            'academic'         => '#3b82f6',
            'Personal'         => '#64748b',
            'personal'         => '#64748b',
            'Kesehatan'        => '#ef4444',
            'health'           => '#ef4444',
            'Pengembangan Diri'=> '#06b6d4',
            'Organisasi'       => '#f59e0b',
            'Freelance'        => '#a855f7',
            'Shutterstock'     => '#ec4899',
        ];

        $rows = $user->tasks()
            ->selectRaw('category, COUNT(*) as total, SUM(CASE WHEN status="done" THEN 1 ELSE 0 END) as done')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return $rows->map(fn($r) => [
            'name'  => $r->category,
            'total' => (int)$r->total,
            'done'  => (int)$r->done,
            'color' => $colorMap[$r->category] ?? '#6b7280',
        ])->values()->toArray();
    }
}
