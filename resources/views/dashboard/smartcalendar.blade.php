{{-- resources/views/dashboard/smartcalendar.blade.php --}}
@extends('layouts.app-dashboard')
@section('title', 'Smart Calendar | StudentHub')
@section('page-title', 'Smart Calendar')

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

        .fade-up {
            animation: fadeUp .4s ease-out both
        }

        .cal-day {
            min-height: 80px;
            cursor: pointer;
            transition: all .15s
        }

        .cal-day:hover {
            background: #fff7ed
        }

        .dark .cal-day:hover {
            background: rgba(249, 115, 22, .07)
        }

        .cal-day.today {
            background: #fff7ed;
            border: 2px solid #f97316
        }

        .dark .cal-day.today {
            background: rgba(249, 115, 22, .1)
        }

        .event-chip {
            height: 17px;
            font-size: 10px;
            border-radius: 4px;
            padding: 1px 4px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            color: #fff;
            cursor: pointer;
            line-height: 15px
        }

        .tab-btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .9rem;
            border-radius: .75rem;
            font-size: .8rem;
            font-weight: 500;
            color: #78716c;
            cursor: pointer;
            border: none;
            background: none;
            transition: all .15s
        }

        .tab-btn:hover {
            background: #f5f5f4;
            color: #1c1917
        }

        .dark .tab-btn:hover {
            background: #292524;
            color: #fafaf9
        }

        .tab-btn.active {
            background: #fff7ed;
            color: #ea580c;
            font-weight: 600
        }

        .dark .tab-btn.active {
            background: rgba(249, 115, 22, .13);
            color: #fb923c
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

        /* Error state */
        .fi.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, .11)
        }

        .err-msg {
            font-size: .7rem;
            color: #ef4444;
            margin-top: .25rem;
            display: none
        }

        .err-msg.show {
            display: block
        }

        /* Spinner */
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: .8
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite
        }

        @keyframes spin {
            to {
                transform: translateY(-50%) rotate(360deg)
            }
        }
    </style>
@endpush

