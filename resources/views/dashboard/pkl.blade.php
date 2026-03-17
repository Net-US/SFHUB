{{-- resources/views/dashboard/pkl.blade.php --}}
@extends('layouts.app-dashboard')
@section('title', 'PKL Manager | StudentHub')
@section('page-title', 'PKL / Magang')

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
            background: #ecfdf5;
            color: #059669;
            font-weight: 600
        }

        .dark .tab-pill.active {
            background: rgba(16, 185, 129, .13);
            color: #34d399
        }
    </style>
@endpush

@section('content')
    @php
        $activities = $activities ?? [];
        $schedule = $schedule ?? [];
        $pklInfoArr = $pklInfoArr ?? [
            'company' => '-',
            'department' => '-',
            'supervisor' => '-',
            'supervisor_hp' => '-',
            'address' => '-',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(6)->format('Y-m-d'),
            'hours_required' => 720,
            'hours_done' => 0,
            'allowance' => 0,
        ];
        $hoursDone = $hoursDone ?? 0;
        $daysLeft = $daysLeft ?? 0;
        $pctDone = $pctDone ?? 0;
        $calPct = $calPct ?? 0;
        $start = $start ?? null;
        $end = $end ?? null;

        $weeklyLogs = collect($activities)->groupBy(function ($a) {
            return \Carbon\Carbon::parse($a['date'])->isoFormat('W');
        });
    @endphp

    <div class="fade-up space-y-5">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">PKL Manager</h2>
                <p class="text-stone-400 text-xs">Manajemen jadwal, log aktivitas, dan laporan magang</p>
            </div>
            <div class="flex gap-2">
                <button onclick="openModal('modal-log-activity')"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-pen-to-square text-xs"></i> Log Hari Ini
                </button>
                <button onclick="openModal('modal-edit-pkl')"
                    class="flex items-center gap-2 px-3 py-2 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-300 rounded-xl text-sm transition-colors">
                    <i class="fa-solid fa-gear text-xs"></i>
                </button>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg">
                <p class="text-emerald-100 text-xs mb-1">Jam Terselesaikan</p>
                <h3 class="text-2xl font-bold">{{ $pklInfoArr['hours_done'] }}</h3>
                <p class="text-emerald-200 text-[11px] mt-1">dari {{ $pklInfoArr['hours_required'] }} jam</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <p class="text-stone-400 text-xs mb-1">Progres Jam</p>
                <h3 class="text-2xl font-bold text-stone-800 dark:text-white">{{ $pctDone }}%</h3>
                <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-1.5 mt-2">
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width:{{ $pctDone }}%"></div>
                </div>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <p class="text-stone-400 text-xs mb-1">Sisa Hari</p>
                <h3 class="text-2xl font-bold {{ $daysLeft < 30 ? 'text-rose-600' : 'text-stone-800 dark:text-white' }}">
                    {{ $daysLeft > 0 ? $daysLeft : '0' }}
                </h3>
                <p class="text-stone-400 text-[11px] mt-1">Selesai: {{ $end?->isoFormat('D MMM YYYY') ?? '-' }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <p class="text-stone-400 text-xs mb-1">Tunjangan/Bulan</p>
                <h3 class="text-xl font-bold text-emerald-600">Rp {{ number_format($pklInfoArr['allowance'], 0, ',', '.') }}
                </h3>
                <p class="text-stone-400 text-[11px] mt-1">{{ $pklInfoArr['department'] }}</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 bg-stone-100 dark:bg-stone-800 p-1 rounded-xl w-fit flex-wrap">
            @foreach ([['jadwal', 'fa-calendar-week', 'Jadwal Mingguan'], ['aktivitas', 'fa-clipboard-list', 'Log Aktivitas'], ['info', 'fa-building', 'Info PKL']] as [$id, $ic, $lbl])
                <button onclick="switchPKLTab('{{ $id }}')" id="pkltab-{{ $id }}"
                    class="tab-pill {{ $id === 'jadwal' ? 'active' : '' }}">
                    <i class="fa-solid {{ $ic }} text-xs"></i> {{ $lbl }}
                </button>
            @endforeach
        </div>

        {{-- ═══════ TAB: JADWAL ═══════ --}}
        <div id="pkl-jadwal" class="pkl-pane space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                {{-- Weekly schedule --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                        <h3 class="font-bold text-stone-800 dark:text-white text-sm flex items-center gap-2">
                            <i class="fa-solid fa-calendar-week text-emerald-500"></i> Jadwal Rutin PKL
                        </h3>
                        <button onclick="openModal('modal-edit-schedule')"
                            class="text-xs text-stone-400 hover:text-emerald-600 flex items-center gap-1 transition-colors">
                            <i class="fa-solid fa-pen text-[10px]"></i> Edit
                        </button>
                    </div>
                    <div class="divide-y divide-stone-100 dark:divide-stone-800">
                        @foreach ($schedule as $s)
                            <div class="flex items-center justify-between px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-16 text-xs font-semibold text-stone-600 dark:text-stone-400">{{ $s['day'] }}</span>
                                    @if ($s['type'] === 'off')
                                        <span class="text-xs text-stone-300 dark:text-stone-600">Libur</span>
                                    @else
                                        <span
                                            class="text-xs {{ $s['type'] === 'full' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }} font-medium">
                                            {{ $s['start'] }} – {{ $s['end'] }}
                                        </span>
                                        @if ($s['notes'])
                                            <span class="text-[10px] text-stone-400">· {{ $s['notes'] }}</span>
                                        @endif
                                    @endif
                                </div>
                                @if ($s['type'] !== 'off')
                                    <span
                                        class="text-[10px] px-2 py-0.5 rounded-full font-semibold
                        {{ $s['type'] === 'full' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' }}">
                                        {{ $s['type'] === 'full' ? 'Full Day' : 'Half Day' }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="px-6 py-3 bg-stone-50 dark:bg-stone-800 border-t border-stone-100 dark:border-stone-800">
                        <p class="text-[11px] text-stone-400">
                            <i class="fa-solid fa-clock text-emerald-400 mr-1"></i>
                            Total: 36 jam/minggu · Berlaku {{ $start?->isoFormat('D MMM YYYY') ?? '-' }} –
                            {{ $end?->isoFormat('D MMM YYYY') ?? '-' }}
                        </p>
                    </div>
                </div>

                {{-- Progress timeline --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
                    <h3 class="font-bold text-stone-800 dark:text-white text-sm mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-chart-gantt text-emerald-500"></i> Progress PKL
                    </h3>
                    <div class="space-y-4">
                        {{-- Overall duration --}}
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-stone-500 dark:text-stone-400">Durasi PKL</span>
                                <span class="font-semibold text-stone-700 dark:text-stone-300">{{ $calPct }}%
                                    berlalu</span>
                            </div>
                            <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-3">
                                <div class="bg-blue-500 h-3 rounded-full relative" style="width:{{ $calPct }}%">
                                    <span
                                        class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-1/2 w-4 h-4 rounded-full bg-blue-600 border-2 border-white dark:border-stone-900 shadow-sm"></span>
                                </div>
                            </div>
                            <div class="flex justify-between text-[10px] text-stone-400 mt-1">
                                <span>{{ $start?->isoFormat('D MMM') ?? '-' }}</span>
                                <span>{{ $end?->isoFormat('D MMM YYYY') ?? '-' }}</span>
                            </div>
                        </div>
                        {{-- Hours --}}
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-stone-500 dark:text-stone-400">Jam Kerja</span>
                                <span
                                    class="font-semibold text-stone-700 dark:text-stone-300">{{ $pklInfoArr['hours_done'] }}/{{ $pklInfoArr['hours_required'] }}
                                    jam</span>
                            </div>
                            <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-3">
                                <div class="bg-emerald-500 h-3 rounded-full" style="width:{{ $pctDone }}%"></div>
                            </div>
                        </div>
                        {{-- Stats grid --}}
                        <div class="grid grid-cols-3 gap-3 pt-2">
                            @foreach ([['Hari Ini', '' . now()->isoFormat('dddd'), 'fa-calendar-check', 'text-emerald-600'], ['Sisa Jam', $pklInfoArr['hours_required'] - $pklInfoArr['hours_done'] . ' jam', 'fa-hourglass-half', 'text-amber-600'], ['Selesai', $end?->isoFormat('MMM YY') ?? '-', 'fa-flag-checkered', 'text-blue-600']] as [$l, $v, $ic, $tc])
                                <div class="bg-stone-50 dark:bg-stone-800 rounded-xl p-3 text-center">
                                    <i class="fa-solid {{ $ic }} {{ $tc }} text-base mb-1 block"></i>
                                    <p class="text-xs font-bold text-stone-800 dark:text-white">{{ $v }}</p>
                                    <p class="text-[10px] text-stone-400">{{ $l }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════ TAB: AKTIVITAS ═══════ --}}
        <div id="pkl-aktivitas" class="pkl-pane hidden space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- Log list --}}
                <div
                    class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                        <h3 class="font-bold text-stone-800 dark:text-white text-sm">Log Aktivitas PKL</h3>
                        <button onclick="openModal('modal-log-activity')"
                            class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-xl transition-colors">
                            <i class="fa-solid fa-plus text-[10px]"></i> Log Baru
                        </button>
                    </div>
                    <div class="divide-y divide-stone-100 dark:divide-stone-800">
                        @foreach ($activities as $a)
                            @php $dObj = \Carbon\Carbon::parse($a['date']); @endphp
                            <div
                                class="flex items-start gap-4 px-5 py-4 hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">
                                <div
                                    class="w-10 h-10 rounded-xl
                        {{ $a['status'] === 'done' ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-amber-100 dark:bg-amber-900/30' }}
                        flex items-center justify-center flex-shrink-0">
                                    <i
                                        class="fa-solid {{ $a['status'] === 'done' ? 'fa-check text-emerald-600 dark:text-emerald-400' : 'fa-clock text-amber-600 dark:text-amber-400' }} text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-stone-800 dark:text-white">{{ $a['task'] }}</p>
                                    <div class="flex items-center gap-3 mt-1 flex-wrap">
                                        <span
                                            class="text-[10px] bg-stone-100 dark:bg-stone-700 text-stone-600 dark:text-stone-300 px-2 py-0.5 rounded-full">{{ $a['category'] }}</span>
                                        <span class="text-[10px] text-stone-400"><i
                                                class="fa-regular fa-clock mr-0.5"></i>{{ $a['hours'] }} jam</span>
                                        <span
                                            class="text-[10px] text-stone-400">{{ $dObj->isoFormat('D MMM YYYY') }}</span>
                                        @if ($a['notes'])
                                            <span class="text-[10px] text-stone-400">· {{ $a['notes'] }}</span>
                                        @endif
                                    </div>
                                </div>
                                <button
                                    class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-400 hover:text-orange-500 hover:bg-orange-50 flex items-center justify-center transition-colors flex-shrink-0">
                                    <i class="fa-solid fa-pen text-[10px]"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Weekly stats --}}
                <div class="space-y-4">
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                        <h4 class="font-bold text-stone-800 dark:text-white text-sm mb-3">Minggu Ini</h4>
                        @php
                            $thisWeekActs = collect($activities)->filter(
                                fn($a) => \Carbon\Carbon::parse($a['date'])->isCurrentWeek(),
                            );
                            $thisWeekHours = $thisWeekActs->sum('hours');
                        @endphp
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-stone-500">Total Jam</span>
                                <span class="font-bold text-stone-800 dark:text-white">{{ $thisWeekHours }} jam</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-stone-500">Aktivitas</span>
                                <span class="font-bold text-stone-800 dark:text-white">{{ $thisWeekActs->count() }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-stone-500">Selesai</span>
                                <span
                                    class="font-bold text-emerald-600">{{ $thisWeekActs->where('status', 'done')->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                        <h4 class="font-bold text-stone-800 dark:text-white text-sm mb-3">Kategori Terbanyak</h4>
                        @php $catCounts = collect($activities)->groupBy('category')->map->count()->sortDesc(); @endphp
                        @foreach ($catCounts->take(4) as $cat => $cnt)
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs text-stone-600 dark:text-stone-400">{{ $cat }}</span>
                                <span
                                    class="text-xs font-bold text-stone-800 dark:text-white bg-stone-100 dark:bg-stone-700 px-2 py-0.5 rounded-full">{{ $cnt }}x</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════ TAB: INFO PKL ═══════ --}}
        <div id="pkl-info" class="pkl-pane hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-stone-800 dark:text-white text-sm flex items-center gap-2">
                            <i class="fa-solid fa-building text-emerald-500"></i> Informasi Perusahaan
                        </h3>
                        <button onclick="openModal('modal-edit-pkl')"
                            class="text-xs text-stone-400 hover:text-emerald-500 flex items-center gap-1 transition-colors">
                            <i class="fa-solid fa-pen text-[10px]"></i> Edit
                        </button>
                    </div>
                    <div class="space-y-3">
                        @foreach ([['Perusahaan', $pklInfoArr['company'], 'fa-building'], ['Departemen', $pklInfoArr['department'], 'fa-sitemap'], ['Supervisor', $pklInfoArr['supervisor'], 'fa-user-tie'], ['HP Supervisor', $pklInfoArr['supervisor_hp'], 'fa-phone'], ['Alamat', $pklInfoArr['address'], 'fa-location-dot'], ['Mulai PKL', \Carbon\Carbon::parse($pklInfoArr['start_date'])->isoFormat('D MMMM YYYY'), 'fa-calendar-day'], ['Selesai PKL', \Carbon\Carbon::parse($pklInfoArr['end_date'])->isoFormat('D MMMM YYYY'), 'fa-calendar-check'], ['Total Jam', $pklInfoArr['hours_required'] . ' jam', 'fa-clock'], ['Tunjangan', 'Rp ' . number_format($pklInfoArr['allowance'], 0, ',', '.') . '/ bulan', 'fa-coins']] as [$l, $v, $ic])
                            <div
                                class="flex items-start gap-3 py-2 border-b border-stone-50 dark:border-stone-800 last:border-0">
                                <i
                                    class="fa-solid {{ $ic }} text-emerald-400 text-sm w-4 text-center mt-0.5 flex-shrink-0"></i>
                                <div class="flex-1">
                                    <p class="text-[10px] text-stone-400">{{ $l }}</p>
                                    <p class="text-sm font-medium text-stone-800 dark:text-white">{{ $v }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
                    <h3 class="font-bold text-stone-800 dark:text-white text-sm mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-emerald-500"></i> Checklist Laporan PKL
                    </h3>
                    <div class="space-y-2" id="checklist-container">
                        @foreach ([['Log Aktivitas Harian', true], ['Laporan Mingguan ke Supervisor', true], ['Dokumentasi Foto Kegiatan', true], ['Laporan Bulanan ke Kampus', false], ['Presentasi Progres PKL', false], ['Draft Laporan Akhir', false], ['Tanda Tangan Supervisor', false], ['Penilaian Akhir PKL', false]] as [$item, $done])
                            <div
                                class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">
                                <button
                                    class="w-5 h-5 rounded-full border-2 {{ $done ? 'bg-emerald-500 border-emerald-500' : 'border-stone-300 dark:border-stone-600' }} flex items-center justify-center flex-shrink-0 hover:border-emerald-400 transition-colors"
                                    onclick="this.classList.toggle('bg-emerald-500'); this.classList.toggle('border-emerald-500'); this.innerHTML = this.classList.contains('bg-emerald-500') ? \'<i class=fa-solid fa-check text-white text-[9px]></i>\' : ''">
                                    @if ($done)
                                        <i class="fa-solid fa-check text-white text-[9px]"></i>
                                    @endif
                                </button>
                                <span
                                    class="text-sm {{ $done ? 'line-through text-stone-400' : 'text-stone-700 dark:text-stone-300' }}">{{ $item }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- MODAL: LOG ACTIVITY --}}
    <div id="modal-log-activity"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white">Log Aktivitas PKL</h3>
                <button onclick="closeModal('modal-log-activity')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Aktivitas /
                        Tugas *</label>
                    <input type="text" id="la-task"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Apa yang dikerjakan hari ini?">
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Tanggal</label>
                        <input type="date" id="la-date"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                            value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Jam
                            Kerja</label>
                        <input type="number" id="la-hours" min="0.5" max="12" step="0.5"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="4">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Kategori</label>
                        <select id="la-cat"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option>Design</option>
                            <option>Development</option>
                            <option>Marketing</option>
                            <option>Meeting</option>
                            <option>Social Media</option>
                            <option>Administration</option>
                            <option>Presentation</option>
                            <option>Lainnya</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Output /
                        Hasil</label>
                    <textarea id="la-notes" rows="3"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none dark:bg-stone-800 dark:text-white resize-none"
                        placeholder="Hasil pekerjaan, hambatan, catatan untuk laporan..."></textarea>
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-log-activity')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="saveActivity()"
                    class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan Log
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL: EDIT SCHEDULE --}}
    <div id="modal-edit-schedule"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white">Edit Jadwal PKL</h3>
                <button onclick="closeModal('modal-edit-schedule')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-3">
                @foreach ($schedule as $i => $s)
                    <div class="flex items-center gap-3 p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                        <span
                            class="w-16 text-sm font-semibold text-stone-700 dark:text-stone-300 flex-shrink-0">{{ $s['day'] }}</span>
                        <select
                            class="flex-1 border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-1.5 text-sm dark:bg-stone-700 dark:text-white">
                            <option {{ $s['type'] === 'full' ? 'selected' : '' }}>full</option>
                            <option {{ $s['type'] === 'half' ? 'selected' : '' }}>half</option>
                            <option {{ $s['type'] === 'off' ? 'selected' : '' }}>off</option>
                        </select>
                        <input type="time" value="{{ $s['start'] }}" placeholder="08:00"
                            class="w-24 border border-stone-300 dark:border-stone-700 rounded-lg px-2 py-1.5 text-sm text-center dark:bg-stone-700 dark:text-white">
                        <span class="text-stone-400 text-sm">–</span>
                        <input type="time" value="{{ $s['end'] }}" placeholder="17:00"
                            class="w-24 border border-stone-300 dark:border-stone-700 rounded-lg px-2 py-1.5 text-sm text-center dark:bg-stone-700 dark:text-white">
                    </div>
                @endforeach
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-edit-schedule')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="closeModal('modal-edit-schedule'); toast('Jadwal berhasil disimpan!')"
                    class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan Jadwal
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL: EDIT PKL INFO --}}
    <div id="modal-edit-pkl"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white">Edit Informasi PKL</h3>
                <button onclick="closeModal('modal-edit-pkl')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                @foreach ([['mc-company', 'Nama Perusahaan *', 'text', $pklInfoArr['company']], ['mc-dept', 'Departemen *', 'text', $pklInfoArr['department']], ['mc-supervisor', 'Nama Supervisor *', 'text', $pklInfoArr['supervisor']], ['mc-supervisor-hp', 'HP Supervisor', 'text', $pklInfoArr['supervisor_hp']], ['mc-address', 'Alamat', 'text', $pklInfoArr['address']]] as [$id, $lbl, $type, $val])
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">{{ $lbl }}</label>
                        <input type="{{ $type }}" id="{{ $id }}" value="{{ $val }}"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                @endforeach
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Tanggal
                            Mulai *</label>
                        <input type="date" id="mc-start" value="{{ $pklInfoArr['start_date'] }}"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Tanggal
                            Selesai *</label>
                        <input type="date" id="mc-end" value="{{ $pklInfoArr['end_date'] }}"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Total Jam
                            Wajib</label>
                        <input type="number" id="mc-hours" value="{{ $pklInfoArr['hours_required'] }}"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Tunjangan/Bulan
                            (Rp)</label>
                        <input type="number" id="mc-allowance" value="{{ $pklInfoArr['allowance'] }}"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-edit-pkl')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="closeModal('modal-edit-pkl'); toast('Info PKL berhasil disimpan!')"
                    class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openModal(id) {
            document.getElementById(id)?.classList.remove('hidden');
            document.body.classList.add('modal-open');
        }

        function closeModal(id) {
            document.getElementById(id)?.classList.add('hidden');
            document.body.classList.remove('modal-open');
        }

        function toast(msg, ok = true) {
            const t = document.createElement('div');
            t.className =
                `fixed bottom-6 right-6 z-[9999] flex items-center gap-2 px-4 py-3 ${ok ? 'bg-emerald-500' : 'bg-rose-500'} text-white text-sm font-medium rounded-2xl shadow-xl`;
            t.innerHTML = `<i class="fa-solid ${ok ? 'fa-check-circle' : 'fa-circle-xmark'}"></i> ${msg}`;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 2500);
        }

        function switchPKLTab(tab) {
            document.querySelectorAll('.pkl-pane').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('[id^="pkltab-"]').forEach(b => b.classList.remove('active'));
            document.getElementById('pkl-' + tab)?.classList.remove('hidden');
            document.getElementById('pkltab-' + tab)?.classList.add('active');
        }

        function saveActivity() {
            const task = document.getElementById('la-task').value.trim();
            if (!task) {
                alert('Isi aktivitas dulu');
                return;
            }
            closeModal('modal-log-activity');
            toast('Aktivitas PKL berhasil dicatat!');
        }
    </script>
@endpush
