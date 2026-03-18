{{-- resources/views/dashboard/focus.blade.php --}}
@extends('layouts.app-dashboard')
@section('title', 'Focus Today | StudentHub')
@section('page-title', 'Focus Today')

@push('styles')
    <style>
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(14px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @keyframes slideNow {
            from {
                opacity: 0;
                transform: translateY(-8px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @keyframes pulseNow {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(249, 115, 22, .5)
            }

            50% {
                box-shadow: 0 0 0 8px rgba(249, 115, 22, 0)
            }
        }

        .fade-up {
            animation: fadeUp .4s ease-out both
        }

        /* ── Gantt Timeline ───────────────────────────────── */
        .gantt-wrap {
            overflow-x: auto;
            padding-bottom: 6px
        }

        .gantt-wrap::-webkit-scrollbar {
            height: 5px
        }

        .gantt-wrap::-webkit-scrollbar-thumb {
            background: #e7e5e4;
            border-radius: 3px
        }

        .dark .gantt-wrap::-webkit-scrollbar-thumb {
            background: #44403c
        }

        .gantt-grid {
            min-width: 760px;
            position: relative
        }

        .gantt-row {
            display: flex;
            align-items: center;
            height: 44px;
            position: relative
        }

        .gantt-row+.gantt-row {
            border-top: 1px solid #f5f5f4
        }

        .dark .gantt-row+.gantt-row {
            border-color: #292524
        }

        .gantt-label {
            width: 130px;
            flex-shrink: 0;
            font-size: 11px;
            font-weight: 600;
            color: #78716c;
            padding-right: 10px;
            text-align: right;
            white-space: nowrap
        }

        .dark .gantt-label {
            color: #a8a29e
        }

        .gantt-track {
            flex: 1;
            position: relative;
            height: 100%;
            display: flex;
            align-items: center
        }

        .gantt-hour-grid {
            position: absolute;
            inset: 0;
            display: flex;
            pointer-events: none
        }

        .gantt-hour-cell {
            flex: 1;
            border-right: 1px dashed #e7e5e480
        }

        .dark .gantt-hour-cell {
            border-color: #29252430
        }

        .gantt-block {
            position: absolute;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            padding: 0 10px;
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            cursor: pointer;
            transition: opacity .15s, transform .12s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .12)
        }

        .gantt-block:hover {
            opacity: .85;
            transform: translateY(-1px)
        }

        .gantt-block.active {
            box-shadow: 0 0 0 2.5px white, 0 0 0 4.5px currentColor, 0 4px 12px rgba(0, 0, 0, .2)
        }

        .gantt-block.suggested {
            border: 2px dashed rgba(255, 255, 255, .5);
            opacity: .8
        }

        .gantt-block.urgent-override {
            animation: pulseNow 2s ease-in-out infinite
        }

        .now-needle {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 2.5px;
            background: linear-gradient(to bottom, #f97316, #ef4444);
            z-index: 20;
            border-radius: 2px;
            pointer-events: none
        }

        .now-needle::before {
            content: '';
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #f97316;
            border: 2px solid white;
            animation: pulseNow 2s ease-in-out infinite
        }

        .now-label {
            position: absolute;
            top: -22px;
            transform: translateX(-50%);
            background: #f97316;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 6px;
            white-space: nowrap;
            animation: slideNow .4s ease-out both
        }

        .now-label::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: #f97316
        }

        .gantt-header {
            display: flex;
            margin-left: 130px;
            min-width: calc(100% - 130px)
        }

        .gantt-hour-lbl {
            flex: 1;
            text-align: center;
            font-size: 9px;
            color: #a8a29e;
            padding-bottom: 4px;
            border-right: 1px dashed #e7e5e440
        }

        .dark .gantt-hour-lbl {
            color: #57534e
        }

        .priority-do {
            border-left: 3.5px solid #ef4444
        }

        .priority-schedule {
            border-left: 3.5px solid #3b82f6
        }

        .priority-delegate {
            border-left: 3.5px solid #f97316
        }

        .priority-eliminate {
            border-left: 3.5px solid #9ca3af
        }

        /* ── Countdown Cards Styling ──────────────────────────────────────────── */
        .countdown-card {
            position: relative;
            transition: all 0.3s ease;
        }

        .countdown-card:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .countdown-unit {
            text-align: center;
        }

        .countdown-num {
            font-size: 1.5rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            line-height: 1;
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Code', 'Courier New', monospace;
        }

        .countdown-lbl {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            opacity: 0.6;
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            .countdown-num {
                font-size: 1.25rem;
            }

            .countdown-lbl {
                font-size: 0.6rem;
            }
        }

        /* ── Animations ──────────────────────────────────────────────────────── */
        @keyframes pulse-urgent {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }

            50% {
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }
        }

        .countdown-card:has([data-countdown-days="0"]) {
            animation: pulse-urgent 2s infinite;
        }
    </style>
@endpush

@section('content')
    @php
        $currentHour = now()->hour;
        $currentMinute = now()->minute;
        // GANTT TIMELINE SETUP
        $timelineStart = 0; // Mulai jam 00:00 (Sebelumnya 6)
        $timelineEnd = 24; // Sampai jam 24:00 (Sebelumnya 23)
        $timelineHours = $timelineEnd - $timelineStart;
        $nowDecimal = $currentHour + $currentMinute / 60;
        $needlePct = max(0, min(100, (($nowDecimal - $timelineStart) / $timelineHours) * 100));
        $headerHours = range($timelineStart, $timelineEnd);

        // Pastikan variabel ada
        $ganttRows = $ganttRows ?? [];
        $eisenhowerTasks = $eisenhowerTasks ?? [
            'q1' => collect(),
            'q2' => collect(),
            'q3' => collect(),
            'q4' => collect(),
        ];
        $recommendations = $recommendations ?? [];
        $thesisMilestone = $thesisMilestone ?? null;
        $thesisDeadline = $thesisDeadline ?? null;
        $urgentTask = $urgentTask ?? null;
        $todayDoneCount = $todayDoneCount ?? 0;
        $todayTotalCount = $todayTotalCount ?? 0;
        $pklHoursToday = $pklHoursToday ?? 0;
        $gaps = $gaps ?? [];
        $countdowns = $countdowns ?? [];

        // Cari aktivitas aktif sekarang
        $actNow = null;
        $actRow = null;
        foreach ($ganttRows as $row) {
            foreach ($row['blocks'] ?? [] as $b) {
                if ($nowDecimal >= $b['start'] && $nowDecimal < $b['end']) {
                    $actNow = $b;
                    $actRow = $row['label'];
                }
            }
        }
    @endphp

    <div class="fade-up space-y-5">
        <div
            class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-stone-800 via-stone-900 to-black text-white shadow-2xl">
            <div class="absolute inset-0 opacity-15"
                style="background-image:radial-gradient(circle at 20% 50%,#f97316 0%,transparent 60%),radial-gradient(circle at 80% 20%,#3b82f6 0%,transparent 50%)">
            </div>
            <div class="relative z-10 p-6 md:p-8">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                    <div class="flex-1">
                        <span
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/15 backdrop-blur-sm border border-white/10 text-xs font-medium mb-4">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Mahasiswa Aktif · Freelancer
                        </span>
                        <h2 class="text-3xl md:text-4xl font-bold mb-2">
                            Selamat datang, <span class="text-orange-400">{{ auth()->user()->name }}</span> 👋
                        </h2>
                        <p class="text-stone-300 text-sm mb-4">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
                        <div class="flex items-center gap-3 flex-wrap">
                            <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl px-4 py-2.5">
                                <p class="text-xs text-stone-400 mb-0.5">Sekarang</p>
                                <p class="text-2xl font-mono font-bold text-white" id="live-clock">
                                    {{ now()->format('H:i') }}</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl px-4 py-2.5">
                                <p class="text-xs text-stone-400 mb-0.5">Selesai Hari Ini</p>
                                <p class="text-2xl font-bold text-emerald-400">{{ $todayDoneCount }}<span
                                        class="text-sm text-stone-400">/{{ $todayTotalCount }}</span></p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl px-4 py-2.5">
                                <p class="text-xs text-stone-400 mb-0.5">Aktivitas Kini</p>
                                @if ($actNow)
                                    <p class="text-sm font-bold text-orange-300">{{ $actNow['icon'] }}
                                        {{ Str::limit($actNow['label'], 20) }}</p>
                                @else
                                    <p class="text-sm font-bold text-stone-300">🌅 Bebas</p>
                                @endif
                            </div>
                            @if ($pklHoursToday > 0)
                                <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl px-4 py-2.5">
                                    <p class="text-xs text-stone-400 mb-0.5">Jam PKL Hari Ini</p>
                                    <p class="text-2xl font-bold text-blue-400">{{ $pklHoursToday }}j</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Quick stats --}}
                    <div class="md:w-64 space-y-3">
                        <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl p-4">
                            <p class="text-xs text-stone-400 mb-2">Progres Hari Ini</p>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-white/10 rounded-full h-2">
                                    <div class="bg-orange-500 h-2 rounded-full transition-all"
                                        style="width:{{ min(100, round((($nowDecimal - 6) / 17) * 100)) }}%"></div>
                                </div>
                                <span class="text-sm font-bold text-white">{{ now()->format('H:i') }}</span>
                            </div>
                            <p class="text-[10px] text-stone-500 mt-1.5">{{ 23 - $currentHour }} jam tersisa hari ini</p>
                        </div>
                        @if (!empty($gaps))
                            <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl p-4">
                                <p class="text-xs text-stone-400 mb-1">Waktu Kosong</p>
                                <p class="text-lg font-bold text-blue-300">{{ count($gaps) }} slot</p>
                                <p class="text-[10px] text-stone-500">tersedia untuk tugas</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        {{-- ─── GANTT TIMELINE ─────────────────────────────────────────────────── --}}
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800 flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <i class="fa-solid fa-timeline text-orange-500 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-stone-800 dark:text-white text-sm">Smart Timeline —
                            {{ now()->isoFormat('dddd, D MMM YYYY') }}</h3>
                        <p class="text-[11px] text-stone-400">
                            06:00–23:00 · Blok solid = jadwal tetap · <span
                                class="border-b border-dashed border-stone-400">Garis putus</span> = saran tugas · Jarum
                            oranye = sekarang
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @foreach ([['#10b981', 'PKL'], ['#3b82f6', 'Kuliah'], ['#f97316', 'Skripsi'], ['#8b5cf6', 'Freelance'], ['#94a3b8', 'Personal']] as [$c, $l])
                        <div class="hidden lg:flex items-center gap-1">
                            <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $c }}"></span>
                            <span class="text-[10px] text-stone-400">{{ $l }}</span>
                        </div>
                    @endforeach
                    <button onclick="openModal('modal-add-focus-task')"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-stone-800 dark:bg-stone-700 hover:bg-stone-900 text-white text-xs font-medium rounded-xl transition-colors">
                        <i class="fa-solid fa-plus text-[10px]"></i> Tugas
                    </button>
                </div>
            </div>

            <div class="px-5 pt-4 pb-5">
                <div class="gantt-wrap">
                    <div class="gantt-grid" id="gantt-grid">
                        <div class="gantt-header">
                            @foreach (range(0, 23) as $h)
                                <div class="gantt-hour-lbl">{{ sprintf('%02d', $h) }}</div>
                            @endforeach
                        </div>

                        {{-- Rows --}}
                        @foreach ($ganttRows as $row)
                            <div class="gantt-row">
                                <div class="gantt-label">{{ $row['label'] }}</div>
                                <div class="gantt-track" id="track-{{ $loop->index }}">
                                    <div class="gantt-hour-grid">
                                        @foreach ($headerHours as $h)
                                            <div class="gantt-hour-cell"></div>
                                        @endforeach
                                    </div>

                                    @foreach ($row['blocks'] ?? [] as $b)
                                        @php
                                            $left = max(0, (($b['start'] - $timelineStart) / $timelineHours) * 100);
                                            $width = max(0.5, (($b['end'] - $b['start']) / $timelineHours) * 100);
                                            $isActive = $nowDecimal >= $b['start'] && $nowDecimal < $b['end'];
                                            $isSug = $b['is_fixed'] === false ?? $b['source'] === 'suggested';
                                            $isUrgent = ($b['is_urgent'] ?? false) && $isSug;
                                            $fmtStart = sprintf(
                                                '%02d:%02d',
                                                (int) $b['start'],
                                                (int) (($b['start'] - (int) $b['start']) * 60),
                                            );
                                            $fmtEnd = sprintf(
                                                '%02d:%02d',
                                                (int) $b['end'],
                                                (int) (($b['end'] - (int) $b['end']) * 60),
                                            );
                                        @endphp
                                        <div class="gantt-block {{ $isActive ? 'active' : '' }} {{ $isSug ? 'suggested' : '' }} {{ $isUrgent ? 'urgent-override' : '' }}"
                                            style="left:{{ $left }}%;width:{{ $width }}%;background:{{ $b['color'] }};
                                   {{ $isActive ? 'outline:2.5px solid ' . $b['color'] . ';outline-offset:2px' : '' }}"
                                            title="{{ $b['label'] }} ({{ $fmtStart }}–{{ $fmtEnd }}){{ $isSug ? ' — Saran Tugas' : '' }}">
                                            <span class="truncate">{{ $b['icon'] ?? '📌' }} {{ $b['label'] }}</span>
                                            @if ($isActive)
                                                <span
                                                    class="ml-1.5 px-1.5 py-0.5 bg-white/25 rounded text-[9px] font-bold flex-shrink-0">SEKARANG</span>
                                            @endif
                                            @if ($isSug)
                                                <span
                                                    class="ml-1 px-1 py-0.5 bg-white/20 rounded text-[8px] flex-shrink-0">Saran</span>
                                            @endif
                                        </div>
                                    @endforeach

                                    @if ($loop->first)
                                        <div class="now-needle" id="now-needle" style="left:{{ $needlePct }}%">
                                            <div class="now-label" id="now-label">{{ now()->format('H:i') }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>

                {{-- Mobile legend --}}
                <div class="flex flex-wrap gap-3 mt-4 lg:hidden">
                    @foreach ([['#10b981', 'PKL / Kerja'], ['#3b82f6', 'Kuliah'], ['#f97316', 'Skripsi'], ['#8b5cf6', 'Freelance'], ['#94a3b8', 'Personal']] as [$c, $l])
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-sm" style="background:{{ $c }}"></span>
                            <span class="text-xs text-stone-500 dark:text-stone-400">{{ $l }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-sm border-2 border-dashed border-stone-400 bg-transparent"></span>
                        <span class="text-xs text-stone-500 dark:text-stone-400">Saran Tugas</span>
                    </div>
                </div>
            </div>

            {{-- Current activity bar --}}
            @if ($actNow)
                <div class="mx-5 mb-5 flex items-center gap-4 p-4 rounded-xl border-2"
                    style="background:{{ $actNow['color'] }}18;border-color:{{ $actNow['color'] }}40">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-lg"
                        style="background:{{ $actNow['color'] }}30">
                        {{ $actNow['icon'] ?? '📌' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-stone-800 dark:text-white text-sm flex items-center gap-2 flex-wrap">
                            {{ $actNow['label'] }}
                            <span class="text-[10px] px-2 py-0.5 rounded-full text-white font-bold animate-pulse"
                                style="background:{{ $actNow['color'] }}">AKTIF SEKARANG</span>
                            @if (($actNow['is_fixed'] ?? true) === false)
                                <span
                                    class="text-[10px] px-2 py-0.5 rounded-full bg-stone-200 dark:bg-stone-700 text-stone-600 dark:text-stone-400 font-medium">💡
                                    Saran Sistem</span>
                            @endif
                        </p>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">
                            {{ $actRow }} ·
                            {{ sprintf('%02d:%02d', (int) $actNow['start'], (int) (($actNow['start'] - (int) $actNow['start']) * 60)) }}
                            –
                            {{ sprintf('%02d:%02d', (int) $actNow['end'], (int) (($actNow['end'] - (int) $actNow['end']) * 60)) }}
                            ·
                            Sisa ~{{ round($actNow['end'] - $nowDecimal, 1) }} jam
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs text-stone-400">Selesai</p>
                        <p class="font-bold text-stone-800 dark:text-white text-sm">
                            {{ sprintf('%02d:%02d', (int) $actNow['end'], (int) (($actNow['end'] - (int) $actNow['end']) * 60)) }}
                        </p>
                    </div>
                </div>
            @else
                <div
                    class="mx-5 mb-5 flex items-center gap-3 p-3 bg-stone-50 dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700">
                    <i class="fa-solid fa-mug-hot text-stone-400 text-lg"></i>
                    <p class="text-sm text-stone-500 dark:text-stone-400">Tidak ada aktivitas terjadwal saat ini —
                        istirahat atau kerjakan tugas tambahan!</p>
                </div>
            @endif
        </div>

        {{-- ─── Eisenhower Matrix + Rekomendasi ────────────────────────────────── --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

            {{-- Eisenhower --}}
            <div
                class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                            <i class="fa-solid fa-crosshairs text-orange-500 text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-stone-800 dark:text-white text-sm">Eisenhower Matrix</h3>
                            <p class="text-[11px] text-stone-400">Prioritas dari database real-time</p>
                        </div>
                    </div>
                    <button onclick="openModal('modal-add-focus-task')"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-stone-800 dark:bg-stone-700 hover:bg-stone-900 text-white text-xs font-medium rounded-xl transition-colors">
                        <i class="fa-solid fa-plus text-[10px]"></i> Tugas
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-px bg-stone-100 dark:bg-stone-800">
                    @php
                        $quadrants = [
                            ['q1', 'red', 'Do First', 'Urgent+Penting', 'priority-do', 'bg-red-50 dark:bg-red-900/10'],
                            [
                                'q2',
                                'blue',
                                'Schedule',
                                'Tidak Urgent+Penting',
                                'priority-schedule',
                                'bg-blue-50 dark:bg-blue-900/10',
                            ],
                            [
                                'q3',
                                'orange',
                                'Delegate',
                                'Urgent+Tidak Penting',
                                'priority-delegate',
                                'bg-orange-50 dark:bg-orange-900/10',
                            ],
                            [
                                'q4',
                                'stone',
                                'Eliminate',
                                'Tidak Urgent+Tidak Penting',
                                'priority-eliminate',
                                'bg-stone-50 dark:bg-stone-800',
                            ],
                        ];
                        $qTextColors = [
                            'red' => 'text-red-600 dark:text-red-400',
                            'blue' => 'text-blue-600 dark:text-blue-400',
                            'orange' => 'text-orange-600 dark:text-orange-400',
                            'stone' => 'text-stone-500',
                        ];
                        $qBgDots = [
                            'red' => 'bg-red-500',
                            'blue' => 'bg-blue-500',
                            'orange' => 'bg-orange-500',
                            'stone' => 'bg-stone-400',
                        ];
                    @endphp
                    @foreach ($quadrants as [$qk, $qc, $ql, $qsub, $qcls, $qbg])
                        <div class="bg-white dark:bg-stone-900 p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2.5 h-2.5 rounded-full {{ $qBgDots[$qc] }}"></span>
                                <span
                                    class="text-xs font-bold {{ $qTextColors[$qc] }} uppercase tracking-wider">{{ $ql }}</span>
                                <span
                                    class="ml-auto text-[10px] text-stone-400 hidden sm:block">{{ $qsub }}</span>
                                <span
                                    class="ml-auto text-[10px] font-bold text-stone-500">{{ ($eisenhowerTasks[$qk] ?? collect())->count() }}</span>
                            </div>
                            <div class="space-y-2" id="{{ $qk }}-tasks">
                                @forelse($eisenhowerTasks[$qk] ?? [] as $task)
                                    <div
                                        class="{{ $qcls }} {{ $qcls }} rounded-r-xl p-2.5 {{ $qcls }}">
                                        <p class="text-sm font-medium text-stone-800 dark:text-white leading-tight">
                                            {{ Str::limit($task->title, 40) }}</p>
                                        <span class="text-[10px] text-stone-400">
                                            {{ $task->due_date ? $task->due_date->isoFormat('D MMM') : 'Tanpa deadline' }}
                                            @if ($task->category)
                                                · {{ $task->category }}
                                            @endif
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-xs text-stone-400 italic">Tidak ada tugas</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Rekomendasi + Quick Access --}}
            <div class="space-y-4">
                {{-- Rekomendasi dinamis --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                            <i class="fa-solid fa-brain text-white text-sm"></i>
                        </div>
                        <h3 class="font-bold text-stone-800 dark:text-white text-sm">Rekomendasi Sistem</h3>
                    </div>
                    <div class="space-y-2.5">
                        @forelse($recommendations as $rec)
                            <div class="flex items-start gap-3 p-3 {{ $rec['cls'] }} rounded-xl">
                                <i class="fa-solid {{ $rec['icon'] }} mt-0.5 text-sm flex-shrink-0"></i>
                                <div>
                                    <p class="text-xs font-bold text-stone-800 dark:text-white">{{ $rec['title'] }}</p>
                                    <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5 leading-relaxed">
                                        {{ $rec['desc'] }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-start gap-3 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                                <i class="fa-solid fa-check-circle text-emerald-500 mt-0.5 text-sm"></i>
                                <div>
                                    <p class="text-xs font-bold text-stone-800 dark:text-white">Jadwal Bersih</p>
                                    <p class="text-xs text-stone-500 dark:text-stone-400">Tidak ada tugas mendesak.
                                        Manfaatkan waktu untuk pengembangan diri!</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Waktu kosong hari ini --}}
                @if (!empty($gaps))
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                        <h3 class="font-bold text-stone-800 dark:text-white text-sm mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-hourglass-half text-blue-500"></i> Slot Waktu Tersedia
                        </h3>
                        <div class="space-y-2">
                            @foreach ($gaps as $gap)
                                <div
                                    class="flex items-center justify-between p-2.5 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                                    <span class="text-xs font-semibold text-blue-700 dark:text-blue-400">
                                        {{ sprintf('%02d:00', (int) $gap['start']) }} –
                                        {{ sprintf('%02d:00', (int) $gap['end']) }}
                                    </span>
                                    <span
                                        class="text-[10px] text-blue-500 font-bold">{{ round($gap['duration_hours'], 1) }}
                                        jam</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Quick Access --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                    <h3 class="font-bold text-stone-800 dark:text-white text-sm mb-3">Akses Cepat</h3>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ([[route('dashboard.finance'), 'fa-wallet', 'Finance', 'bg-amber-50 dark:bg-amber-900/20 text-amber-600'], [route('dashboard.academic'), 'fa-graduation-cap', 'Akademik', 'bg-blue-50 dark:bg-blue-900/20 text-blue-600'], [route('dashboard.pkl'), 'fa-briefcase', 'PKL', 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600'], [route('dashboard.productivity'), 'fa-chart-line', 'Analytics', 'bg-purple-50 dark:bg-purple-900/20 text-purple-600']] as [$url, $icon, $label, $cls])
                            <a href="{{ $url }}"
                                class="flex items-center gap-2.5 p-3 {{ $cls }} rounded-xl hover:opacity-80 transition-opacity text-sm font-medium">
                                <i class="fa-solid {{ $icon }} text-sm"></i> {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- MODAL: Tambah Tugas --}}
    <div id="modal-add-focus-task"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl border border-stone-200 dark:border-stone-800">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white">Tambah Tugas Prioritas</h3>
                <button onclick="closeModal('modal-add-focus-task')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Judul Tugas
                        *</label>
                    <input type="text" id="ft-title"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Apa yang harus dikerjakan?">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Kuadran
                            *</label>
                        <select id="ft-quadrant"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="urgent-important">🔴 Do First</option>
                            <option value="important-not-urgent">🔵 Schedule</option>
                            <option value="urgent-not-important">🟠 Delegate</option>
                            <option value="not-urgent-not-important">⚪ Eliminate</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Deadline</label>
                        <input type="date" id="ft-deadline"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                            value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
                <div>
                    <label
                        class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Kategori</label>
                    <select id="ft-category"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                        @foreach (['Skripsi', 'PKL', 'Academic', 'Creative', 'Freelance', 'Personal', 'Kesehatan'] as $c)
                            <option>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-focus-task')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="saveFocusTask(this)"
                    class="flex-1 py-2.5 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
                    <i class="fa-solid fa-plus mr-1.5"></i>Tambah
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ── Live clock + needle ──────────────────────────────────────────────────
        (function tickClock() {
            const el = document.getElementById('live-clock');
            const nl = document.getElementById('now-label');
            if (el) {
                const n = new Date();
                const ts = n.toTimeString().slice(0, 5);
                el.textContent = ts;
                if (nl) nl.textContent = ts;
                const h = n.getHours() + n.getMinutes() / 60;
                const pct = Math.max(0, Math.min(100, ((h - 0) / 24) * 100));
                const need = document.getElementById('now-needle');
                if (need) need.style.left = pct + '%';
            }
            setTimeout(tickClock, 15000);
        })();

        // ── Stretch needle height ────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            const needle = document.getElementById('now-needle');
            const grid = document.getElementById('gantt-grid');
            if (needle && grid) {
                needle.style.height = grid.offsetHeight + 'px';
                needle.style.top = '28px';
            }
            // Scroll to current time
            const pct = {{ $needlePct }};
            const wrap = document.querySelector('.gantt-wrap');
            if (wrap) {
                const target = (wrap.scrollWidth * pct / 100) - (wrap.clientWidth / 2);
                wrap.scrollLeft = Math.max(0, target);
            }
        });

        // ── Modal helpers ────────────────────────────────────────────────────────
        function openModal(id) {
            document.getElementById(id)?.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal(id) {
            document.getElementById(id)?.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function toast(msg, ok = true) {
            const t = document.createElement('div');
            t.className =
                `fixed bottom-6 right-6 z-[9999] flex items-center gap-2 px-4 py-3.5 ${ok?'bg-emerald-500':'bg-rose-500'} text-white text-sm font-semibold rounded-2xl shadow-xl`;
            t.style.animation = 'fadeUp .28s ease-out both';
            t.innerHTML = `<i class="fa-solid ${ok?'fa-check-circle':'fa-circle-xmark'}"></i> ${msg}`;
            document.body.appendChild(t);
            setTimeout(() => {
                t.style.transition = 'opacity .3s';
                t.style.opacity = '0';
                setTimeout(() => t.remove(), 300);
            }, 2800);
        }

        // ── Save focus task → backend ────────────────────────────────────────────
        async function saveFocusTask(btn) {
            const title = document.getElementById('ft-title').value.trim();
            const priority = document.getElementById('ft-quadrant').value;
            const deadline = document.getElementById('ft-deadline').value;
            const category = document.getElementById('ft-category').value;
            if (!title) {
                toast('Isi judul tugas dulu!', false);
                return;
            }

            if (btn) {
                btn.disabled = true;
                btn.style.opacity = '.7';
            }
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            try {
                const res = await fetch('{{ route('focus.task.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title,
                        priority,
                        due_date: deadline,
                        category
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    // Add to Eisenhower matrix UI
                    const qMap = {
                        'urgent-important': {
                            id: 'q1-tasks',
                            cls: 'priority-do bg-red-50 dark:bg-red-900/10'
                        },
                        'important-not-urgent': {
                            id: 'q2-tasks',
                            cls: 'priority-schedule bg-blue-50 dark:bg-blue-900/10'
                        },
                        'urgent-not-important': {
                            id: 'q3-tasks',
                            cls: 'priority-delegate bg-orange-50 dark:bg-orange-900/10'
                        },
                        'not-urgent-not-important': {
                            id: 'q4-tasks',
                            cls: 'priority-eliminate bg-stone-50 dark:bg-stone-800'
                        },
                    };
                    const q = qMap[priority];
                    const el = document.createElement('div');
                    el.className = `${q.cls} rounded-r-xl p-2.5 mt-2`;
                    el.innerHTML =
                        `<p class="text-sm font-medium text-stone-800 dark:text-white">${title}</p><span class="text-[10px] text-stone-400">${category}${deadline ? ' · ' + deadline : ''}</span>`;
                    document.getElementById(q.id)?.appendChild(el);
                    closeModal('modal-add-focus-task');
                    document.getElementById('ft-title').value = '';
                    toast('Tugas berhasil ditambahkan!');
                } else {
                    toast(data.message || 'Gagal.', false);
                }
            } catch (e) {
                toast('Gagal menghubungi server.', false);
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                }
            }
        }

        // ═══════════════════════════════════════════════════════════════════════════
        // LIVE COUNTDOWN UPDATE - Update setiap detik untuk multiple countdowns
        // ═══════════════════════════════════════════════════════════════════════════
        setInterval(() => {
            document.querySelectorAll('.countdown-card').forEach((card, index) => {
                const daysEl = card.querySelector('[data-countdown-days]');
                const hoursEl = card.querySelector('[data-countdown-hours]');
                const minutesEl = card.querySelector('[data-countdown-minutes]');
                const secondsEl = card.querySelector('[data-countdown-seconds]');

                if (!daysEl || !hoursEl || !minutesEl || !secondsEl) return;

                let days = parseInt(daysEl.textContent);
                let hours = parseInt(hoursEl.textContent);
                let minutes = parseInt(minutesEl.textContent);
                let seconds = parseInt(secondsEl.textContent);

                // Decrement
                seconds--;

                if (seconds < 0) {
                    seconds = 59;
                    minutes--;

                    if (minutes < 0) {
                        minutes = 59;
                        hours--;

                        if (hours < 0) {
                            hours = 23;
                            days--;

                            if (days < 0) {
                                // Countdown finished - reload page
                                location.reload();
                                return;
                            }
                        }
                    }
                }

                // Update display
                daysEl.textContent = days;
                hoursEl.textContent = String(hours).padStart(2, '0');
                minutesEl.textContent = String(minutes).padStart(2, '0');
                secondsEl.textContent = String(seconds).padStart(2, '0');

                // Add urgent animation if less than 24 hours
                if (days === 0) {
                    card.classList.add('border-red-500/50');
                    daysEl.classList.add('text-red-400');
                    hoursEl.classList.add('text-red-400');
                    minutesEl.classList.add('text-red-400');
                    secondsEl.classList.add('text-red-400');
                }
            });
        }, 1000);

        // ═══════════════════════════════════════════════════════════════════════════
        // LIVE CLOCK UPDATE - Update jam setiap detik
        // ═══════════════════════════════════════════════════════════════════════════
        setInterval(() => {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const clockEl = document.getElementById('live-clock');
            if (clockEl) {
                clockEl.textContent = `${hours}:${minutes}`;
            }
        }, 1000);

        // ═══════════════════════════════════════════════════════════════════════════
        // NOTIFICATION - Tampilkan notifikasi jika deadline sangat dekat
        // ═══════════════════════════════════════════════════════════════════════════
        function checkUrgentDeadlines() {
            document.querySelectorAll('.countdown-card').forEach((card) => {
                const daysEl = card.querySelector('[data-countdown-days]');
                if (!daysEl) return;

                const days = parseInt(daysEl.textContent);
                const hours = parseInt(card.querySelector('[data-countdown-hours]').textContent);
                const label = card.querySelector('h4')?.textContent || 'Deadline';

                // Jika kurang dari 3 jam
                if (days === 0 && hours < 3) {
                    console.warn(`⚠️ URGENT: "${label}" deadline dalam ${hours} jam!`);

                    // Optional: Tampilkan notifikasi browser jika diizinkan
                    if (Notification.permission === 'granted') {
                        new Notification('Deadline Mendesak! 🚨', {
                            body: `"${label}" tersisa ${hours} jam!`,
                            icon: '/favicon.ico'
                        });
                    }
                }
            });
        }

        // Minta izin notifikasi
        if (Notification && Notification.permission !== 'denied') {
            Notification.requestPermission();
        }

        // Check urgent deadlines setiap 5 menit
        setInterval(checkUrgentDeadlines, 300000);
        checkUrgentDeadlines(); // Check saat load

        // ═══════════════════════════════════════════════════════════════════════════
        // AUTO REFRESH - Refresh halaman setiap 1 jam untuk update data
        // ═══════════════════════════════════════════════════════════════════════════
        setTimeout(() => {
            location.reload();
        }, 3600000); // 1 hour

        // Fallback countdown untuk data lama
        const cdEl = document.getElementById('countdown-timer');
        if (cdEl) {
            const targetDate = new Date(cdEl.dataset.target).getTime();
            setInterval(() => {
                const now = new Date().getTime();
                const distance = targetDate - now;
                if (distance < 0) return;
                document.getElementById('cd-days').innerText = String(Math.floor(distance / (1000 * 60 * 60 * 24)))
                    .padStart(2, '0');
                document.getElementById('cd-hours').innerText = String(Math.floor((distance % (1000 * 60 * 60 *
                    24)) / (1000 * 60 * 60))).padStart(2, '0');
                document.getElementById('cd-mins').innerText = String(Math.floor((distance % (1000 * 60 * 60)) / (
                    1000 * 60))).padStart(2, '0');
            }, 1000);
        }
    </script>
@endpush
