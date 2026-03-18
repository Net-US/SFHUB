{{-- resources/views/dashboard/productivity.blade.php --}}
@extends('layouts.app-dashboard')
@section('title', 'Analytics | StudentHub')
@section('page-title', 'Analytics & Produktivitas')

@push('styles')
    <style>
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(12px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .fade-up {
            animation: fadeUp .4s ease-out both
        }

        .range-btn {
            padding: .45rem .9rem;
            border-radius: .65rem;
            font-size: .78rem;
            font-weight: 600;
            border: 1.5px solid #e7e5e4;
            color: #78716c;
            background: transparent;
            cursor: pointer;
            transition: all .15s
        }

        .range-btn:hover,
        .range-btn.active {
            background: #1c1917;
            color: #fff;
            border-color: #1c1917
        }

        .dark .range-btn {
            border-color: #44403c;
            color: #a8a29e
        }

        .dark .range-btn:hover,
        .dark .range-btn.active {
            background: #f5f5f4;
            color: #1c1917;
            border-color: #f5f5f4
        }
    </style>
@endpush

@section('content')
    @php
        $weekly = $weekly ?? [];
        $categories = $categories ?? [];
        $totalDone = $totalDone ?? 0;
        $totalPlanned = $totalPlanned ?? 0;
        $completionPct = $completionPct ?? 0;
        $avgFocus = $avgFocus ?? 0;
        $bestDayArr = $bestDayArr ?? ['day' => '-', 'focus' => 0];
        $pklHours = $pklHours ?? 0;
        $academicDone = $academicDone ?? 0;
        $range = $range ?? 'week';
        $startDate = $startDate ?? now()->startOfWeek();
        $endDate = $endDate ?? now()->endOfDay();

        $rangeLabel = match ($range) {
            'month' => 'Bulan Ini',
            'year' => 'Tahun Ini',
            default => 'Minggu Ini',
        };
    @endphp

    <div class="fade-up space-y-5">

        {{-- Header + Filter ─────────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Analytics & Produktivitas</h2>
                <p class="text-stone-400 text-xs">{{ $startDate->isoFormat('D MMM YYYY') }} –
                    {{ $endDate->isoFormat('D MMM YYYY') }}</p>
            </div>
            {{-- Range Filter --}}
            <div class="flex gap-2">
                @foreach (['week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini'] as $k => $v)
                    <a href="{{ request()->fullUrlWithQuery(['range' => $k]) }}"
                        class="range-btn {{ $range === $k ? 'active' : '' }}">{{ $v }}</a>
                @endforeach
            </div>
        </div>

        {{-- Hero Summary ─────────────────────────────────────────────────────── --}}
        <div class="bg-gradient-to-r from-indigo-600 to-violet-600 rounded-2xl p-6 text-white shadow-xl">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-5">
                <div>
                    <h2 class="text-xl font-bold mb-1">Ringkasan Produktivitas — {{ $rangeLabel }}</h2>
                    <p class="text-indigo-200 text-sm">Data dari database · {{ now()->isoFormat('D MMMM YYYY, H:mm') }}</p>
                </div>
                <div class="flex gap-4 sm:gap-6 text-center flex-wrap">
                    @foreach ([[$completionPct . '%', 'Completion Rate'], [round($avgFocus) . '%', 'Avg Focus'], [ucfirst($bestDayArr['day'] ?? '-'), 'Peak Day'], [$totalDone . ' / ' . $totalPlanned, 'Tasks Done'], [$pklHours . 'j', 'Jam PKL'], [$academicDone, 'Tugas Akad.']] as [$v, $l])
                        <div>
                            <p class="text-2xl font-bold">{{ $v }}</p>
                            <p class="text-xs text-indigo-200 uppercase tracking-wide">{{ $l }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Charts 2-col ─────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
                <h3 class="font-bold text-stone-800 dark:text-white mb-4 text-sm">
                    📈 Tren Produktivitas — {{ $rangeLabel }}
                </h3>
                <div style="position:relative;height:220px">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
                <h3 class="font-bold text-stone-800 dark:text-white mb-4 text-sm">
                    🗂️ Komposisi per Kategori
                </h3>
                @if (empty($categories))
                    <div class="flex items-center justify-center h-52 text-stone-300">
                        <div class="text-center">
                            <i class="fa-solid fa-chart-pie text-4xl mb-2 block"></i>
                            <p class="text-sm">Belum ada data kategori</p>
                        </div>
                    </div>
                @else
                    <div style="position:relative;height:220px">
                        <canvas id="catChart"></canvas>
                    </div>
                @endif
            </div>
        </div>

        {{-- Category Progress ───────────────────────────────────────────────── --}}
        @if (!empty($categories))
            <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
                <h3 class="font-bold text-stone-800 dark:text-white mb-5 text-sm">Progress per Kategori</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($categories as $c)
                        @php $pct = $c['total'] > 0 ? round(($c['done'] / $c['total']) * 100) : 0; @endphp
                        <div class="p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                            <div class="flex justify-between items-center mb-2">
                                <span
                                    class="text-sm font-semibold text-stone-700 dark:text-stone-300">{{ $c['name'] }}</span>
                                <span class="text-sm font-bold text-stone-800 dark:text-white">{{ $pct }}%</span>
                            </div>
                            <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2 mb-2">
                                <div class="h-2 rounded-full transition-all"
                                    style="width:{{ $pct }}%;background:{{ $c['color'] }}"></div>
                            </div>
                            <p class="text-xs text-stone-400">{{ $c['done'] }}/{{ $c['total'] }} tugas selesai</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Daily Table ─────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
            <h3 class="font-bold text-stone-800 dark:text-white mb-4 text-sm">Detail per Periode</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-stone-200 dark:border-stone-700">
                            @foreach (['Periode', 'Selesai', 'Direncanakan', 'Completion', 'Focus Score', 'Status'] as $h)
                                <th class="text-left py-3 pr-4 text-stone-500 dark:text-stone-400 font-medium">
                                    {{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($weekly as $d)
                            @php
                                $cr = ($d['planned'] ?? 0) > 0 ? round(($d['done'] / $d['planned']) * 100) : 0;
                                $fs = $d['focus'] ?? 0;
                                $statusCls =
                                    $fs >= 80
                                        ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                                        : ($fs >= 60
                                            ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'
                                            : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400');
                                $statusLbl = $fs >= 80 ? 'Optimal' : ($fs >= 60 ? 'Cukup' : 'Perlu Perbaikan');
                            @endphp
                            <tr
                                class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">
                                <td class="py-3 pr-4 font-semibold text-stone-800 dark:text-white">{{ $d['day'] }}</td>
                                <td class="py-3 pr-4 text-stone-700 dark:text-stone-300">{{ $d['done'] ?? 0 }}</td>
                                <td class="py-3 pr-4 text-stone-700 dark:text-stone-300">{{ $d['planned'] ?? 0 }}</td>
                                <td class="py-3 pr-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-stone-100 dark:bg-stone-700 rounded-full h-1.5">
                                            <div class="bg-blue-500 h-1.5 rounded-full" style="width:{{ $cr }}%">
                                            </div>
                                        </div>
                                        <span class="text-stone-600 dark:text-stone-400">{{ $cr }}%</span>
                                    </div>
                                </td>
                                <td class="py-3 pr-4 text-stone-700 dark:text-stone-300">{{ $fs }}%</td>
                                <td class="py-3">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusCls }}">{{ $statusLbl }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-stone-400 text-sm">Belum ada data untuk
                                    periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- History Table ────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
            <h3 class="font-bold text-stone-800 dark:text-white mb-4 text-sm flex items-center gap-2">
                <i class="fa-solid fa-history text-stone-400"></i> Riwayat Tugas Selesai
            </h3>
            @if ($history->isEmpty())
                <div class="text-center py-12 text-stone-400">
                    <i class="fa-solid fa-clipboard-list text-4xl mb-3 block opacity-30"></i>
                    <p class="text-sm">Belum ada tugas selesai di periode ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-stone-200 dark:border-stone-700">
                                <th class="text-left py-3 pr-4 text-stone-500 dark:text-stone-400 font-medium">Tanggal</th>
                                <th class="text-left py-3 pr-4 text-stone-500 dark:text-stone-400 font-medium">Judul Tugas
                                </th>
                                <th class="text-left py-3 pr-4 text-stone-500 dark:text-stone-400 font-medium">Kategori</th>
                                <th class="text-left py-3 pr-4 text-stone-500 dark:text-stone-400 font-medium">Prioritas
                                </th>
                                <th class="text-left py-3 text-stone-500 dark:text-stone-400 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($history as $h)
                                @php
                                    $priColor = match ($h->priority) {
                                        'urgent-important'
                                            => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                        'important-not-urgent'
                                            => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                                        'urgent-not-important'
                                            => 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
                                        'not-urgent-not-important' => 'bg-stone-100 dark:bg-stone-700 text-stone-500',
                                        default => 'bg-stone-100 dark:bg-stone-700 text-stone-500',
                                    };
                                    $priLabel = match ($h->priority) {
                                        'urgent-important' => '🔴 P1',
                                        'important-not-urgent' => '🔵 P2',
                                        'urgent-not-important' => '🟠 P3',
                                        'not-urgent-not-important' => '⚪ P4',
                                        default => '-',
                                    };
                                @endphp
                                <tr
                                    class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">
                                    <td class="py-3 pr-4 text-stone-500 dark:text-stone-400 text-xs">
                                        {{ $h->updated_at->isoFormat('D MMM, H:mm') }}</td>
                                    <td class="py-3 pr-4 font-medium text-stone-800 dark:text-white max-w-xs">
                                        <span class="truncate block max-w-[200px]">{{ $h->title }}</span>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <span
                                            class="text-xs px-2 py-0.5 bg-stone-100 dark:bg-stone-700 text-stone-600 dark:text-stone-300 rounded-full">{{ $h->category ?? '-' }}</span>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full {{ $priColor }}">{{ $priLabel }}</span>
                                    </td>
                                    <td class="py-3">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-medium">✓
                                            Selesai</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($history->hasPages())
                    <div class="mt-5 flex items-center justify-between gap-3 flex-wrap">
                        <p class="text-xs text-stone-400">
                            Menampilkan {{ $history->firstItem() }}–{{ $history->lastItem() }} dari
                            {{ $history->total() }} entri
                        </p>
                        <div class="flex gap-1">
                            {{-- Prev --}}
                            @if ($history->onFirstPage())
                                <span
                                    class="px-3 py-1.5 text-xs rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-400 cursor-not-allowed">←
                                    Prev</span>
                            @else
                                <a href="{{ $history->previousPageUrl() }}"
                                    class="px-3 py-1.5 text-xs rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700 transition-colors">←
                                    Prev</a>
                            @endif

                            {{-- Pages --}}
                            @foreach ($history->getUrlRange(max(1, $history->currentPage() - 2), min($history->lastPage(), $history->currentPage() + 2)) as $page => $url)
                                @if ($page == $history->currentPage())
                                    <span
                                        class="px-3 py-1.5 text-xs rounded-lg bg-stone-800 dark:bg-stone-200 text-white dark:text-stone-900 font-bold">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}"
                                        class="px-3 py-1.5 text-xs rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700 transition-colors">{{ $page }}</a>
                                @endif
                            @endforeach

                            {{-- Next --}}
                            @if ($history->hasMorePages())
                                <a href="{{ $history->nextPageUrl() }}"
                                    class="px-3 py-1.5 text-xs rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700 transition-colors">Next
                                    →</a>
                            @else
                                <span
                                    class="px-3 py-1.5 text-xs rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-400 cursor-not-allowed">Next
                                    →</span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Insights ─────────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-800 flex items-center justify-center">
                        <i class="fa-solid fa-lightbulb text-blue-600 dark:text-blue-300"></i>
                    </div>
                    <h4 class="font-bold text-blue-800 dark:text-blue-300 text-sm">Pola Kerja Optimal</h4>
                </div>
                <p class="text-sm text-blue-700 dark:text-blue-400">
                    @if (($bestDayArr['focus'] ?? 0) > 0)
                        Kamu paling produktif pada <strong>{{ ucfirst($bestDayArr['day'] ?? '-') }}
                            ({{ $bestDayArr['focus'] ?? 0 }}% focus score)</strong>. Coba terapkan rutinitas yang sama di
                        hari lain.
                    @else
                        Belum ada data focus score. Gunakan fitur Log PKL dan selesaikan tugas rutin untuk mulai tracking
                        produktivitas.
                    @endif
                </p>
            </div>
            <div
                class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-2xl p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-800 flex items-center justify-center">
                        <i class="fa-solid fa-triangle-exclamation text-orange-600 dark:text-orange-300"></i>
                    </div>
                    <h4 class="font-bold text-orange-800 dark:text-orange-300 text-sm">Area Perbaikan</h4>
                </div>
                <p class="text-sm text-orange-700 dark:text-orange-400">
                    @php
                        $lowest = collect($weekly)->where('planned', '>', 0)->sortBy('focus')->first();
                    @endphp
                    @if ($lowest && ($lowest['focus'] ?? 0) < 70)
                        Produktivitas paling rendah di <strong>{{ $lowest['day'] }} ({{ $lowest['focus'] }}%)</strong>.
                        Pertimbangkan jadwal lebih ringan atau istirahat berkualitas.
                    @else
                        Tidak ada area kritis yang terdeteksi minggu ini. Pertahankan konsistensi dan terus catat progres
                        harianmu!
                    @endif
                </p>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const weekly = @json($weekly);
            const cats = @json($categories);
            const isDark = document.documentElement.classList.contains('dark');
            const gridC = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.05)';
            const tickC = isDark ? '#a8a29e' : '#78716c';

            // ── Weekly Line Chart ────────────────────────────────────────────────
            const wCtx = document.getElementById('weeklyChart');
            if (wCtx && weekly.length > 0) {
                new Chart(wCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: weekly.map(d => d.day),
                        datasets: [{
                                label: 'Focus Score (%)',
                                data: weekly.map(d => d.focus),
                                borderColor: '#8b5cf6',
                                backgroundColor: 'rgba(139,92,246,.1)',
                                fill: true,
                                tension: .4,
                                borderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            },
                            {
                                label: 'Completion (%)',
                                data: weekly.map(d => d.planned > 0 ? Math.round(d.done / d.planned * 100) :
                                    0),
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,.08)',
                                fill: true,
                                tension: .4,
                                borderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                grid: {
                                    color: gridC
                                },
                                ticks: {
                                    color: tickC,
                                    callback: v => v + '%'
                                },
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: tickC
                                },
                            },
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: tickC,
                                    usePointStyle: true,
                                    font: {
                                        size: 11
                                    }
                                },
                            },
                        },
                    },
                });
            } else if (wCtx) {
                const ctx = wCtx.getContext('2d');
                ctx.fillStyle = isDark ? '#a8a29e' : '#78716c';
                ctx.font = '14px sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('Belum ada data untuk periode ini', wCtx.width / 2, wCtx.height / 2);
            }

            // ── Doughnut Chart ───────────────────────────────────────────────────
            const cCtx = document.getElementById('catChart');
            if (cCtx && cats.length > 0) {
                new Chart(cCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: cats.map(c => c.name),
                        datasets: [{
                            data: cats.map(c => c.total),
                            backgroundColor: cats.map(c => c.color),
                            borderWidth: 2,
                            borderColor: isDark ? '#1c1917' : '#fff',
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    color: tickC,
                                    usePointStyle: true,
                                    font: {
                                        size: 11
                                    }
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: c => {
                                        const cat = cats[c.dataIndex];
                                        const pct = cat.total > 0 ? Math.round(cat.done / cat.total * 100) :
                                            0;
                                        return `${c.label}: ${cat.done}/${cat.total} (${pct}% selesai)`;
                                    },
                                },
                            },
                        },
                    },
                });
            }
        })();
    </script>
@endpush
