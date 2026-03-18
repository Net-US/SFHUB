{{-- resources/views/dashboard/pkl.blade.php --}}
@extends('layouts.app-dashboard')
@section('title','PKL Manager | StudentHub')
@section('page-title','PKL / Magang')

@push('styles')
<style>
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.fade-up{animation:fadeUp .4s ease-out both}
.tab-pill{display:flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:.75rem;font-size:.8125rem;font-weight:500;color:#78716c;cursor:pointer;border:none;background:none;transition:all .15s}
.tab-pill:hover{background:#f5f5f4;color:#1c1917}
.dark .tab-pill:hover{background:#292524;color:#fafaf9}
.tab-pill.active{background:#ecfdf5;color:#059669;font-weight:600}
.dark .tab-pill.active{background:rgba(16,185,129,.13);color:#34d399}
.fi{width:100%;border:1.5px solid #e7e5e4;border-radius:.8rem;padding:.6rem .95rem;font-size:.875rem;background:#fafaf9;color:#1c1917;outline:none;transition:border .18s}
.fi:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.1)}
.dark .fi{background:#292524;border-color:#44403c;color:#fafaf9}
.fi-label{display:block;font-size:.7rem;font-weight:700;color:#a8a29e;letter-spacing:.05em;text-transform:uppercase;margin-bottom:.35rem}
.split-row{background:rgba(59,130,246,.06);border:1.5px solid rgba(59,130,246,.15);border-radius:.75rem;padding:.75rem}
</style>
@endpush

@section('content')
@php
    $activities  = $activities  ?? [];
    $schedule    = $schedule    ?? [];
    $pklInfoArr  = $pklInfoArr  ?? ['company'=>'-','department'=>'-','supervisor'=>'-','supervisor_hp'=>'-','address'=>'-','start_date'=>now()->format('Y-m-d'),'end_date'=>now()->addMonths(6)->format('Y-m-d'),'hours_required'=>720,'hours_done'=>0,'allowance'=>0];
    $hoursDone   = $hoursDone   ?? 0;
    $daysLeft    = $daysLeft    ?? 0;
    $pctDone     = $pctDone     ?? 0;
    $calPct      = $calPct      ?? 0;
    $start       = $start       ?? now();
    $end         = $end         ?? now()->addMonths(6);
    $weeklyLogs  = collect($activities)->groupBy(fn($a)=>\Carbon\Carbon::parse($a['date'])->isoFormat('W'));
@endphp

<div class="fade-up space-y-5">

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h2 class="text-2xl font-bold text-stone-900 dark:text-white">PKL Manager</h2>
        <p class="text-stone-400 text-xs">Manajemen jadwal, log aktivitas, dan laporan magang</p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <button onclick="openModal('modal-edit-schedule')"
            class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors">
            <i class="fa-solid fa-calendar-days text-xs"></i> Atur Jadwal
        </button>
        <button onclick="openModal('modal-log-activity')"
            class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors">
            <i class="fa-solid fa-plus text-xs"></i> Log Aktivitas
        </button>
        <button onclick="openModal('modal-edit-pkl')"
            class="flex items-center gap-2 px-4 py-2 bg-stone-700 hover:bg-stone-800 text-white rounded-xl text-sm font-medium transition-colors">
            <i class="fa-solid fa-building text-xs"></i> Info PKL
        </button>
    </div>
</div>

@if(session('success'))
<div class="flex items-center gap-3 px-5 py-3.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm">
    <i class="fa-solid fa-circle-check flex-shrink-0"></i>{{ session('success') }}
</div>
@endif

@if($pklInfoArr['company'] === '-')
<div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-5 flex items-start gap-4">
    <i class="fa-solid fa-triangle-exclamation text-amber-500 text-xl flex-shrink-0 mt-0.5"></i>
    <div>
        <p class="font-bold text-amber-800 dark:text-amber-400 text-sm">Belum ada data PKL</p>
        <p class="text-amber-700 dark:text-amber-500 text-xs mt-0.5">Isi informasi perusahaan dan jadwal PKL terlebih dahulu agar fitur ini berfungsi penuh.</p>
        <button onclick="openModal('modal-edit-pkl')" class="mt-2 px-4 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-xl transition-colors">
            <i class="fa-solid fa-plus mr-1"></i> Setup PKL Sekarang
        </button>
    </div>
</div>
@endif

{{-- Progress Hero --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach([
        [number_format($hoursDone,1),'Jam Selesai','fa-clock','bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400'],
        [$pklInfoArr['hours_required'],'Jam Wajib','fa-hourglass-half','bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'],
        [$pctDone.'%','Progres Jam','fa-chart-pie','bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400'],
        [$daysLeft,'Hari Tersisa','fa-calendar-days','bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400'],
    ] as [$v,$l,$ic,$cls])
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl {{ $cls }} flex items-center justify-center flex-shrink-0">
            <i class="fa-solid {{ $ic }} text-lg"></i>
        </div>
        <div><p class="text-2xl font-bold text-stone-800 dark:text-white">{{ $v }}</p><p class="text-xs text-stone-400">{{ $l }}</p></div>
    </div>
    @endforeach
</div>

{{-- Tabs --}}
<div class="flex gap-1 bg-stone-100 dark:bg-stone-800 p-1 rounded-xl w-fit flex-wrap">
    @foreach([['log','fa-clipboard-list','Log Aktivitas'],['jadwal','fa-calendar-week','Jadwal PKL'],['info','fa-building','Info Perusahaan']] as [$id,$ic,$lbl])
    <button onclick="switchPKLTab('{{ $id }}')" id="pkltab-{{ $id }}" class="tab-pill {{ $id==='log' ? 'active' : '' }}">
        <i class="fa-solid {{ $ic }} text-xs"></i> {{ $lbl }}
    </button>
    @endforeach
</div>

{{-- ══════ TAB: LOG AKTIVITAS ══════ --}}
<div id="pkl-log" class="pkl-pane space-y-4">
    @if(empty($activities))
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-12 text-center">
        <i class="fa-solid fa-clipboard-list text-4xl text-stone-300 mb-4 block"></i>
        <p class="font-bold text-stone-500 mb-1">Belum ada log aktivitas</p>
        <p class="text-xs text-stone-400 mb-4">Catat aktivitas PKL harianmu untuk melacak progres jam magang</p>
        <button onclick="openModal('modal-log-activity')" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors">
            <i class="fa-solid fa-plus mr-1.5"></i>Log Aktivitas Pertama
        </button>
    </div>
    @else
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-100 dark:border-stone-800 flex items-center justify-between">
            <h3 class="font-bold text-stone-800 dark:text-white text-sm">Riwayat Aktivitas PKL</h3>
            <span class="text-xs text-stone-400">{{ count($activities) }} entri</span>
        </div>
        <div class="divide-y divide-stone-100 dark:divide-stone-800">
            @foreach($activities as $act)
            @php $catColor = match($act['category'] ?? 'Lainnya'){
                'Design'=>'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
                'Development'=>'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                'Marketing'=>'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
                'Meeting'=>'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                'Social Media'=>'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400',
                default=>'bg-stone-100 dark:bg-stone-700 text-stone-500'
            }; @endphp
            <div class="flex items-start gap-4 px-6 py-4 hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors group" id="act-row-{{ $act['id'] }}">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-briefcase text-emerald-600 dark:text-emerald-400 text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-stone-800 dark:text-white">{{ $act['task'] }}</p>
                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                        <span class="text-[11px] font-medium text-stone-500 dark:text-stone-400">
                            <i class="fa-regular fa-calendar text-[10px] mr-0.5"></i>
                            {{ \Carbon\Carbon::parse($act['date'])->isoFormat('ddd, D MMM Y') }}
                        </span>
                        <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                            <i class="fa-regular fa-clock text-[10px] mr-0.5"></i>{{ $act['hours'] }} jam
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-medium {{ $catColor }}">{{ $act['category'] }}</span>
                    </div>
                    @if($act['notes'])
                    <p class="text-xs text-stone-400 mt-1">{{ Str::limit($act['notes'], 80) }}</p>
                    @endif
                </div>
                <button onclick="deleteActivity({{ $act['id'] }}, this)"
                    class="w-8 h-8 rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all" title="Hapus">
                    <i class="fa-solid fa-trash text-xs"></i>
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- ══════ TAB: JADWAL PKL ══════ --}}
<div id="pkl-jadwal" class="pkl-pane hidden space-y-4">
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-100 dark:border-stone-800 flex items-center justify-between">
            <h3 class="font-bold text-stone-800 dark:text-white text-sm flex items-center gap-2">
                <i class="fa-solid fa-calendar-week text-emerald-500"></i> Jadwal Mingguan PKL
            </h3>
            <button onclick="openModal('modal-edit-schedule')" class="text-xs text-emerald-600 hover:text-emerald-700 flex items-center gap-1 font-medium transition-colors">
                <i class="fa-solid fa-pen text-[10px]"></i> Edit Jadwal
            </button>
        </div>
        <div class="divide-y divide-stone-100 dark:divide-stone-800">
            @foreach($schedule as $s)
            @php
                $typeCls = match($s['type']){
                    'full'  => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
                    'half'  => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                    'split' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                    default => 'bg-stone-100 dark:bg-stone-700 text-stone-500',
                };
                $typeLabel = match($s['type']){
                    'full'  => 'Full Day','half'=>'Half Day','split'=>'Split Shift','off'=>'Libur',default=>'Off'
                };
            @endphp
            <div class="flex items-center gap-4 px-6 py-4">
                <div class="w-20 flex-shrink-0">
                    <p class="text-sm font-bold text-stone-800 dark:text-white">{{ $s['day'] }}</p>
                </div>
                <div class="flex-1">
                    @if($s['type'] === 'off')
                    <span class="text-sm text-stone-400 italic">Libur / Tidak masuk</span>
                    @else
                    {{-- Sesi 1 --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-semibold text-stone-700 dark:text-stone-300">
                            {{ $s['start'] }} – {{ $s['end'] }}
                        </span>
                        @if($s['has_split'] ?? false)
                        <span class="text-[10px] bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded-full font-bold">Sesi 1</span>
                        @endif
                        <span class="text-[11px] text-stone-400">({{ number_format($s['total_hours'],1) }} jam total)</span>
                    </div>
                    {{-- Sesi 2 (split) --}}
                    @if($s['has_split'] ?? false)
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm font-semibold text-blue-700 dark:text-blue-400">
                            {{ $s['start2'] }} – {{ $s['end2'] }}
                        </span>
                        <span class="text-[10px] bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded-full font-bold">Sesi 2</span>
                        <span class="text-[10px] text-stone-400">Lanjut setelah jeda</span>
                    </div>
                    @endif
                    @endif
                </div>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold {{ $typeCls }} flex-shrink-0">{{ $typeLabel }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══════ TAB: INFO PERUSAHAAN ══════ --}}
<div id="pkl-info" class="pkl-pane hidden space-y-4">
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-stone-800 dark:text-white text-sm flex items-center gap-2">
                <i class="fa-solid fa-building text-emerald-500"></i> Informasi Perusahaan
            </h3>
            <button onclick="openModal('modal-edit-pkl')" class="text-xs text-stone-400 hover:text-emerald-500 flex items-center gap-1 transition-colors">
                <i class="fa-solid fa-pen text-[10px]"></i> Edit
            </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach([
                ['Perusahaan',$pklInfoArr['company'],'fa-building'],
                ['Departemen',$pklInfoArr['department'],'fa-sitemap'],
                ['Supervisor',$pklInfoArr['supervisor'],'fa-user-tie'],
                ['HP Supervisor',$pklInfoArr['supervisor_hp'],'fa-phone'],
                ['Alamat',$pklInfoArr['address'],'fa-location-dot'],
                ['Mulai PKL',\Carbon\Carbon::parse($pklInfoArr['start_date'])->isoFormat('D MMMM YYYY'),'fa-calendar-day'],
                ['Selesai PKL',\Carbon\Carbon::parse($pklInfoArr['end_date'])->isoFormat('D MMMM YYYY'),'fa-calendar-check'],
                ['Total Jam',$pklInfoArr['hours_required'].' jam','fa-clock'],
                ['Tunjangan','Rp '.number_format($pklInfoArr['allowance'],0,',','.').'/ bulan','fa-coins'],
            ] as [$l,$v,$ic])
            <div class="flex items-start gap-3 p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                <i class="fa-solid {{ $ic }} text-emerald-400 text-sm w-4 text-center mt-0.5 flex-shrink-0"></i>
                <div>
                    <p class="text-[10px] text-stone-400 mb-0.5">{{ $l }}</p>
                    <p class="text-sm font-medium text-stone-800 dark:text-white">{{ $v }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

</div>{{-- end .fade-up --}}

{{-- ════════════════════════════════════════════════════════════════════════
     MODAL: LOG AKTIVITAS — FORM REAL
════════════════════════════════════════════════════════════════════════ --}}
<div id="modal-log-activity" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
    <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800">
        <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
            <div>
                <h3 class="font-bold text-stone-900 dark:text-white">Log Aktivitas PKL</h3>
                <p class="text-xs text-stone-400 mt-0.5">Catat kegiatan & jam kerja harianmu</p>
            </div>
            <button onclick="closeModal('modal-log-activity')" class="text-stone-400 hover:text-stone-700"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>

        <form method="POST" action="{{ route('pkl.activity.store') }}" id="form-log-activity">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="fi-label">Aktivitas / Tugas <span class="text-rose-400">*</span></label>
                    <input type="text" name="task" id="la-task" class="fi" placeholder="Apa yang dikerjakan hari ini?" required>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="fi-label">Tanggal <span class="text-rose-400">*</span></label>
                        <input type="date" name="log_date" id="la-date" class="fi" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div>
                        <label class="fi-label">Jam Kerja <span class="text-rose-400">*</span></label>
                        <input type="number" name="hours" id="la-hours" min="0.5" max="24" step="0.5" class="fi" placeholder="4" required>
                    </div>
                    <div>
                        <label class="fi-label">Kategori <span class="text-rose-400">*</span></label>
                        <select name="category" id="la-cat" class="fi">
                            @foreach(['Design','Development','Marketing','Meeting','Social Media','Administration','Presentation','Research','Lainnya'] as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="fi-label">Output / Hasil / Catatan</label>
                    <textarea name="notes" id="la-notes" rows="3" class="fi resize-none" placeholder="Hasil pekerjaan, hambatan, catatan untuk laporan..."></textarea>
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button type="button" onclick="closeModal('modal-log-activity')" class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan Log
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     MODAL: EDIT JADWAL — FORM REAL + SPLIT SHIFT
════════════════════════════════════════════════════════════════════════ --}}
<div id="modal-edit-schedule" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
    <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-2xl shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
            <div>
                <h3 class="font-bold text-stone-900 dark:text-white">Edit Jadwal PKL</h3>
                <p class="text-xs text-stone-400 mt-0.5">Atur jadwal per hari, termasuk split-shift (dua sesi)</p>
            </div>
            <button onclick="closeModal('modal-edit-schedule')" class="text-stone-400 hover:text-stone-700 flex-shrink-0"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>

        <form method="POST" action="{{ route('pkl.schedule.update') }}" id="form-edit-schedule">
            @csrf
            <div class="p-6 space-y-3">

                {{-- Panduan split-shift --}}
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-[11px] text-blue-700 dark:text-blue-400 flex items-start gap-2 mb-2">
                    <i class="fa-solid fa-circle-info flex-shrink-0 mt-0.5"></i>
                    <span><strong>Split Shift:</strong> Pilih tipe "Split Shift" lalu isi <strong>Sesi 1</strong> (misal 08:00–09:00) dan <strong>Sesi 2</strong> (misal 13:00–16:00) untuk jadwal kuliah di tengah hari. Total jam dihitung otomatis.</span>
                </div>

                @foreach($schedule as $i => $s)
                @php $isOff = $s['type'] === 'off'; @endphp
                <div class="bg-stone-50 dark:bg-stone-800 rounded-xl p-4 space-y-3 border border-stone-200 dark:border-stone-700" id="sched-row-{{ $i }}">
                    {{-- Row header: hari + tipe --}}
                    <div class="flex items-center gap-3">
                        <span class="w-20 text-sm font-bold text-stone-700 dark:text-stone-300 flex-shrink-0">{{ $s['day'] }}</span>
                        <input type="hidden" name="schedules[{{ $i }}][day]" value="{{ $s['day'] }}">
                        <select name="schedules[{{ $i }}][type]" id="type-{{ $i }}" onchange="toggleScheduleType({{ $i }}, this.value)"
                            class="fi" style="width:auto;flex:1">
                            <option value="full"  {{ $s['type']==='full'  ? 'selected':'' }}>✅ Full Day</option>
                            <option value="half"  {{ $s['type']==='half'  ? 'selected':'' }}>🌤 Half Day</option>
                            <option value="split" {{ $s['type']==='split' ? 'selected':'' }}>⚡ Split Shift</option>
                            <option value="off"   {{ $s['type']==='off'   ? 'selected':'' }}>❌ Libur</option>
                        </select>
                    </div>

                    {{-- Sesi 1 --}}
                    <div id="sesi1-{{ $i }}" class="{{ $isOff ? 'hidden' : '' }}">
                        <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1.5">Sesi 1</p>
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <label class="fi-label text-[10px]">Jam Mulai</label>
                                <input type="time" name="schedules[{{ $i }}][start_time]" id="start1-{{ $i }}"
                                    value="{{ $s['start'] }}"
                                    class="fi"
                                    {{ $isOff ? 'disabled' : '' }}>
                            </div>
                            <span class="text-stone-400 text-sm mt-5 flex-shrink-0">–</span>
                            <div class="flex-1">
                                <label class="fi-label text-[10px]">Jam Selesai</label>
                                <input type="time" name="schedules[{{ $i }}][end_time]" id="end1-{{ $i }}"
                                    value="{{ $s['end'] }}"
                                    class="fi"
                                    {{ $isOff ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>

                    {{-- Sesi 2 (split shift) --}}
                    <div id="sesi2-{{ $i }}" class="{{ ($s['type'] !== 'split') ? 'hidden' : '' }}">
                        <div class="split-row space-y-2">
                            <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">⚡ Sesi 2 — Lanjut setelah jeda (misal: setelah kuliah)</p>
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <label class="fi-label text-[10px]">Jam Mulai Sesi 2</label>
                                    <input type="time" name="schedules[{{ $i }}][start_time_2]" id="start2-{{ $i }}"
                                        value="{{ $s['start2'] ?? '' }}"
                                        class="fi">
                                </div>
                                <span class="text-stone-400 text-sm mt-5 flex-shrink-0">–</span>
                                <div class="flex-1">
                                    <label class="fi-label text-[10px]">Jam Selesai Sesi 2</label>
                                    <input type="time" name="schedules[{{ $i }}][end_time_2]" id="end2-{{ $i }}"
                                        value="{{ $s['end2'] ?? '' }}"
                                        class="fi">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Catatan per hari --}}
                    <div id="notes-{{ $i }}" class="{{ $isOff ? 'hidden' : '' }}">
                        <label class="fi-label text-[10px]">Catatan (opsional)</label>
                        <input type="text" name="schedules[{{ $i }}][notes]" value="{{ $s['notes'] ?? '' }}"
                            class="fi text-xs" placeholder="misal: WFH, tugas lapangan, dll">
                    </div>
                </div>
                @endforeach

            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button type="button" onclick="closeModal('modal-edit-schedule')" class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     MODAL: EDIT PKL INFO — FORM REAL
════════════════════════════════════════════════════════════════════════ --}}
<div id="modal-edit-pkl" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
    <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
            <div>
                <h3 class="font-bold text-stone-900 dark:text-white">Informasi PKL</h3>
                <p class="text-xs text-stone-400 mt-0.5">Data perusahaan dan periode magang</p>
            </div>
            <button onclick="closeModal('modal-edit-pkl')" class="text-stone-400 hover:text-stone-700"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>

        <form method="POST" action="{{ route('pkl.info.store') }}" id="form-edit-pkl">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="fi-label">Nama Perusahaan <span class="text-rose-400">*</span></label>
                    <input type="text" name="company" id="mc-company" value="{{ $pklInfoArr['company'] !== '-' ? $pklInfoArr['company'] : '' }}" class="fi" placeholder="PT. Nama Perusahaan" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fi-label">Departemen</label>
                        <input type="text" name="department" id="mc-dept" value="{{ $pklInfoArr['department'] !== '-' ? $pklInfoArr['department'] : '' }}" class="fi" placeholder="IT / Marketing / Desain">
                    </div>
                    <div>
                        <label class="fi-label">Nama Supervisor</label>
                        <input type="text" name="supervisor" id="mc-supervisor" value="{{ $pklInfoArr['supervisor'] !== '-' ? $pklInfoArr['supervisor'] : '' }}" class="fi" placeholder="Bapak/Ibu ...">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fi-label">HP Supervisor</label>
                        <input type="text" name="supervisor_phone" id="mc-supervisor-hp" value="{{ $pklInfoArr['supervisor_hp'] !== '-' ? $pklInfoArr['supervisor_hp'] : '' }}" class="fi" placeholder="08xx-xxxx-xxxx">
                    </div>
                    <div>
                        <label class="fi-label">Tunjangan/Bulan (Rp)</label>
                        <input type="number" name="allowance" id="mc-allowance" value="{{ $pklInfoArr['allowance'] }}" class="fi" placeholder="0">
                    </div>
                </div>
                <div>
                    <label class="fi-label">Alamat Perusahaan</label>
                    <input type="text" name="address" id="mc-address" value="{{ $pklInfoArr['address'] !== '-' ? $pklInfoArr['address'] : '' }}" class="fi" placeholder="Jl. ...">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fi-label">Tanggal Mulai PKL <span class="text-rose-400">*</span></label>
                        <input type="date" name="start_date" id="mc-start" value="{{ $pklInfoArr['start_date'] }}" class="fi">
                    </div>
                    <div>
                        <label class="fi-label">Tanggal Selesai PKL <span class="text-rose-400">*</span></label>
                        <input type="date" name="end_date" id="mc-end" value="{{ $pklInfoArr['end_date'] }}" class="fi">
                    </div>
                </div>
                <div>
                    <label class="fi-label">Total Jam Wajib</label>
                    <input type="number" name="hours_required" id="mc-hours" value="{{ $pklInfoArr['hours_required'] }}" class="fi" placeholder="720" min="1">
                    <p class="text-[10px] text-stone-400 mt-1">Contoh: 720 jam = ±6 bulan PKL</p>
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button type="button" onclick="closeModal('modal-edit-pkl')" class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan Info PKL
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function openModal(id)  { document.getElementById(id)?.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
function closeModal(id) { document.getElementById(id)?.classList.add('hidden');    document.body.classList.remove('overflow-hidden'); }

function switchPKLTab(tab) {
    document.querySelectorAll('.pkl-pane').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('[id^="pkltab-"]').forEach(b => b.classList.remove('active'));
    document.getElementById('pkl-' + tab)?.classList.remove('hidden');
    document.getElementById('pkltab-' + tab)?.classList.add('active');
}

// ── Toggle tampilan input berdasarkan tipe jadwal ──────────────────────
function toggleScheduleType(idx, type) {
    const sesi1  = document.getElementById('sesi1-' + idx);
    const sesi2  = document.getElementById('sesi2-' + idx);
    const notes  = document.getElementById('notes-' + idx);
    const start1 = document.getElementById('start1-' + idx);
    const end1   = document.getElementById('end1-' + idx);

    const isOff = type === 'off';
    sesi1?.classList.toggle('hidden', isOff);
    notes?.classList.toggle('hidden', isOff);
    sesi2?.classList.toggle('hidden', type !== 'split');

    // Disable inputs jika off agar tidak terkirim
    if (start1) start1.disabled = isOff;
    if (end1)   end1.disabled   = isOff;
}

// ── Delete aktivitas PKL via fetch ────────────────────────────────────
async function deleteActivity(id, btn) {
    if (!confirm('Hapus log aktivitas ini?')) return;
    btn.disabled = true;
    try {
        const res = await fetch(`/dashboard/pkl/activity/${id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        if (data.success || res.ok) {
            const row = document.getElementById('act-row-' + id);
            if (row) { row.style.transition='all .25s'; row.style.opacity='0'; setTimeout(()=>row.remove(),250); }
            toast('Aktivitas dihapus.');
        } else {
            toast(data.message || 'Gagal menghapus.', false);
            btn.disabled = false;
        }
    } catch(e) {
        toast('Gagal menghubungi server.', false);
        btn.disabled = false;
    }
}

function toast(msg, ok=true) {
    const t = document.createElement('div');
    t.className = `fixed bottom-6 right-6 z-[9999] flex items-center gap-2 px-4 py-3.5 ${ok?'bg-emerald-500':'bg-rose-500'} text-white text-sm font-semibold rounded-2xl shadow-xl`;
    t.style.animation = 'fadeUp .28s ease-out both';
    t.innerHTML = `<i class="fa-solid ${ok?'fa-check-circle':'fa-circle-xmark'}"></i> ${msg}`;
    document.body.appendChild(t);
    setTimeout(()=>{t.style.transition='opacity .3s';t.style.opacity='0';setTimeout(()=>t.remove(),300);},2800);
}

// ── Restore tab from hash ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const h = location.hash.replace('#', '');
    if (['log','jadwal','info'].includes(h)) switchPKLTab(h);
});
</script>
@endpush
