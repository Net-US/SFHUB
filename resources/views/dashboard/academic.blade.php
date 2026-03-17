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

        .task-item {
            transition: background .15s
        }

        .task-item:hover {
            background: #fafaf9
        }

        .dark .task-item:hover {
            background: #292524
        }
    </style>
@endpush

@section('content')
    @php
        $courses = $courses ?? [];
        $tasks = $tasks ?? [];
        $milestones = $milestones ?? [];
        $tasksByCourse = collect($tasks)->groupBy('course_id');
        $thesisProgress = $thesisProgress ?? 0;
    @endphp

    <div class="fade-up space-y-5">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Academic Hub</h2>
                <p class="text-stone-400 text-xs">Kelola mata kuliah, tugas, dan progres skripsi</p>
            </div>
            <div class="flex gap-2">
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

        {{-- Tabs --}}
        <div class="flex gap-1 bg-stone-100 dark:bg-stone-800 p-1 rounded-xl w-fit">
            @foreach ([['matkul', 'fa-graduation-cap', 'Mata Kuliah'], ['tugas', 'fa-clipboard-list', 'Tugas & Deadline'], ['skripsi', 'fa-book-open', 'Skripsi']] as [$id, $ic, $lbl])
                <button onclick="switchAcadTab('{{ $id }}')" id="acadtab-{{ $id }}"
                    class="tab-pill {{ $id === 'matkul' ? 'active' : '' }}">
                    <i class="fa-solid {{ $ic }} text-xs"></i> {{ $lbl }}
                </button>
            @endforeach
        </div>

        {{-- ═══════ TAB: MATA KULIAH ═══════ --}}
        <div id="acad-matkul" class="acad-pane space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($courses as $c)
                    @php $cTasks = $tasksByCourse->get($c['id'], collect()); @endphp
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span
                                            class="text-[10px] font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2 py-0.5 rounded-full">{{ $c['code'] }}</span>
                                        <span class="text-[10px] text-stone-400">{{ $c['sks'] }} SKS</span>
                                    </div>
                                    <h4 class="font-bold text-stone-800 dark:text-white">{{ $c['name'] }}</h4>
                                    <p class="text-xs text-stone-400 mt-0.5">{{ $c['lecturer'] }}</p>
                                </div>
                                <div class="flex gap-1 flex-shrink-0 ml-2">
                                    <button onclick="editCourse({{ $c['id'] }})"
                                        class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-800 hover:bg-orange-100 dark:hover:bg-orange-900/30 text-stone-400 hover:text-orange-500 flex items-center justify-center transition-colors">
                                        <i class="fa-solid fa-pen text-[10px]"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Schedule info --}}
                            <div class="flex gap-3 mb-3">
                                <div class="flex items-center gap-1.5 text-xs text-stone-500 dark:text-stone-400">
                                    <i class="fa-solid fa-calendar-day text-blue-400 text-[10px]"></i>{{ $c['day'] }}
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-stone-500 dark:text-stone-400">
                                    <i class="fa-solid fa-clock text-blue-400 text-[10px]"></i>{{ $c['time'] }}
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-stone-500 dark:text-stone-400">
                                    <i class="fa-solid fa-door-open text-blue-400 text-[10px]"></i>{{ $c['room'] }}
                                </div>
                            </div>

                            {{-- Progress --}}
                            <div class="mb-3">
                                <div class="flex justify-between text-[10px] text-stone-400 mb-1">
                                    <span>Progres Semester</span><span>{{ $c['progress'] }}%</span>
                                </div>
                                <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full bg-blue-500" style="width:{{ $c['progress'] }}%"></div>
                                </div>
                            </div>

                            {{-- Drive link & notes --}}
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-stone-400">{{ $cTasks->count() }} tugas aktif</span>
                                <div class="flex gap-2">
                                    @if ($c['drive_link'])
                                        <a href="{{ $c['drive_link'] }}" target="_blank"
                                            class="flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[11px] font-medium rounded-lg hover:bg-blue-100 transition-colors">
                                            <i class="fa-brands fa-google-drive text-[10px]"></i> Materi
                                        </a>
                                    @else
                                        <button onclick="editCourse({{ $c['id'] }})"
                                            class="flex items-center gap-1.5 px-2.5 py-1 bg-stone-100 dark:bg-stone-800 text-stone-400 text-[11px] rounded-lg hover:bg-stone-200 transition-colors">
                                            <i class="fa-solid fa-link text-[10px]"></i> Tambah Link
                                        </button>
                                    @endif
                                    <button
                                        onclick="openModal('modal-add-task'); document.getElementById('at-course').value={{ $c['id'] }}"
                                        class="flex items-center gap-1.5 px-2.5 py-1 bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 text-[11px] font-medium rounded-lg hover:bg-orange-100 transition-colors">
                                        <i class="fa-solid fa-plus text-[10px]"></i> Tugas
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
        </div>

        {{-- ═══════ TAB: TUGAS ═══════ --}}
        <div id="acad-tugas" class="acad-pane hidden space-y-4">
            {{-- Filter --}}
            <div class="flex gap-2 flex-wrap">
                @foreach (['all' => 'Semua', 'todo' => 'Belum Mulai', 'doing' => 'Dikerjakan', 'done' => 'Selesai'] as $k => $v)
                    <button onclick="filterTasks('{{ $k }}')" id="tf-{{ $k }}"
                        class="px-3 py-1.5 text-xs rounded-full font-medium transition-colors {{ $k === 'all' ? 'bg-stone-800 dark:bg-stone-700 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:bg-stone-200' }}">
                        {{ $v }}
                    </button>
                @endforeach
            </div>

            <div
                class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                <div class="divide-y divide-stone-100 dark:divide-stone-800" id="task-list">
                    @forelse($tasks as $t)
                        @php
                            $course = collect($courses)->firstWhere('id', $t['course_id']);
                            $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($t['deadline']), false);
                            $isOverdue = $daysLeft < 0 && $t['status'] !== 'done';
                        @endphp
                        <div class="task-item flex items-start gap-4 px-5 py-4 {{ $t['status'] === 'done' ? 'opacity-60' : '' }}"
                            data-status="{{ $t['status'] }}" data-priority="{{ $t['priority'] }}">
                            {{-- Checkbox --}}
                            <button onclick="toggleTaskStatus({{ $t['id'] }}, this)"
                                class="mt-0.5 w-5 h-5 rounded-full border-2 {{ $t['status'] === 'done' ? 'bg-emerald-500 border-emerald-500' : 'border-stone-300 dark:border-stone-600' }} flex items-center justify-center flex-shrink-0 hover:border-emerald-400 transition-colors">
                                @if ($t['status'] === 'done')
                                    <i class="fa-solid fa-check text-white text-[9px]"></i>
                                @endif
                            </button>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p
                                        class="text-sm font-semibold text-stone-800 dark:text-white {{ $t['status'] === 'done' ? 'line-through' : '' }}">
                                        {{ $t['title'] }}</p>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span
                                            class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold
                                {{ $t['priority'] === 'high' ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : ($t['priority'] === 'medium' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400' : 'bg-stone-100 dark:bg-stone-700 text-stone-500') }}">
                                            {{ ucfirst($t['priority']) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 mt-1 flex-wrap">
                                    @if ($course)
                                        <span class="text-[10px] text-stone-400"><i
                                                class="fa-solid fa-book text-blue-400 mr-1"></i>{{ $course['name'] }}</span>
                                    @endif
                                    <span
                                        class="text-[10px] font-medium {{ $isOverdue ? 'text-rose-600 dark:text-rose-400' : ($daysLeft <= 2 ? 'text-amber-600 dark:text-amber-400' : 'text-stone-400') }}">
                                        <i class="fa-regular fa-clock mr-1"></i>
                                        @if ($isOverdue)
                                            Terlambat {{ abs($daysLeft) }} hari
                                        @elseif($daysLeft == 0)
                                            Hari ini!
                                        @elseif($daysLeft == 1)
                                            Besok
                                        @else
                                            {{ $daysLeft }} hari lagi
                                        @endif
                                    </span>
                                    @if ($t['notes'])
                                        <span class="text-[10px] text-stone-400">· {{ $t['notes'] }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-1 flex-shrink-0">
                                @if ($t['drive_link'])
                                    <a href="{{ $t['drive_link'] }}" target="_blank"
                                        class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center hover:bg-blue-100 transition-colors"
                                        title="Buka di Drive">
                                        <i class="fa-brands fa-google-drive text-sm"></i>
                                    </a>
                                @endif
                                <button onclick="editTask({{ $t['id'] }})"
                                    class="w-8 h-8 rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-400 hover:text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/20 flex items-center justify-center transition-colors">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                <button
                                    class="w-8 h-8 rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center justify-center transition-colors">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-stone-400">
                            <i class="fa-solid fa-clipboard-list text-3xl mb-2 block opacity-30"></i>
                            <p class="text-sm">Belum ada tugas. Tambahkan tugas pertamamu!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ═══════ TAB: SKRIPSI ═══════ --}}
        <div id="acad-skripsi" class="acad-pane hidden space-y-5">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- Milestones --}}
                <div
                    class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-flag-checkered text-orange-500"></i> Milestone Skripsi
                    </h3>
                    <div class="relative pl-8 border-l-2 border-stone-200 dark:border-stone-700 space-y-8">
                        @foreach ($milestones as $m)
                            <div class="relative">
                                <div
                                    class="absolute -left-[41px] w-6 h-6 rounded-full border-4 border-white dark:border-stone-900 shadow-sm flex items-center justify-center
                        {{ $m['done'] ? 'bg-emerald-500' : (isset($m['active']) ? 'bg-orange-500 animate-pulse' : 'bg-stone-300 dark:bg-stone-600') }}">
                                    @if ($m['done'])
                                        <i class="fa-solid fa-check text-white text-[8px]"></i>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-semibold text-stone-800 dark:text-white text-sm">
                                            {{ $m['label'] }}</h4>
                                        <p class="text-xs text-stone-400 mt-0.5">Target: {{ $m['date'] }}</p>
                                    </div>
                                    <span
                                        class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $m['done'] ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : (isset($m['active']) ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 animate-pulse' : 'bg-stone-100 dark:bg-stone-800 text-stone-400') }}">
                                        {{ $m['done'] ? '✅ Selesai' : (isset($m['active']) ? '🔄 Proses' : '⏳ Belum') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Skripsi progress sidebar --}}
                <div class="space-y-4">
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-5 text-white shadow-lg">
                        <p class="text-blue-100 text-xs mb-1">Progres Keseluruhan</p>
                        <h3 class="text-3xl font-bold mb-2">{{ $thesisProgress }}%</h3>
                        <div class="w-full bg-white/20 rounded-full h-2 mb-3">
                            <div class="bg-white h-2 rounded-full" style="width:{{ $thesisProgress }}%"></div>
                        </div>
                        <p class="text-blue-200 text-xs">Estimasi selesai: Juni 2024</p>
                    </div>

                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                        <h4 class="font-bold text-stone-800 dark:text-white text-sm mb-3">Quick Actions</h4>
                        <div class="space-y-2">
                            <button
                                onclick="openModal('modal-add-task'); document.getElementById('at-course').value=3; document.getElementById('at-type').value='skripsi'"
                                class="w-full flex items-center gap-3 px-3 py-2.5 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-xl text-xs font-medium transition-colors">
                                <i class="fa-solid fa-plus w-4 text-center"></i>Tambah Target Skripsi
                            </button>
                            <button
                                class="w-full flex items-center gap-3 px-3 py-2.5 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 text-purple-700 dark:text-purple-400 rounded-xl text-xs font-medium transition-colors">
                                <i class="fa-brands fa-google-drive w-4 text-center"></i>Buka Folder Skripsi Drive
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- MODAL: ADD COURSE --}}
    <div id="modal-add-course"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white" id="modal-course-title">Tambah Mata Kuliah</h3>
                <button onclick="closeModal('modal-add-course')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Kode MK
                            *</label>
                        <input type="text" id="mc-code"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="IF401">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">SKS
                            *</label>
                        <input type="number" id="mc-sks" min="1" max="6"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="3">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Nama Mata
                        Kuliah *</label>
                    <input type="text" id="mc-name"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Nama mata kuliah">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Dosen</label>
                    <input type="text" id="mc-lecturer"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Nama dosen pengampu">
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Hari</label>
                        <select id="mc-day"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option>Senin</option>
                            <option>Selasa</option>
                            <option>Rabu</option>
                            <option>Kamis</option>
                            <option>Jumat</option>
                            <option>Sabtu</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Jam
                            Mulai</label>
                        <input type="time" id="mc-start-time"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                            value="08:00">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Jam
                            Selesai</label>
                        <input type="time" id="mc-end-time"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                            value="10:00">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Ruang</label>
                        <input type="text" id="mc-room"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="R.202">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">
                        <i class="fa-brands fa-google-drive text-blue-500 mr-1"></i>Link Google Drive Materi
                    </label>
                    <input type="url" id="mc-drive"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="https://drive.google.com/drive/folders/...">
                    <p class="text-[10px] text-stone-400 mt-1">Link folder Google Drive berisi slide, materi, dan referensi
                        kuliah</p>
                </div>
                <div>
                    <label
                        class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Catatan</label>
                    <textarea id="mc-notes" rows="2"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white resize-none"
                        placeholder="Catatan penting..."></textarea>
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-course')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="saveCourse()"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-graduation-cap mr-1.5"></i>Simpan
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL: ADD TASK --}}
    <div id="modal-add-task"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white" id="modal-task-title">Tambah Tugas</h3>
                <button onclick="closeModal('modal-add-task')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Judul Tugas
                        *</label>
                    <input type="text" id="at-title"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Nama tugas">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Mata
                            Kuliah</label>
                        <select id="at-course"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                            @foreach ($courses as $c)
                                <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Tipe</label>
                        <select id="at-type"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white">
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
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Deadline
                            *</label>
                        <input type="date" id="at-deadline"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                            value="{{ now()->addWeek()->format('Y-m-d') }}">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Prioritas</label>
                        <select id="at-priority"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="high">🔴 Tinggi</option>
                            <option value="medium" selected>🟡 Sedang</option>
                            <option value="low">🟢 Rendah</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">
                        <i class="fa-brands fa-google-drive text-blue-500 mr-1"></i>Link Tugas / Materi (Google Drive)
                    </label>
                    <input type="url" id="at-drive"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="https://drive.google.com/...">
                    <p class="text-[10px] text-stone-400 mt-1">Link file tugas, soal, atau materi referensi di Google Drive
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Catatan /
                        Instruksi</label>
                    <textarea id="at-notes" rows="2"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-stone-800 dark:text-white resize-none"
                        placeholder="Catatan instruksi, platform submit, dll..."></textarea>
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-task')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="saveTask()"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-clipboard-list mr-1.5"></i>Simpan Tugas
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

        function switchAcadTab(tab) {
            document.querySelectorAll('.acad-pane').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('[id^="acadtab-"]').forEach(b => b.classList.remove('active'));
            document.getElementById('acad-' + tab)?.classList.remove('hidden');
            document.getElementById('acadtab-' + tab)?.classList.add('active');
        }

        function filterTasks(status) {
            ['all', 'todo', 'doing', 'done'].forEach(s => {
                const btn = document.getElementById('tf-' + s);
                btn.className = s === status ?
                    'px-3 py-1.5 text-xs rounded-full font-medium bg-stone-800 dark:bg-stone-700 text-white transition-colors' :
                    'px-3 py-1.5 text-xs rounded-full font-medium bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:bg-stone-200 transition-colors';
            });
            document.querySelectorAll('#task-list .task-item').forEach(el => {
                el.style.display = (status === 'all' || el.dataset.status === status) ? '' : 'none';
            });
        }

        function toggleTaskStatus(id, btn) {
            const isChecked = btn.classList.contains('bg-emerald-500');
            if (isChecked) {
                btn.classList.remove('bg-emerald-500', 'border-emerald-500');
                btn.classList.add('border-stone-300', 'dark:border-stone-600');
                btn.innerHTML = '';
                btn.closest('.task-item').classList.remove('opacity-60');
                btn.closest('.task-item').dataset.status = 'todo';
            } else {
                btn.classList.add('bg-emerald-500', 'border-emerald-500');
                btn.classList.remove('border-stone-300', 'dark:border-stone-600');
                btn.innerHTML = '<i class="fa-solid fa-check text-white text-[9px]"></i>';
                btn.closest('.task-item').classList.add('opacity-60');
                btn.closest('.task-item').dataset.status = 'done';
            }
        }

        function editCourse(id) {
            openModal('modal-add-course');
            document.getElementById('modal-course-title').textContent = 'Edit Mata Kuliah';
        }

        function editTask(id) {
            openModal('modal-add-task');
            document.getElementById('modal-task-title').textContent = 'Edit Tugas';
        }

        const _csrf = document.querySelector('meta[name=csrf-token]')?.content || '';

        function postForm(url, data) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': _csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            }).then(r => r.json());
        }

        function saveCourse() {
            const name = document.getElementById('mc-name').value.trim();
            if (!name) {
                alert('Isi nama mata kuliah dulu');
                return;
            }
            const data = {
                code: document.getElementById('mc-code').value,
                name,
                sks: document.getElementById('mc-sks').value,
                lecturer: document.getElementById('mc-lecturer').value,
                day_of_week: document.getElementById('mc-day').value,
                start_time: document.getElementById('mc-start-time').value,
                end_time: document.getElementById('mc-end-time').value,
                room: document.getElementById('mc-room').value,
                drive_link: document.getElementById('mc-drive').value,
                notes: document.getElementById('mc-notes').value,
            };
            postForm('{{ route('academic.courses.store') }}', data)
                .then(r => {
                    closeModal('modal-add-course');
                    if (r.success || r.message) {
                        toast(r.message || 'Disimpan!');
                        setTimeout(() => location.reload(), 800);
                    } else toast(r.message || 'Error', false);
                })
                .catch(() => toast('Gagal menyimpan', false));
        }

        function saveTask() {
            const title = document.getElementById('at-title').value.trim();
            if (!title) {
                alert('Isi judul tugas dulu');
                return;
            }
            const data = {
                title,
                linked_subject_id: document.getElementById('at-course').value,
                task_type: document.getElementById('at-type').value,
                due_date: document.getElementById('at-deadline').value,
                priority: document.getElementById('at-priority').value,
                drive_link: document.getElementById('at-drive').value,
                notes: document.getElementById('at-notes').value,
            };
            postForm('{{ route('academic.tasks.store') }}', data)
                .then(r => {
                    closeModal('modal-add-task');
                    if (r.success || r.message) {
                        toast(r.message || 'Disimpan!');
                        setTimeout(() => location.reload(), 800);
                    } else toast(r.message || 'Error', false);
                })
                .catch(() => toast('Gagal menyimpan', false));
        }

        function toggleTaskStatus(id, btn) {
            const isChecked = btn.classList.contains('bg-emerald-500');
            postForm('{{ url('/academic/tasks') }}/' + id + '/status', {})
                .then(() => {
                    if (!isChecked) {
                        btn.classList.add('bg-emerald-500', 'border-emerald-500');
                        btn.classList.remove('border-stone-300');
                        btn.innerHTML = '<i class="fa-solid fa-check text-white text-[9px]"></i>';
                        btn.closest('.task-item').classList.add('opacity-60');
                        btn.closest('.task-item').dataset.status = 'done';
                    } else {
                        btn.classList.remove('bg-emerald-500', 'border-emerald-500');
                        btn.classList.add('border-stone-300');
                        btn.innerHTML = '';
                        btn.closest('.task-item').classList.remove('opacity-60');
                        btn.closest('.task-item').dataset.status = 'todo';
                    }
                });
        }

        function deleteTask(id) {
            if (!confirm('Hapus tugas ini?')) return;
            fetch('{{ url('/academic/tasks') }}/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': _csrf,
                    'Accept': 'application/json'
                }
            }).then(() => location.reload());
        }

        function deleteCourse(id) {
            if (!confirm('Hapus mata kuliah ini?')) return;
            fetch('{{ url('/academic/courses') }}/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': _csrf,
                    'Accept': 'application/json'
                }
            }).then(() => location.reload());
        }

        function toast(msg, ok = true) {
            const t = document.createElement('div');
            t.className =
                `fixed bottom-6 right-6 z-[9999] flex items-center gap-2 px-4 py-3 ${ok ? 'bg-emerald-500' : 'bg-rose-500'} text-white text-sm font-medium rounded-2xl shadow-xl`;
            t.innerHTML = `<i class="fa-solid ${ok ? 'fa-check-circle' : 'fa-circle-xmark'}"></i> ${msg}`;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 2500);
        }
    </script>
@endpush
