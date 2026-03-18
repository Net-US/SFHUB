@extends('layouts.app-dashboard')
@section('title', 'Focus Today | StudentHub')
@section('page-title', 'Focus Today')

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

        .card-hover {
            transition: all .18s
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, .1)
        }

        /* Gantt Chart Styles */
        .gantt-wrap {
            position: relative;
            overflow-x: auto;
            overflow-y: hidden;
            border-radius: 12px;
            background: linear-gradient(to right, #f8fafc, #f1f5f9);
        }

        .dark .gantt-wrap {
            background: linear-gradient(to right, #292524, #44403c);
        }

        .gantt-grid {
            min-width: 900px;
            position: relative;
        }

        .gantt-header {
            display: flex;
            margin-left: 130px;
            min-width: calc(100% - 130px);
        }

        .gantt-hour-lbl {
            flex: 1;
            text-align: center;
            font-size: 9px;
            color: #a8a29e;
            padding-bottom: 4px;
            border-right: 1px dashed #e7e5e440;
        }

        .dark .gantt-hour-lbl {
            color: #57534e;
        }

        .gantt-row {
            display: flex;
            min-height: 40px;
            border-bottom: 1px solid #e7e5e420;
        }

        .gantt-label {
            width: 130px;
            display: flex;
            align-items: center;
            padding: 0 10px;
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            background: #f9fafb;
            border-right: 1px solid #e5e7eb;
        }

        .dark .gantt-label {
            color: #f9fafb;
            background: #1f2937;
            border-color: #374151;
        }

        .gantt-track {
            flex: 1;
            position: relative;
            background: linear-gradient(to right, #f3f4f6 1px, transparent 1px);
            background-size: 37.5px 100%;
        }

        .gantt-hour-grid {
            position: absolute;
            inset: 0;
            display: flex;
        }

        .gantt-hour-cell {
            flex: 1;
            border-right: 1px dashed #e5e7eb30;
        }

        .gantt-block {
            position: absolute;
            top: 8px;
            bottom: 8px;
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
            border-radius: 6px;
        }

        .gantt-block:hover {
            opacity: .85;
            transform: translateY(-1px);
        }

        .gantt-block.active {
            box-shadow: 0 0 0 2.5px white, 0 0 0 4.5px currentColor, 0 4px 12px rgba(0, 0, 0, .2);
        }

        .gantt-block.suggested {
            border: 2px dashed rgba(255, 255, 255, .5);
            opacity: .85;
        }

        .gantt-block .countdown-badge {
            margin-left: auto;
            font-size: 9px;
            padding: 2px 6px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 4px;
            margin-left: 6px;
        }

        .now-needle {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 2.5px;
            background: linear-gradient(to bottom, #f97316, #ef4444);
            z-index: 20;
            border-radius: 2px;
            pointer-events: none;
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
            animation: pulseNow 2s ease-in-out infinite;
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
            animation: slideNow .4s ease-out both;
        }

        .now-label::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: #f97316;
        }

        /* Priority borders for Eisenhower */
        .priority-do {
            border-left: 3.5px solid #ef4444;
        }

        .priority-schedule {
            border-left: 3.5px solid #3b82f6;
        }

        .priority-delegate {
            border-left: 3.5px solid #f97316;
        }

        .priority-eliminate {
            border-left: 3.5px solid #9ca3af;
        }

        /* Countdown Cards Styling */
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
            
            .gantt-label {
                width: 100px;
                font-size: 10px;
            }
            
            .gantt-header {
                margin-left: 100px;
                min-width: calc(100% - 100px);
            }
            
            .gantt-block {
                font-size: 10px;
                padding: 0 6px;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $currentHour = $currentHour ?? now()->hour + now()->minute / 60;
        $timelineStart = 0;
        $timelineEnd = 24;
        $timelineHours = 24;
        $nowDecimal = $currentHour;
        $needlePct = max(0, min(100, (($nowDecimal - $timelineStart) / $timelineHours) * 100));
        $headerHours = range($timelineStart, $timelineEnd - 1);

        // Default values
        $ganttRows = $ganttRows ?? [];
        $eisenhowerTasks = $eisenhowerTasks ?? [
            'q1' => collect(),
            'q2' => collect(),
            'q3' => collect(),
            'q4' => collect(),
        ];
        $recommendations = $recommendations ?? [];
        $countdowns = $countdowns ?? [];
        $gaps = $gaps ?? [];
        $todayDoneCount = $todayDoneCount ?? 0;
        $todayTotalCount = $todayTotalCount ?? 0;
        $currentActivity = $currentActivity ?? null;

        $actNow = $currentActivity;
    @endphp

    <div class="fade-up space-y-5">
        {{-- Header Section --}}
        <div
            class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-stone-800 via-stone-900 to-black text-white shadow-2xl">
            <div class="absolute inset-0 opacity-15"
                style="background-image:radial-gradient(circle at 20% 50%,#f97316 0%,transparent 60%),radial-gradient(circle at 80% 20%,#3b82f6 0%,transparent 50%)">
            </div>
            <div class="relative z-10 p-6 md:p-8">
                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                    <div class="flex-1">
                        <span
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/15 backdrop-blur-sm border border-white/10 text-xs font-medium mb-4">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Smart Timeline Aktif
                        </span>
                        <h2 class="text-3xl md:text-4xl font-bold mb-2">
                            Selamat datang, <span class="text-orange-400">{{ auth()->user()->name }}</span> 👋
                        </h2>
                        <p class="text-stone-300 text-sm md:text-base mb-4">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1 bg-white/10 rounded-full text-xs font-medium">{{ now()->isoFormat('dddd') }}</span>
                            <span class="px-3 py-1 bg-white/10 rounded-full text-xs font-medium">{{ now()->format('d F Y') }}</span>
                            <span class="px-3 py-1 bg-white/10 rounded-full text-xs font-medium">{{ now()->format('H:i') }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="text-center sm:text-right">
                            <p class="text-sm font-medium text-stone-300 mb-1">Progress Hari Ini</p>
                            <div class="flex items-baseline gap-1 justify-center sm:justify-end">
                                <span class="text-3xl font-bold">{{ $todayDoneCount ?? 0 }}</span>
                                <span class="text-lg text-stone-400">/ {{ $todayTotalCount ?? 0 }}</span>
                            </div>
                            <div class="w-32 h-2 bg-white/20 rounded-full mt-2 mx-auto sm:mx-0 sm:ml-auto">
                                <div class="h-2 bg-emerald-400 rounded-full transition-all duration-500"
                                    style="width:{{ $todayTotalCount > 0 ? ($todayDoneCount / $todayTotalCount) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div class="text-center sm:text-right">
                            <p class="text-sm font-medium text-stone-300 mb-1">Waktu Tersisa</p>
                            <div class="flex items-center gap-2 justify-center sm:justify-end">
                                <i class="fa-solid fa-clock text-stone-400"></i>
                                <span class="text-2xl font-bold text-white" id="live-clock">{{ now()->format('H:i') }}</span>
                            </div>
                            <p class="text-[10px] text-stone-500 mt-1.5">{{ round(24 - $currentHour) }} jam tersisa hari ini</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- GANTT TIMELINE --}}
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800 gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <i class="fa-solid fa-timeline text-orange-500 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-stone-800 dark:text-white text-sm">Smart Timeline —
                            {{ now()->isoFormat('dddd, D MMM YYYY') }}</h3>
                        <p class="text-[11px] text-stone-400">
                            06:00–23:00 · Blok solid = jadwal tetap · <span
                                class="border-b border-dashed border-stone-400">Garis putus</span> = saran tugas ·
                            Jarum oranye = sekarang
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ([['#3b82f6', 'Akademik'], ['#10b981', 'PKL'], ['#f97316', 'Skripsi'], ['#8b5cf6', 'Freelance'], ['#94a3b8', 'Personal']] as [$c, $l])
                        <div class="hidden md:flex items-center gap-1">
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
                                            $isSug = !($b['is_fixed'] ?? true);
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
                                        <div class="gantt-block {{ $isActive ? 'active' : '' }} {{ $isSug ? 'suggested' : '' }}"
                                            style="left:{{ $left }}%;width:{{ $width }}%;background:{{ $b['color'] }};{{ $isActive ? 'outline:2.5px solid ' . $b['color'] . ';outline-offset:2px' : '' }}"
                                            title="{{ $b['label'] }} ({{ $fmtStart }}–{{ $fmtEnd }}){{ $isSug ? ' — Saran Tugas' : '' }}">
                                            <span class="truncate">{{ $b['icon'] ?? '📌' }}
                                                {{ Str::limit($b['label'], 25) }}</span>
                                            @if ($isActive)
                                                <span
                                                    class="ml-1.5 px-1.5 py-0.5 bg-white/25 rounded text-[9px] font-bold flex-shrink-0">SEKARANG</span>
                                            @endif
                                            @if ($isSug && isset($b['countdown']))
                                                <span class="countdown-badge">{{ $b['countdown']['days'] }}h</span>
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
                <div class="mx-5 mb-5 flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 rounded-xl border-2"
                    style="background:{{ $actNow['color'] }}18;border-color:{{ $actNow['color'] }}40">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-lg"
                        style="background:{{ $actNow['color'] }}30">
                        {{ $actNow['icon'] ?? '📌' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-stone-800 dark:text-white text-sm flex flex-col sm:flex-row sm:items-center gap-2 flex-wrap">
                            {{ $actNow['label'] }}
                            <span class="text-[10px] px-2 py-0.5 rounded-full text-white font-bold animate-pulse"
                                style="background:{{ $actNow['color'] }}">AKTIF SEKARANG</span>
                            @if (!($actNow['is_fixed'] ?? true))
                                <span
                                    class="text-[10px] px-2 py-0.5 rounded-full bg-stone-200 dark:bg-stone-700 text-stone-600 dark:text-stone-400 font-medium">💡
                                    Saran Sistem</span>
                            @endif
                        </p>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">
                            {{ sprintf('%02d:%02d', (int) $actNow['start'], (int) (($actNow['start'] - (int) $actNow['start']) * 60)) }}
                            –
                            {{ sprintf('%02d:%02d', (int) $actNow['end'], (int) (($actNow['end'] - (int) $actNow['end']) * 60)) }}
                            ·
                            Sisa ~{{ round($actNow['end'] - $nowDecimal, 1) }} jam
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0 w-full sm:w-auto">
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

        {{-- Countdown Section --}}
        @if (!empty($countdowns))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($countdowns as $cd)
                    @php
                        $isOverdue = $cd['countdown']['is_overdue'] ?? false;
                        $days = abs($cd['countdown']['days'] ?? 0);
                        $hours = $cd['countdown']['hours'] ?? 0;
                        $minutes = $cd['countdown']['minutes'] ?? 0;
                    @endphp
                    <div class="countdown-card rounded-2xl p-5 text-white"
                        style="background: linear-gradient(135deg, {{ $cd['color'] }}, {{ $cd['color'] }}dd)">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid {{ $cd['icon'] }}"></i>
                            <span
                                class="text-xs font-medium opacity-80 uppercase tracking-wider">{{ $cd['type'] }}</span>
                        </div>
                        <h4 class="font-bold text-sm mb-3 truncate">{{ Str::limit($cd['label'], 25) }}</h4>

                        @if ($isOverdue)
                            <div class="text-red-200 font-bold text-sm mb-1">TERLAMBAT</div>
                            <div class="flex gap-2">
                                <div class="countdown-unit">
                                    <div class="countdown-num">{{ $days }}</div>
                                    <div class="countdown-lbl">hari</div>
                                </div>
                            </div>
                        @else
                            <div class="flex gap-3">
                                @if ($days > 0)
                                    <div class="countdown-unit">
                                        <div class="countdown-num">{{ $days }}</div>
                                        <div class="countdown-lbl">hari</div>
                                    </div>
                                @endif
                                @if ($days == 0 && $hours > 0)
                                    <div class="countdown-unit">
                                        <div class="countdown-num">{{ $hours }}</div>
                                        <div class="countdown-lbl">jam</div>
                                    </div>
                                    <div class="countdown-unit">
                                        <div class="countdown-num">{{ $minutes }}</div>
                                        <div class="countdown-lbl">menit</div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Eisenhower Matrix + Rekomendasi --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            {{-- Eisenhower Matrix --}}
            <div
                class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                            <i class="fa-solid fa-th text-orange-500 text-sm"></i>
                        </div>
                        <h3 class="font-bold text-stone-800 dark:text-white text-sm">Eisenhower Matrix</h3>
                    </div>
                </div>

                <div class="p-5">
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Q1: Urgent & Important --}}
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold text-red-600 dark:text-red-400">🔴 URGENT & PENTING</h4>
                            @forelse($eisenhowerTasks['q1'] ?? collect() as $task)
                                <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800">
                                    <p class="text-xs font-medium text-stone-800 dark:text-white truncate">{{ $task->title }}</p>
                                    @if($task->due_date)
                                        <p class="text-[10px] text-red-600 dark:text-red-400 mt-1">{{ $task->due_date->isoFormat('D MMM') }}</p>
                                    @endif
                                </div>
                            @empty
                                <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl text-center">
                                    <p class="text-xs text-stone-400">Kosong</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Q2: Important Not Urgent --}}
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold text-blue-600 dark:text-blue-400">🔵 PENTING</h4>
                            @forelse($eisenhowerTasks['q2'] ?? collect() as $task)
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                                    <p class="text-xs font-medium text-stone-800 dark:text-white truncate">{{ $task->title }}</p>
                                    @if($task->due_date)
                                        <p class="text-[10px] text-blue-600 dark:text-blue-400 mt-1">{{ $task->due_date->isoFormat('D MMM') }}</p>
                                    @endif
                                </div>
                            @empty
                                <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl text-center">
                                    <p class="text-xs text-stone-400">Kosong</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Q3: Urgent Not Important --}}
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold text-orange-600 dark:text-orange-400">🟠 URGENT</h4>
                            @forelse($eisenhowerTasks['q3'] ?? collect() as $task)
                                <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-xl border border-orange-200 dark:border-orange-800">
                                    <p class="text-xs font-medium text-stone-800 dark:text-white truncate">{{ $task->title }}</p>
                                    @if($task->due_date)
                                        <p class="text-[10px] text-orange-600 dark:text-orange-400 mt-1">{{ $task->due_date->isoFormat('D MMM') }}</p>
                                    @endif
                                </div>
                            @empty
                                <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl text-center">
                                    <p class="text-xs text-stone-400">Kosong</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Q4: Not Urgent Not Important --}}
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold text-gray-600 dark:text-gray-400">⚪ NORMAL</h4>
                            @forelse($eisenhowerTasks['q4'] ?? collect() as $task)
                                <div class="p-3 bg-gray-50 dark:bg-gray-900/20 rounded-xl border border-gray-200 dark:border-gray-800">
                                    <p class="text-xs font-medium text-stone-800 dark:text-white truncate">{{ $task->title }}</p>
                                    @if($task->due_date)
                                        <p class="text-[10px] text-gray-600 dark:text-gray-400 mt-1">{{ $task->due_date->isoFormat('D MMM') }}</p>
                                    @endif
                                </div>
                            @empty
                                <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl text-center">
                                    <p class="text-xs text-stone-400">Kosong</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rekomendasi --}}
            <div class="space-y-5">
                @if (!empty($recommendations))
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-9 h-9 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-brain text-violet-500 text-sm"></i>
                            </div>
                            <h3 class="font-bold text-stone-800 dark:text-white text-sm">Rekomendasi Sistem</h3>
                        </div>
                        <div class="space-y-3">
                            @foreach ($recommendations as $rec)
                                <div class="flex items-start gap-3 p-3 {{ $rec['cls'] }} rounded-xl">
                                    <i class="fa-solid {{ $rec['icon'] }} mt-0.5 text-sm shrink-0"></i>
                                    <div>
                                        <p class="text-xs font-bold text-stone-800 dark:text-white">{{ $rec['title'] }}</p>
                                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5 leading-relaxed">
                                            {{ $rec['desc'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Tugas Prioritas --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-list-check text-orange-500 text-sm"></i>
                            </div>
                            <h3 class="font-bold text-stone-800 dark:text-white text-sm">Tugas Prioritas</h3>
                        </div>
                        <button onclick="openModal('modal-add-focus-task')"
                            class="flex items-center gap-1.5 px-3 py-1.5 bg-stone-800 dark:bg-stone-700 hover:bg-stone-900 text-white text-xs font-medium rounded-xl transition-colors">
                            <i class="fa-solid fa-plus text-[10px]"></i> Tugas
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-stone-50 dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-stone-600 dark:text-stone-400">Tugas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-stone-600 dark:text-stone-400">Kategori</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-stone-600 dark:text-stone-400">Prioritas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-stone-600 dark:text-stone-400">Deadline</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-stone-600 dark:text-stone-400">Sisa Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200 dark:divide-stone-700">
                                @php
                                    // Gabungkan semua tugas dari Eisenhower matrix
                                    $allTasks = collect(array_merge(
                                        ($eisenhowerTasks['q1'] ?? collect())->toArray(),
                                        ($eisenhowerTasks['q2'] ?? collect())->toArray(),
                                        ($eisenhowerTasks['q3'] ?? collect())->toArray(),
                                        ($eisenhowerTasks['q4'] ?? collect())->toArray()
                                    ));
                                    
                                    // Urutkan berdasarkan prioritas (1=highest, 4=lowest) lalu deadline
                                    $sortedTasks = $allTasks->sortBy(function($task) {
                                        $priorityOrder = [
                                            'urgent-important' => 1,
                                            'important-not-urgent' => 2,
                                            'urgent-not-important' => 3,
                                            'not-urgent-not-important' => 4
                                        ];
                                        $priority = $priorityOrder[$task->priority ?? 'not-urgent-not-important'] ?? 4;
                                        $deadline = $task->due_date ? $task->due_date->timestamp : PHP_INT_MAX;
                                        return [$priority, $deadline];
                                    })->take(10); // Ambil 10 tugas teratas
                                @endphp
                                
                                @forelse($sortedTasks as $task)
                                    @php
                                        $priorityColors = [
                                            'urgent-important' => 'bg-red-100 text-red-700',
                                            'important-not-urgent' => 'bg-blue-100 text-blue-700',
                                            'urgent-not-important' => 'bg-orange-100 text-orange-700',
                                            'not-urgent-not-important' => 'bg-gray-100 text-gray-700'
                                        ];
                                        $priorityLabels = [
                                            'urgent-important' => '🔴 Urgent & Penting',
                                            'important-not-urgent' => '🔵 Penting',
                                            'urgent-not-important' => '🟠 Urgent',
                                            'not-urgent-not-important' => '⚪ Normal'
                                        ];
                                        $categoryColors = [
                                            'academic' => 'bg-blue-100 text-blue-700',
                                            'skripsi' => 'bg-orange-100 text-orange-700',
                                            'pkl' => 'bg-green-100 text-green-700',
                                            'creative' => 'bg-purple-100 text-purple-700',
                                            'freelance' => 'bg-pink-100 text-pink-700',
                                            'personal' => 'bg-gray-100 text-gray-700',
                                            'health' => 'bg-emerald-100 text-emerald-700',
                                            'routine' => 'bg-cyan-100 text-cyan-700'
                                        ];
                                        $categoryLabels = [
                                            'academic' => 'Akademik',
                                            'skripsi' => 'Skripsi',
                                            'pkl' => 'PKL',
                                            'creative' => 'Kreatif',
                                            'freelance' => 'Freelance',
                                            'personal' => 'Personal',
                                            'health' => 'Kesehatan',
                                            'routine' => 'Rutin'
                                        ];
                                        $priorityClass = $priorityColors[$task->priority] ?? 'bg-gray-100 text-gray-700';
                                        $priorityLabel = $priorityLabels[$task->priority] ?? '⚪ Normal';
                                        $categoryClass = $categoryColors[$task->category] ?? 'bg-gray-100 text-gray-700';
                                        $categoryLabel = $categoryLabels[$task->category] ?? ucfirst($task->category);
                                    @endphp
                                    <tr class="hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">
                                        <td class="px-4 py-3">
                                            <p class="text-sm font-medium text-stone-800 dark:text-white">{{ Str::limit($task->title, 40) }}</p>
                                            @if($task->description)
                                                <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">{{ Str::limit($task->description, 60) }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $categoryClass }}">
                                                {{ $categoryLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $priorityClass }}">
                                                {{ $priorityLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="text-sm text-stone-800 dark:text-white">
                                                {{ $task->due_date ? $task->due_date->isoFormat('D MMM YYYY') : 'Tanpa deadline' }}
                                            </p>
                                            @if($task->due_date)
                                                <p class="text-xs text-stone-500">{{ $task->due_date->isoFormat('HH:mm') }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if(isset($task->countdown_label))
                                                <span class="text-xs font-medium {{ $task->countdown['is_overdue'] ?? false ? 'text-red-600' : 'text-amber-600' }}">
                                                    {{ $task->countdown_label }}
                                                </span>
                                            @else
                                                <span class="text-xs text-stone-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center">
                                            <i class="fa-solid fa-check-circle text-emerald-500 text-2xl mb-2"></i>
                                            <p class="text-sm text-stone-500 dark:text-stone-400">Tidak ada tugas pending</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Add Focus Task --}}
    <div id="modal-add-focus-task"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl border border-stone-200 dark:border-stone-800">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white">Tambah Tugas Focus</h3>
                <button onclick="closeModal('modal-add-focus-task')"
                    class="text-stone-400 hover:text-stone-600"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form action="{{ route('dashboard.focus.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Judul Tugas</label>
                    <input type="text" name="title" required
                        class="w-full px-3 py-2 border border-stone-300 dark:border-stone-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:bg-stone-800 dark:text-white"
                        placeholder="Masukkan judul tugas">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Kategori</label>
                        <select name="category" required
                            class="w-full px-3 py-2 border border-stone-300 dark:border-stone-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:bg-stone-800 dark:text-white">
                            <option value="academic">📚 Akademik</option>
                            <option value="skripsi">✍️ Skripsi</option>
                            <option value="pkl">💼 PKL</option>
                            <option value="creative">🎬 Kreatif</option>
                            <option value="freelance">💰 Freelance</option>
                            <option value="personal">👤 Personal</option>
                            <option value="health">🏃 Kesehatan</option>
                            <option value="routine">🔄 Rutinitas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Prioritas</label>
                        <select name="priority" required
                            class="w-full px-3 py-2 border border-stone-300 dark:border-stone-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:bg-stone-800 dark:text-white">
                            <option value="urgent-important">🔴 Urgent & Penting</option>
                            <option value="important-not-urgent">🔵 Penting</option>
                            <option value="urgent-not-important">🟠 Urgent</option>
                            <option value="not-urgent-not-important">⚪ Normal</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Deadline</label>
                    <input type="datetime-local" name="due_date"
                        class="w-full px-3 py-2 border border-stone-300 dark:border-stone-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Estimasi Waktu</label>
                    <input type="text" name="estimated_time"
                        class="w-full px-3 py-2 border border-stone-300 dark:border-stone-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:bg-stone-800 dark:text-white"
                        placeholder="Contoh: 2 jam">
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeModal('modal-add-focus-task')"
                        class="flex-1 px-4 py-2 border border-stone-300 dark:border-stone-600 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium">
                        Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Update clock every minute
        setInterval(() => {
            const now = new Date();
            const timeStr = now.toTimeString().slice(0,5);
            const clockEl = document.getElementById('live-clock');
            if (clockEl) clockEl.textContent = timeStr;
            
            const nowLabel = document.getElementById('now-label');
            if (nowLabel) nowLabel.textContent = timeStr;
            
            // Update needle position
            const currentHour = now.getHours() + now.getMinutes() / 60;
            const needlePct = Math.max(0, Math.min(100, ((currentHour - 0) / 24) * 100));
            const needle = document.getElementById('now-needle');
            if (needle) needle.style.left = needlePct + '%';
        }, 60000);

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal on backdrop click
        document.getElementById('modal-add-focus-task')?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                closeModal('modal-add-focus-task');
            }
        });
    </script>
@endsection
