{{-- resources/views/dashboard/creative.blade.php --}}
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

        .kanban-col {
            min-height: 350px
        }

        .card-hover {
            transition: all .18s
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, .1)
        }

        .progress-ring {
            transition: stroke-dashoffset .5s ease
        }

        .tag {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 600
        }
    </style>
@endpush

@section('content')
    @php
        $projects = $projects ?? [];
        $stages = [
            [
                'id' => 'script',
                'label' => 'Script & Concept',
                'color' => 'border-t-4 border-slate-400',
                'bg' => 'bg-slate-50 dark:bg-slate-900/20',
                'badge_bg' => 'bg-slate-200 dark:bg-slate-700',
                'tc' => 'text-slate-700 dark:text-slate-300',
            ],
            [
                'id' => 'production',
                'label' => 'Production',
                'color' => 'border-t-4 border-orange-400',
                'bg' => 'bg-orange-50 dark:bg-orange-900/10',
                'badge_bg' => 'bg-orange-200 dark:bg-orange-900',
                'tc' => 'text-orange-700 dark:text-orange-400',
            ],
            [
                'id' => 'revision',
                'label' => 'Revision / QC',
                'color' => 'border-t-4 border-amber-400',
                'bg' => 'bg-amber-50 dark:bg-amber-900/10',
                'badge_bg' => 'bg-amber-200 dark:bg-amber-900',
                'tc' => 'text-amber-700 dark:text-amber-400',
            ],
            [
                'id' => 'done',
                'label' => 'Ready to Deliver',
                'color' => 'border-t-4 border-emerald-400',
                'bg' => 'bg-emerald-50 dark:bg-emerald-900/10',
                'badge_bg' => 'bg-emerald-200 dark:bg-emerald-900',
                'tc' => 'text-emerald-700 dark:text-emerald-400',
            ],
        ];

    @endphp

    <div class="fade-up space-y-5">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Creative Studio</h2>
                <p class="text-stone-400 text-xs">Kanban board proyek freelance, konten, & microstock</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button onclick="openModal('modal-add-project')"
                    class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-pink-500 text-white rounded-xl text-sm font-medium hover:opacity-90 transition-opacity shadow-lg shadow-orange-500/20">
                    <i class="fa-solid fa-plus"></i> Proyek Baru
                </button>
            </div>
        </div>

        {{-- Summary stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $statItems = [
                    [
                        'label' => 'Total Proyek',
                        'val' => count($projects),
                        'icon' => 'fa-film',
                        'color' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-600',
                    ],
                    [
                        'label' => 'Sedang Dikerjakan',
                        'val' => collect($projects)->where('stage', 'production')->count(),
                        'icon' => 'fa-video',
                        'color' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600',
                    ],
                    [
                        'label' => 'Review',
                        'val' => collect($projects)->where('stage', 'revision')->count(),
                        'icon' => 'fa-magnifying-glass',
                        'color' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600',
                    ],
                    [
                        'label' => 'Selesai',
                        'val' => collect($projects)->where('stage', 'done')->count(),
                        'icon' => 'fa-check-circle',
                        'color' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600',
                    ],
                ];
            @endphp
            @foreach ($statItems as $s)
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl {{ $s['color'] }} flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid {{ $s['icon'] }} text-lg"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-stone-800 dark:text-white">{{ $s['val'] }}</p>
                        <p class="text-xs text-stone-400">{{ $s['label'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- KANBAN BOARD --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($stages as $stage)
                @php $stageProjects = collect($projects)->where('stage', $stage['id']); @endphp
                <div class="{{ $stage['bg'] }} {{ $stage['color'] }} rounded-2xl p-4 kanban-col">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-bold {{ $stage['tc'] }} text-sm">{{ $stage['label'] }}</span>
                        <span
                            class="text-xs px-2 py-0.5 {{ $stage['badge_bg'] }} {{ $stage['tc'] }} rounded-full font-bold">{{ $stageProjects->count() }}</span>
                    </div>
                    <div class="space-y-3">
                        @forelse($stageProjects as $p)
                            <div class="bg-white dark:bg-stone-800 rounded-xl p-4 shadow-sm {{ $p['color'] }} card-hover cursor-pointer"
                                onclick="openModal('modal-project-detail'); setProjectDetail({{ $p['id'] }})">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-stone-800 dark:text-white text-sm leading-snug flex-1 pr-2">
                                        {{ $p['title'] }}</h4>
                                    <span
                                        class="text-[10px] px-1.5 py-0.5 rounded-full flex-shrink-0
                        {{ $p['priority'] === 'high' ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : ($p['priority'] === 'medium' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400' : 'bg-stone-100 dark:bg-stone-700 text-stone-500') }}
                        font-semibold">
                                        {{ ucfirst($p['priority']) }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-1 mb-3">
                                    <span
                                        class="tag bg-stone-100 dark:bg-stone-700 text-stone-600 dark:text-stone-300">{{ $p['type'] }}</span>
                                    @foreach ($p['tags'] as $tag)
                                        <span
                                            class="tag bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">{{ $tag }}</span>
                                    @endforeach
                                </div>
                                {{-- Progress bar --}}
                                <div class="mb-2">
                                    <div class="flex justify-between text-[10px] text-stone-400 mb-1">
                                        <span>Progress</span><span>{{ $p['progress'] }}%</span>
                                    </div>
                                    <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full bg-orange-500" style="width:{{ $p['progress'] }}%">
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-[10px] text-stone-400">
                                    <span><i class="fa-regular fa-clock mr-1"></i>{{ $p['deadline'] }}</span>
                                    <div class="flex gap-1" onclick="event.stopPropagation()">
                                        <button
                                            class="w-6 h-6 rounded-lg bg-stone-100 dark:bg-stone-700 hover:bg-orange-100 dark:hover:bg-orange-900/30 flex items-center justify-center text-stone-400 hover:text-orange-500 transition-colors"
                                            onclick="editProject({{ $p['id'] }})"><i
                                                class="fa-solid fa-pen text-[9px]"></i></button>
                                        <button
                                            class="w-6 h-6 rounded-lg bg-stone-100 dark:bg-stone-700 hover:bg-rose-100 dark:hover:bg-rose-900/30 flex items-center justify-center text-stone-400 hover:text-rose-500 transition-colors"><i
                                                class="fa-solid fa-trash text-[9px]"></i></button>
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

    </div>

    {{-- MODAL: ADD PROJECT --}}
    <div id="modal-add-project"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white">Proyek Kreatif Baru</h3>
                <button onclick="closeModal('modal-add-project')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Judul Proyek
                        *</label>
                    <input type="text" id="cp-title"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Nama proyek kreatif">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Tipe</label>
                        <select id="cp-type"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option>Freelance</option>
                            <option>Shutterstock</option>
                            <option>YouTube</option>
                            <option>Instagram</option>
                            <option>Personal</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Prioritas</label>
                        <select id="cp-priority"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="high">🔴 Tinggi</option>
                            <option value="medium" selected>🟡 Sedang</option>
                            <option value="low">🟢 Rendah</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Stage
                            Awal</label>
                        <select id="cp-stage"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="script">Script & Concept</option>
                            <option value="production">Production</option>
                            <option value="revision">Revision</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Deadline</label>
                        <input type="date" id="cp-deadline"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                            value="{{ now()->addWeek()->format('Y-m-d') }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Software /
                        Tools</label>
                    <input type="text" id="cp-tags"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="After Effects, Premiere, Figma (pisahkan koma)">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Deskripsi /
                        Brief</label>
                    <textarea id="cp-desc" rows="3"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white resize-none"
                        placeholder="Brief proyek, requirement klien, catatan penting..."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Link Drive /
                        Referensi</label>
                    <input type="url" id="cp-link"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="https://drive.google.com/...">
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-project')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="saveProject()"
                    class="flex-1 py-2.5 bg-gradient-to-r from-orange-500 to-pink-500 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
                    <i class="fa-solid fa-film mr-1.5"></i>Buat Proyek
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL: PROJECT DETAIL --}}
    <div id="modal-project-detail"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-2xl shadow-2xl border border-stone-200 dark:border-stone-800 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-5 border-b border-stone-100 dark:border-stone-800">
                <div class="flex items-center gap-3">
                    <h3 class="font-bold text-stone-900 dark:text-white" id="pd-title">Detail Proyek</h3>
                    <span id="pd-type-badge"
                        class="text-[10px] px-2 py-0.5 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-500">Freelance</span>
                </div>
                <button onclick="closeModal('modal-project-detail')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div id="pd-body" class="p-5">
                <p class="text-center text-stone-400 py-6 text-sm">Memuat detail proyek...</p>
            </div>
            <div class="p-5 pt-0 border-t border-stone-100 dark:border-stone-800">
                <div class="flex gap-3">
                    <button onclick="closeModal('modal-project-detail')"
                        class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Tutup</button>
                    <button id="pd-btn-delete" onclick="deleteProject(currentProjectId)"
                        class="px-4 py-2.5 bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-xl text-sm hover:bg-rose-200 transition-colors">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentProjectId = null;
        let currentTaskData = null;

        function openModal(id) {
            document.getElementById(id)?.classList.remove('hidden');
            document.body.classList.add('modal-open');
        }

        function closeModal(id) {
            document.getElementById(id)?.classList.add('hidden');
            document.body.classList.remove('modal-open');
            currentProjectId = null;
            currentTaskData = null;
        }

        const projectsData = @json($projects);
        const _csrf = document.querySelector('meta[name=csrf-token]')?.content || '';

        function toast(msg, ok = true) {
            const t = document.createElement('div');
            t.className =
                `fixed bottom-6 right-6 z-[9999] flex items-center gap-2 px-4 py-3 ${ok ? 'bg-emerald-500' : 'bg-rose-500'} text-white text-sm font-medium rounded-2xl shadow-xl`;
            t.innerHTML = `<i class="fa-solid ${ok ? 'fa-check-circle' : 'fa-circle-xmark'}"></i> ${msg}`;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 2500);
        }

        async function setProjectDetail(id) {
            currentProjectId = id;
            const body = document.getElementById('pd-body');
            body.innerHTML =
                '<p class="text-center text-stone-400 py-6"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Memuat...</p>';

            try {
                const res = await fetch(`/dashboard/creative/task/${id}`);
                const data = await res.json();

                if (!data.success) {
                    body.innerHTML = '<p class="text-center text-rose-400 py-6">Gagal memuat data</p>';
                    return;
                }

                currentTaskData = data.task;
                renderProjectDetail(data.task);
            } catch (e) {
                body.innerHTML = '<p class="text-center text-rose-400 py-6">Error memuat data</p>';
            }
        }

        function renderProjectDetail(task) {
            document.getElementById('pd-title').textContent = task.title;
            document.getElementById('pd-type-badge').textContent = task.project_type || 'Freelance';

            const hasSubtasks = task.subtasks && task.subtasks.length > 0;
            const isSimpleProject = !hasSubtasks;

            let html = `
                <div class="space-y-5">
                    {{-- Info Cards --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                            <p class="text-[10px] text-stone-400 uppercase">Deadline</p>
                            <p class="text-sm font-semibold text-stone-800 dark:text-white">${task.due_date || 'Flexible'}</p>
                        </div>
                        <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                            <p class="text-[10px] text-stone-400 uppercase">Status</p>
                            <p class="text-sm font-semibold text-stone-800 dark:text-white capitalize">${task.status}</p>
                        </div>
                    </div>

                    {{-- Progress --}}
                    <div class="p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                        <div class="flex justify-between text-xs mb-2">
                            <span class="text-stone-500">Progress</span>
                            <span class="font-bold text-stone-800 dark:text-white" id="pd-progress-text">${task.progress}%</span>
                        </div>
                        <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2">
                            <div id="pd-progress-bar" class="h-2 rounded-full bg-orange-500 transition-all" style="width:${task.progress}%"></div>
                        </div>
                    </div>

                    ${task.description ? `
                        <div class="p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                            <p class="text-[10px] text-stone-400 uppercase mb-1">Deskripsi</p>
                            <p class="text-sm text-stone-600 dark:text-stone-300">${task.description}</p>
                        </div>
                        ` : ''}
            `;

            if (isSimpleProject) {
                html += `
                    {{-- Simple Project Actions --}}
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-lightbulb text-blue-500 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-semibold text-blue-700 dark:text-blue-400">Proyek Sederhana</p>
                                <p class="text-xs text-blue-600/80 dark:text-blue-300/80 mt-1">Proyek ini tidak memiliki langkah-langkah workflow. Tambahkan workflow untuk project kompleks (Video, Desain, dll).</p>
                                <button onclick="createWorkflowStages()" class="mt-2 px-3 py-1.5 bg-blue-500 text-white text-xs rounded-lg hover:bg-blue-600 transition-colors">
                                    <i class="fa-solid fa-plus mr-1"></i>Tambah Workflow
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                html += `
                    {{-- Workflow Stages --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-semibold text-stone-500 uppercase tracking-wider">Workflow / Subtasks</p>
                            <span class="text-[10px] text-stone-400">${task.subtasks.filter(s => s.status === 'completed').length}/${task.subtasks.length} selesai</span>
                        </div>
                        <div class="space-y-2" id="subtasks-list">
                            ${renderSubtasks(task.subtasks)}
                        </div>
                    </div>

                    {{-- Add Subtask --}}
                    <div class="flex gap-2">
                        <input type="text" id="new-subtask-title" placeholder="Tambah subtask baru..."
                            class="flex-1 border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">
                        <button onclick="addSubtask()" class="px-3 py-2 bg-stone-800 text-white rounded-lg text-sm hover:bg-stone-700">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                `;
            }

            if (task.links && task.links.length > 0) {
                html += `
                    {{-- Links --}}
                    <div>
                        <p class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-2">Links</p>
                        <div class="flex flex-wrap gap-2">
                            ${task.links.map(link => `
                                    <a href="${link.url}" target="_blank" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-xs rounded-lg hover:bg-blue-100">
                                        <i class="fa-solid fa-link"></i>
                                        ${link.label || link.type}
                                    </a>
                                `).join('')}
                        </div>
                    </div>
                `;
            }

            if (task.tags && task.tags.length > 0) {
                html += `
                    {{-- Tags --}}
                    <div>
                        <p class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-2">Tools / Tags</p>
                        <div class="flex flex-wrap gap-1">
                            ${task.tags.map(t => `<span class="text-xs px-2 py-0.5 bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 rounded-full">${t}</span>`).join('')}
                        </div>
                    </div>
                `;
            }

            html += `</div>`;
            document.getElementById('pd-body').innerHTML = html;
        }

        function renderSubtasks(subtasks) {
            if (!subtasks || subtasks.length === 0) return '';

            return subtasks.map((s, idx) => {
                const statusColor = s.status === 'completed' ? 'bg-emerald-500' : (s.status === 'in_progress' ?
                    'bg-orange-400' : 'bg-stone-300');
                const textClass = s.status === 'completed' ? 'text-stone-400 line-through' :
                    'text-stone-700 dark:text-stone-300';
                return `
                    <div class="flex items-center gap-3 p-3 bg-stone-50 dark:bg-stone-800 rounded-xl group">
                        <button onclick="toggleSubtask(${s.id}, '${s.status === 'completed' ? 'pending' : 'completed'}')" class="w-5 h-5 rounded-full ${statusColor} flex items-center justify-center flex-shrink-0 transition-colors">
                            ${s.status === 'completed' ? '<i class="fa-solid fa-check text-white text-[10px]"></i>' : ''}
                        </button>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium ${textClass}">${s.title}</p>
                            ${s.stage_label ? `<p class="text-[10px] text-stone-400">${s.stage_label}</p>` : ''}
                        </div>
                        <button onclick="deleteSubtask(${s.id})" class="opacity-0 group-hover:opacity-100 w-7 h-7 rounded-lg hover:bg-rose-100 text-stone-400 hover:text-rose-500 transition-all">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                `;
            }).join('');
        }

        async function toggleSubtask(subtaskId, newStatus) {
            try {
                const res = await fetch(`/dashboard/creative/task/${currentProjectId}/subtask/${subtaskId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': _csrf
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById('pd-progress-text').textContent = data.task_progress + '%';
                    document.getElementById('pd-progress-bar').style.width = data.task_progress + '%';
                    await setProjectDetail(currentProjectId);
                    toast('Status diperbarui');
                }
            } catch (e) {
                toast('Gagal update status', false);
            }
        }

        async function addSubtask() {
            const title = document.getElementById('new-subtask-title')?.value?.trim();
            if (!title) return;

            try {
                const res = await fetch(`/dashboard/creative/task/${currentProjectId}/subtask`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': _csrf
                    },
                    body: JSON.stringify({
                        title
                    })
                });
                const data = await res.json();
                if (data.success) {
                    await setProjectDetail(currentProjectId);
                    toast('Subtask ditambahkan');
                }
            } catch (e) {
                toast('Gagal menambah subtask', false);
            }
        }

        async function deleteSubtask(subtaskId) {
            if (!confirm('Hapus subtask ini?')) return;

            try {
                const res = await fetch(`/dashboard/creative/task/${currentProjectId}/subtask/${subtaskId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': _csrf
                    }
                });
                const data = await res.json();
                if (data.success) {
                    await setProjectDetail(currentProjectId);
                    toast('Subtask dihapus');
                }
            } catch (e) {
                toast('Gagal menghapus', false);
            }
        }

        async function createWorkflowStages() {
            try {
                const res = await fetch(`/dashboard/creative/task/${currentProjectId}/create-default-subtasks`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': _csrf
                    }
                });
                const data = await res.json();
                if (data.success) {
                    await setProjectDetail(currentProjectId);
                    toast('Workflow stages dibuat!');
                } else {
                    toast(data.message, false);
                }
            } catch (e) {
                toast('Gagal membuat workflow', false);
            }
        }

        function editProject(id) {
            const p = projectsData.find(x => x.id === id);
            if (!p) return;
            document.getElementById('cp-title').value = p.title;
            document.getElementById('cp-type').value = p.type;
            document.getElementById('cp-priority').value = p.priority;
            document.getElementById('cp-stage').value = p.stage;
            openModal('modal-add-project');
        }

        function saveProject() {
            const title = document.getElementById('cp-title').value.trim();
            if (!title) {
                alert('Isi judul proyek dulu');
                return;
            }
            const tagsRaw = document.getElementById('cp-tags').value;
            const data = {
                title,
                project_type: document.getElementById('cp-type').value,
                priority: document.getElementById('cp-priority').value,
                workflow_stage: document.getElementById('cp-stage').value,
                due_date: document.getElementById('cp-deadline').value,
                tags: tagsRaw ? tagsRaw.split(',').map(t => t.trim()) : [],
                description: document.getElementById('cp-desc').value,
                category: 'Creative',
            };
            fetch('{{ route('dashboard.creative.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': _csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            }).then(r => r.json()).then(r => {
                closeModal('modal-add-project');
                toast('Proyek berhasil dibuat!');
                setTimeout(() => location.reload(), 1000);
            }).catch(() => toast('Gagal menyimpan', false));
        }

        function deleteProject(id) {
            if (!confirm('Hapus proyek ini? Semua data termasuk subtasks akan dihapus.')) return;
            fetch('{{ url('/dashboard/creative') }}/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': _csrf,
                    'Accept': 'application/json'
                }
            }).then(() => {
                closeModal('modal-project-detail');
                toast('Proyek dihapus');
                setTimeout(() => location.reload(), 800);
            });
        }
    </script>
@endpush
