{{-- resources/views/dashboard/academic.blade.php --}}
@extends('layouts.app-dashboard')
@section('title', 'Academic Hub | StudentHub')
@section('page-title', 'Academic Hub')

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

        .tab-pill {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem 1rem;
            border-radius: .75rem;
            font-size: .8125rem;
            font-weight: 500;
            color: #78716c;
            cursor: pointer;
            border: none;
            background: none;
            transition: all .15s
        }

        .tab-pill:hover {
            background: #f5f5f4;
            color: #1c1917
        }

        .dark .tab-pill:hover {
            background: #292524;
            color: #fafaf9
        }

        .tab-pill.active {
            background: #fff7ed;
            color: #ea580c;
            font-weight: 600
        }

        .dark .tab-pill.active {
            background: rgba(249, 115, 22, .13);
            color: #fb923c
        }

        .task-row {
            transition: background .12s
        }

        .task-row:hover {
            background: #fafaf9
        }

        .dark .task-row:hover {
            background: #292524
        }

        .fi {
            width: 100%;
            border: 1.5px solid #e7e5e4;
            border-radius: .8rem;
            padding: .6rem .95rem;
            font-size: .875rem;
            background: #fafaf9;
            color: #1c1917;
            outline: none;
            transition: border .18s, box-shadow .18s
        }

        .fi:focus {
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, .11)
        }

        .dark .fi {
            background: #292524;
            border-color: #44403c;
            color: #fafaf9
        }

        .dark .fi:focus {
            border-color: #f97316;
            background: #1c1917
        }

        .fi-label {
            display: block;
            font-size: .7rem;
            font-weight: 700;
            color: #a8a29e;
            letter-spacing: .05em;
            text-transform: uppercase;
            margin-bottom: .35rem
        }

        .schedule-card {
            position: relative;
            border-left: 3.5px solid var(--c, #3b82f6);
            transition: all .18s
        }

        .schedule-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, .08)
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
            width: 100px;
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
            height: 26px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            padding: 0 8px;
            font-size: 10px;
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
            white-space: nowrap
        }

        .gantt-header {
            display: flex;
            margin-left: 100px;
            min-width: calc(100% - 100px)
        }

        .gantt-hour-lbl {
            flex: 1;
            text-align: center;
            font-size: 9px;
            color: #a8a29e;
            padding-bottom: 4px;
            border-right: 1px dashed #e7e5e440
        }
    </style>
@endpush

