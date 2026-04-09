{{-- resources/views/dashboard/creative-studio.blade.php --}}
@extends('layouts.app-dashboard')
@section('title', 'Creative Studio | StudentHub')
@section('page-title', 'Creative Studio')

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

        .tag {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 600
        }

        /* Sub-steps pipeline */
        .pipeline-step {
            flex: 1;
            text-align: center;
            position: relative
        }

        .pipeline-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 50%;
            right: 0;
            width: 100%;
            height: 2px;
            background: #e7e5e4;
            transform: translateY(-50%);
            z-index: 0
        }

        .dark .pipeline-step:not(:last-child)::after {
            background: #44403c
        }

        .pipeline-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 4px;
            font-size: 11px;
            font-weight: 700;
            position: relative;
            z-index: 1;
            transition: all .2s
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
            transition: border .18s
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
            border-color: #f97316
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
    </style>
@endpush

@section('content')
    @php
        $projects = $projects ?? [];
        $stats = $stats ?? [];
        $kanbanCols = [
            [
                'id' => 'script',
                'label' => 'Script & Concept',
                'bg' => 'bg-slate-50 dark:bg-slate-900/20',
                'border' => 'border-t-4 border-slate-400',
                'tc' => 'text-slate-700 dark:text-slate-300',
                'badge' => 'bg-slate-200 dark:bg-slate-700',
            ],
            [
                'id' => 'production',
                'label' => 'Production',
                'bg' => 'bg-orange-50 dark:bg-orange-900/10',
                'border' => 'border-t-4 border-orange-400',
                'tc' => 'text-orange-700 dark:text-orange-400',
                'badge' => 'bg-orange-200 dark:bg-orange-900',
            ],
            [
                'id' => 'revision',
                'label' => 'Revision / QC',
                'bg' => 'bg-amber-50 dark:bg-amber-900/10',
                'border' => 'border-t-4 border-amber-400',
                'tc' => 'text-amber-700 dark:text-amber-400',
                'badge' => 'bg-amber-200 dark:bg-amber-900',
            ],
            [
                'id' => 'done',
                'label' => 'Done ✓',
                'bg' => 'bg-emerald-50 dark:bg-emerald-900/10',
                'border' => 'border-t-4 border-emerald-400',
                'tc' => 'text-emerald-700 dark:text-emerald-400',
                'badge' => 'bg-emerald-200 dark:bg-emerald-900',
            ],
        ];
    @endphp

    <div class="fade-up space-y-5">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Creative Studio</h2>
                <p class="text-stone-400 text-xs">Kanban proyek freelance, konten & microstock · Tipe A: Berjenjang · Tipe B:
                    Mandiri</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button onclick="openModal('modal-add-project')"
                    class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-pink-500 text-white rounded-xl text-sm font-medium hover:opacity-90 transition-opacity shadow-lg shadow-orange-500/20">
                    <i class="fa-solid fa-plus"></i> Proyek Baru
                </button>
            </div>
        </div>

        @if (session('success'))
            <div
                class="flex items-center gap-3 px-5 py-3.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm">
                <i class="fa-solid fa-circle-check flex-shrink-0"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([[$stats['total_projects'] ?? 0, 'Total Proyek', 'fa-film', 'bg-orange-100 dark:bg-orange-900/30 text-orange-600'], [$stats['active_projects'] ?? 0, 'Sedang Jalan', 'fa-video', 'bg-blue-100 dark:bg-blue-900/30 text-blue-600'], [$stats['completed_projects'] ?? 0, 'Selesai', 'fa-check-circle', 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600'], [$stats['overdue_projects'] ?? 0, 'Terlambat', 'fa-clock', 'bg-rose-100 dark:bg-rose-900/30 text-rose-600']] as [$v, $l, $ic, $cls])
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl {{ $cls }} flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid {{ $ic }} text-lg"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-stone-800 dark:text-white">{{ $v }}</p>
                        <p class="text-xs text-stone-400">{{ $l }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- KANBAN BOARD --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($kanbanCols as $col)
                @php $colProjects = collect($projects)->where('stage', $col['id']); @endphp
                <div class="{{ $col['bg'] }} {{ $col['border'] }} rounded-2xl p-4" style="min-height:420px">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-bold {{ $col['tc'] }} text-sm">{{ $col['label'] }}</span>
                        <span
                            class="text-xs px-2 py-0.5 {{ $col['badge'] }} {{ $col['tc'] }} rounded-full font-bold">{{ $colProjects->count() }}</span>
                    </div>
                    <div class="space-y-3" id="col-{{ $col['id'] }}">
                        @forelse($colProjects as $p)
                            @php
                                $pricls = match ($p['priority']) {
                                    'high' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
                                    'medium' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
                                    default => 'bg-stone-100 dark:bg-stone-700 text-stone-500',
                                };
                                $isSeq = ($p['project_mode'] ?? 'simple') === 'sequential';
                                $totalS = $p['total_subtasks'] ?? 0;
                                $doneS = $p['completed_subtasks'] ?? 0;
                            @endphp
                            <div class="bg-white dark:bg-stone-800 rounded-xl p-4 shadow-sm {{ $p['color'] }} card-hover cursor-pointer"
                                onclick="showProjectDetail({{ $p['id'] }})">
                                {{-- Header --}}
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-stone-800 dark:text-white text-sm leading-snug flex-1 pr-2">
                                        {{ $p['title'] }}</h4>
                                    <span
                                        class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold flex-shrink-0 {{ $pricls }}">{{ ucfirst($p['priority']) }}</span>
                                </div>

                                {{-- Type badge + mode --}}
                                <div class="flex flex-wrap gap-1 mb-2">
                                    <span
                                        class="tag bg-stone-100 dark:bg-stone-700 text-stone-600 dark:text-stone-300">{{ $p['type'] }}</span>
                                    <span
                                        class="tag {{ $isSeq ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' }}">
                                        {{ $isSeq ? '⛓ Berjenjang' : '🎯 Mandiri' }}
                                    </span>
                                </div>

                                {{-- Pipeline (Tipe A sequential) --}}
                                @if ($isSeq && $totalS > 0)
                                    <div class="flex items-center mb-2 gap-px overflow-hidden">
                                        @foreach ($p['subtasks'] ?? [] as $sub)
                                            @php
                                                $dotCls = match ($sub['status']) {
                                                    'completed' => 'bg-emerald-500 text-white',
                                                    'in_progress' => 'bg-orange-500 text-white ring-2 ring-orange-300',
                                                    default => 'bg-stone-200 dark:bg-stone-700 text-stone-400',
                                                };
                                            @endphp
                                            <div class="pipeline-step" title="{{ $sub['stage_label'] }}">
                                                <div class="pipeline-dot text-[8px] {{ $dotCls }}"
                                                    style="width:20px;height:20px">
                                                    @if ($sub['status'] === 'completed')
                                                        ✓
                                                    @elseif($sub['status'] === 'in_progress')
                                                        ▶
                                                    @else
                                                        {{ $loop->iteration }}
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-[9px] text-stone-400 mb-2">{{ $doneS }}/{{ $totalS }}
                                        tahap selesai</p>
                                @endif

                                {{-- Progress bar --}}
                                <div class="mb-2">
                                    <div class="flex justify-between text-[10px] text-stone-400 mb-1">
                                        <span>Progress</span><span>{{ $p['progress'] }}%</span>
                                    </div>
                                    <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ $p['stage'] === 'done' ? 'bg-emerald-500' : 'bg-orange-500' }}"
                                            style="width:{{ $p['progress'] }}%"></div>
                                    </div>
                                </div>

                                {{-- Footer --}}
                                <div class="flex items-center justify-between text-[10px] text-stone-400">
                                    <span><i class="fa-regular fa-clock mr-0.5"></i>{{ $p['deadline'] }}</span>
                                    <div class="flex gap-1" onclick="event.stopPropagation()">
                                        @if ($p['stage'] !== 'done')
                                            <button onclick="markProjectDone({{ $p['id'] }}, this)"
                                                class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-900/20 text-emerald-600 hover:bg-emerald-200 flex items-center justify-center transition-colors"
                                                title="Selesai">
                                                <i class="fa-solid fa-check text-[9px]"></i>
                                            </button>
                                        @endif
                                        @if (($p['project_mode'] ?? 'simple') === 'simple' && $p['stage'] !== 'done')
                                            <button onclick="rescheduleProject({{ $p['id'] }}, this)"
                                                class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-200 flex items-center justify-center transition-colors"
                                                title="Jadwal Ulang">
                                                <i class="fa-solid fa-calendar-plus text-[9px]"></i>
                                            </button>
                                        @endif
                                        <button onclick="editProject({{ $p['id'] }})"
                                            class="w-6 h-6 rounded-lg bg-stone-100 dark:bg-stone-700 hover:bg-orange-100 text-stone-400 hover:text-orange-500 flex items-center justify-center transition-colors"
                                            title="Edit">
                                            <i class="fa-solid fa-pen text-[9px]"></i>
                                        </button>
                                        <button
                                            onclick="deleteProject({{ $p['id'] }}, '{{ addslashes($p['title']) }}')"
                                            class="w-6 h-6 rounded-lg bg-stone-100 dark:bg-stone-700 hover:bg-rose-100 text-stone-400 hover:text-rose-500 flex items-center justify-center transition-colors"
                                            title="Hapus">
                                            <i class="fa-solid fa-trash text-[9px]"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div
                                class="text-center py-8 border-2 border-dashed border-stone-200 dark:border-stone-700 rounded-xl">
                                <i class="fa-solid fa-plus text-stone-300 text-xl mb-1 block"></i>
                                <p class="text-xs text-stone-400">Kosong</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        {{-- PROJECT HISTORY & ANALYTICS --}}
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <i class="fa-solid fa-history text-purple-500 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-stone-800 dark:text-white text-sm">History Project</h3>
                        <p class="text-[11px] text-stone-400">Project yang telah selesai (bulan ini)</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select class="fi text-sm" onchange="filterHistory(this.value)">
                        <option value="all">Semua</option>
                        <option value="this_month">Bulan Ini</option>
                        <option value="last_month">Bulan Lalu</option>
                        <option value="this_year">Tahun Ini</option>
                    </select>
                </div>
            </div>

            {{-- Analytics Summary --}}
            <div class="px-6 py-4 bg-stone-50 dark:bg-stone-800 border-b border-stone-200 dark:border-stone-800">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-purple-600">{{ $historyStats['total_completed'] ?? 0 }}</p>
                        <p class="text-xs text-stone-500">Project Selesai</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-emerald-600">{{ $historyStats['avg_completion_days'] ?? 0 }}</p>
                        <p class="text-xs text-stone-500">Rata-rata Hari</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ $historyStats['total_revenue'] ?? 'Rp0' }}</p>
                        <p class="text-xs text-stone-500">Total Pendapatan</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-orange-600">{{ $historyStats['client_satisfaction'] ?? 0 }}%</p>
                        <p class="text-xs text-stone-500">Kepuasan Klien</p>
                    </div>
                </div>
            </div>

            {{-- History Table --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-stone-50 dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-stone-600 dark:text-stone-400">Project
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-stone-600 dark:text-stone-400">Klien
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-stone-600 dark:text-stone-400">Tipe</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-stone-600 dark:text-stone-400">Mulai
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-stone-600 dark:text-stone-400">Selesai
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-stone-600 dark:text-stone-400">Durasi
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-stone-600 dark:text-stone-400">Nilai
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-stone-600 dark:text-stone-400">Rating
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200 dark:divide-stone-700" id="history-tbody">
                        @forelse($completedProjects ?? [] as $project)
                            @php
                                $duration =
                                    $project->created_at && $project->completed_at
                                        ? $project->created_at->diffInDays($project->completed_at)
                                        : 0;
                                $rating = $project->client_rating ?? 0;
                            @endphp
                            <tr class="hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-stone-800 dark:text-white">{{ $project->title }}
                                    </p>
                                    @if ($project->description)
                                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">
                                            {{ Str::limit($project->description, 50) }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-sm text-stone-800 dark:text-white">{{ $project->client ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                                        {{ $project->type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-sm text-stone-800 dark:text-white">
                                        {{ $project->created_at?->format('d M Y') }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-sm text-stone-800 dark:text-white">
                                        {{ $project->completed_at?->format('d M Y') }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-sm text-stone-800 dark:text-white">{{ $duration }} hari</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-emerald-600">
                                        {{ $project->project_value ? 'Rp' . number_format($project->project_value, 0, ',', '.') : '-' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($rating > 0)
                                        <div class="flex items-center gap-1">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i
                                                    class="fa-solid fa-star text-xs {{ $i <= $rating ? 'text-yellow-400' : 'text-stone-300' }}"></i>
                                            @endfor
                                            <span class="text-xs text-stone-500 ml-1">{{ $rating }}/5</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-stone-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center">
                                    <i class="fa-solid fa-inbox text-stone-300 text-2xl mb-2"></i>
                                    <p class="text-sm text-stone-500 dark:text-stone-400">Belum ada project yang selesai
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if (isset($completedProjects) && $completedProjects->hasPages())
                <div class="px-6 py-4 border-t border-stone-200 dark:border-stone-800">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-stone-500">
                            Menampilkan {{ $completedProjects->firstItem() }}-{{ $completedProjects->lastItem() }} dari
                            {{ $completedProjects->total() }} project
                        </p>
                        <div class="flex gap-2">
                            {{ $completedProjects->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
     MODAL: ADD / EDIT PROJECT
════════════════════════════════════════════════════════════════════════ --}}
    <div id="modal-add-project"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[95vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <div>
                    <h3 class="font-bold text-stone-900 dark:text-white" id="modal-project-title">Proyek Baru</h3>
                    <p class="text-xs text-stone-400 mt-0.5">Freelance, konten, microstock, atau personal</p>
                </div>
                <button onclick="closeModal('modal-add-project')"
                    class="text-stone-400 hover:text-stone-700 flex-shrink-0"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <input type="hidden" id="project-edit-id">
            <div class="p-6 space-y-4">
                <div>
                    <label class="fi-label">Judul Proyek <span class="text-rose-400">*</span></label>
                    <input type="text" id="cp-title" class="fi" placeholder="Nama proyek">
                </div>

                {{-- Tipe Proyek — PILIHAN UTAMA --}}
                <div>
                    <label class="fi-label">Tipe Alur Proyek <span class="text-rose-400">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="selectProjectMode('sequential', this)"
                            class="mode-btn p-3.5 rounded-xl border-2 border-stone-200 dark:border-stone-700 text-left transition-all hover:border-blue-400"
                            id="mode-sequential">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="text-lg">⛓</span>
                                <span class="text-sm font-bold text-stone-800 dark:text-white">Berjenjang</span>
                                <span
                                    class="text-[10px] bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-1.5 py-0.5 rounded-full">Tipe
                                    A</span>
                            </div>
                            <p class="text-[11px] text-stone-400 leading-relaxed">Sub-tugas berurutan. Progres total naik
                                otomatis. Cocok untuk video, animasi, desain.</p>
                        </button>
                        <button type="button" onclick="selectProjectMode('simple', this)"
                            class="mode-btn p-3.5 rounded-xl border-2 border-stone-200 dark:border-stone-700 text-left transition-all hover:border-purple-400"
                            id="mode-simple">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="text-lg">🎯</span>
                                <span class="text-sm font-bold text-stone-800 dark:text-white">Mandiri</span>
                                <span
                                    class="text-[10px] bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 px-1.5 py-0.5 rounded-full">Tipe
                                    B</span>
                            </div>
                            <p class="text-[11px] text-stone-400 leading-relaxed">Tugas tunggal, bisa dijadwal ulang jika
                                terlewat. Cocok untuk posting konten harian.</p>
                        </button>
                    </div>
                    <input type="hidden" id="cp-mode" value="simple">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fi-label">Tipe Konten</label>
                        <select id="cp-type" class="fi">
                            <option value="video_editing">🎬 Video Editing</option>
                            <option value="animation">✨ Animasi</option>
                            <option value="motion_graphics">🎭 Motion Graphics</option>
                            <option value="graphic_design">🎨 Graphic Design</option>
                            <option value="social_media">📱 Social Media</option>
                            <option value="photography">📷 Photography</option>
                            <option value="illustration">🖌️ Ilustrasi</option>
                            <option value="audio_production">🎵 Audio</option>
                            <option value="branding">💼 Branding</option>
                            <option value="other">📦 Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="fi-label">Prioritas</label>
                        <select id="cp-priority" class="fi">
                            <option value="high">🔴 Tinggi</option>
                            <option value="medium" selected>🟡 Sedang</option>
                            <option value="low">🟢 Rendah</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="fi-label">Stage Awal</label>
                        <select id="cp-stage" class="fi">
                            <option value="script">Script & Concept</option>
                            <option value="production">Production</option>
                            <option value="revision">Revision / QC</option>
                        </select>
                    </div>
                    <div>
                        <label class="fi-label">Deadline</label>
                        <input type="date" id="cp-deadline" class="fi"
                            value="{{ now()->addWeek()->format('Y-m-d') }}">
                    </div>
                </div>
                <div>
                    <label class="fi-label">Klien / Untuk</label>
                    <input type="text" id="cp-client" class="fi"
                        placeholder="Klien A / Instagram / Shutterstock">
                </div>
                <div>
                    <label class="fi-label"><i class="fa-brands fa-google-drive text-blue-500 mr-1"></i>Link Drive /
                        Referensi</label>
                    <input type="url" id="cp-drive" class="fi" placeholder="https://drive.google.com/...">
                </div>
                <div>
                    <label class="fi-label">Deskripsi / Brief</label>
                    <textarea id="cp-desc" rows="2" class="fi resize-none" placeholder="Brief proyek, requirement klien..."></textarea>
                </div>

                <div id="simple-info"
                    class="hidden p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl text-[11px] text-purple-700 dark:text-purple-400">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    <strong>Proyek Mandiri:</strong> Tidak ada sub-urutan. Jika terlewati, bisa dijadwal ulang ke minggu
                    depan tanpa merusak alur proyek lain.
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-project')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="saveProject(this)"
                    class="flex-1 py-2.5 bg-gradient-to-r from-orange-500 to-pink-500 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                    <i class="fa-solid fa-film"></i> <span id="cp-submit-label">Buat Proyek</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
     MODAL: PROJECT DETAIL
════════════════════════════════════════════════════════════════════════ --}}
    <div id="modal-project-detail"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-2xl shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[95vh] overflow-y-auto">
            <div class="flex items-center justify-between p-5 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white" id="pd-title">Detail Proyek</h3>
                <button onclick="closeModal('modal-project-detail')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div id="pd-body" class="p-5">
                <div class="text-center py-8"><i class="fa-solid fa-spinner fa-spin text-2xl text-stone-400"></i></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const projectsData = @json($projects);

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
            t.style.cssText = 'animation:fadeUp .28s ease-out both';
            t.innerHTML = `<i class="fa-solid ${ok?'fa-check-circle':'fa-circle-xmark'}"></i>${msg}`;
            document.body.appendChild(t);
            setTimeout(() => {
                t.style.transition = 'opacity .3s';
                t.style.opacity = '0';
                setTimeout(() => t.remove(), 300);
            }, 3000);
        }

        function setLoading(btn, on) {
            if (on) {
                btn.disabled = true;
                btn.style.opacity = '.7';
            } else {
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        }

        async function api(method, url, data = {}) {
            const isForm = data instanceof FormData;
            const opts = {
                method: method.toUpperCase(),
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                }
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
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw json;
            return json;
        }

        // ── Project mode selector ────────────────────────────────────────────────
        function selectProjectMode(mode, btn) {
            document.querySelectorAll('.mode-btn').forEach(b => {
                b.classList.remove('border-blue-400', 'border-purple-400', 'bg-blue-50', 'dark:bg-blue-900/20',
                    'bg-purple-50', 'dark:bg-purple-900/20');
                b.classList.add('border-stone-200', 'dark:border-stone-700');
            });
            const color = mode === 'sequential' ? 'blue' : 'purple';
            btn.classList.remove('border-stone-200', 'dark:border-stone-700');
            btn.classList.add(`border-${color}-400`, `bg-${color}-50`, `dark:bg-${color}-900/20`);
            document.getElementById('cp-mode').value = mode;
            document.getElementById('seq-info').classList.toggle('hidden', mode !== 'sequential');
            document.getElementById('simple-info').classList.toggle('hidden', mode !== 'simple');
        }

        // ── Save project ─────────────────────────────────────────────────────────
        async function saveProject(btn) {
            const title = document.getElementById('cp-title').value.trim();
            const mode = document.getElementById('cp-mode').value;
            if (!title) {
                toast('Judul proyek wajib diisi!', false);
                return;
            }
            if (!mode) {
                toast('Pilih tipe alur proyek!', false);
                return;
            }

            const editId = document.getElementById('project-edit-id').value;
            const payload = {
                title,
                project_mode: mode,
                project_type: document.getElementById('cp-type').value,
                priority: document.getElementById('cp-priority').value,
                workflow_stage: document.getElementById('cp-stage').value,
                due_date: document.getElementById('cp-deadline').value || null,
                client: document.getElementById('cp-client').value.trim() || null,
                drive_link: document.getElementById('cp-drive').value.trim() || null,
                description: document.getElementById('cp-desc').value.trim() || null,
            };

            setLoading(btn, true);
            try {
                const url = editId ? `/dashboard/creative/${editId}` : '{{ route('dashboard.creative.store') }}';
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
                toast('Proyek berhasil ' + (editId ? 'diperbarui!' : 'dibuat!') + (mode === 'sequential' ?
                    ' Tahap otomatis dibuat ⛓' : ''));
                closeModal('modal-add-project');
                setTimeout(() => location.reload(), 800);
            } catch (e) {
                const msg = e?.errors ? Object.values(e.errors).flat().join(', ') : (e?.message ||
                    'Terjadi kesalahan.');
                toast(msg, false);
            } finally {
                setLoading(btn, false);
            }
        }

        // ── Edit project ─────────────────────────────────────────────────────────
        function editProject(id) {
            const p = projectsData.find(x => x.id === id);
            if (!p) return;
            document.getElementById('project-edit-id').value = p.id;
            document.getElementById('cp-title').value = p.title || '';
            document.getElementById('cp-type').value = p.type || 'other';
            document.getElementById('cp-priority').value = p.priority || 'medium';
            document.getElementById('cp-stage').value = p.stage || 'script';
            document.getElementById('cp-deadline').value = p.due_date_raw || '';
            document.getElementById('cp-client').value = p.client || '';
            document.getElementById('cp-drive').value = p.drive_link || '';
            document.getElementById('cp-desc').value = p.description || '';
            // Set project mode
            const modeBtn = document.getElementById('mode-' + (p.project_mode || 'simple'));
            if (modeBtn) selectProjectMode(p.project_mode || 'simple', modeBtn);
            document.getElementById('modal-project-title').textContent = 'Edit Proyek';
            document.getElementById('cp-submit-label').textContent = 'Simpan Perubahan';
            openModal('modal-add-project');
        }

        // ── Delete project ───────────────────────────────────────────────────────
        async function deleteProject(id, title) {
            showDeleteConfirm({
                title: 'Hapus Proyek?',
                message: `Hapus "${title || 'Proyek ini'}"?`,
                warning: 'Semua sub-tugas akan ikut terhapus.',
                onConfirm: async () => {
                    try {
                        const fd = new FormData();
                        fd.append('_method', 'DELETE');
                        const res = await fetch(`/dashboard/creative/${id}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF
                            },
                            body: fd
                        }).then(r => r.json());
                        if (res.success) {
                            toast('Proyek dihapus.');
                            setTimeout(() => location.reload(), 700);
                        } else {
                            toast(res.message || 'Gagal.', false);
                        }
                    } catch (e) {
                        toast('Gagal menghapus.', false);
                    }
                }
            });
        }

        // ── Mark done ────────────────────────────────────────────────────────────
        async function markProjectDone(id, btn) {
            if (!confirm('Tandai proyek ini selesai? Semua sub-tahap akan ditandai done.')) return;
            setLoading(btn, true);
            try {
                const res = await api('POST', `/dashboard/creative/${id}/done`);
                toast(res.message || 'Proyek selesai! 🎉');
                setTimeout(() => location.reload(), 700);
            } catch (e) {
                toast(e?.message || 'Gagal.', false);
            } finally {
                setLoading(btn, false);
            }
        }

        // ── Reschedule (Tipe B) ──────────────────────────────────────────────────
        async function rescheduleProject(id, btn) {
            if (!confirm('Jadwalkan ulang tugas ini ke minggu depan?')) return;
            setLoading(btn, true);
            try {
                const res = await api('POST', `/dashboard/creative/${id}/reschedule`);
                toast(res.message || 'Dijadwalkan ulang!');
                setTimeout(() => location.reload(), 700);
            } catch (e) {
                toast(e?.message || 'Gagal.', false);
            } finally {
                setLoading(btn, false);
            }
        }

        // ── Show project detail (modal) ──────────────────────────────────────────
        async function showProjectDetail(id) {
            document.getElementById('pd-body').innerHTML =
                '<div class="text-center py-8"><i class="fa-solid fa-spinner fa-spin text-2xl text-stone-400"></i></div>';
            openModal('modal-project-detail');
            try {
                const res = await api('GET', `/dashboard/creative/task/${id}`);
                const t = res.task;
                const isSeq = t.project_mode === 'sequential';

                document.getElementById('pd-title').textContent = t.title;

                let subtasksHtml = '';
                if (isSeq && t.subtasks.length > 0) {
                    subtasksHtml = `
            <div class="mb-5">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-bold text-stone-800 dark:text-white text-sm">⛓ Alur Tahap (${t.completed_subtasks}/${t.total_subtasks} selesai)</h4>
                </div>
                <div class="space-y-2">
                    ${t.subtasks.map(s => {
                        const isCompleted = s.status === 'completed';
                        const isProgress  = s.status === 'in_progress';
                        const iconCls     = isCompleted ? 'bg-emerald-500 text-white' : isProgress ? 'bg-orange-500 text-white animate-pulse' : 'bg-stone-200 dark:bg-stone-700 text-stone-400';
                        return `
                                                <div class="flex items-center gap-3 p-3 ${isCompleted ? 'bg-emerald-50 dark:bg-emerald-900/20' : isProgress ? 'bg-orange-50 dark:bg-orange-900/20' : 'bg-stone-50 dark:bg-stone-800'} rounded-xl" id="sub-row-${s.id}">
                                                    <div class="w-8 h-8 rounded-full ${iconCls} flex items-center justify-center text-sm flex-shrink-0">
                                                        ${isCompleted ? '✓' : isProgress ? '▶' : (t.subtasks.indexOf(s)+1)}
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-stone-800 dark:text-white ${isCompleted ? 'line-through opacity-70' : ''}">${s.stage_label || s.title}</p>
                                                        <p class="text-[10px] text-stone-400">${s.status === 'completed' ? '✅ Selesai' : s.status === 'in_progress' ? '🔄 Sedang dikerjakan' : '⏳ Menunggu'}</p>
                                                    </div>
                                                    ${!isCompleted ? `
                            <div class="flex gap-1">
                                ${s.status !== 'in_progress' ? `<button onclick="updateSubtask(${t.id},${s.id},'in_progress')"
                                                            class="px-2 py-1 bg-orange-100 dark:bg-orange-900/20 text-orange-600 text-[10px] font-medium rounded-lg hover:bg-orange-200 transition-colors">Mulai</button>` : ''}
                                <button onclick="updateSubtask(${t.id},${s.id},'completed')"
                                    class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/20 text-emerald-600 text-[10px] font-medium rounded-lg hover:bg-emerald-200 transition-colors">Selesai</button>
                            </div>` : ''}
                                                </div>`;
                    }).join('')}
                </div>
            </div>`;
                }

                document.getElementById('pd-body').innerHTML = `
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                    <p class="text-[10px] text-stone-400 mb-0.5">Progress</p>
                    <p class="font-bold text-stone-800 dark:text-white text-lg">${t.progress}%</p>
                    <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-1.5 mt-1"><div class="h-1.5 rounded-full bg-orange-500" style="width:${t.progress}%"></div></div>
                </div>
                <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                    <p class="text-[10px] text-stone-400 mb-0.5">Deadline</p>
                    <p class="font-bold text-stone-800 dark:text-white">${t.due_date_fmt || 'Flexible'}</p>
                </div>
                <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                    <p class="text-[10px] text-stone-400 mb-0.5">Tipe Alur</p>
                    <p class="font-bold text-stone-800 dark:text-white">${isSeq ? '⛓ Berjenjang' : '🎯 Mandiri'}</p>
                </div>
                <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                    <p class="text-[10px] text-stone-400 mb-0.5">Klien</p>
                    <p class="font-bold text-stone-800 dark:text-white truncate">${t.client || '-'}</p>
                </div>
            </div>
            ${t.description ? `<p class="text-sm text-stone-600 dark:text-stone-400 mb-4 leading-relaxed">${t.description}</p>` : ''}
            ${subtasksHtml}
            ${t.drive_link ? `<a href="${t.drive_link}" target="_blank" class="flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-xl text-sm hover:bg-blue-100 transition-colors mb-4">
                                        <i class="fa-brands fa-google-drive"></i> Buka Drive / Referensi
                                    </a>` : ''}
            <div class="flex gap-2 pt-3 border-t border-stone-100 dark:border-stone-800">
                ${t.status !== 'done' ? `<button onclick="markProjectDone(${t.id}, this)"
                                            class="flex-1 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-sm font-medium transition-colors">
                                            <i class="fa-solid fa-check mr-1.5"></i>Tandai Selesai
                                        </button>` : ''}
                <button onclick="editProject(${t.id}); closeModal('modal-project-detail')"
                    class="flex-1 py-2 bg-stone-100 dark:bg-stone-700 hover:bg-stone-200 dark:hover:bg-stone-600 text-stone-700 dark:text-stone-300 rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-pen mr-1.5"></i>Edit
                </button>
            </div>`;
            } catch (e) {
                document.getElementById('pd-body').innerHTML =
                    '<p class="text-center text-rose-500 py-6">Gagal memuat detail proyek.</p>';
            }
        }

        // ── Update subtask (dari detail modal) ───────────────────────────────────
        async function updateSubtask(taskId, subtaskId, status) {
            try {
                const res = await api('PUT', `/dashboard/creative/task/${taskId}/subtask/${subtaskId}`, {
                    status,
                    progress: status === 'completed' ? 100 : 50
                });
                toast(res.message || 'Sub-tahap diperbarui!');

                // Refresh row di modal
                const row = document.getElementById('sub-row-' + subtaskId);
                if (row) {
                    const dot = row.querySelector('.w-8');
                    const title = row.querySelector('p.font-medium');
                    const status2 = row.querySelector('p.text-\\[10px\\]');
                    if (status === 'completed') {
                        if (dot) {
                            dot.className = dot.className.replace(/bg-\w+-\d+/g, '').replace(/animate-pulse/, '');
                            dot.classList.add('bg-emerald-500', 'text-white');
                            dot.textContent = '✓';
                        }
                        if (title) title.classList.add('line-through', 'opacity-70');
                        if (status2) status2.textContent = '✅ Selesai';
                        row.className = row.className.replace(/bg-orange-50.*|bg-stone-50.*/,
                            'bg-emerald-50 dark:bg-emerald-900/20').trim();
                        row.querySelector('.flex.gap-1')?.remove();
                    } else if (status === 'in_progress') {
                        if (dot) {
                            dot.className = dot.className.replace(/bg-\w+-\d+/g, '').replace(/animate-pulse/, '');
                            dot.classList.add('bg-orange-500', 'text-white', 'animate-pulse');
                            dot.textContent = '▶';
                        }
                        if (status2) status2.textContent = '🔄 Sedang dikerjakan';
                        row.querySelector('[onclick*="Mulai"]')?.remove();
                    }
                }

                // Update progress bar di parent
                if (res.task_progress !== undefined) {
                    setTimeout(() => location.reload(), 1200);
                }
            } catch (e) {
                toast(e?.message || 'Gagal update.', false);
            }
        }

        // ── Filter History ───────────────────────────────────────────────────────
        async function filterHistory(period) {
            try {
                const res = await api('GET', `/dashboard/creative/history?period=${period}`);
                document.getElementById('history-tbody').innerHTML = res.html;
            } catch (e) {
                toast(e?.message || 'Gagal memfilter history.', false);
            }
        }

        // ── Init ─────────────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            // Default: pilih mode simple
            const simpleBtn = document.getElementById('mode-simple');
            if (simpleBtn) selectProjectMode('simple', simpleBtn);

            // Reset modal saat close
            document.getElementById('modal-add-project')?.addEventListener('click', e => {
                if (e.target === e.currentTarget) closeModal('modal-add-project');
            });
        });
    </script>
@endpush
