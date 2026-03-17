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

        /* ── Gantt Timeline ─────────────────────────────────────── */
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
            height: 42px;
            position: relative
        }

        .gantt-row+.gantt-row {
            border-top: 1px solid #f5f5f4
        }

        .dark .gantt-row+.gantt-row {
            border-color: #292524
        }

        .gantt-label {
            width: 120px;
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

        /* hour cells — subtle vertical grid */
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

        /* blocks */
        .gantt-block {
            position: absolute;
            height: 28px;
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

        /* "Sekarang" needle */
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

        /* header hours */
        .gantt-header {
            display: flex;
            margin-left: 120px;
            min-width: calc(100% - 120px)
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

        /* time block vertical (right side) */
        .time-block {
            transition: all .18s
        }

        .time-block:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, .1)
        }

        /* priority borders */
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
    </style>
@endpush

@section('content')
    @php
        $currentHour = now()->hour;
        $currentMinute = now()->minute;
        // Timeline spans 06:00–23:00 = 17 hours
        $timelineStart = 6;
        $timelineEnd = 23;
        $timelineHours = $timelineEnd - $timelineStart;

        // Calculate needle position %
        $nowDecimal = $currentHour + $currentMinute / 60;
        $needlePct = max(0, min(100, (($nowDecimal - $timelineStart) / $timelineHours) * 100));

        // Helper: convert hour (float) to % within timeline
        // Used in blade inline style

        // Build ganttRows from controller-passed schedule data (fallback to empty)
        $todaySchedule = $todaySchedule ?? collect();
        $colorMap = [
            'pkl' => '#10b981',
            'work' => '#059669',
            'academic' => '#3b82f6',
            'skripsi' => '#f97316',
            'creative' => '#8b5cf6',
            'freelance' => '#a855f7',
            'personal' => '#94a3b8',
            'rest' => '#64748b',
        ];
        $iconMap = [
            'pkl' => '💼',
            'work' => '💼',
            'academic' => '�',
            'skripsi' => '✍️',
            'creative' => '🎬',
            'freelance' => '🎬',
            'personal' => '🌅',
            'rest' => '☕',
        ];
        // Group schedules by type/category for gantt rows
        $ganttGroups = [];
        foreach (
            $todaySchedule instanceof \Illuminate\Support\Collection ? $todaySchedule : collect($todaySchedule)
            as $s
        ) {
            $type = $s->type ?? ($s['type'] ?? 'personal');
            $start = is_string($s->start_time ?? null)
                ? (float) explode(':', $s->start_time)[0] + (float) (explode(':', $s->start_time)[1] ?? 0) / 60
                : $s['start'] ?? 8;
            $end = is_string($s->end_time ?? null)
                ? (float) explode(':', $s->end_time)[0] + (float) (explode(':', $s->end_time)[1] ?? 0) / 60
                : $s['end'] ?? 9;
            $label = $s->activity ?? ($s->title ?? ($s['label'] ?? ucfirst($type)));
            $ganttGroups[$type][] = [
                'start' => $start,
                'end' => $end,
                'label' => $label,
                'color' => $colorMap[$type] ?? '#94a3b8',
                'icon' => $iconMap[$type] ?? '📌',
            ];
        }
        $labelMap = [
            'pkl' => 'PKL / Kerja',
            'academic' => 'Kuliah',
            'skripsi' => 'Skripsi / Deep Work',
            'creative' => 'Freelance / Kreatif',
            'personal' => 'Personal',
        ];
        $ganttRows = [];
        foreach ($ganttGroups as $type => $blocks) {
            $ganttRows[] = ['label' => $labelMap[$type] ?? ucfirst($type), 'blocks' => $blocks];
        }
        // Fallback if no schedule data yet
        if (empty($ganttRows)) {
            $ganttRows = [
                ['label' => 'PKL / Kerja', 'blocks' => []],
                ['label' => 'Kuliah', 'blocks' => []],
                ['label' => 'Skripsi', 'blocks' => []],
                ['label' => 'Freelance', 'blocks' => []],
                ['label' => 'Personal', 'blocks' => []],
            ];
        }

        // Hours to show in header (every hour)
        $headerHours = range($timelineStart, $timelineEnd);
    @endphp

    <div class="fade-up space-y-5">

        {{-- ─── HERO CARD ──────────────────────────────────────────────────── --}}
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
                        <div class="flex items-center gap-3">
                            <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl px-4 py-2">
                                <p class="text-xs text-stone-400 mb-0.5">Sekarang</p>
                                <p class="text-2xl font-mono font-bold text-white" id="live-clock">
                                    {{ now()->format('H:i') }}</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl px-4 py-2">
                                <p class="text-xs text-stone-400 mb-0.5">Hari ke</p>
                                <p class="text-2xl font-bold text-orange-400">{{ now()->dayOfYear }}</p>
                                <p class="text-xs text-stone-400">dari 365</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl px-4 py-2">
                                @php
                                    $actNow = 'Bebas';
                                    $actColor = 'text-stone-300';
                                    foreach ($ganttRows as $row) {
                                        foreach ($row['blocks'] as $b) {
                                            if ($nowDecimal >= $b['start'] && $nowDecimal < $b['end']) {
                                                $actNow = $b['icon'] . ' ' . $b['label'];
                                                $actColor = 'text-orange-300';
                                            }
                                        }
                                    }
                                @endphp
                                <p class="text-xs text-stone-400 mb-0.5">Aktivitas kini</p>
                                <p class="text-sm font-bold {{ $actColor }}">{{ $actNow }}</p>
                            </div>
                        </div>
                    </div>
                    {{-- Quick stats --}}
                    <div class="grid grid-cols-2 gap-3 md:w-64">
                        <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl p-4 col-span-2">
                            <p class="text-xs text-stone-400 mb-2">Progres Hari Ini</p>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-white/10 rounded-full h-2">
                                    <div class="bg-orange-500 h-2 rounded-full"
                                        style="width:{{ min(100, round((($nowDecimal - 6) / 17) * 100)) }}%"></div>
                                </div>
                                <span class="text-sm font-bold text-white">{{ now()->format('H:i') }}</span>
                            </div>
                            <p class="text-[10px] text-stone-500 mt-1.5">{{ 23 - $currentHour }} jam tersisa hari ini</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════
     GANTT TIMELINE CARD
════════════════════════════════════════════════════════════════ --}}
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
            {{-- Card header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <i class="fa-solid fa-timeline text-orange-500 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-stone-800 dark:text-white text-sm">Timeline Harian —
                            {{ now()->isoFormat('dddd, D MMM YYYY') }}</h3>
                        <p class="text-[11px] text-stone-400">Jadwal 06:00–23:00 · Garis oranye = posisi kamu sekarang</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Legend --}}
                    @foreach ([['#10b981', 'PKL'], ['#3b82f6', 'Kuliah'], ['#f97316', 'Skripsi'], ['#8b5cf6', 'Freelance'], ['#94a3b8', 'Personal']] as [$c, $l])
                        <div class="hidden lg:flex items-center gap-1">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                style="background:{{ $c }}"></span>
                            <span class="text-[10px] text-stone-400">{{ $l }}</span>
                        </div>
                    @endforeach
                    <button onclick="openModal('modal-add-focus-task')"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-stone-800 dark:bg-stone-700 hover:bg-stone-900 text-white text-xs font-medium rounded-xl transition-colors ml-2">
                        <i class="fa-solid fa-plus text-[10px]"></i> Tugas
                    </button>
                </div>
            </div>

            {{-- Gantt body --}}
            <div class="px-5 pt-4 pb-5">
                <div class="gantt-wrap">
                    <div class="gantt-grid" id="gantt-grid">

                        {{-- Hour header --}}
                        <div class="gantt-header">
                            @foreach ($headerHours as $h)
                                <div class="gantt-hour-lbl">{{ sprintf('%02d', $h) }}</div>
                            @endforeach
                        </div>

                        {{-- Rows --}}
                        @foreach ($ganttRows as $row)
                            <div class="gantt-row">
                                <div class="gantt-label">{{ $row['label'] }}</div>
                                <div class="gantt-track" id="track-{{ $loop->index }}">
                                    {{-- Hour grid lines (background) --}}
                                    <div class="gantt-hour-grid">
                                        @foreach ($headerHours as $h)
                                            <div class="gantt-hour-cell"></div>
                                        @endforeach
                                    </div>

                                    {{-- Blocks --}}
                                    @foreach ($row['blocks'] as $b)
                                        @php
                                            $left = (($b['start'] - $timelineStart) / $timelineHours) * 100;
                                            $width = (($b['end'] - $b['start']) / $timelineHours) * 100;
                                            $isActive = $nowDecimal >= $b['start'] && $nowDecimal < $b['end'];
                                        @endphp
                                        <div class="gantt-block {{ $isActive ? 'active' : '' }}"
                                            style="left:{{ $left }}%;width:{{ $width }}%;background:{{ $b['color'] }};
                                   {{ $isActive ? 'outline:2.5px solid ' . $b['color'] . ';outline-offset:2px' : '' }}"
                                            title="{{ $b['label'] }} ({{ sprintf('%02d:00', $b['start']) }}–{{ sprintf('%02d:00', $b['end']) }})">
                                            <span class="truncate">{{ $b['icon'] }} {{ $b['label'] }}</span>
                                            @if ($isActive)
                                                <span
                                                    class="ml-1.5 px-1.5 py-0.5 bg-white/25 rounded text-[9px] font-bold flex-shrink-0">SEKARANG</span>
                                            @endif
                                        </div>
                                    @endforeach

                                    {{-- NOW needle — only on first row, span all rows via JS --}}
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
                    @foreach ([['#10b981', 'PKL / Kerja'], ['#3b82f6', 'Kuliah'], ['#f97316', 'Skripsi / Deep Work'], ['#8b5cf6', 'Freelance'], ['#94a3b8', 'Personal']] as [$c, $l])
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-sm flex-shrink-0" style="background:{{ $c }}"></span>
                            <span class="text-xs text-stone-500 dark:text-stone-400">{{ $l }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Current activity highlight bar --}}
            @php
                $currentBlock = null;
                $currentRowLabel = null;
                foreach ($ganttRows as $row) {
                    foreach ($row['blocks'] as $b) {
                        if ($nowDecimal >= $b['start'] && $nowDecimal < $b['end']) {
                            $currentBlock = $b;
                            $currentRowLabel = $row['label'];
                        }
                    }
                }
            @endphp
            @if ($currentBlock)
                <div class="mx-5 mb-5 flex items-center gap-4 p-4 rounded-xl border-2"
                    style="background:{{ $currentBlock['color'] }}18;border-color:{{ $currentBlock['color'] }}40">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-lg"
                        style="background:{{ $currentBlock['color'] }}30">
                        {{ $currentBlock['icon'] }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-stone-800 dark:text-white text-sm flex items-center gap-2">
                            {{ $currentBlock['label'] }}
                            <span class="text-[10px] px-2 py-0.5 rounded-full text-white font-bold animate-pulse"
                                style="background:{{ $currentBlock['color'] }}">AKTIF SEKARANG</span>
                        </p>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">
                            {{ $currentRowLabel }} ·
                            {{ sprintf('%02d:00', $currentBlock['start']) }} –
                            {{ sprintf('%02d:00', $currentBlock['end']) }} ·
                            Sisa ~{{ round($currentBlock['end'] - $nowDecimal, 1) }} jam
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs text-stone-400">Selesai</p>
                        <p class="font-bold text-stone-800 dark:text-white text-sm">
                            {{ sprintf('%02d:00', $currentBlock['end']) }}</p>
                    </div>
                </div>
            @else
                <div
                    class="mx-5 mb-5 flex items-center gap-3 p-3 bg-stone-50 dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700">
                    <i class="fa-solid fa-mug-hot text-stone-400 text-lg"></i>
                    <p class="text-sm text-stone-500 dark:text-stone-400">Tidak ada aktivitas terjadwal saat ini. Waktu
                        bebas — istirahat atau kerjakan tugas tambahan!</p>
                </div>
            @endif
        </div>

        {{-- ─── 2-COLUMN: Eisenhower + Quick Stats ────────────────────────── --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

            {{-- Eisenhower Matrix --}}
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
                            <p class="text-[11px] text-stone-400">Prioritas berdasarkan urgensi & kepentingan</p>
                        </div>
                    </div>
                    <button onclick="openModal('modal-add-focus-task')"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-stone-800 dark:bg-stone-700 hover:bg-stone-900 text-white text-xs font-medium rounded-xl transition-colors">
                        <i class="fa-solid fa-plus text-[10px]"></i> Tugas
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-px bg-stone-100 dark:bg-stone-800">
                    <div class="bg-white dark:bg-stone-900 p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                            <span class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wider">Do
                                First</span>
                            <span class="ml-auto text-[10px] text-stone-400">Urgent+Penting</span>
                        </div>
                        <div class="space-y-2" id="q1-tasks">
                            @forelse ($eisenhowerTasks['q1'] as $task)
                                <div class="priority-do bg-red-50 dark:bg-red-900/10 rounded-r-xl p-2.5">
                                    <p class="text-sm font-medium text-stone-800 dark:text-white">{{ $task->title }}</p>
                                    <span
                                        class="text-[10px] text-stone-400">{{ $task->due_date ? $task->due_date->isoFormat('D MMM') : 'Tanpa deadline' }}</span>
                                </div>
                            @empty
                                <p class="text-xs text-stone-400 italic">Tidak ada tugas prioritas 1</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="bg-white dark:bg-stone-900 p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span
                                class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Schedule</span>
                            <span class="ml-auto text-[10px] text-stone-400">Tidak Urgent+Penting</span>
                        </div>
                        <div class="space-y-2" id="q2-tasks">
                            @forelse ($eisenhowerTasks['q2'] as $task)
                                <div class="priority-schedule bg-blue-50 dark:bg-blue-900/10 rounded-r-xl p-2.5">
                                    <p class="text-sm font-medium text-stone-800 dark:text-white">{{ $task->title }}</p>
                                    <span
                                        class="text-[10px] text-stone-400">{{ $task->due_date ? $task->due_date->isoFormat('D MMM') : 'Flexible' }}</span>
                                </div>
                            @empty
                                <p class="text-xs text-stone-400 italic">Tidak ada tugas prioritas 2</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="bg-white dark:bg-stone-900 p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                            <span
                                class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-wider">Delegate</span>
                            <span class="ml-auto text-[10px] text-stone-400">Urgent+Tidak Penting</span>
                        </div>
                        <div class="space-y-2" id="q3-tasks">
                            @forelse ($eisenhowerTasks['q3'] as $task)
                                <div class="priority-delegate bg-orange-50 dark:bg-orange-900/10 rounded-r-xl p-2.5">
                                    <p class="text-sm font-medium text-stone-800 dark:text-white">{{ $task->title }}</p>
                                    <span
                                        class="text-[10px] text-stone-400">{{ $task->due_date ? $task->due_date->isoFormat('D MMM') : 'Hari ini' }}</span>
                                </div>
                            @empty
                                <p class="text-xs text-stone-400 italic">Tidak ada tugas prioritas 3</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="bg-white dark:bg-stone-900 p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-stone-400"></span>
                            <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Eliminate</span>
                            <span class="ml-auto text-[10px] text-stone-400">Tidak Urgent+Tidak Penting</span>
                        </div>
                        <div class="space-y-2" id="q4-tasks">
                            @forelse ($eisenhowerTasks['q4'] as $task)
                                <div class="priority-eliminate bg-stone-50 dark:bg-stone-800 rounded-r-xl p-2.5">
                                    <p class="text-sm font-medium text-stone-800 dark:text-white">{{ $task->title }}</p>
                                    <span
                                        class="text-[10px] text-stone-400">{{ $task->due_date ? $task->due_date->isoFormat('D MMM') : 'Kapanpun' }}</span>
                                </div>
                            @empty
                                <p class="text-xs text-stone-400 italic">Tidak ada tugas prioritas 4</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: AI Recommendation + Quick access --}}
            <div class="space-y-4">
                {{-- AI recs --}}
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
                        @php
                            $recs = [
                                [
                                    'fa-fire',
                                    'text-red-500 bg-red-50 dark:bg-red-900/20',
                                    'Prioritas Utama',
                                    'Selesaikan Laporan PKL sebelum jam 12.00. Deadline besok.',
                                ],
                                [
                                    'fa-bolt',
                                    'text-blue-500 bg-blue-50 dark:bg-blue-900/20',
                                    'Deep Work Window',
                                    'Jam 19:00–22:00 adalah waktu terbaik untuk skripsi.',
                                ],
                                [
                                    'fa-coins',
                                    'text-amber-500 bg-amber-50 dark:bg-amber-900/20',
                                    'Keuangan',
                                    'Budget makan bulan ini sudah 75%. Pertimbangkan masak sendiri.',
                                ],
                            ];
                        @endphp
                        @foreach ($recs as [$ic, $cls, $t, $d])
                            <div class="flex items-start gap-3 p-3 {{ $cls }} rounded-xl">
                                <i
                                    class="fa-solid {{ $ic }} {{ $cls }} mt-0.5 text-sm flex-shrink-0"></i>
                                <div>
                                    <p class="text-xs font-bold text-stone-800 dark:text-white">{{ $t }}</p>
                                    <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5 leading-relaxed">
                                        {{ $d }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Quick access --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                    <h3 class="font-bold text-stone-800 dark:text-white text-sm mb-3">Akses Cepat</h3>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ([[route('dashboard.finance'), 'fa-wallet', 'Finance', 'bg-amber-50 dark:bg-amber-900/20  text-amber-600'], [route('dashboard.academic'), 'fa-graduation-cap', 'Akademik', 'bg-blue-50 dark:bg-blue-900/20    text-blue-600'], [route('dashboard.pkl'), 'fa-briefcase', 'PKL', 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600'], [route('dashboard.investments'), 'fa-chart-line', 'Investasi', 'bg-purple-50 dark:bg-purple-900/20 text-purple-600']] as [$url, $icon, $label, $cls])
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

    {{-- ─── MODAL: TAMBAH TUGAS ────────────────────────────────────────── --}}
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
                            <option value="q1">🔴 Do First</option>
                            <option value="q2">🔵 Schedule</option>
                            <option value="q3">🟠 Delegate</option>
                            <option value="q4">⚪ Eliminate</option>
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
                        <option>Skripsi</option>
                        <option>PKL</option>
                        <option>Kuliah</option>
                        <option>Freelance</option>
                        <option>Creative</option>
                        <option>Personal</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-focus-task')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="saveFocusTask()"
                    class="flex-1 py-2.5 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
                    <i class="fa-solid fa-plus mr-1.5"></i>Tambah
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ── Live clock ──────────────────────────────────────────────────────
        (function tickClock() {
            const el = document.getElementById('live-clock');
            const nl = document.getElementById('now-label');
            if (el) {
                const n = new Date();
                const ts = n.toTimeString().slice(0, 5);
                el.textContent = ts;
                if (nl) nl.textContent = ts;

                // Update needle position
                const h = n.getHours() + n.getMinutes() / 60;
                const pct = Math.max(0, Math.min(100, ((h - 6) / 17) * 100));
                const needle = document.getElementById('now-needle');
                if (needle) needle.style.left = pct + '%';
            }
            setTimeout(tickClock, 15000); // update every 15s
        })();

        // ── Extend needle height to cover all rows ──────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            const needle = document.getElementById('now-needle');
            const grid = document.getElementById('gantt-grid');
            if (needle && grid) {
                const gridH = grid.offsetHeight;
                needle.style.height = gridH + 'px';
                needle.style.top = '28px'; // offset header
            }

            // Scroll gantt to show current time
            const pct = {{ $needlePct }};
            const wrap = document.querySelector('.gantt-wrap');
            if (wrap) {
                const scrollTo = (wrap.scrollWidth * pct / 100) - (wrap.clientWidth / 2);
                wrap.scrollLeft = Math.max(0, scrollTo);
            }
        });

        function openModal(id) {
            document.getElementById(id)?.classList.remove('hidden');
            document.body.classList.add('modal-open');
        }

        function closeModal(id) {
            document.getElementById(id)?.classList.add('hidden');
            document.body.classList.remove('modal-open');
        }

        function saveFocusTask() {
            const title = document.getElementById('ft-title').value.trim();
            const quadrant = document.getElementById('ft-quadrant').value;
            const deadline = document.getElementById('ft-deadline').value;
            const category = document.getElementById('ft-category').value;
            if (!title) {
                alert('Isi judul tugas dulu');
                return;
            }

            const colors = {
                q1: 'priority-do bg-red-50 dark:bg-red-900/10',
                q2: 'priority-schedule bg-blue-50 dark:bg-blue-900/10',
                q3: 'priority-delegate bg-orange-50 dark:bg-orange-900/10',
                q4: 'priority-eliminate bg-stone-50 dark:bg-stone-800'
            };
            const el = document.createElement('div');
            el.className = colors[quadrant] + ' rounded-r-xl p-2.5 mt-2';
            el.innerHTML =
                `<p class="text-sm font-medium text-stone-800 dark:text-white">${title}</p><span class="text-[10px] text-stone-400">${category}${deadline ? ' · ' + deadline : ''}</span>`;
            document.getElementById(quadrant + '-tasks')?.appendChild(el);
            closeModal('modal-add-focus-task');
            document.getElementById('ft-title').value = '';

            const t = document.createElement('div');
            t.className =
                'fixed bottom-6 right-6 z-[9999] flex items-center gap-2 px-4 py-3 bg-emerald-500 text-white text-sm font-medium rounded-2xl shadow-xl';
            t.innerHTML = '<i class="fa-solid fa-check-circle"></i> Tugas berhasil ditambahkan!';
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 2500);
        }
    </script>
@endpush