@section('content')
    @php
        $courses = collect($courses ?? []);
        $tasks = collect($tasks ?? []);
        $milestones = collect($milestones ?? []);
        $thesisProgress = $thesisProgress ?? 0;
        $tasksByCourse = $tasks->groupBy('course_id');

        $hariOrder = [
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
            'Minggu' => 7,
        ];
        $hariColor = [
            'Senin' => '#10b981',
            'Selasa' => '#3b82f6',
            'Rabu' => '#8b5cf6',
            'Kamis' => '#f97316',
            'Jumat' => '#ef4444',
            'Sabtu' => '#f59e0b',
            'Minggu' => '#94a3b8',
        ];
        $todayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w')];

        $scheduleByDay = $courses
            ->sortBy(fn($c) => [$hariOrder[$c['day'] ?? '-'] ?? 8, $c['start_time']])
            ->groupBy('day');

        // GANTT TIMELINE SETUP
        $timelineStart = 6; // Mulai jam 06:00
        $timelineEnd = 23; // Sampai jam 23:00
        $timelineHours = $timelineEnd - $timelineStart;

        $nowDecimal = now()->hour + now()->minute / 60;
        $needlePct = max(0, min(100, (($nowDecimal - $timelineStart) / $timelineHours) * 100));

        $ganttRows = [];
        foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari) {
            $dayCourses = $scheduleByDay->get($hari, collect());
            if ($dayCourses->isEmpty()) {
                continue;
            } // Hanya tampilkan hari yang ada matkul

            $blocks = [];
            foreach ($dayCourses as $c) {
                $st = explode(':', $c['start_time'] ?: '00:00');
                $et = explode(':', $c['end_time'] ?: '00:00');
                $sDec = (int) $st[0] + (int) ($st[1] ?? 0) / 60;
                $eDec = (int) $et[0] + (int) ($et[1] ?? 0) / 60;

                $blocks[] = [
                    'start' => max($timelineStart, $sDec),
                    'end' => min($timelineEnd, $eDec),
                    'label' => $c['name'],
                    'color' => $hariColor[$hari] ?? '#3b82f6',
                    'start_time' => $c['start_time'],
                    'end_time' => $c['end_time'],
                ];
            }
            $ganttRows[] = ['label' => $hari, 'blocks' => $blocks];
        }
        $headerHours = range($timelineStart, $timelineEnd);
    @endphp

    <div class="fade-up space-y-5">

        {{-- ── Header ─────────────────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Academic Hub</h2>
                <p class="text-stone-400 text-xs">Kelola mata kuliah, jadwal, tugas & skripsi</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button onclick="openModal('modal-add-course')"
                    class="flex items-center gap-2 px-4 py-2 bg-stone-800 dark:bg-stone-700 hover:bg-stone-900 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-plus text-xs"></i> Mata Kuliah
                </button>
                <button onclick="openModal('modal-add-task')"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-plus text-xs"></i> Tugas
                </button>
            </div>
        </div>

        @if (session('success'))
            <div
                class="flex items-center gap-3 px-5 py-3.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm">
                <i class="fa-solid fa-circle-check flex-shrink-0"></i>{{ session('success') }}
            </div>
        @endif

        {{-- ── Tabs ────────────────────────────────────────────────────────────── --}}
        <div class="flex gap-1 bg-stone-100 dark:bg-stone-800 p-1 rounded-xl w-fit flex-wrap">
            @foreach ([['jadwal', 'fa-calendar-week', 'Jadwal'], ['matkul', 'fa-graduation-cap', 'Mata Kuliah'], ['tugas', 'fa-clipboard-list', 'Tugas'], ['skripsi', 'fa-book-open', 'Skripsi']] as [$id, $ic, $lbl])
                <button onclick="switchAcadTab('{{ $id }}')" id="acadtab-{{ $id }}"
                    class="tab-pill {{ $id === 'jadwal' ? 'active' : '' }}">
                    <i class="fa-solid {{ $ic }} text-xs"></i> {{ $lbl }}
                </button>
            @endforeach
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════
         TAB: JADWAL KULIAH (Gantt & Kotak)
        ══════════════════════════════════════════════════════════════════════ --}}
        <div id="acad-jadwal" class="acad-pane space-y-4">

            {{-- ── TIMELINE JADWAL (GANTT) ── --}}
            @if (!empty($ganttRows))
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden mb-6">
                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-timeline text-blue-500 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-stone-800 dark:text-white text-sm">Visualisasi Jadwal Mingguan
                                </h3>
                                <p class="text-[11px] text-stone-400">Ikhtisar padat jadwal 06:00–23:00</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-5 pt-4 pb-5">
                        <div class="gantt-wrap">
                            <div class="gantt-grid" id="gantt-grid">
                                {{-- Header --}}
                                <div class="gantt-header">
                                    @foreach ($headerHours as $h)
                                        <div class="gantt-hour-lbl">{{ sprintf('%02d', $h) }}</div>
                                    @endforeach
                                </div>
                                {{-- Rows --}}
                                @foreach ($ganttRows as $row)
                                    <div
                                        class="gantt-row {{ $row['label'] === $todayName ? 'bg-orange-50 dark:bg-orange-900/10' : '' }}">
                                        <div class="gantt-label flex items-center justify-end gap-2">
                                            @if ($row['label'] === $todayName)
                                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                                            @endif
                                            {{ $row['label'] }}
                                        </div>
                                        <div class="gantt-track">
                                            <div class="gantt-hour-grid">
                                                @foreach ($headerHours as $h)
                                                    <div class="gantt-hour-cell"></div>
                                                @endforeach
                                            </div>
                                            @foreach ($row['blocks'] as $b)
                                                @php
                                                    $left = (($b['start'] - $timelineStart) / $timelineHours) * 100;
                                                    $width = (($b['end'] - $b['start']) / $timelineHours) * 100;
                                                @endphp
                                                <div class="gantt-block"
                                                    style="left:{{ $left }}%;width:{{ $width }}%;background:{{ $b['color'] }};"
                                                    title="{{ $b['label'] }} ({{ $b['start_time'] }}–{{ $b['end_time'] }})">
                                                    <span class="truncate">📚 {{ $b['label'] }}</span>
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
                    </div>
                </div>
            @endif

            {{-- ── DAFTAR KELAS MINGGUAN KOTAK-KOTAK ── --}}
            @if ($courses->isEmpty())
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-12 text-center">
                    <div
                        class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-calendar-week text-blue-500 text-2xl"></i>
                    </div>
                    <p class="font-bold text-stone-600 dark:text-stone-400 mb-1">Belum ada mata kuliah</p>
                    <p class="text-xs text-stone-400 mb-4">Tambahkan mata kuliah untuk melihat jadwal otomatis</p>
                    <button onclick="openModal('modal-add-course')"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors">
                        <i class="fa-solid fa-plus mr-1.5"></i>Tambah Mata Kuliah
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach ($hariOrder as $hari => $idx)
                        @php $dayCourses = collect($scheduleByDay->get($hari, [])); @endphp
                        <div
                            class="bg-white dark:bg-stone-900 rounded-2xl border {{ $hari === $todayName ? 'border-orange-300 dark:border-orange-700' : 'border-stone-200 dark:border-stone-800' }} shadow-sm overflow-hidden">
                            <div class="flex items-center justify-between px-5 py-3 border-b border-stone-100 dark:border-stone-800"
                                style="background:{{ $hariColor[$hari] ?? '#94a3b8' }}12">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0"
                                        style="background:{{ $hariColor[$hari] ?? '#94a3b8' }}"></span>
                                    <h4 class="font-bold text-stone-800 dark:text-white text-sm">{{ $hari }}</h4>
                                    @if ($hari === $todayName)
                                        <span
                                            class="text-[10px] bg-orange-500 text-white px-2 py-0.5 rounded-full font-bold animate-pulse">Hari
                                            Ini</span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-stone-400">{{ $dayCourses->count() }} kelas</span>
                            </div>

                            @if ($dayCourses->isEmpty())
                                <div class="px-5 py-4 text-center">
                                    <p class="text-xs text-stone-400 italic">Tidak ada kelas</p>
                                </div>
                            @else
                                <div class="divide-y divide-stone-100 dark:divide-stone-800">
                                    @foreach ($dayCourses->sortBy('start_time') as $c)
                                        @php
                                            $taskCount = $tasksByCourse
                                                ->get($c['id'], collect())
                                                ->where('status', '!=', 'done')
                                                ->count();
                                        @endphp
                                        <div class="flex items-start gap-4 px-5 py-4 schedule-card hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors"
                                            style="--c:{{ $hariColor[$c['day']] ?? '#3b82f6' }}">
                                            <div class="text-center flex-shrink-0 w-14">
                                                <p class="text-xs font-bold text-stone-800 dark:text-white">
                                                    {{ $c['start_time'] ?? '-' }}</p>
                                                <p class="text-[10px] text-stone-400">–</p>
                                                <p class="text-xs font-bold text-stone-800 dark:text-white">
                                                    {{ $c['end_time'] ?? '-' }}</p>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-2 mb-0.5">
                                                            @if ($c['code'])
                                                                <span
                                                                    class="text-[10px] font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2 py-0.5 rounded-full">{{ $c['code'] }}</span>
                                                            @endif
                                                            <span class="text-[10px] text-stone-400">{{ $c['sks'] }}
                                                                SKS</span>
                                                            @if ($taskCount > 0)
                                                                <span
                                                                    class="text-[10px] bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 px-1.5 py-0.5 rounded-full font-bold">{{ $taskCount }}
                                                                    tugas</span>
                                                            @endif
                                                        </div>
                                                        <p
                                                            class="font-semibold text-stone-800 dark:text-white text-sm truncate">
                                                            {{ $c['name'] }}</p>
                                                        <p class="text-xs text-stone-400">
                                                            {{ $c['lecturer'] ?: 'Dosen belum diisi' }}</p>
                                                    </div>
                                                    <div class="flex gap-1 flex-shrink-0">
                                                        <button onclick="editCourse({{ $c['id'] }})"
                                                            class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-400 hover:text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/20 flex items-center justify-center transition-colors"
                                                            title="Edit">
                                                            <i class="fa-solid fa-pen text-[10px]"></i>
                                                        </button>
                                                        <button
                                                            onclick="deleteCourse({{ $c['id'] }}, '{{ addslashes($c['name']) }}')"
                                                            class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center justify-center transition-colors"
                                                            title="Hapus">
                                                            <i class="fa-solid fa-trash text-[10px]"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3 mt-1.5 flex-wrap">
                                                    @if ($c['room'])
                                                        <span class="text-[10px] text-stone-400"><i
                                                                class="fa-solid fa-door-open mr-0.5 text-[9px]"></i>{{ $c['room'] }}</span>
                                                    @endif
                                                    <div class="flex items-center gap-1.5 flex-1 min-w-0">
                                                        <div
                                                            class="flex-1 bg-stone-100 dark:bg-stone-700 rounded-full h-1.5">
                                                            <div class="h-1.5 rounded-full bg-blue-500"
                                                                style="width:{{ $c['progress'] }}%"></div>
                                                        </div>
                                                        <span
                                                            class="text-[10px] text-stone-400 flex-shrink-0">{{ $c['progress'] }}%</span>
                                                    </div>
                                                    @if ($c['drive_link'])
                                                        <a href="{{ $c['drive_link'] }}" target="_blank"
                                                            class="text-[10px] text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-0.5 flex-shrink-0">
                                                            <i class="fa-brands fa-google-drive text-[9px]"></i> Materi
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════
         TAB: MATA KULIAH (card summary)
        ══════════════════════════════════════════════════════════════════════ --}}

        <div id="acad-matkul" class="acad-pane hidden space-y-4">
            @if ($courses->isEmpty())
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-12 text-center">
                    <i class="fa-solid fa-graduation-cap text-4xl text-stone-300 mb-3 block"></i>
                    <p class="font-bold text-stone-500 mb-1">Belum ada mata kuliah</p>
                    <button onclick="openModal('modal-add-course')"
                        class="mt-2 px-4 py-2 bg-stone-800 text-white rounded-xl text-sm">Tambah Sekarang</button>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($courses as $c)
                        @php
                            $cTasks = $tasksByCourse->get($c['id'], collect());
                            $cTasksDone = collect($cTasks)->where('status', 'done')->count();
                        @endphp
                        <div
                            class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                            <div class="h-1" style="background:{{ $hariColor[$c['day']] ?? '#3b82f6' }}">
                            </div>
                            <div class="p-5">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                                            @if ($c['code'])
                                                <span
                                                    class="text-[10px] font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2 py-0.5 rounded-full">{{ $c['code'] }}</span>
                                            @endif
                                            <span class="text-[10px] text-stone-400">{{ $c['sks'] }}
                                                SKS</span>
                                            <span class="text-[10px] text-stone-400">Sem.
                                                {{ $c['semester'] }}</span>
                                        </div>
                                        <h4 class="font-bold text-stone-800 dark:text-white">{{ $c['name'] }}
                                        </h4>
                                        <p class="text-xs text-stone-400 mt-0.5">{{ $c['lecturer'] }}</p>
                                    </div>
                                    <div class="flex gap-1 flex-shrink-0 ml-2">
                                        <button onclick="editCourse({{ $c['id'] }})"
                                            class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-700 hover:bg-orange-100 dark:hover:bg-orange-900/20 text-stone-400 hover:text-orange-500 flex items-center justify-center transition-colors">
                                            <i class="fa-solid fa-pen text-[10px]"></i>
                                        </button>
                                        <button
                                            onclick="deleteCourse({{ $c['id'] }}, '{{ addslashes($c['name']) }}')"
                                            class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-700 hover:bg-rose-100 dark:hover:bg-rose-900/20 text-stone-400 hover:text-rose-500 flex items-center justify-center transition-colors">
                                            <i class="fa-solid fa-trash text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex gap-3 mb-3 flex-wrap">
                                    <span class="text-[11px] text-stone-500 dark:text-stone-400 flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0"
                                            style="background:{{ $hariColor[$c['day']] ?? '#3b82f6' }}"></span>
                                        {{ $c['day'] }}
                                    </span>
                                    <span class="text-[11px] text-stone-500 dark:text-stone-400 flex items-center gap-1">
                                        <i
                                            class="fa-solid fa-clock text-[9px]"></i>{{ $c['start_time'] }}–{{ $c['end_time'] }}
                                    </span>
                                    @if ($c['room'])
                                        <span
                                            class="text-[11px] text-stone-500 dark:text-stone-400 flex items-center gap-1"><i
                                                class="fa-solid fa-door-open text-[9px]"></i>{{ $c['room'] }}</span>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <div class="flex justify-between text-[10px] text-stone-400 mb-1"><span>Progres
                                            Semester</span><span>{{ $c['progress'] }}%</span></div>
                                    <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full bg-blue-500" style="width:{{ $c['progress'] }}%">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <span class="text-[10px] text-stone-400">{{ $cTasks->count() }} tugas ·
                                        {{ $cTasksDone }} selesai</span>
                                    <div class="flex gap-2">
                                        @if ($c['drive_link'])
                                            <a href="{{ $c['drive_link'] }}" target="_blank"
                                                class="flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[11px] font-medium rounded-lg hover:bg-blue-100 transition-colors">
                                                <i class="fa-brands fa-google-drive text-[10px]"></i> Materi
                                            </a>
                                        @endif
                                        <button
                                            onclick="openModal('modal-add-task'); document.getElementById('at-course').value='{{ $c['id'] }}'"
                                            class="flex items-center gap-1.5 px-2.5 py-1 bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 text-[11px] font-medium rounded-lg hover:bg-orange-100 transition-colors">
                                            <i class="fa-solid fa-plus text-[10px]"></i> Tugas
                                        </button>
                                        {{-- Tombol Lihat Sesi (Tambahkan di sini) --}}
                                        <button onclick="openSessionsModal({{ $c['id'] }})"
                                            class="flex items-center gap-1.5 px-2.5 py-1 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 text-[11px] font-medium rounded-lg hover:bg-purple-100 transition-colors">
                                            <i class="fa-solid fa-list-check text-[10px]"></i> 16 Sesi
                                        </button>
                                    </div>
                                </div>
                                @if ($c['notes'])
                                    <div class="mt-3 pt-3 border-t border-stone-100 dark:border-stone-800">
                                        <p class="text-[10px] text-stone-400"><i
                                                class="fa-solid fa-circle-exclamation text-amber-400 mr-1"></i>{{ $c['notes'] }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div id="acad-tugas" class="acad-pane hidden space-y-4">
            <div class="flex gap-2 flex-wrap">
                @foreach (['all' => 'Semua', 'todo' => 'Belum', 'doing' => 'Proses', 'done' => 'Selesai'] as $k => $v)
                    <button onclick="filterTasks('{{ $k }}')" id="tf-{{ $k }}"
                        class="px-3 py-1.5 text-xs rounded-full font-medium transition-colors {{ $k === 'all' ? 'bg-stone-800 dark:bg-stone-700 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:bg-stone-200' }}">
                        {{ $v }}
                    </button>
                @endforeach
            </div>

            <div
                class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                @if ($tasks->isEmpty())
                    <div class="text-center py-12 text-stone-400">
                        <i class="fa-solid fa-clipboard-list text-3xl mb-3 block opacity-30"></i>
                        <p class="text-sm">Belum ada tugas. Tambah tugas pertamamu!</p>
                    </div>
                @else
                    <div class="divide-y divide-stone-100 dark:divide-stone-800" id="task-list">
                        @foreach ($tasks as $t)
                            @php
                                $course = collect($courses)->firstWhere('id', $t['course_id']);
                                $dl = $t['deadline'] ?? null;
                                $daysLeft = $dl ? now()->diffInDays(\Carbon\Carbon::parse($dl), false) : null;
                                $isOverdue = $daysLeft !== null && $daysLeft < 0 && $t['status'] !== 'done';
                                $priCls = match ($t['priority'] ?? 'medium') {
                                    'high' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
                                    'medium' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
                                    default => 'bg-stone-100 dark:bg-stone-700 text-stone-500',
                                };
                            @endphp
                            <div class="task-row flex items-start gap-4 px-5 py-4 {{ $t['status'] === 'done' ? 'opacity-60' : '' }}"
                                data-status="{{ $t['status'] }}" id="task-row-{{ $t['id'] }}">
                                {{-- Checkbox Mark Done --}}
                                <button onclick="toggleTaskDone({{ $t['id'] }}, this)"
                                    title="{{ $t['status'] === 'done' ? 'Tandai belum selesai' : 'Tandai selesai' }}"
                                    class="mt-0.5 w-5 h-5 rounded-full border-2 {{ $t['status'] === 'done' ? 'bg-emerald-500 border-emerald-500' : 'border-stone-300 dark:border-stone-600 hover:border-emerald-400' }} flex items-center justify-center flex-shrink-0 transition-colors">
                                    @if ($t['status'] === 'done')
                                        <i class="fa-solid fa-check text-white text-[9px]"></i>
                                    @endif
                                </button>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <p
                                            class="text-sm font-semibold text-stone-800 dark:text-white {{ $t['status'] === 'done' ? 'line-through' : '' }}">
                                            {{ $t['title'] }}</p>
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <span
                                                class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold {{ $priCls }}">{{ ucfirst($t['priority'] ?? 'medium') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1 flex-wrap">
                                        @if ($course)
                                            <span class="text-[10px] text-stone-400"><i
                                                    class="fa-solid fa-book text-blue-400 mr-0.5"></i>{{ $course['name'] }}</span>
                                        @endif
                                        @if ($dl)
                                            <span
                                                class="text-[10px] font-medium {{ $isOverdue ? 'text-rose-600 dark:text-rose-400' : ($daysLeft <= 2 ? 'text-amber-600 dark:text-amber-400' : 'text-stone-400') }}">
                                                <i class="fa-regular fa-clock mr-0.5"></i>
                                                @if ($isOverdue)
                                                    Terlambat {{ abs((int) $daysLeft) }} hari
                                                @elseif($daysLeft == 0)
                                                    Hari ini!
                                                @elseif($daysLeft == 1)
                                                    Besok
                                                @else
                                                    {{ (int) $daysLeft }} hari lagi
                                                @endif
                                            </span>
                                        @endif
                                        @if (!empty($t['notes']))
                                            <span
                                                class="text-[10px] text-stone-400 truncate max-w-xs">{{ Str::limit($t['notes'], 40) }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 flex-shrink-0">
                                    @if (!empty($t['drive_link']))
                                        <a href="{{ $t['drive_link'] }}" target="_blank"
                                            class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center hover:bg-blue-100 transition-colors"
                                            title="Buka Drive">
                                            <i class="fa-brands fa-google-drive text-sm"></i>
                                        </a>
                                    @endif
                                    <button onclick="editTask({{ json_encode($t) }})"
                                        class="w-8 h-8 rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-400 hover:text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/20 flex items-center justify-center transition-colors"
                                        title="Edit">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <button onclick="deleteTask({{ $t['id'] }}, '{{ addslashes($t['title']) }}')"
                                        class="w-8 h-8 rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center justify-center transition-colors"
                                        title="Hapus">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div id="acad-skripsi" class="acad-pane hidden space-y-5">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- Milestone timeline --}}
                <div
                    class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-bold text-stone-800 dark:text-white text-sm flex items-center gap-2">
                            <i class="fa-solid fa-flag-checkered text-orange-500"></i> Milestone Skripsi
                        </h3>
                        <button onclick="openModal('modal-add-milestone')"
                            class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-xl transition-colors">
                            <i class="fa-solid fa-plus text-[10px]"></i> Milestone
                        </button>
                    </div>
                    @if ($milestones->isEmpty())
                        <p class="text-center text-stone-400 text-sm py-8">Belum ada milestone. Tambahkan milestone
                            skripsimu!</p>
                    @else
                        <div class="relative pl-8 border-l-2 border-stone-200 dark:border-stone-700 space-y-6">
                            @foreach ($milestones as $m)
                                <div class="relative group" id="ms-row-{{ $m['id'] }}">
                                    <div
                                        class="absolute -left-[41px] w-6 h-6 rounded-full border-4 border-white dark:border-stone-900 shadow-sm flex items-center justify-center transition-colors
                        {{ $m['done'] ? 'bg-emerald-500' : ($m['is_active'] ? 'bg-orange-500 animate-pulse' : 'bg-stone-300 dark:bg-stone-600') }}">
                                        @if ($m['done'])
                                            <i class="fa-solid fa-check text-white text-[8px]"></i>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <h4
                                                class="font-semibold text-stone-800 dark:text-white text-sm {{ $m['done'] ? 'line-through opacity-70' : '' }}">
                                                {{ $m['label'] }}</h4>
                                            <p class="text-xs text-stone-400 mt-0.5">Target:
                                                {{ $m['target_date'] ?? 'Belum ditentukan' }}</p>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span
                                                class="text-[10px] px-2 py-0.5 rounded-full font-semibold
                                {{ $m['done'] ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : ($m['is_active'] ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' : 'bg-stone-100 dark:bg-stone-700 text-stone-400') }}">
                                                {{ $m['done'] ? '✅ Selesai' : ($m['is_active'] ? '🔄 Aktif' : '⏳ Belum') }}
                                            </span>
                                            <button onclick="toggleMilestone({{ $m['id'] }}, this)"
                                                class="w-7 h-7 rounded-lg {{ $m['done'] ? 'bg-emerald-100 dark:bg-emerald-900/20 text-emerald-600' : 'bg-stone-100 dark:bg-stone-700 text-stone-400 hover:text-emerald-500' }} flex items-center justify-center transition-colors"
                                                title="Toggle selesai">
                                                <i class="fa-solid fa-check text-[10px]"></i>
                                            </button>
                                            <button onclick="editMilestone({{ json_encode($m) }})"
                                                class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-400 hover:text-orange-500 flex items-center justify-center transition-colors"
                                                title="Edit">
                                                <i class="fa-solid fa-pen text-[10px]"></i>
                                            </button>
                                            <button
                                                onclick="deleteMilestone({{ $m['id'] }}, '{{ addslashes($m['label']) }}')"
                                                class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-400 hover:text-rose-500 flex items-center justify-center transition-colors"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash text-[10px]"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Sidebar skripsi --}}
                <div class="space-y-4">
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-5 text-white shadow-lg">
                        <p class="text-blue-100 text-xs mb-1">Progres Keseluruhan</p>
                        <h3 class="text-3xl font-bold mb-2">{{ $thesisProgress }}%</h3>
                        <div class="w-full bg-white/20 rounded-full h-2 mb-2">
                            <div class="bg-white h-2 rounded-full transition-all" style="width:{{ $thesisProgress }}%">
                            </div>
                        </div>
                        @php
                            $doneMilestones = collect($milestones)->where('done', true)->count();
                            $totalMilestones = collect($milestones)->count();
                        @endphp
                        <p class="text-blue-200 text-xs">{{ $doneMilestones }}/{{ $totalMilestones }} milestone
                            selesai
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                        <h4 class="font-bold text-stone-800 dark:text-white text-sm mb-3">Quick Actions</h4>
                        <div class="space-y-2">
                            <button onclick="openModal('modal-add-milestone')"
                                class="w-full flex items-center gap-3 px-3 py-2.5 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-xl text-xs font-medium transition-colors">
                                <i class="fa-solid fa-plus w-4 text-center"></i>Tambah Milestone
                            </button>
                            <button
                                onclick="openModal('modal-add-task'); document.getElementById('at-type').value='skripsi'"
                                class="w-full flex items-center gap-3 px-3 py-2.5 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 text-purple-700 dark:text-purple-400 rounded-xl text-xs font-medium transition-colors">
                                <i class="fa-solid fa-clipboard-list w-4 text-center"></i>Tambah Tugas Skripsi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

{{-- ✅ BUNGKUS SEMUA MODAL DI DALAM PUSH INI --}}
@push('modals')
    <div id="modal-add-course"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[95vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white" id="modal-course-title">Tambah Mata Kuliah</h3>
                <button onclick="closeModal('modal-add-course')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <input type="hidden" id="course-edit-id">
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fi-label">Kode MK</label>
                        <input type="text" id="mc-code" class="fi" placeholder="IF401">
                    </div>
                    <div>
                        <label class="fi-label">SKS <span class="text-rose-400">*</span></label>
                        <input type="number" id="mc-sks" min="1" max="6" class="fi"
                            value="3">
                    </div>
                </div>
                <div>
                    <label class="fi-label">Nama Mata Kuliah <span class="text-rose-400">*</span></label>
                    <input type="text" id="mc-name" class="fi" placeholder="Metodologi Penelitian">
                </div>
                <div>
                    <label class="fi-label">Dosen Pengampu</label>
                    <input type="text" id="mc-lecturer" class="fi" placeholder="Dr. Nama Dosen, M.T.">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="fi-label">Hari <span class="text-rose-400">*</span></label>
                        <select id="mc-day" class="fi">
                            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                                <option value="{{ $h }}">{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="fi-label">Jam Mulai <span class="text-rose-400">*</span></label>
                        <input type="time" id="mc-start" class="fi" value="08:00">
                    </div>
                    <div>
                        <label class="fi-label">Jam Selesai <span class="text-rose-400">*</span></label>
                        <input type="time" id="mc-end" class="fi" value="10:30">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fi-label">Ruang</label>
                        <input type="text" id="mc-room" class="fi" placeholder="R.202 / Lab B">
                    </div>
                    <div>
                        <label class="fi-label">Semester</label>
                        <input type="number" id="mc-semester" min="1" max="14" class="fi"
                            value="6">
                    </div>
                </div>
                <div>
                    <label class="fi-label"><i class="fa-brands fa-google-drive text-blue-500 mr-1"></i>Link Google
                        Drive
                        Materi</label>
                    <input type="url" id="mc-drive" class="fi" placeholder="https://drive.google.com/...">
                </div>
                <div>
                    <label class="fi-label">Catatan</label>
                    <textarea id="mc-notes" rows="2" class="fi resize-none" placeholder="Catatan penting..."></textarea>
                </div>
                <div id="mc-start-date-wrap">
                    <label class="fi-label">Tanggal Mulai Kuliah (Pertemuan 1) <span
                            class="text-rose-400">*</span></label>
                    <input type="date" id="mc-start-date" class="fi" value="{{ now()->format('Y-m-d') }}">
                    <p class="text-[10px] text-stone-400 mt-1">Sistem akan otomatis membuat jadwal 16 sesi (termasuk
                        UTS & UAS) setiap minggu mulai dari tanggal ini.</p>
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-course')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="saveCourse(this)"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-graduation-cap"></i> <span id="mc-submit-label">Simpan</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ══════ MODAL: ADD / EDIT TASK ══════ --}}
    <div id="modal-add-task"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[95vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white" id="modal-task-title">Tambah Tugas</h3>
                <button onclick="closeModal('modal-add-task')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <input type="hidden" id="task-edit-id">
            <div class="p-6 space-y-4">
                <div>
                    <label class="fi-label">Judul Tugas <span class="text-rose-400">*</span></label>
                    <input type="text" id="at-title" class="fi" placeholder="Nama tugas">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fi-label">Mata Kuliah</label>
                        <select id="at-course" class="fi">
                            <option value="">— Pilih mata kuliah —</option>
                            @foreach ($courses as $c)
                                <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="fi-label">Tipe Tugas</label>
                        <select id="at-type" class="fi">
                            <option value="assignment">📝 Tugas</option>
                            <option value="quiz">📋 Quiz</option>
                            <option value="lab">💻 Praktikum</option>
                            <option value="uts">📚 UTS</option>
                            <option value="uas">📖 UAS</option>
                            <option value="skripsi">🎓 Skripsi</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fi-label">Deadline</label>
                        <input type="date" id="at-deadline" class="fi"
                            value="{{ now()->addWeek()->format('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="fi-label">Prioritas</label>
                        <select id="at-priority" class="fi">
                            <option value="high">🔴 Tinggi</option>
                            <option value="medium" selected>🟡 Sedang</option>
                            <option value="low">🟢 Rendah</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="fi-label"><i class="fa-brands fa-google-drive text-blue-500 mr-1"></i>Link Drive /
                        Tugas</label>
                    <input type="url" id="at-drive" class="fi" placeholder="https://drive.google.com/...">
                    <p class="text-[10px] text-stone-400 mt-1">Link soal, materi, atau file tugas di Google Drive</p>
                </div>
                <div>
                    <label class="fi-label">Catatan / Instruksi</label>
                    <textarea id="at-notes" rows="2" class="fi resize-none" placeholder="Platform submit, instruksi, catatan..."></textarea>
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-task')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="saveTask(this)"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-clipboard-list"></i> <span id="at-submit-label">Simpan Tugas</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ══════ MODAL: ADD / EDIT MILESTONE ══════ --}}
    <div id="modal-add-milestone"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl border border-stone-200 dark:border-stone-800">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white" id="modal-ms-title">Tambah Milestone Skripsi</h3>
                <button onclick="closeModal('modal-add-milestone')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <input type="hidden" id="ms-edit-id">
            <div class="p-6 space-y-4">
                <div>
                    <label class="fi-label">Label Milestone <span class="text-rose-400">*</span></label>
                    <input type="text" id="ms-label" class="fi"
                        placeholder="misal: Pengajuan Judul, Bab 1 Selesai...">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fi-label">Target Tanggal (Deadline)</label>
                        {{-- ✅ UBAH type="text" menjadi type="date" --}}
                        <input type="date" id="ms-date" class="fi">
                        <p class="text-[10px] text-stone-400 mt-1">Tentukan tanggal deadline milestone.</p>
                    </div>
                    <div>
                        <label class="fi-label">Urutan</label>
                        <input type="number" id="ms-order" class="fi" value="999" min="1">
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                    <input type="checkbox" id="ms-active" class="w-4 h-4 accent-orange-500">
                    <label for="ms-active" class="text-sm text-stone-600 dark:text-stone-300 cursor-pointer">Tandai
                        sebagai milestone aktif saat ini</label>
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-milestone')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="saveMilestone(this)"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-flag-checkered"></i> <span id="ms-submit-label">Simpan Milestone</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ══════ MODAL: KELOLA 16 SESI KULIAH ══════ --}}
    <div id="modal-course-sessions"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-2xl shadow-2xl border border-stone-200 dark:border-stone-800 flex flex-col max-h-[90vh]">
            <div
                class="flex items-center justify-between p-5 border-b border-stone-100 dark:border-stone-800 flex-shrink-0">
                <div>
                    <h3 class="font-bold text-stone-900 dark:text-white text-lg" id="mcs-title">Tracking Sesi Kuliah
                    </h3>
                    <p class="text-xs text-stone-400 mt-0.5">Kelola progres, UTS, UAS, dan jadwal libur/undur.</p>
                </div>
                <button onclick="closeModal('modal-course-sessions')"
                    class="text-stone-400 hover:text-stone-700 w-8 h-8 rounded-full hover:bg-stone-100 dark:hover:bg-stone-800 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Daftar 16 Sesi akan dirender oleh Javascript di sini --}}
            <div class="p-5 overflow-y-auto flex-1 space-y-3" id="mcs-list">
            </div>

            <div
                class="p-5 border-t border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/50 rounded-b-2xl flex-shrink-0">
                <button onclick="closeModal('modal-course-sessions')"
                    class="w-full py-2.5 bg-stone-200 dark:bg-stone-700 hover:bg-stone-300 dark:hover:bg-stone-600 text-stone-700 dark:text-stone-300 rounded-xl text-sm font-semibold transition-colors">Tutup</button>
            </div>
        </div>
    </div>
    {{-- ══════ MODAL: ATUR ULANG JADWAL SESI ══════ --}}
    <div id="modal-reschedule-session"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[70] hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl border border-stone-200 dark:border-stone-800 overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-stone-100 dark:border-stone-800">
                <div>
                    <h3 class="font-bold text-stone-900 dark:text-white text-lg">Atur Jadwal Sesi</h3>
                    <p class="text-xs text-stone-400 mt-1">Pilih cara mengatur ulang jadwal pertemuan ini</p>
                </div>
                <button onclick="closeModal('modal-reschedule-session')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <div class="p-5 space-y-4">
                <input type="hidden" id="rs-session-id">

                {{-- Panduan Singkat --}}
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                    <p class="text-xs text-blue-700 dark:text-blue-300">
                        <i class="fa-solid fa-lightbulb mr-1"></i>
                        <strong>Tip:</strong> Pilih opsi sesuai kondisi. Semua perubahan bisa diubah lagi nanti.
                    </p>
                </div>

                <div class="space-y-3">
                    {{-- Opsi 1: Kelas Pengganti --}}
                    <label
                        class="flex items-start gap-3 p-3 border-2 border-blue-200 dark:border-blue-800 rounded-xl cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors group"
                        title="Gunakan ini kalau kelas tetap diadakan tapi di hari/tanggal berbeda">
                        <input type="radio" name="rs_action" value="reschedule" class="mt-1 accent-blue-500"
                            onchange="toggleRsDate(true)" checked>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-calendar-check text-blue-500"></i>
                                <p class="text-sm font-bold text-stone-800 dark:text-white">Kelas Pengganti</p>
                            </div>
                            <p class="text-[11px] text-stone-500 mt-1">Kelas tetap diadakan, pindah ke tanggal lain.</p>
                            <p class="text-[10px] text-blue-600 mt-1"><i class="fa-solid fa-arrow-right mr-1"></i>Pilih
                                tanggal baru di bawah</p>
                        </div>
                    </label>

                    <div id="rs-date-wrap" class="pl-10 pr-3">
                        <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-1">Tanggal Kelas
                            Pengganti:</label>
                        <input type="date" id="rs-new-date" class="fi w-full text-sm">
                    </div>

                    {{-- Opsi 2: Ditunda (TBA) --}}
                    <label
                        class="flex items-start gap-3 p-3 border border-stone-200 dark:border-stone-700 rounded-xl cursor-pointer hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors"
                        title="Gunakan ini kalau belum tahu kapan jadwal penggantinya">
                        <input type="radio" name="rs_action" value="tba" class="mt-1 accent-orange-500"
                            onchange="toggleRsDate(false)">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-clock text-orange-500"></i>
                                <p class="text-sm font-bold text-stone-800 dark:text-white">Ditunda (Belum Ada Jadwal)</p>
                            </div>
                            <p class="text-[11px] text-stone-500 mt-1">Kelas diundur, jadwal pengganti belum ditentukan.
                            </p>
                            <p class="text-[10px] text-orange-600 mt-1"><i class="fa-solid fa-info-circle mr-1"></i>Bisa
                                atur jadwal nanti</p>
                        </div>
                    </label>

                    {{-- Opsi 3: Libur Tetap --}}
                    <label
                        class="flex items-start gap-3 p-3 border border-stone-200 dark:border-stone-700 rounded-xl cursor-pointer hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                        title="Gunakan ini untuk libur nasional atau cuti tanpa pengganti">
                        <input type="radio" name="rs_action" value="holiday" class="mt-1 accent-rose-500"
                            onchange="toggleRsDate(false)">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-umbrella-beach text-rose-500"></i>
                                <p class="text-sm font-bold text-stone-800 dark:text-white">Libur (Tanpa Pengganti)</p>
                            </div>
                            <p class="text-[11px] text-stone-500 mt-1">Libur total, materi bisa dipelajari mandiri.</p>
                            <p class="text-[10px] text-rose-600 mt-1"><i class="fa-solid fa-minus-circle mr-1"></i>Tidak
                                ada kelas pengganti</p>
                        </div>
                    </label>

                    {{-- Opsi 4: Libur & Geser --}}
                    <label
                        class="flex items-start gap-3 p-3 border-2 border-amber-200 dark:border-amber-700 rounded-xl cursor-pointer hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors bg-amber-50/30 dark:bg-amber-900/10"
                        title="Gunakan ini untuk libur panjang yang menggeser semua jadwal berikutnya">
                        <input type="radio" name="rs_action" value="holiday_shift" class="mt-1 accent-amber-500"
                            onchange="toggleResumeDate(true)">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-arrow-right-long text-amber-500"></i>
                                <p class="text-sm font-bold text-amber-800 dark:text-amber-400">Libur & Geser Semua Jadwal
                                </p>
                            </div>
                            <p class="text-[11px] text-stone-600 dark:text-stone-400 mt-1">Libur ini + semua sesi
                                berikutnya digeser.</p>
                            <p class="text-[10px] text-amber-600 mt-1"><i
                                    class="fa-solid fa-layer-group mr-1"></i>Otomatis mengatur ulang jadwal</p>
                        </div>
                    </label>

                    <div id="rs-resume-date-wrap" class="pl-10 pr-3 hidden">
                        <label class="block text-xs font-medium text-amber-700 dark:text-amber-400 mb-1">
                            <i class="fa-solid fa-play mr-1"></i>Tanggal Mulai Lagi Perkuliahan:
                        </label>
                        <input type="date" id="rs-resume-date"
                            class="fi w-full text-sm border-amber-300 dark:border-amber-700 focus:border-amber-500 focus:ring-amber-500">
                        <p class="text-[10px] text-amber-700 mt-1 bg-amber-50 dark:bg-amber-900/20 p-2 rounded">
                            <i class="fa-solid fa-calculator mr-1"></i>
                            Sistem akan menghitung selisih hari dan menggeser semua sesi berikutnya secara otomatis.
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex gap-2 p-5 border-t border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/50">
                <button onclick="closeModal('modal-reschedule-session')"
                    class="flex-1 py-2.5 bg-stone-200 dark:bg-stone-700 hover:bg-stone-300 dark:hover:bg-stone-600 text-stone-700 dark:text-stone-300 rounded-xl text-sm font-semibold transition-colors">Batal</button>
                <button onclick="submitReschedule(this)"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors">Simpan
                    Perubahan</button>
            </div>
        </div>
    </div>

    {{-- ══════ MODAL: KELOLA MATERI SESI ══════ --}}
    <div id="modal-session-material"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[70] hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800 overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-stone-100 dark:border-stone-800">
                <div>
                    <h3 class="font-bold text-stone-900 dark:text-white text-lg">Kelola Materi Sesi</h3>
                    <p class="text-xs text-stone-400 mt-0.5">Catatan dan link materi pembelajaran</p>
                </div>
                <button onclick="closeModal('modal-session-material')" class="text-stone-400 hover:text-stone-700">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <input type="hidden" id="sm-session-id">

                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1.5">Catatan
                        Materi</label>
                    <textarea id="sm-notes" rows="4" class="fi w-full text-sm resize-none"
                        placeholder="Tulis catatan materi yang dipelajari..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1.5">Link Materi</label>
                    <div class="relative">
                        <i class="fa-solid fa-link absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 text-sm"></i>
                        <input type="url" id="sm-material-link" class="fi w-full text-sm pl-9"
                            placeholder="https://drive.google.com/... atau link materi lain">
                    </div>
                    <p class="text-[10px] text-stone-400 mt-1">Bisa link Google Drive, YouTube, atau sumber materi lainnya
                    </p>
                </div>

                <div id="sm-current-material" class="hidden">
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1.5">Link Saat
                        Ini</label>
                    <a id="sm-current-link" href="#" target="_blank"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg text-sm hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                        <i class="fa-solid fa-external-link-alt"></i>
                        <span id="sm-link-text">Buka Materi</span>
                    </a>
                </div>
            </div>
            <div class="flex gap-2 p-5 border-t border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/50">
                <button onclick="closeModal('modal-session-material')"
                    class="flex-1 py-2.5 bg-stone-200 dark:bg-stone-700 hover:bg-stone-300 dark:hover:bg-stone-600 text-stone-700 dark:text-stone-300 rounded-xl text-sm font-semibold transition-colors">Batal</button>
                <button onclick="saveMaterial(this)"
                    class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-save mr-1"></i>Simpan Materi
                </button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        function openModal(id) {
            document.getElementById(id)?.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal(id) {
            document.getElementById(id)?.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function switchAcadTab(id) {
            document.querySelectorAll('.acad-pane').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('[id^="acadtab-"]').forEach(b => b.classList.remove('active'));
            document.getElementById('acad-' + id)?.classList.remove('hidden');
            document.getElementById('acadtab-' + id)?.classList.add('active');
            history.replaceState(null, '', '#' + id);
        }

        function filterTasks(s) {
            const active =
                'px-3 py-1.5 text-xs rounded-full font-medium bg-stone-800 dark:bg-stone-700 text-white transition-colors';
            const inact =
                'px-3 py-1.5 text-xs rounded-full font-medium bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:bg-stone-200 transition-colors';
            ['all', 'todo', 'doing', 'done'].forEach(k => {
                document.getElementById('tf-' + k).className = k === s ? active : inact;
            });
            document.querySelectorAll('#task-list [data-status]').forEach(el => {
                el.style.display = (s === 'all' || el.dataset.status === s) ? '' : 'none';
            });
        }

        // ── API helper ──────────────────────────────────────────────────────────
        async function api(method, url, data = {}) {
            const isForm = data instanceof FormData;
            const opts = {
                method: method.toUpperCase(),
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
            };
            if (!['GET', 'HEAD'].includes(opts.method)) {
                if (isForm) {
                    opts.body = data;
                } else {
                    opts.headers['Content-Type'] = 'application/json';
                    opts.body = JSON.stringify(data);
                }
            }
            const res = await fetch(url, opts);
            const json = await res.json();
            if (!res.ok) throw json;
            return json;
        }

        function toast(msg, ok = true) {
            const t = document.createElement('div');
            t.className =
                `fixed bottom-6 right-6 z-[9999] flex items-center gap-2 px-4 py-3.5 ${ok?'bg-emerald-500':'bg-rose-500'} text-white text-sm font-semibold rounded-2xl shadow-2xl`;
            t.style.animation = 'fadeUp .28s ease-out both';
            t.innerHTML = `<i class="fa-solid ${ok?'fa-check-circle':'fa-circle-xmark'}"></i>${msg}`;
            document.body.appendChild(t);
            setTimeout(() => {
                t.style.transition = 'opacity .3s';
                t.style.opacity = '0';
                setTimeout(() => t.remove(), 300);
            }, 2800);
        }

        function setLoading(btn, on) {
            if (on) {
                btn.classList.add('opacity-70', 'pointer-events-none');
                btn.innerHTML += '<i class="fa-solid fa-spinner fa-spin ml-2"></i>';
            } else {
                btn.classList.remove('opacity-70', 'pointer-events-none');
                btn.querySelector('.fa-spinner')?.remove();
            }
        }

        // ─────────────────────────────── COURSES ───────────────────────────────
        function editCourse(id) {
            const allCourses = @json($courses);
            const c = allCourses.find(x => x.id === id);
            if (!c) return;
            document.getElementById('course-edit-id').value = c.id;
            document.getElementById('mc-code').value = c.code || '';
            document.getElementById('mc-name').value = c.name || '';
            document.getElementById('mc-sks').value = c.sks || 3;
            document.getElementById('mc-lecturer').value = c.lecturer || '';
            document.getElementById('mc-day').value = c.day || 'Senin';
            document.getElementById('mc-start').value = c.start_time || '08:00';
            document.getElementById('mc-end').value = c.end_time || '10:30';
            document.getElementById('mc-room').value = c.room || '';
            document.getElementById('mc-semester').value = c.semester || 6;
            document.getElementById('mc-drive').value = c.drive_link || '';
            document.getElementById('mc-notes').value = c.notes || '';

            document.getElementById('modal-course-title').textContent = 'Edit Mata Kuliah';
            document.getElementById('mc-submit-label').textContent = 'Simpan Perubahan';
            document.getElementById('mc-start-date-wrap').classList.add('hidden');
            openModal('modal-add-course');

        }

        async function saveCourse(btn) {
            const name = document.getElementById('mc-name').value.trim();
            const sks = document.getElementById('mc-sks').value;
            const day = document.getElementById('mc-day').value;
            const startT = document.getElementById('mc-start').value;
            const endT = document.getElementById('mc-end').value;
            if (!name) {
                toast('Nama mata kuliah wajib diisi!', false);
                return;
            }
            if (!startT || !endT) {
                toast('Jam mulai dan selesai wajib diisi!', false);
                return;
            }

            const editId = document.getElementById('course-edit-id').value;
            const payload = {
                code: document.getElementById('mc-code').value.trim(),
                name,
                sks: parseInt(sks),
                lecturer: document.getElementById('mc-lecturer').value.trim(),
                day_of_week: day,
                start_time: startT,
                end_time: endT,
                room: document.getElementById('mc-room').value.trim(),
                semester: parseInt(document.getElementById('mc-semester').value) || 1,
                drive_link: document.getElementById('mc-drive').value.trim() || null,
                notes: document.getElementById('mc-notes').value.trim() || null,

            };

            // TAMBAHKAN BARIS INI: Kirim start_date hanya jika membuat matkul baru (bukan edit)
            if (!editId) {
                payload.start_date = document.getElementById('mc-start-date').value;
            }
            setLoading(btn, true);
            try {
                const url = editId ? `/academic/courses/${editId}` : '{{ route('academic.courses.store') }}';
                const method = editId ? 'PUT' : 'POST';

                if (method === 'PUT') {
                    const fd = new FormData();
                    fd.append('_method', 'PUT');
                    Object.entries(payload).forEach(([k, v]) => {
                        if (v !== null && v !== undefined) fd.append(k, v);
                    });
                    await api('POST', url, fd);
                } else {
                    await api('POST', url, payload);
                }
                toast('Mata kuliah berhasil ' + (editId ? 'diperbarui!' : 'ditambahkan!'));
                closeModal('modal-add-course');
                setTimeout(() => location.reload(), 700);
            } catch (e) {
                const msg = e?.errors ? Object.values(e.errors).flat().join(', ') : (e?.message ||
                    'Terjadi kesalahan.');
                toast(msg, false);
            } finally {
                setLoading(btn, false);
            }
        }

        async function deleteCourse(id, name) {
            showDeleteConfirm({
                title: 'Hapus Mata Kuliah?',
                message: `Hapus "${name || 'Mata kuliah ini'}"?`,
                warning: 'Tugas yang terhubung tidak ikut terhapus.',
                onConfirm: async () => {
                    try {
                        const fd = new FormData();
                        fd.append('_method', 'DELETE');
                        await fetch(`/academic/courses/${id}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF
                            },
                            body: fd,
                        }).then(r => r.json());
                        toast('Mata kuliah dihapus.');
                        setTimeout(() => location.reload(), 700);
                    } catch (e) {
                        toast('Gagal menghapus.', false);
                    }
                }
            });
        }

        // ─────────────────────────────── SESSIONS TRACKING ─────────────────────────────────
        const allCoursesData = @json($courses);

        function openSessionsModal(courseId) {
            const course = allCoursesData.find(c => c.id === courseId);
            if (!course) return;

            document.getElementById('mcs-title').textContent = `Sesi: ${course.name}`;
            const listContainer = document.getElementById('mcs-list');
            listContainer.innerHTML = '';

            if (!course.sessions || course.sessions.length === 0) {
                listContainer.innerHTML =
                    '<p class="text-center text-stone-400 py-8 text-sm">Data sesi tidak ditemukan.</p>';
            } else {
                course.sessions.forEach(ses => {
                    let statusHtml = '';
                    let actionsHtml = '';
                    let bgClass = 'bg-white dark:bg-stone-800 border-stone-200 dark:border-stone-700';
                    let opacityClass = '';

                    if (ses.status === 'completed') {
                        bgClass =
                            'bg-emerald-50 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-800/30';
                        opacityClass = 'opacity-80';
                        statusHtml =
                            `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">✅ Selesai</span>`;
                        // Tombol: Batal Selesai & Materi
                        const materialIcon = ses.material_link ? 'fa-book-open' : 'fa-book';
                        const materialClass = ses.material_link ?
                            'text-emerald-600 bg-emerald-100 dark:bg-emerald-900/30' :
                            'text-stone-500 bg-stone-200 dark:bg-stone-700';
                        actionsHtml = `
                            <button onclick="toggleSession(${ses.id}, this)" class="w-8 h-8 rounded bg-stone-200 dark:bg-stone-700 text-stone-500 hover:text-rose-500 transition-colors" title="Batal Selesai"><i class="fa-solid fa-rotate-left text-xs"></i></button>
                            <button onclick="openMaterialModal(${ses.id}, '${ses.notes ? ses.notes.replace(/'/g, "\\'") : ''}', '${ses.material_link || ''}')" class="w-8 h-8 rounded ${materialClass} hover:bg-emerald-500 hover:text-white transition-colors" title="Kelola Materi"><i class="fa-solid ${materialIcon} text-xs"></i></button>
                        `;
                    } else if (ses.status === 'holiday') {
                        bgClass = 'bg-stone-100 dark:bg-stone-900 border-stone-200 dark:border-stone-800';
                        opacityClass = 'opacity-75';

                        // Mendeteksi apakah ini TBA, Libur murni, atau Libur+Geser dari titlenya
                        let badgeLabel = '🏖️ Libur';
                        let badgeClass = 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400';

                        if (ses.title.includes('Menunggu')) {
                            badgeLabel = '🔄 Belum Ada Jadwal';
                        } else if (ses.title.includes('Kelas Pengganti')) {
                            badgeLabel = '🔄 Kelas Pengganti';
                            badgeClass = 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400';
                        } else if (ses.title.includes('Mulai:')) {
                            // Extract tanggal mulai lagi dari title format: (Libur, Mulai: DD MMM)
                            const match = ses.title.match(/Mulai: (\d+ \w+)/);
                            const resumeDate = match ? match[1] : '';
                            badgeLabel = resumeDate ? `📅 Mulai ${resumeDate}` : '📅 Libur Fleksibel';
                            badgeClass = 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400';
                        }

                        statusHtml =
                            `<span class="px-2 py-0.5 rounded text-[10px] font-bold ${badgeClass}">${badgeLabel}</span>`;

                        // Tombol: Selesai, Materi & Edit Ulang Tanggal (SAMA dengan sesi normal)
                        const materialIcon = ses.material_link ? 'fa-book-open' : 'fa-book';
                        const materialClass = ses.material_link ?
                            'text-emerald-600 bg-emerald-100 dark:bg-emerald-900/30' :
                            'text-stone-500 bg-stone-200 dark:bg-stone-700';
                        actionsHtml = `
                            <button onclick="toggleSession(${ses.id}, this)" class="w-8 h-8 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-colors" title="Tandai Selesai"><i class="fa-solid fa-check text-xs"></i></button>
                            <button onclick="openMaterialModal(${ses.id}, '${ses.notes ? ses.notes.replace(/'/g, "\\'") : ''}', '${ses.material_link || ''}')" class="w-8 h-8 rounded ${materialClass} hover:bg-emerald-500 hover:text-white transition-colors" title="Kelola Materi"><i class="fa-solid ${materialIcon} text-xs"></i></button>
                            <button onclick="openRescheduleModal(${ses.id}, '${ses.date.split('T')[0]}')" class="w-8 h-8 rounded bg-rose-100 dark:bg-rose-900/30 text-rose-600 hover:bg-rose-500 hover:text-white transition-colors" title="Atur Ulang / Liburkan"><i class="fa-solid fa-calendar-xmark text-xs"></i></button>
                        `;
                    } else {
                        // Tombol: Selesai, Materi & Buka Modal Reschedule
                        const materialIcon = ses.material_link ? 'fa-book-open' : 'fa-book';
                        const materialClass = ses.material_link ?
                            'text-emerald-600 bg-emerald-100 dark:bg-emerald-900/30' :
                            'text-stone-500 bg-stone-200 dark:bg-stone-700';
                        actionsHtml = `
                            <button onclick="toggleSession(${ses.id}, this)" class="w-8 h-8 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-colors" title="Tandai Selesai"><i class="fa-solid fa-check text-xs"></i></button>
                            <button onclick="openMaterialModal(${ses.id}, '${ses.notes ? ses.notes.replace(/'/g, "\\'") : ''}', '${ses.material_link || ''}')" class="w-8 h-8 rounded ${materialClass} hover:bg-emerald-500 hover:text-white transition-colors" title="Kelola Materi"><i class="fa-solid ${materialIcon} text-xs"></i></button>
                            <button onclick="openRescheduleModal(${ses.id}, '${ses.date.split('T')[0]}')" class="w-8 h-8 rounded bg-rose-100 dark:bg-rose-900/30 text-rose-600 hover:bg-rose-500 hover:text-white transition-colors" title="Atur Ulang / Liburkan"><i class="fa-solid fa-calendar-xmark text-xs"></i></button>
                        `;
                    }

                    const d = new Date(ses.date);
                    const dateStr = d.toLocaleDateString('id-ID', {
                        weekday: 'short',
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric'
                    });

                    // Bersihkan title dari label status untuk tampilan
                    let cleanTitle = ses.title
                        .replace(/ \(Libur[^)]*\)/g, '')
                        .replace(/ \(Kelas Pengganti\)/g, '')
                        .replace(/ \(Menunggu Jadwal\)/g, '');

                    // Cek status untuk subtitle
                    let subtitleBadges = '';
                    if (ses.title.includes('Libur')) {
                        const match = ses.title.match(/Mulai: (\d+ \w+)/);
                        const resumeText = match ? `Mulai ${match[1]}` : 'Ditunda';
                        subtitleBadges +=
                            `<span class="text-[10px] text-amber-600 dark:text-amber-400">📅 ${resumeText}</span>`;
                    }
                    if (ses.title.includes('Kelas Pengganti')) {
                        subtitleBadges += subtitleBadges ? ' · ' : '';
                        subtitleBadges +=
                            `<span class="text-[10px] text-blue-600 dark:text-blue-400">🔄 Kelas Pengganti</span>`;
                    }
                    if (ses.title.includes('Menunggu')) {
                        subtitleBadges = `<span class="text-[10px] text-stone-500">⏳ Menunggu Jadwal</span>`;
                    }

                    // Badge untuk materi dan catatan
                    let infoBadges = '';
                    if (ses.material_link) {
                        infoBadges +=
                            `<a href="${ses.material_link}" target="_blank" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 hover:bg-blue-200" title="Buka Materi"><i class="fa-solid fa-link text-[8px]"></i>Materi</a>`;
                    }
                    if (ses.notes) {
                        infoBadges += infoBadges ? ' ' : '';
                        infoBadges +=
                            `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400" title="Ada Catatan"><i class="fa-solid fa-sticky-note text-[8px]"></i>Catatan</span>`;
                    }

                    listContainer.innerHTML += `
                        <div class="flex items-start gap-3 p-3 border rounded-xl ${bgClass} ${opacityClass} transition-all">
                            <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-500 font-bold">
                                ${ses.session_number}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <h4 class="font-bold text-sm text-stone-800 dark:text-white truncate">${cleanTitle}</h4>
                                    ${statusHtml}
                                </div>
                                <p class="text-xs text-stone-500 mb-1"><i class="fa-regular fa-calendar mr-1"></i>${dateStr}</p>
                                ${subtitleBadges ? `<p class="text-xs mb-1">${subtitleBadges}</p>` : ''}
                                ${infoBadges ? `<div class="flex gap-1">${infoBadges}</div>` : ''}
                            </div>
                            <div class="flex gap-1.5 flex-shrink-0 pt-0.5">
                                ${actionsHtml}
                            </div>
                        </div>
                    `;
                });
            }

            openModal('modal-course-sessions');
        }

        async function toggleSession(id, btn) {
            setLoading(btn, true);
            try {
                const res = await api('POST', `/academic/sessions/${id}/complete`);
                toast(res.message);
                setTimeout(() => location.reload(), 600); // Reload agar progress bar utama juga update
            } catch (e) {
                toast('Gagal mengupdate sesi.', false);
                setLoading(btn, false);
            }
        }

        function openRescheduleModal(id, currentDate) {
            document.getElementById('rs-session-id').value = id;
            document.getElementById('rs-new-date').value = currentDate;
            // Set default resume date to 1 week after current date
            const defaultResume = new Date(currentDate);
            defaultResume.setDate(defaultResume.getDate() + 7);
            document.getElementById('rs-resume-date').value = defaultResume.toISOString().split('T')[0];
            // Reset radio ke opsi pertama (Reschedule)
            document.querySelector('input[name="rs_action"][value="reschedule"]').checked = true;
            toggleRsDate(true);
            toggleResumeDate(false);
            openModal('modal-reschedule-session');
        }

        function toggleRsDate(show) {
            const wrap = document.getElementById('rs-date-wrap');
            if (show) wrap.classList.remove('hidden');
            else wrap.classList.add('hidden');
            // Hide resume date when this is shown
            if (show) toggleResumeDate(false);
        }

        function toggleResumeDate(show) {
            const wrap = document.getElementById('rs-resume-date-wrap');
            if (show) wrap.classList.remove('hidden');
            else wrap.classList.add('hidden');
            // Hide regular date when resume date is shown
            if (show) {
                const dateWrap = document.getElementById('rs-date-wrap');
                dateWrap.classList.add('hidden');
            }
        }

        async function submitReschedule(btn) {
            const id = document.getElementById('rs-session-id').value;
            const action = document.querySelector('input[name="rs_action"]:checked').value;
            const newDate = document.getElementById('rs-new-date').value;
            const resumeDate = document.getElementById('rs-resume-date').value;

            if (action === 'reschedule' && !newDate) {
                toast('Pilih tanggal pengganti terlebih dahulu!', false);
                return;
            }

            if (action === 'holiday_shift' && !resumeDate) {
                toast('Pilih tanggal mulai lagi perkuliahan!', false);
                return;
            }

            setLoading(btn, true);
            try {
                const payload = {
                    action: action,
                    new_date: newDate
                };

                if (action === 'holiday_shift') {
                    payload.resume_date = resumeDate;
                }

                const res = await api('POST', `/academic/sessions/${id}/reschedule`, payload);
                toast(res.message);
                closeModal('modal-reschedule-session');
                setTimeout(() => location.reload(), 800);
            } catch (e) {
                const msg = e?.message || e?.errors?.resume_date?.[0] || 'Gagal mengatur ulang jadwal.';
                toast(msg, false);
                setLoading(btn, false);
            }
        }

        // ─────────────────────────────── SESSION MATERIAL ─────────────────────────────────
        function openMaterialModal(id, notes, materialLink) {
            document.getElementById('sm-session-id').value = id;
            document.getElementById('sm-notes').value = notes || '';
            document.getElementById('sm-material-link').value = materialLink || '';

            // Show/hide current link section
            const currentMaterialDiv = document.getElementById('sm-current-material');
            const currentLink = document.getElementById('sm-current-link');
            if (materialLink) {
                currentMaterialDiv.classList.remove('hidden');
                currentLink.href = materialLink;
                document.getElementById('sm-link-text').textContent = materialLink.length > 40 ? materialLink.substring(0,
                    40) + '...' : materialLink;
            } else {
                currentMaterialDiv.classList.add('hidden');
            }

            openModal('modal-session-material');
        }

        async function saveMaterial(btn) {
            const id = document.getElementById('sm-session-id').value;
            const notes = document.getElementById('sm-notes').value;
            const materialLink = document.getElementById('sm-material-link').value;

            setLoading(btn, true);
            try {
                const res = await api('POST', `/academic/sessions/${id}/material`, {
                    notes: notes,
                    material_link: materialLink
                });
                toast(res.message);
                closeModal('modal-session-material');
                setTimeout(() => location.reload(), 800);
            } catch (e) {
                const msg = e?.message || e?.errors?.material_link?.[0] || 'Gagal menyimpan materi.';
                toast(msg, false);
                setLoading(btn, false);
            }
        }

        // ─────────────────────────────── TASKS ─────────────────────────────────
        function editTask(t) {
            document.getElementById('task-edit-id').value = t.id;
            document.getElementById('at-title').value = t.title || '';
            document.getElementById('at-course').value = t.course_id || '';
            document.getElementById('at-type').value = t.type || 'assignment';
            document.getElementById('at-deadline').value = t.deadline || '';
            document.getElementById('at-priority').value = t.priority || 'medium';
            document.getElementById('at-drive').value = t.drive_link || '';
            document.getElementById('at-notes').value = t.notes || '';
            document.getElementById('modal-task-title').textContent = 'Edit Tugas';
            document.getElementById('at-submit-label').textContent = 'Simpan Perubahan';
            openModal('modal-add-task');
        }

        async function saveTask(btn) {
            const title = document.getElementById('at-title').value.trim();
            if (!title) {
                toast('Judul tugas wajib diisi!', false);
                return;
            }

            const editId = document.getElementById('task-edit-id').value;
            const payload = {
                title,
                linked_subject_id: document.getElementById('at-course').value || null,
                task_type: document.getElementById('at-type').value,
                due_date: document.getElementById('at-deadline').value || null,
                priority: document.getElementById('at-priority').value,
                drive_link: document.getElementById('at-drive').value.trim() || null,
                notes: document.getElementById('at-notes').value.trim() || null,
            };

            setLoading(btn, true);
            try {
                const url = editId ? `/academic/tasks/${editId}` : '{{ route('academic.tasks.store') }}';
                const method = editId ? 'PUT' : 'POST';
                if (method === 'PUT') {
                    const fd = new FormData();
                    fd.append('_method', 'PUT');
                    Object.entries(payload).forEach(([k, v]) => {
                        if (v !== null && v !== undefined) fd.append(k, v);
                    });
                    await api('POST', url, fd);
                } else {
                    await api('POST', url, payload);
                }
                toast('Tugas berhasil ' + (editId ? 'diperbarui!' : 'ditambahkan!'));
                closeModal('modal-add-task');
                setTimeout(() => location.reload(), 700);
            } catch (e) {
                toast((e?.errors ? Object.values(e.errors).flat().join(', ') : e?.message) || 'Gagal.', false);
            } finally {
                setLoading(btn, false);
            }
        }

        async function toggleTaskDone(id, btn) {
            try {
                const res = await api('POST', `/academic/tasks/${id}/status`);
                const row = document.getElementById('task-row-' + id);
                if (row) {
                    row.dataset.status = res.status;
                    row.classList.toggle('opacity-60', res.status === 'done');
                    const title = row.querySelector('p');
                    if (title) title.classList.toggle('line-through', res.status === 'done');
                    // Update checkbox UI
                    btn.className = btn.className.replace(
                        /bg-emerald-500 border-emerald-500|border-stone-300 dark:border-stone-600 hover:border-emerald-400/,
                        '');
                    if (res.status === 'done') {
                        btn.classList.add('bg-emerald-500', 'border-emerald-500');
                        btn.innerHTML = '<i class="fa-solid fa-check text-white text-[9px]"></i>';
                    } else {
                        btn.classList.add('border-stone-300', 'dark:border-stone-600', 'hover:border-emerald-400');
                        btn.innerHTML = '';
                    }
                }
                toast(res.message);
            } catch (e) {
                toast('Gagal update status.', false);
            }
        }

        async function deleteTask(id, title) {
            showDeleteConfirm({
                title: 'Hapus Tugas?',
                message: `Hapus "${title || 'Tugas ini'}"?`,
                warning: 'Tugas yang dihapus tidak dapat dikembalikan.',
                onConfirm: async () => {
                    try {
                        const fd = new FormData();
                        fd.append('_method', 'DELETE');
                        await fetch(`/academic/tasks/${id}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF
                            },
                            body: fd
                        }).then(r => r.json());
                        document.getElementById('task-row-' + id)?.remove();
                        toast('Tugas dihapus.');
                    } catch (e) {
                        toast('Gagal menghapus.', false);
                    }
                }
            });
        }

        // ─────────────────────────────── MILESTONES ────────────────────────────
        function editMilestone(m) {
            document.getElementById('ms-edit-id').value = m.id;
            document.getElementById('ms-label').value = m.label || '';
            document.getElementById('ms-date').value = m.target_date_raw || m.target_date || '';
            document.getElementById('ms-order').value = m.sort_order || 999;
            document.getElementById('ms-active').checked = !!m.is_active;
            document.getElementById('modal-ms-title').textContent = 'Edit Milestone';
            document.getElementById('ms-submit-label').textContent = 'Simpan Perubahan';
            openModal('modal-add-milestone');
        }

        async function saveMilestone(btn) {
            const label = document.getElementById('ms-label').value.trim();
            if (!label) {
                toast('Label milestone wajib diisi!', false);
                return;
            }
            const editId = document.getElementById('ms-edit-id').value;
            const payload = {
                label,
                target_date: document.getElementById('ms-date').value.trim() || null,
                sort_order: parseInt(document.getElementById('ms-order').value) || 999,
                is_active: document.getElementById('ms-active').checked ? 1 : 0,
            };
            setLoading(btn, true);
            try {
                const url = editId ? `/academic/milestones/${editId}` :
                    '{{ route('academic.milestones.store') }}';
                const method = editId ? 'PUT' : 'POST';
                if (method === 'PUT') {
                    const fd = new FormData();
                    fd.append('_method', 'PUT');
                    Object.entries(payload).forEach(([k, v]) => {
                        if (v !== null && v !== undefined) fd.append(k, v);
                    });
                    await api('POST', url, fd);
                } else {
                    await api('POST', url, payload);
                }
                toast('Milestone berhasil ' + (editId ? 'diperbarui!' : 'ditambahkan!'));
                closeModal('modal-add-milestone');
                setTimeout(() => location.reload(), 700);
            } catch (e) {
                toast(e?.message || 'Gagal.', false);
            } finally {
                setLoading(btn, false);
            }
        }

        async function toggleMilestone(id, btn) {
            try {
                const res = await api('POST', `/academic/milestones/${id}/toggle`);
                const row = document.getElementById('ms-row-' + id);
                if (row) {
                    const dot = row.querySelector('.absolute');
                    if (dot) {
                        dot.className = dot.className.replace(
                            /bg-emerald-500|bg-orange-500 animate-pulse|bg-stone-300 dark:bg-stone-600/, '');
                        dot.classList.add(res.done ? 'bg-emerald-500' : 'bg-stone-300');
                        dot.innerHTML = res.done ? '<i class="fa-solid fa-check text-white text-[8px]"></i>' : '';
                    }
                    const title = row.querySelector('h4');
                    if (title) {
                        title.classList.toggle('line-through', res.done);
                        title.classList.toggle('opacity-70', res.done);
                    }
                }
                toast(res.message);
            } catch (e) {
                toast('Gagal toggle milestone.', false);
            }
        }

        async function deleteMilestone(id, label) {
            showDeleteConfirm({
                title: 'Hapus Milestone?',
                message: `Hapus "${label || 'Milestone ini'}"?`,
                warning: 'Milestone yang dihapus tidak dapat dikembalikan.',
                onConfirm: async () => {
                    try {
                        const fd = new FormData();
                        fd.append('_method', 'DELETE');
                        await fetch(`/academic/milestones/${id}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF
                            },
                            body: fd
                        }).then(r => r.json());
                        document.getElementById('ms-row-' + id)?.remove();
                        toast('Milestone dihapus.');
                    } catch (e) {
                        toast('Gagal menghapus.', false);
                    }
                }
            });
        }

        // ── Init: restore tab from hash ──────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            const h = location.hash.replace('#', '');
            if (['jadwal', 'matkul', 'tugas', 'skripsi'].includes(h)) switchAcadTab(h);
            else switchAcadTab('jadwal');

            // Reset modal saat tutup
            document.getElementById('modal-add-course')?.addEventListener('click', e => {
                if (e.target === e.currentTarget) {
                    closeModal('modal-add-course');
                    document.getElementById('course-edit-id').value = '';
                    document.getElementById('modal-course-title').textContent = 'Tambah Mata Kuliah';
                    document.getElementById('mc-submit-label').textContent = 'Simpan';
                    // ✅ TAMBAHKAN BARIS INI SEBAGAI GANTINYA (Untuk mereset form tanggal saat modal ditutup):
                    document.getElementById('mc-start-date-wrap').classList.remove('hidden');
                }
            });
        });
    </script>
    <script>
        // ── Live Clock & Needle Update untuk Gantt Chart ──
        (function tickClock() {
            const nl = document.getElementById('now-label');
            if (nl) {
                const n = new Date();
                const ts = n.toTimeString().slice(0, 5);
                nl.textContent = ts;

                const h = n.getHours() + n.getMinutes() / 60;
                const pct = Math.max(0, Math.min(100, ((h - 6) / 17) * 100));
                const needle = document.getElementById('now-needle');
                if (needle) needle.style.left = pct + '%';
            }
            setTimeout(tickClock, 15000);
        })();

        document.addEventListener('DOMContentLoaded', () => {
            const needle = document.getElementById('now-needle');
            const grid = document.getElementById('gantt-grid');
            if (needle && grid) {
                needle.style.height = grid.offsetHeight + 'px';
                needle.style.top = '28px';
            }
        });
    </script>
@endpush