@section('content')
    @php
        $month = (int) $currentDate->month;
        $year = (int) $currentDate->year;
        $daysInMon = $currentDate->daysInMonth;
        $startDow = $currentDate->copy()->startOfMonth()->dayOfWeek; // 0=Sun
        $today = now()->format('Y-m-d');
        $monthName = $currentDate->isoFormat('MMMM YYYY');

        $freqLabels = ['daily' => 'Setiap Hari', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'];
        $catLabels = [
            'pkl' => 'PKL',
            'academic' => 'Akademik',
            'creative' => 'Kreatif',
            'finance' => 'Keuangan',
            'health' => 'Kesehatan',
            'personal' => 'Personal',
            'routine' => 'Rutin',
        ];
    @endphp

    <div class="fade-up space-y-5">

        {{-- ── Header ─────────────────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">{{ $monthName }}</h2>
                <p class="text-stone-400 text-xs">Kalender terintegrasi — PKL, Kuliah, Deadline, Freelance + Kegiatan Rutin
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('dashboard.smartcalendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
                    class="px-3 py-2 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl hover:bg-stone-50 text-sm transition-colors">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
                <a href="{{ route('dashboard.smartcalendar', ['month' => now()->month, 'year' => now()->year]) }}"
                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-medium transition-colors">Hari
                    Ini</a>
                <a href="{{ route('dashboard.smartcalendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
                    class="px-3 py-2 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl hover:bg-stone-50 text-sm transition-colors">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
                <button onclick="openModal('modal-add-event')"
                    class="flex items-center gap-2 px-4 py-2 bg-stone-800 dark:bg-stone-700 hover:bg-stone-900 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-calendar-plus text-xs"></i> Event
                </button>
                <button onclick="openModal('modal-add-recurring')"
                    class="flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-rotate text-xs"></i> Kegiatan Rutin
                </button>
            </div>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div
                class="flex items-center gap-3 px-5 py-3.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm">
                <i class="fa-solid fa-circle-check text-emerald-500 flex-shrink-0"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Tabs --}}
        <div class="flex gap-1 bg-stone-100 dark:bg-stone-800 p-1 rounded-xl w-fit flex-wrap">
            <button onclick="switchCalTab('kalender')" id="caltab-kalender" class="tab-btn active">
                <i class="fa-solid fa-calendar-days text-xs"></i> Kalender
            </button>
            <button onclick="switchCalTab('rutin')" id="caltab-rutin" class="tab-btn">
                <i class="fa-solid fa-rotate text-xs"></i> Kegiatan Rutin
                @if (count($recurringActivities) > 0)
                    <span
                        class="px-1.5 py-0.5 bg-orange-500 text-white text-[10px] font-bold rounded-full">{{ count($recurringActivities) }}</span>
                @endif
            </button>
            <button onclick="switchCalTab('jadwal')" id="caltab-jadwal" class="tab-btn">
                <i class="fa-solid fa-table-cells text-xs"></i> Jadwal Mingguan
            </button>
        </div>

        {{-- ══════ TAB: KALENDER ══════ --}}
        <div id="calpane-kalender" class="cal-pane">
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-5">
                <div
                    class="xl:col-span-3 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                    <div class="grid grid-cols-7 border-b border-stone-100 dark:border-stone-800">
                        @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $d)
                            <div class="text-center text-xs font-bold text-stone-400 py-3">{{ $d }}</div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-7">
                        @for ($e = 0; $e < $startDow; $e++)
                            <div
                                class="border-b border-r border-stone-100 dark:border-stone-800 bg-stone-50/50 dark:bg-stone-800/30 cal-day p-1.5">
                            </div>
                        @endfor

                        @for ($d = 1; $d <= $daysInMon; $d++)
                            @php
                                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                $isToday = $dateStr === $today;
                                $dayEvs = collect($events)->where('date', $dateStr);
                                $dayRecs = $recEventsByDate[$dateStr] ?? [];
                                $col = ($d + $startDow - 1) % 7;
                                $isWeekend = $col === 0 || $col === 6;
                            @endphp
                            <div class="cal-day border-b border-r border-stone-100 dark:border-stone-800 p-1 {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'bg-stone-50/60 dark:bg-stone-800/20' : '' }}"
                                onclick="showDayDetail('{{ $dateStr }}')">
                                <div class="flex items-center justify-between mb-0.5 px-0.5">
                                    <span
                                        class="text-xs font-bold {{ $isToday ? 'text-orange-600 dark:text-orange-400' : 'text-stone-700 dark:text-stone-300' }}">{{ $d }}</span>
                                    @if ($isToday)
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                    @endif
                                </div>
                                @foreach (collect($dayRecs)->take(1) as $rec)
                                    <div class="event-chip mb-0.5 opacity-80" style="background:{{ $rec['color'] }}"
                                        title="{{ $rec['title'] }}">
                                        {{ $rec['icon'] }} {{ Str::limit($rec['title'], 10) }}
                                    </div>
                                @endforeach
                                @foreach ($dayEvs->take(2) as $ev)
                                    <div class="event-chip mb-0.5" style="background:{{ $ev['color'] }}"
                                        title="{{ $ev['title'] }}">{{ $ev['title'] }}</div>
                                @endforeach
                                @if ($dayEvs->count() + count($dayRecs) > 3)
                                    <div class="text-[9px] text-stone-400 pl-0.5">
                                        +{{ $dayEvs->count() + count($dayRecs) - 3 }} lagi</div>
                                @endif
                            </div>
                        @endfor

                        @php $trailing = (7 - (($daysInMon + $startDow) % 7)) % 7; @endphp
                        @for ($e = 0; $e < $trailing; $e++)
                            <div
                                class="border-b border-r border-stone-100 dark:border-stone-800 bg-stone-50/50 dark:bg-stone-800/30 cal-day p-1.5">
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="space-y-4">
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-4">
                        <h4 class="font-bold text-stone-800 dark:text-white text-sm mb-3">Kode Warna</h4>
                        @foreach ([['#ef4444', 'Deadline'], ['#8b5cf6', 'Skripsi / Akademik'], ['#3b82f6', 'Kuliah / UTS'], ['#f97316', 'Proyek Kreatif'], ['#10b981', 'PKL'], ['#f59e0b', 'Keuangan / Gaji']] as [$c, $l])
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                    style="background:{{ $c }}"></span>
                                <span class="text-xs text-stone-500 dark:text-stone-400">{{ $l }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-4">
                        <h4 class="font-bold text-stone-800 dark:text-white text-sm mb-3">Deadline Mendekat</h4>
                        @forelse($upcomingDeadlines as $dl)
                            @php $diff = now()->diffInDays(\Carbon\Carbon::parse($dl['date']), false); @endphp
                            <div class="flex items-start gap-2.5 mb-2.5 last:mb-0">
                                <div class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                                    style="background:{{ $dl['color'] }}">
                                    <i class="fa-solid fa-calendar-day text-white text-[9px]"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-stone-800 dark:text-white truncate">
                                        {{ $dl['title'] }}</p>
                                    <p class="text-[10px] text-stone-400">
                                        {{ \Carbon\Carbon::parse($dl['date'])->isoFormat('D MMM') }} ·
                                        @if ($diff < 0)
                                            Terlambat
                                        @elseif($diff == 0)
                                            Hari ini!
                                        @elseif($diff == 1)
                                            Besok
                                        @else
                                            {{ $diff }} hari
                                        @endif
                                    </p>
                                </div>
                                @if ($diff >= 0 && $diff <= 3)
                                    <span
                                        class="text-[9px] bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 px-1.5 py-0.5 rounded-full font-bold flex-shrink-0">Segera</span>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-stone-400 text-center py-4 italic">Tidak ada deadline mendekat</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════ TAB: KEGIATAN RUTIN ══════ --}}
        <div id="calpane-rutin" class="cal-pane hidden space-y-4">
            <div class="flex gap-2 flex-wrap">
                @foreach (['all' => 'Semua', 'daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'] as $k => $v)
                    <button onclick="filterRecurring('{{ $k }}')" id="recf-{{ $k }}"
                        class="px-3 py-1.5 text-xs rounded-full font-medium transition-colors {{ $k === 'all' ? 'bg-stone-800 dark:bg-stone-700 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500 hover:bg-stone-200' }}">
                        {{ $v }}
                    </button>
                @endforeach
            </div>

            @if (count($recurringActivities) === 0)
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-12 text-center">
                    <div
                        class="w-16 h-16 rounded-2xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-rotate text-orange-500 text-2xl"></i>
                    </div>
                    <p class="font-bold text-stone-600 dark:text-stone-400 mb-1">Belum ada kegiatan rutin</p>
                    <p class="text-xs text-stone-400 mb-4">Tambahkan jadwal PKL, kuliah, olahraga, atau kegiatan berulang
                        lainnya</p>
                    <button onclick="openModal('modal-add-recurring')"
                        class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-medium transition-colors">
                        <i class="fa-solid fa-plus mr-1.5"></i>Tambah Kegiatan Rutin
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" id="recurring-list">
                    @foreach ($recurringActivities as $rec)
                        @php
                            $startR = \Carbon\Carbon::parse($rec['start_date']);
                            $endR = \Carbon\Carbon::parse($rec['end_date']);
                            $pTotal = max(1, $startR->diffInDays($endR));
                            $pDone = max(0, min($pTotal, (int) $startR->diffInDays(now(), false)));
                            $pPct = min(100, round(($pDone / $pTotal) * 100));
                            $isActive = now()->between($startR, $endR);
                        @endphp
                        <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden"
                            data-freq="{{ $rec['frequency'] }}">
                            <div class="h-1.5" style="background:{{ $rec['color'] }}"></div>
                            <div class="p-5">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg flex-shrink-0"
                                            style="background:{{ $rec['color'] }}20">{{ $rec['icon'] }}</div>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-stone-800 dark:text-white text-sm truncate">
                                                {{ $rec['title'] }}</h4>
                                            <span
                                                class="text-[10px] px-2 py-0.5 rounded-full font-semibold text-white inline-block mt-0.5"
                                                style="background:{{ $rec['color'] }}">{{ $freqLabels[$rec['frequency']] ?? $rec['frequency'] }}</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-1 flex-shrink-0 ml-2">
                                        <button onclick="editRecurring({{ $rec['id'] }})"
                                            class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-400 hover:text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/20 flex items-center justify-center transition-colors"
                                            title="Edit">
                                            <i class="fa-solid fa-pen text-[10px]"></i>
                                        </button>
                                        <button onclick="deleteRecurring({{ $rec['id'] }}, this)"
                                            class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center justify-center transition-colors"
                                            title="Hapus">
                                            <i class="fa-solid fa-trash text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Day pills (weekly) --}}
                                @if ($rec['frequency'] === 'weekly' && !empty($rec['days']))
                                    <div class="flex gap-1 mb-3">
                                        @foreach ([0, 1, 2, 3, 4, 5, 6] as $di)
                                            <span
                                                class="w-7 h-7 rounded-full text-[10px] font-bold flex items-center justify-center flex-shrink-0
                        {{ in_array($di, $rec['days']) ? 'text-white' : 'bg-stone-100 dark:bg-stone-700 text-stone-400' }}"
                                                style="{{ in_array($di, $rec['days']) ? 'background:' . $rec['color'] : '' }}">
                                                {{ ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][$di] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @elseif($rec['frequency'] === 'monthly')
                                    <div class="mb-3 text-xs text-stone-500 dark:text-stone-400">Setiap tanggal <strong
                                            class="text-stone-800 dark:text-white">{{ $rec['day_of_month'] }}</strong>
                                    </div>
                                @else
                                    <div class="mb-3 text-xs text-stone-500 dark:text-stone-400">Setiap hari dalam periode
                                        aktif</div>
                                @endif

                                {{-- Period bar --}}
                                <div class="mb-3">
                                    <div class="flex justify-between text-[10px] text-stone-400 mb-1">
                                        <span>{{ $startR->isoFormat('D MMM YY') }}</span>
                                        <span>{{ $endR->isoFormat('D MMM YY') }}</span>
                                    </div>
                                    <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-2">
                                        <div class="h-2 rounded-full"
                                            style="width:{{ $pPct }}%;background:{{ $rec['color'] }}"></div>
                                    </div>
                                    <div class="flex justify-between items-center mt-1">
                                        <span class="text-[10px] text-stone-400">{{ $pPct }}% berlalu</span>
                                        @if ($isActive)
                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold text-white"
                                                style="background:{{ $rec['color'] }}">✓ AKTIF</span>
                                        @elseif(now()->lt($startR))
                                            <span class="text-[10px] text-stone-400">Belum mulai</span>
                                        @else
                                            <span class="text-[10px] text-stone-400">Selesai</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="space-y-1.5 pt-2.5 border-t border-stone-100 dark:border-stone-800">
                                    @if ($rec['start_time'] && $rec['end_time'])
                                        <div class="flex items-center gap-2 text-xs text-stone-500 dark:text-stone-400">
                                            <i class="fa-solid fa-clock text-[10px] w-3 text-center"
                                                style="color:{{ $rec['color'] }}"></i>
                                            {{ $rec['start_time'] }} – {{ $rec['end_time'] }}
                                        </div>
                                    @endif
                                    @if ($rec['notes'])
                                        <div class="flex items-start gap-2 text-xs text-stone-500 dark:text-stone-400">
                                            <i class="fa-solid fa-circle-info text-[10px] w-3 text-center mt-0.5"
                                                style="color:{{ $rec['color'] }}"></i>
                                            <span>{{ Str::limit($rec['notes'], 60) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-2 text-xs text-stone-500 dark:text-stone-400">
                                        <i class="fa-solid fa-tag text-[10px] w-3 text-center"
                                            style="color:{{ $rec['color'] }}"></i>
                                        {{ $catLabels[$rec['category']] ?? ucfirst($rec['category']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ══════ TAB: JADWAL MINGGUAN ══════ --}}
        <div id="calpane-jadwal" class="cal-pane hidden">
            <div
                class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
                <h3 class="font-bold text-stone-800 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-rotate text-orange-500"></i> Jadwal Rutin Mingguan
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
                    @foreach ($weeklySchedule as $s)
                        <div class="rounded-xl border border-stone-200 dark:border-stone-700 overflow-hidden">
                            <div class="{{ $s['color'] }} py-2 text-center">
                                <span class="text-xs font-bold text-white">{{ $s['day'] }}</span>
                            </div>
                            <div class="p-3 bg-stone-50 dark:bg-stone-800 space-y-1">
                                @foreach ($s['items'] as $item)
                                    <p class="text-[10px] text-stone-600 dark:text-stone-400">{{ $item }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════
     MODAL: DAY DETAIL
══════════════════════════════════════════════════════════════════ --}}
    <div id="modal-day-detail"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl border border-stone-200 dark:border-stone-800">
            <div class="flex items-center justify-between p-5 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white" id="day-detail-title">Detail Hari</h3>
                <button onclick="closeModal('modal-day-detail')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div id="day-detail-body" class="p-5 max-h-96 overflow-y-auto">
                <p class="text-center text-stone-400 py-6 text-sm">Pilih tanggal dari kalender</p>
            </div>
            <div class="p-5 pt-0">
                <button onclick="closeModal('modal-day-detail')"
                    class="w-full py-2.5 bg-stone-800 dark:bg-stone-700 hover:bg-stone-900 text-white rounded-xl text-sm font-medium transition-colors">Tutup</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
     MODAL: ADD EVENT (one-off) — FORM REAL
══════════════════════════════════════════════════════════════════ --}}
    <div id="modal-add-event"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[95vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <div>
                    <h3 class="font-bold text-stone-900 dark:text-white" id="ev-modal-title">Tambah Event Kalender</h3>
                    <p class="text-xs text-stone-400 mt-0.5">Event satu kali pada tanggal tertentu</p>
                </div>
                <button onclick="closeModal('modal-add-event')" class="text-stone-400 hover:text-stone-700 flex-shrink-0">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <form id="form-add-event" novalidate>
                @csrf
                <input type="hidden" id="ev-edit-id" value="">

                <div class="p-6 space-y-4">
                    <div>
                        <label class="fi-label">Judul Event <span class="text-rose-400">*</span></label>
                        <input type="text" name="title" id="ev-title" class="fi"
                            placeholder="Nama event, misal: UTS Pengolahan Citra" required>
                        <p class="err-msg" id="ev-title-err">Judul tidak boleh kosong</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="fi-label">Tipe <span class="text-rose-400">*</span></label>
                            <select name="type" id="ev-type" class="fi" required>
                                <option value="academic">📚 Akademik</option>
                                <option value="pkl">💼 PKL</option>
                                <option value="creative">🎨 Creative</option>
                                <option value="deadline">🔴 Deadline</option>
                                <option value="finance">💰 Keuangan</option>
                                <option value="personal">👤 Personal</option>
                                <option value="routine">🔁 Rutin Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="fi-label">Tanggal Mulai <span class="text-rose-400">*</span></label>
                            <input type="date" name="date" id="ev-date" class="fi"
                                value="{{ now()->format('Y-m-d') }}" required>
                            <p class="err-msg" id="ev-date-err">Pilih tanggal mulai</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="fi-label">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="ev-end-date" class="fi">
                            <p class="text-[10px] text-stone-400 mt-1">Kosongkan jika 1 hari</p>
                        </div>
                        <div>
                            <label class="fi-label">Deskripsi / Catatan</label>
                            <textarea name="description" id="ev-notes" rows="2" class="fi resize-none"
                                placeholder="Catatan tambahan, lokasi, dll..."></textarea>
                        </div>
                    </div>

                    {{-- All Day Toggle --}}
                    <div
                        class="flex items-center gap-3 p-3 bg-stone-50 dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_all_day" id="ev-allday" value="1" class="sr-only peer"
                                onchange="toggleAllDay(this)">
                            <div
                                class="w-11 h-6 bg-stone-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500">
                            </div>
                            <span class="ml-3 text-sm font-medium text-stone-700 dark:text-stone-300">Event Seharian (All
                                Day)</span>
                        </label>
                    </div>

                    {{-- JAM: time picker, disembunyikan saat All Day --}}
                    <div id="time-fields" class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="fi-label">Jam Mulai <span class="time-req text-rose-400">*</span></label>
                            <input type="time" name="start_time" id="ev-start-time" class="fi" value="08:00">
                            <p class="err-msg" id="ev-start-err">Masukkan jam mulai</p>
                        </div>
                        <div>
                            <label class="fi-label">Jam Selesai <span class="time-req text-rose-400">*</span></label>
                            <input type="time" name="end_time" id="ev-end-time" class="fi" value="10:00">
                            <p class="err-msg" id="ev-end-err">Masukkan jam selesai</p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 px-6 pb-6">
                    <button type="button" onclick="closeModal('modal-add-event')"
                        class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                    <button type="button" onclick="submitEvent(this)" id="btn-save-event"
                        class="flex-1 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                        <i class="fa-solid fa-calendar-plus"></i> <span id="ev-submit-label">Simpan Event</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
     MODAL: ADD / EDIT RECURRING — FORM REAL
══════════════════════════════════════════════════════════════════ --}}
    <div id="modal-add-recurring"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[95vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <div>
                    <h3 class="font-bold text-stone-900 dark:text-white" id="rec-modal-title">Tambah Kegiatan Rutin
                    </h3>
                    <p class="text-xs text-stone-400 mt-0.5">Kegiatan berulang harian, mingguan, atau bulanan</p>
                </div>
                <button onclick="closeModal('modal-add-recurring')"
                    class="text-stone-400 hover:text-stone-700 flex-shrink-0"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>

            {{-- Hidden edit id --}}
            <input type="hidden" id="rec-edit-id" value="">

            <div class="p-6 space-y-4">
                <div>
                    <label class="fi-label">Nama Kegiatan <span class="text-rose-400">*</span></label>
                    <input type="text" id="rec-title" class="fi"
                        placeholder="misal: PKL Full Day, Olahraga Pagi, Kuliah IF401">
                    <p class="err-msg" id="rec-title-err">Nama kegiatan tidak boleh kosong</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fi-label">Kategori</label>
                        <select id="rec-cat" class="fi">
                            <option value="pkl">💼 PKL</option>
                            <option value="academic">📚 Akademik</option>
                            <option value="creative">🎨 Kreatif / Freelance</option>
                            <option value="finance">💰 Keuangan</option>
                            <option value="health">🏃 Kesehatan / Olahraga</option>
                            <option value="personal">👤 Personal</option>
                            <option value="routine">🔁 Rutin Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="fi-label">Frekuensi <span class="text-rose-400">*</span></label>
                        <select id="rec-freq" onchange="toggleFreqOptions(this.value)" class="fi">
                            <option value="weekly" selected>📅 Mingguan (hari tertentu)</option>
                            <option value="daily">🌞 Harian (setiap hari)</option>
                            <option value="monthly">📆 Bulanan (tanggal tertentu)</option>
                        </select>
                    </div>
                </div>

                {{-- Weekly: day picker --}}
                <div id="weekly-opts">
                    <label class="fi-label">Hari yang Aktif <span class="text-rose-400">*</span></label>
                    <div class="flex gap-1.5">
                        @foreach ([0 => 'Min', 1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab'] as $di => $dn)
                            <button type="button" onclick="toggleDay({{ $di }}, this)"
                                id="day-btn-{{ $di }}"
                                data-day-name="{{ ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$di] }}"
                                class="flex-1 py-2 rounded-xl text-xs font-bold border-2 border-stone-200 dark:border-stone-700 text-stone-400 hover:border-orange-400 hover:text-orange-500 transition-all">
                                {{ $dn }}
                            </button>
                        @endforeach
                    </div>
                    <p class="err-msg" id="rec-days-err">Pilih minimal satu hari</p>
                </div>

                {{-- Monthly: day of month --}}
                <div id="monthly-opts" class="hidden">
                    <label class="fi-label">Tanggal Setiap Bulan <span class="text-rose-400">*</span></label>
                    <div class="flex items-center gap-3">
                        <input type="number" id="rec-dom" min="1" max="31" placeholder="contoh: 15"
                            class="fi w-32">
                        <span class="text-sm text-stone-400">setiap bulan</span>
                    </div>
                    <p class="err-msg" id="rec-dom-err">Masukkan tanggal (1-31)</p>
                </div>

                {{-- JAM: time picker --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fi-label">Jam Mulai <span class="text-rose-400">*</span></label>
                        <input type="time" id="rec-start-time" class="fi" value="08:00">
                        <p class="err-msg" id="rec-stime-err">Masukkan jam mulai</p>
                    </div>
                    <div>
                        <label class="fi-label">Jam Selesai <span class="text-rose-400">*</span></label>
                        <input type="time" id="rec-end-time" class="fi" value="10:00">
                        <p class="err-msg" id="rec-etime-err">Masukkan jam selesai</p>
                    </div>
                </div>
                <div
                    class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-[11px] text-blue-700 dark:text-blue-400 flex items-start gap-2">
                    <i class="fa-solid fa-circle-info mt-0.5 flex-shrink-0"></i>
                    <span>Pilih jam menggunakan time picker. Contoh: olahraga jam 06:00–07:00, PKL jam
                        08:00–17:00.</span>
                </div>

                {{-- Period --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fi-label">Aktif Mulai Tanggal <span class="text-rose-400">*</span></label>
                        <input type="date" id="rec-start" class="fi" value="{{ now()->format('Y-m-d') }}">
                        <p class="err-msg" id="rec-start-err">Pilih tanggal mulai</p>
                    </div>
                    <div>
                        <label class="fi-label">Berakhir Tanggal <span class="text-rose-400">*</span></label>
                        <input type="date" id="rec-end" class="fi"
                            value="{{ now()->addMonths(6)->format('Y-m-d') }}">
                        <p class="err-msg" id="rec-end-err">Pilih tanggal berakhir</p>
                    </div>
                </div>
                <p class="text-[11px] text-stone-400">
                    <i class="fa-solid fa-circle-info text-stone-300 mr-1"></i>
                    Contoh PKL: mulai 08 Jan 2024, berakhir 28 Jun 2024. Kegiatan hanya muncul dalam rentang ini.
                </p>

                <div>
                    <label class="fi-label">Catatan / Keterangan</label>
                    <textarea id="rec-notes" rows="2" class="fi resize-none" placeholder="Lokasi, instruktur, catatan penting..."></textarea>
                </div>

                <div>
                    <label class="fi-label">Warna Label</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach (['#10b981', '#3b82f6', '#8b5cf6', '#f97316', '#ef4444', '#f59e0b', '#14b8a6', '#64748b', '#ec4899', '#0ea5e9'] as $c)
                            <button type="button" onclick="selectRecColor('{{ $c }}', this)"
                                class="w-8 h-8 rounded-full border-2 border-transparent hover:scale-110 transition-transform flex-shrink-0"
                                style="background:{{ $c }}" data-color="{{ $c }}"></button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex gap-3 px-6 pb-6">
                <button type="button" onclick="closeModal('modal-add-recurring')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button type="button" onclick="submitRecurring(this)"
                    class="flex-1 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-rotate"></i> <span id="rec-submit-label">Simpan Kegiatan Rutin</span>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        // ── Helpers ────────────────────────────────────────────────────────────
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
                `fixed bottom-6 right-6 z-[9999] flex items-center gap-2 px-4 py-3.5 ${ok?'bg-gradient-to-r from-emerald-500 to-emerald-600':'bg-gradient-to-r from-rose-500 to-rose-600'} text-white text-sm font-semibold rounded-2xl shadow-2xl`;
            t.style.cssText = 'animation:fadeUp .28s ease-out both;';
            t.innerHTML = `<i class="fa-solid ${ok?'fa-check-circle':'fa-circle-xmark'}"></i>${msg}`;
            document.body.appendChild(t);
            setTimeout(() => {
                t.style.transition = 'opacity .3s';
                t.style.opacity = '0';
                setTimeout(() => t.remove(), 300);
            }, 3200);
        }

        function setLoading(btn, loading) {
            if (loading) {
                btn.classList.add('btn-loading');
                btn.disabled = true;
            } else {
                btn.classList.remove('btn-loading');
                btn.disabled = false;
            }
        }

        function showErr(id, msg = '') {
            const el = document.getElementById(id);
            if (!el) return;
            if (msg) el.textContent = msg;
            el.classList.add('show');
        }

        function clearErr(id) {
            document.getElementById(id)?.classList.remove('show');
        }

        function clearAllErr(...ids) {
            ids.forEach(clearErr);
        }

        // ── Tab switching ──────────────────────────────────────────────────────
        function switchCalTab(tab) {
            document.querySelectorAll('.cal-pane').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('calpane-' + tab)?.classList.remove('hidden');
            document.getElementById('caltab-' + tab)?.classList.add('active');
        }

        // ── Recurring filter ───────────────────────────────────────────────────
        function filterRecurring(type) {
            const on =
                'px-3 py-1.5 text-xs rounded-full font-medium bg-stone-800 dark:bg-stone-700 text-white transition-colors';
            const off =
                'px-3 py-1.5 text-xs rounded-full font-medium bg-stone-100 dark:bg-stone-800 text-stone-500 hover:bg-stone-200 transition-colors';
            ['all', 'daily', 'weekly', 'monthly'].forEach(k => {
                document.getElementById('recf-' + k).className = k === type ? on : off;
            });
            document.querySelectorAll('#recurring-list > div').forEach(el => {
                el.style.display = (type === 'all' || el.dataset.freq === type) ? '' : 'none';
            });
        }

        // ── Frequency toggle ───────────────────────────────────────────────────
        function toggleFreqOptions(val) {
            document.getElementById('weekly-opts').classList.toggle('hidden', val !== 'weekly');
            document.getElementById('monthly-opts').classList.toggle('hidden', val !== 'monthly');
        }

        // ── Day selector ───────────────────────────────────────────────────────
        const selectedDays = new Set();

        function toggleDay(d, btn) {
            const activeClass =
                'flex-1 py-2 rounded-xl text-xs font-bold border-2 border-orange-400 text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20 transition-all';
            const inactiveClass =
                'flex-1 py-2 rounded-xl text-xs font-bold border-2 border-stone-200 dark:border-stone-700 text-stone-400 hover:border-orange-400 hover:text-orange-500 transition-all';
            if (selectedDays.has(d)) {
                selectedDays.delete(d);
                btn.className = inactiveClass;
            } else {
                selectedDays.add(d);
                btn.className = activeClass;
            }
            clearErr('rec-days-err');
        }

        // ── Color selector ─────────────────────────────────────────────────────
        let selectedRecColor = '#10b981';

        function selectRecColor(color, btn) {
            selectedRecColor = color;
            document.querySelectorAll('[data-color]').forEach(b => {
                b.style.outline = 'none';
                b.style.outlineOffset = '0';
                b.style.borderColor = 'transparent';
            });
            btn.style.outline = `3px solid ${color}`;
            btn.style.outlineOffset = '2px';
            btn.style.borderColor = '#fff';
        }

        // ── All Day Toggle ───────────────────────────────────────────────────
        function toggleAllDay(checkbox) {
            const timeFields = document.getElementById('time-fields');
            const timeHint = document.getElementById('time-hint');
            const timeReqs = document.querySelectorAll('.time-req');

            if (checkbox.checked) {
                // All Day: hide time fields
                timeFields?.classList.add('hidden');
                timeHint?.classList.add('hidden');
                timeReqs.forEach(el => el.classList.add('hidden'));
            } else {
                // Regular: show time fields
                timeFields?.classList.remove('hidden');
                timeHint?.classList.remove('hidden');
                timeReqs.forEach(el => el.classList.remove('hidden'));
            }
        }

        // ── Day detail ─────────────────────────────────────────────────────────
        const calEvents = @json($events);
        const recByDate = @json($recEventsByDate);

        function showDayDetail(dateStr) {
            const d = new Date(dateStr + 'T00:00:00');
            document.getElementById('day-detail-title').textContent =
                d.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

            const oneOff = calEvents.filter(e => e.date === dateStr);
            const recs = recByDate[dateStr] || [];
            const body = document.getElementById('day-detail-body');

            if (!oneOff.length && !recs.length) {
                body.innerHTML =
                    '<div class="text-center py-8"><i class="fa-regular fa-calendar text-4xl text-stone-300 mb-3 block"></i><p class="text-stone-400 text-sm">Tidak ada jadwal hari ini.</p></div>';
            } else {
                let html = '';
                if (recs.length) {
                    html +=
                        '<p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2">Kegiatan Rutin</p>';
                    html += recs.map(e => `
                <div class="flex items-center gap-3 p-2.5 rounded-xl mb-2" style="background:${e.color}18;border:1.5px solid ${e.color}35">
                    <span class="text-lg flex-shrink-0">${e.icon}</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-stone-800 dark:text-white truncate">${e.title}</p>
                        <p class="text-[10px] text-stone-400">${e.start_time && e.end_time ? e.start_time + ' – ' + e.end_time : (e.time || '')}</p>
                    </div>
                </div>`).join('');
                }
                if (oneOff.length) {
                    if (recs.length) html += '<div class="border-t border-stone-100 dark:border-stone-800 my-3"></div>';
                    html +=
                        '<p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2">Event Khusus</p>';
                    html += oneOff.map(e => {
                        const timeDisplay = e.is_all_day ?
                            '<span class="text-[10px] bg-orange-100 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 px-1.5 py-0.5 rounded">Seharian</span>' :
                            `<p class="text-[10px] text-stone-400">${e.start_time || '--:--'} – ${e.end_time || '--:--'}</p>`;
                        return `
                <div class="flex items-center gap-3 p-2.5 bg-stone-50 dark:bg-stone-800 rounded-xl mb-2" id="event-card-${e.id}">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:${e.color}">
                        <i class="fa-solid fa-calendar-day text-white text-xs"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-stone-800 dark:text-white truncate">${e.title}</p>
                        ${timeDisplay}
                    </div>
                    <div class="flex gap-1">
                        <button onclick="editEvent(${e.id})" class="w-7 h-7 rounded-lg bg-white dark:bg-stone-700 text-stone-400 hover:text-orange-500 flex items-center justify-center" title="Edit">
                            <i class="fa-solid fa-pen text-[10px]"></i>
                        </button>
                        <button onclick="deleteEvent(${e.id})" class="w-7 h-7 rounded-lg bg-white dark:bg-stone-700 text-stone-400 hover:text-rose-500 flex items-center justify-center" title="Hapus">
                            <i class="fa-solid fa-trash text-[10px]"></i>
                        </button>
                    </div>
                </div>`
                    }).join('');
                }
                body.innerHTML = html;
            }
            openModal('modal-day-detail');
        }

        // ══════════════════════════════════════════════════════════════════════
        // EDIT & DELETE EVENT FUNCTIONS
        // ══════════════════════════════════════════════════════════════════════
        function editEvent(id) {
            const ev = calEvents.find(e => e.id === id);
            if (!ev) return;

            // Isi form dengan data event
            document.getElementById('ev-edit-id').value = ev.id;
            document.getElementById('ev-title').value = ev.title;
            document.getElementById('ev-type').value = ev.type;
            document.getElementById('ev-date').value = ev.date;
            document.getElementById('ev-end-date').value = ev.end_date !== ev.date ? ev.end_date : '';
            document.getElementById('ev-notes').value = ev.description || '';
            document.getElementById('ev-allday').checked = ev.is_all_day;

            // Handle all day toggle
            toggleAllDay(document.getElementById('ev-allday'));

            // Set time jika bukan all day
            if (!ev.is_all_day) {
                document.getElementById('ev-start-time').value = ev.start_time || '08:00';
                document.getElementById('ev-end-time').value = ev.end_time || '10:00';
            }

            // Update modal title
            document.getElementById('ev-modal-title').textContent = 'Edit Event';
            document.getElementById('ev-submit-label').textContent = 'Simpan Perubahan';

            // Tutup modal detail, buka modal form
            closeModal('modal-day-detail');
            openModal('modal-add-event');
        }

        async function deleteEvent(id) {
            const ev = calEvents.find(e => e.id === id);
            const title = ev ? ev.title : 'Event ini';

            showDeleteConfirm({
                title: 'Hapus Event?',
                message: `Apakah Anda yakin ingin menghapus "${title}"?`,
                warning: 'Event yang dihapus tidak dapat dikembalikan.',
                onConfirm: async () => {
                    const res = await fetch(`/calendar/events/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF
                        },
                    });
                    const data = await res.json();

                    if (data.success) {
                        // Hapus card dari DOM
                        const card = document.getElementById(`event-card-${id}`);
                        if (card) {
                            card.style.transition = 'all .25s';
                            card.style.opacity = '0';
                            setTimeout(() => card.remove(), 250);
                        }
                        toast(data.message || 'Event berhasil dihapus!');

                        // Reload setelah delay
                        setTimeout(() => location.reload(), 800);
                    } else {
                        toast(data.message || 'Gagal menghapus.', false);
                    }
                }
            });
        }

        // ══════════════════════════════════════════════════════════════════════
        // SUBMIT EVENT (one-off) — KIRIM KE BACKEND
        // ══════════════════════════════════════════════════════════════════════
        async function submitEvent(btn) {
            clearAllErr('ev-title-err', 'ev-date-err', 'ev-start-err', 'ev-end-err');

            const editId = document.getElementById('ev-edit-id').value;
            const title = document.getElementById('ev-title').value.trim();
            const type = document.getElementById('ev-type').value;
            const date = document.getElementById('ev-date').value;
            const endDate = document.getElementById('ev-end-date').value;
            const startT = document.getElementById('ev-start-time').value;
            const endT = document.getElementById('ev-end-time').value;
            const notes = document.getElementById('ev-notes')?.value.trim() || '';
            const allDay = document.getElementById('ev-allday').checked;

            let valid = true;
            if (!title) {
                showErr('ev-title-err');
                valid = false;
            }
            if (!date) {
                showErr('ev-date-err');
                valid = false;
            }
            // Only validate time if NOT all day
            if (!allDay) {
                if (!startT) {
                    showErr('ev-start-err', 'Masukkan jam mulai');
                    valid = false;
                }
                if (!endT) {
                    showErr('ev-end-err', 'Masukkan jam selesai');
                    valid = false;
                }
            }
            if (!valid) return;

            setLoading(btn, true);
            try {
                const bodyParams = {
                    _token: CSRF,
                    title: title,
                    type: type,
                    date: date,
                    description: notes,
                    is_all_day: allDay ? '1' : '0',
                };

                // Only send end_date if provided
                if (endDate) {
                    bodyParams.end_date = endDate;
                }

                // Only send time if NOT all day
                if (!allDay) {
                    bodyParams.start_time = startT;
                    bodyParams.end_time = endT;
                }

                const isEdit = !!editId;
                const url = isEdit ?
                    `/calendar/events/${editId}` :
                    '{{ route('calendar.events.store') }}';

                const body = new URLSearchParams(bodyParams);
                if (isEdit) body.append('_method', 'PUT');

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: body.toString(),
                });
                const data = await res.json();

                if (data.success) {
                    toast(data.message || (isEdit ? 'Event berhasil diperbarui!' : 'Event berhasil ditambahkan!'));
                    closeModal('modal-add-event');
                    document.getElementById('form-add-event').reset();
                    document.getElementById('ev-edit-id').value = '';
                    // Reset modal title
                    document.getElementById('ev-modal-title').textContent = 'Tambah Event Kalender';
                    document.getElementById('ev-submit-label').textContent = 'Simpan Event';
                    // Reset time fields visibility
                    document.getElementById('time-fields').classList.remove('hidden');
                    document.querySelectorAll('.time-req').forEach(el => el.classList.remove('hidden'));
                    // Reload halaman agar kalender ter-update
                    setTimeout(() => location.reload(), 800);
                } else {
                    // Laravel validation errors
                    const errs = data.errors || {};
                    if (errs.title) showErr('ev-title-err', errs.title[0]);
                    if (errs.date) showErr('ev-date-err', errs.date[0]);
                    if (errs.start_time) showErr('ev-start-err', errs.start_time[0]);
                    if (errs.end_time) showErr('ev-end-err', errs.end_time[0]);
                    toast(data.message || 'Terjadi kesalahan validasi.', false);
                }
            } catch (e) {
                console.error(e);
                toast('Gagal menghubungi server. Cek koneksi.', false);
            } finally {
                setLoading(btn, false);
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // SUBMIT RECURRING — KIRIM KE BACKEND
        // ══════════════════════════════════════════════════════════════════════
        async function submitRecurring(btn) {
            clearAllErr('rec-title-err', 'rec-days-err', 'rec-dom-err', 'rec-stime-err', 'rec-etime-err',
                'rec-start-err', 'rec-end-err');

            const editId = document.getElementById('rec-edit-id').value;
            const title = document.getElementById('rec-title').value.trim();
            const cat = document.getElementById('rec-cat').value;
            const freq = document.getElementById('rec-freq').value;
            const startT = document.getElementById('rec-start-time').value;
            const endT = document.getElementById('rec-end-time').value;
            const startD = document.getElementById('rec-start').value;
            const endD = document.getElementById('rec-end').value;
            const notes = document.getElementById('rec-notes').value.trim();
            const dom = document.getElementById('rec-dom').value;

            // Collect selected days as "Senin,Rabu,Jumat"
            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const daysStr = Array.from(selectedDays).sort().map(d => dayNames[d]).join(',');

            let valid = true;
            if (!title) {
                showErr('rec-title-err');
                valid = false;
            }
            if (!startT) {
                showErr('rec-stime-err', 'Masukkan jam mulai');
                valid = false;
            }
            if (!endT) {
                showErr('rec-etime-err', 'Masukkan jam selesai');
                valid = false;
            }
            if (!startD) {
                showErr('rec-start-err', 'Pilih tanggal mulai');
                valid = false;
            }
            if (!endD) {
                showErr('rec-end-err', 'Pilih tanggal berakhir');
                valid = false;
            }
            if (freq === 'weekly' && selectedDays.size === 0) {
                showErr('rec-days-err', 'Pilih minimal satu hari');
                valid = false;
            }
            if (freq === 'monthly' && (!dom || dom < 1 || dom > 31)) {
                showErr('rec-dom-err', 'Masukkan tanggal 1-31');
                valid = false;
            }
            if (!valid) return;

            setLoading(btn, true);

            const isEdit = !!editId;
            const url = isEdit ?
                `/calendar/schedules/${editId}` :
                '{{ route('calendar.schedules.store') }}';

            try {
                const body = new URLSearchParams({
                    _token: CSRF,
                    activity: title,
                    type: cat,
                    frequency: freq,
                    start_time: startT,
                    end_time: endT,
                    start_date: startD,
                    end_date: endD,
                    notes: notes,
                    color: selectedRecColor,
                });
                if (freq === 'weekly') body.append('days_of_week', daysStr);
                if (freq === 'monthly') body.append('day_of_month', dom);
                if (isEdit) body.append('_method', 'PUT');

                const res = await fetch(url, {
                    method: 'POST', // Laravel method spoofing via _method=PUT
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: body.toString(),
                });
                const data = await res.json();

                if (data.success) {
                    toast(data.message || 'Kegiatan rutin berhasil disimpan!');
                    closeModal('modal-add-recurring');
                    resetRecurringForm();
                    setTimeout(() => location.reload(), 800);
                } else {
                    const errs = data.errors || {};
                    if (errs.activity) showErr('rec-title-err', errs.activity[0]);
                    if (errs.days_of_week) showErr('rec-days-err', errs.days_of_week[0]);
                    if (errs.day_of_month) showErr('rec-dom-err', errs.day_of_month[0]);
                    if (errs.start_time) showErr('rec-stime-err', errs.start_time[0]);
                    if (errs.end_time) showErr('rec-etime-err', errs.end_time[0]);
                    if (errs.start_date) showErr('rec-start-err', errs.start_date[0]);
                    if (errs.end_date) showErr('rec-end-err', errs.end_date[0]);
                    toast(data.message || 'Terjadi kesalahan.', false);
                }
            } catch (e) {
                console.error(e);
                toast('Gagal menghubungi server.', false);
            } finally {
                setLoading(btn, false);
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // EDIT RECURRING — pre-fill form
        // ══════════════════════════════════════════════════════════════════════
        const recurringData = @json($recurringActivities);

        function editRecurring(id) {
            const rec = recurringData.find(r => r.id === id);
            if (!rec) return;

            // Bersihkan form & state dulu
            resetRecurringForm();

            // Set nilai
            document.getElementById('rec-edit-id').value = rec.id;
            document.getElementById('rec-title').value = rec.title;
            document.getElementById('rec-cat').value = rec.category;
            document.getElementById('rec-freq').value = rec.frequency;
            document.getElementById('rec-start-time').value = rec.start_time || '08:00';
            document.getElementById('rec-end-time').value = rec.end_time || '17:00';
            document.getElementById('rec-start').value = rec.start_date;
            document.getElementById('rec-end').value = rec.end_date;
            document.getElementById('rec-notes').value = rec.notes || '';
            if (rec.day_of_month) document.getElementById('rec-dom').value = rec.day_of_month;

            // Tandai hari
            if (rec.frequency === 'weekly' && rec.days.length) {
                rec.days.forEach(d => {
                    selectedDays.add(d);
                    const btn = document.getElementById('day-btn-' + d);
                    if (btn) btn.className =
                        'flex-1 py-2 rounded-xl text-xs font-bold border-2 border-orange-400 text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20 transition-all';
                });
            }

            // Warna
            if (rec.color) {
                selectedRecColor = rec.color;
                const colorBtn = document.querySelector(`[data-color="${rec.color}"]`);
                if (colorBtn) selectRecColor(rec.color, colorBtn);
            }

            toggleFreqOptions(rec.frequency);
            document.getElementById('rec-modal-title').textContent = 'Edit Kegiatan Rutin';
            document.getElementById('rec-submit-label').textContent = 'Simpan Perubahan';
            openModal('modal-add-recurring');
        }

        // ══════════════════════════════════════════════════════════════════════
        // DELETE RECURRING
        // ══════════════════════════════════════════════════════════════════════
        async function deleteRecurring(id, btn) {
            const card = btn.closest('[data-freq]');
            const title = card?.querySelector('h4')?.textContent?.trim() || 'Kegiatan rutin ini';

            showDeleteConfirm({
                title: 'Hapus Kegiatan Rutin?',
                message: `Apakah Anda yakin ingin menghapus "${title}"?`,
                warning: 'Kegiatan rutin yang dihapus akan menghilangkan semua jadwal terkait.',
                btnRef: btn,
                onConfirm: async () => {
                    const res = await fetch(`/calendar/schedules/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF
                        },
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (card) {
                            card.style.transition = 'all .25s';
                            card.style.opacity = '0';
                            setTimeout(() => card.remove(), 250);
                        }
                        toast(data.message || 'Kegiatan berhasil dihapus!');
                    } else {
                        toast(data.message || 'Gagal menghapus.', false);
                    }
                }
            });
        }

        // ── Reset form ─────────────────────────────────────────────────────────
        function resetRecurringForm() {
            document.getElementById('rec-edit-id').value = '';
            document.getElementById('rec-title').value = '';
            document.getElementById('rec-cat').value = 'pkl';
            document.getElementById('rec-freq').value = 'weekly';
            document.getElementById('rec-start-time').value = '08:00';
            document.getElementById('rec-end-time').value = '17:00';
            document.getElementById('rec-start').value = '{{ now()->format('Y-m-d') }}';
            document.getElementById('rec-end').value = '{{ now()->addMonths(6)->format('Y-m-d') }}';
            document.getElementById('rec-notes').value = '';
            document.getElementById('rec-dom').value = '';
            selectedDays.clear();
            // Reset day buttons
            for (let d = 0; d <= 6; d++) {
                const b = document.getElementById('day-btn-' + d);
                if (b) b.className =
                    'flex-1 py-2 rounded-xl text-xs font-bold border-2 border-stone-200 dark:border-stone-700 text-stone-400 hover:border-orange-400 hover:text-orange-500 transition-all';
            }
            // Reset color
            selectedRecColor = '#10b981';
            document.querySelectorAll('[data-color]').forEach(b => {
                b.style.outline = 'none';
                b.style.borderColor = 'transparent';
            });
            // Reset modal title
            document.getElementById('rec-modal-title').textContent = 'Tambah Kegiatan Rutin';
            document.getElementById('rec-submit-label').textContent = 'Simpan Kegiatan Rutin';
            // Reset freq options
            toggleFreqOptions('weekly');
            // Clear errors
            clearAllErr('rec-title-err', 'rec-days-err', 'rec-dom-err', 'rec-stime-err', 'rec-etime-err', 'rec-start-err',
                'rec-end-err');
        }
    </script>
@endpush
