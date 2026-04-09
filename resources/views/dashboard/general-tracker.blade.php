{{-- resources/views/dashboard/general-tracker.blade.php --}}
@extends('layouts.app-dashboard')
@section('title', 'General Tracker | StudentHub')
@section('page-title', 'General Tracker')

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
            transition: border .18s
        }

        .fi:focus {
            border-color: #f43f5e;
            box-shadow: 0 0 0 3px rgba(244, 63, 94, .1)
        }

        .dark .fi {
            background: #292524;
            border-color: #44403c;
            color: #fafaf9
        }
    </style>
@endpush

@section('content')
    @php
        $allTasks = $allTasks ?? [];
        $completedCount = $completedCount ?? 0;
        $totalCount = $totalCount ?? 0;
        $catCounts = $catCounts ?? collect();
        $doneByCat = $doneByCat ?? collect();
    @endphp

    <div class="fade-up space-y-5">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">General Tracker</h2>
                <p class="text-stone-400 text-xs">Semua tugas di luar akademik: kesehatan, personal, perawatan, dan lainnya
                </p>
            </div>
            <button onclick="openModal('modal-add-gtask')"
                class="flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-medium transition-colors self-start sm:self-auto">
                <i class="fa-solid fa-plus text-xs"></i> Tambah Tugas
            </button>
        </div>

        @if (session('success'))
            <div
                class="flex items-center gap-3 px-5 py-3.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm">
                <i class="fa-solid fa-circle-check flex-shrink-0"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([[$completedCount, 'Selesai', 'fa-circle-check', 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600'], [$totalCount - $completedCount, 'Belum Selesai', 'fa-clock', 'bg-rose-100 dark:bg-rose-900/30 text-rose-600'], [$totalCount, 'Total Tugas', 'fa-list-check', 'bg-blue-100 dark:bg-blue-900/30 text-blue-600'], [$catCounts->count(), 'Kategori', 'fa-tags', 'bg-amber-100 dark:bg-amber-900/30 text-amber-600']] as [$v, $l, $ic, $cls])
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl {{ $cls }} flex items-center justify-center shrink-0">
                        <i class="fa-solid {{ $ic }} text-lg"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-stone-800 dark:text-white">{{ $v }}</p>
                        <p class="text-xs text-stone-400">{{ $l }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Task List --}}
            <div
                class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                    <h3 class="font-bold text-stone-800 dark:text-white text-sm">Semua Tugas</h3>
                    <div class="flex gap-1">
                        @foreach (['all' => 'Semua', 'pending' => 'Belum', 'done' => 'Selesai', 'today' => 'Hari Ini'] as $k => $v)
                            <button onclick="filterGT('{{ $k }}')" id="gtf-{{ $k }}"
                                class="px-2.5 py-1 text-[11px] rounded-lg font-medium transition-colors {{ $k === 'all' ? 'bg-stone-800 dark:bg-stone-700 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500 hover:bg-stone-200' }}">
                                {{ $v }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="divide-y divide-stone-100 dark:divide-stone-800" id="gtask-list">
                    @forelse($allTasks as $t)
                        @php $isToday = $t['date'] === now()->format('Y-m-d'); @endphp
                        <div class="task-row flex items-center gap-4 px-5 py-3.5 {{ $t['done'] ? 'opacity-60' : '' }}"
                            data-status="{{ $t['done'] ? 'done' : 'pending' }}" data-today="{{ $isToday ? '1' : '0' }}"
                            id="gt-row-{{ $t['id'] }}">

                            {{-- CHECKBOX: toggle done/undone --}}
                            <button onclick="toggleGT({{ $t['id'] }}, this)"
                                title="{{ $t['done'] ? 'Tandai belum selesai' : 'Tandai selesai' }}"
                                class="w-5 h-5 rounded-full border-2 {{ $t['done'] ? 'bg-emerald-500 border-emerald-500' : 'border-stone-300 dark:border-stone-600 hover:border-emerald-400' }} flex items-center justify-center shrink-0 transition-colors">
                                @if ($t['done'])
                                    <i class="fa-solid fa-check text-white text-[9px]"></i>
                                @endif
                            </button>

                            <div class="flex-1 min-w-0">
                                <p
                                    class="text-sm font-medium text-stone-800 dark:text-white {{ $t['done'] ? 'line-through' : '' }}">
                                    {{ $t['title'] }}</p>
                                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                    <span
                                        class="text-[10px] bg-stone-100 dark:bg-stone-700 text-stone-500 dark:text-stone-400 px-2 py-0.5 rounded-full">{{ $t['category'] }}</span>
                                    @if ($t['time'] && $t['time'] !== '-')
                                        <span class="text-[10px] text-stone-400"><i
                                                class="fa-regular fa-clock text-[9px] mr-0.5"></i>{{ $t['time'] }}</span>
                                    @endif
                                    @if ($isToday)
                                        <span class="text-[10px] text-orange-600 dark:text-orange-400 font-semibold">📅 Hari
                                            ini</span>
                                    @endif
                                </div>
                            </div>

                            {{-- HAPUS: tombol trash dengan onclick deleteGTask --}}
                            <button onclick="deleteGTask({{ $t['id'] }}, this)" title="Hapus tugas"
                                class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center justify-center transition-colors shrink-0">
                                <i class="fa-solid fa-trash text-[10px]"></i>
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-12 text-stone-400">
                            <i class="fa-solid fa-clipboard-list text-4xl mb-3 block opacity-30"></i>
                            <p class="text-sm">Belum ada tugas. Tambahkan tugas pertamamu!</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">
                {{-- Category stats --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                    <h4 class="font-bold text-stone-800 dark:text-white text-sm mb-4">Progress per Kategori</h4>
                    @if ($catCounts->isEmpty())
                        <p class="text-xs text-stone-400 text-center py-4 italic">Belum ada data</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($catCounts as $cat => $total)
                                @php
                                    $done = $doneByCat->get($cat, 0);
                                    $pct = $total > 0 ? round(($done / $total) * 100) : 0;
                                @endphp
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-stone-600 dark:text-stone-400">{{ $cat }}</span>
                                        <span
                                            class="font-bold text-stone-700 dark:text-stone-300">{{ $pct }}%</span>
                                    </div>
                                    <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-1.5">
                                        <div class="bg-rose-500 h-1.5 rounded-full transition-all"
                                            style="width:{{ $pct }}%"></div>
                                    </div>
                                    <p class="text-[10px] text-stone-400 mt-0.5">{{ $done }}/{{ $total }}
                                        selesai</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Quick Add --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                    <h4 class="font-bold text-stone-800 dark:text-white text-sm mb-3">Tambah Cepat</h4>
                    <div class="space-y-2">
                        <input type="text" id="qt-title" placeholder="Judul tugas..." class="fi text-sm py-2"
                            onkeydown="if(event.key==='Enter')quickAddGT()">
                        <select id="qt-cat" class="fi text-sm py-2">
                            @foreach (['Kesehatan', 'Pengembangan Diri', 'Personal', 'Organisasi', 'Perawatan', 'Freelance', 'Shutterstock', 'Lainnya'] as $c)
                                <option>{{ $c }}</option>
                            @endforeach
                        </select>
                        <select id="qt-priority" class="fi text-sm py-2">
                            <option value="urgent-important">🔴 Urgent & Penting</option>
                            <option value="important-not-urgent" selected>🔵 Penting, Tidak Urgent</option>
                            <option value="urgent-not-important">🟠 Urgent, Tidak Penting</option>
                            <option value="not-urgent-not-important">⚪ Tidak Mendesak</option>
                        </select>
                        <button onclick="quickAddGT(this)"
                            class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-medium transition-colors">
                            <i class="fa-solid fa-plus mr-1.5"></i>Tambah
                        </button>
                    </div>
                </div>

                {{-- Recently completed --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                    <h4 class="font-bold text-stone-800 dark:text-white text-sm mb-3">Baru Selesai</h4>
                    @forelse(collect($allTasks)->where('done',true)->take(3) as $t)
                        <div
                            class="flex items-center gap-3 p-2.5 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl mb-2 last:mb-0">
                            <div
                                class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-check text-emerald-600 text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-stone-800 dark:text-white truncate">
                                    {{ $t['title'] }}</p>
                                <p class="text-[10px] text-stone-400">{{ $t['category'] }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-stone-400 text-center py-3">Belum ada tugas selesai</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: ADD TASK --}}
    <div id="modal-add-gtask"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl border border-stone-200 dark:border-stone-800">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white">Tambah Tugas Baru</h3>
                <button onclick="closeModal('modal-add-gtask')" class="text-stone-400 hover:text-stone-700"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Judul <span
                            class="text-rose-400">*</span></label>
                    <input type="text" id="gta-title" class="fi" placeholder="Apa yang akan dikerjakan?">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Kategori</label>
                        <select id="gta-cat" class="fi">
                            @foreach (['Kesehatan', 'Pengembangan Diri', 'Personal', 'Organisasi', 'Perawatan', 'Freelance', 'Shutterstock', 'Lainnya'] as $c)
                                <option>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Estimasi</label>
                        <input type="text" id="gta-time" class="fi" placeholder="30m, 1j, dll">
                    </div>
                </div>
                <div>
                    <label
                        class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Prioritas</label>
                    <select id="gta-priority" class="fi">
                        <option value="urgent-important">🔴 Urgent & Penting</option>
                        <option value="important-not-urgent" selected>🔵 Penting, Tidak Urgent</option>
                        <option value="urgent-not-important">🟠 Urgent, Tidak Penting</option>
                        <option value="not-urgent-not-important">⚪ Tidak Mendesak</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Tanggal
                        Target</label>
                    <input type="date" id="gta-date" class="fi" value="{{ now()->format('Y-m-d') }}">
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-gtask')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="saveGTask(this)"
                    class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-plus mr-1.5"></i>Tambah
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const _csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        function openModal(id) {
            document.getElementById(id)?.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal(id) {
            document.getElementById(id)?.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function filterGT(type) {
            const on =
                'px-2.5 py-1 text-[11px] rounded-lg font-medium bg-stone-800 dark:bg-stone-700 text-white transition-colors';
            const off =
                'px-2.5 py-1 text-[11px] rounded-lg font-medium bg-stone-100 dark:bg-stone-800 text-stone-500 hover:bg-stone-200 transition-colors';
            ['all', 'pending', 'done', 'today'].forEach(k => {
                document.getElementById('gtf-' + k).className = k === type ? on : off;
            });
            document.querySelectorAll('#gtask-list [data-status]').forEach(el => {
                if (type === 'all') {
                    el.style.display = '';
                    return;
                }
                if (type === 'today') {
                    el.style.display = el.dataset.today === '1' ? '' : 'none';
                    return;
                }
                el.style.display = el.dataset.status === type ? '' : 'none';
            });
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

        function setLoading(btn, on) {
            if (!btn) return;
            if (on) {
                btn.disabled = true;
                btn.style.opacity = '.7';
            } else {
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        }

        // ══════════════════════════════════════════════════════════
        // TOGGLE DONE / UNDONE — kirim ke backend
        // ══════════════════════════════════════════════════════════
        async function toggleGT(id, btn) {
            const row = btn.closest('[data-status]');
            const isDone = row?.dataset.status === 'done';
            const newStatus = isDone ? 'todo' : 'done';

            btn.disabled = true;
            try {
                const res = await fetch(`/tasks/${id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': _csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: newStatus
                    }),
                });
                const data = await res.json();

                if (data.success || res.ok) {
                    // Update DOM tanpa reload
                    if (row) {
                        row.dataset.status = newStatus;
                        row.classList.toggle('opacity-60', newStatus === 'done');
                        const title = row.querySelector('p.text-sm');
                        if (title) title.classList.toggle('line-through', newStatus === 'done');
                    }
                    // Update checkbox visuals
                    btn.className = btn.className
                        .replace('bg-emerald-500 border-emerald-500', '')
                        .replace('border-stone-300 dark:border-stone-600 hover:border-emerald-400', '')
                        .trim();
                    if (newStatus === 'done') {
                        btn.classList.add('bg-emerald-500', 'border-emerald-500');
                        btn.innerHTML = '<i class="fa-solid fa-check text-white text-[9px]"></i>';
                    } else {
                        btn.classList.add('border-stone-300', 'dark:border-stone-600', 'hover:border-emerald-400');
                        btn.innerHTML = '';
                    }
                    toast(newStatus === 'done' ? 'Tugas selesai! ✅' : 'Status direset.');
                } else {
                    toast(data.message || 'Gagal update status.', false);
                }
            } catch (e) {
                toast('Gagal menghubungi server.', false);
            } finally {
                btn.disabled = false;
            }
        }

        // ══════════════════════════════════════════════════════════
        // HAPUS TUGAS — kirim DELETE ke backend
        // ══════════════════════════════════════════════════════════
        async function deleteGTask(id, btn) {
            const row = document.getElementById('gt-row-' + id);
            const title = row?.querySelector('h4')?.textContent?.trim() || 'Tugas ini';

            showDeleteConfirm({
                title: 'Hapus Tugas?',
                message: `Hapus "${title}"?`,
                warning: 'Tugas yang dihapus tidak dapat dikembalikan.',
                btnRef: btn,
                onConfirm: async () => {
                    const res = await fetch(`/tasks/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': _csrf,
                            'Accept': 'application/json'
                        },
                    });
                    if (res.ok || res.status === 204) {
                        if (row) {
                            row.style.transition = 'all .25s';
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 250);
                        }
                        toast('Tugas dihapus.');
                    } else {
                        const data = await res.json().catch(() => ({}));
                        toast(data.message || 'Gagal menghapus.', false);
                    }
                }
            });
        }

        // ══════════════════════════════════════════════════════════
        // TAMBAH TUGAS (Quick Add / Modal)
        // ══════════════════════════════════════════════════════════
        async function quickAddGT(btn) {
            const title = document.getElementById('qt-title').value.trim();
            const cat = document.getElementById('qt-cat').value;
            const prio = document.getElementById('qt-priority').value;
            if (!title) {
                toast('Isi judul tugas dulu!', false);
                return;
            }
            setLoading(btn, true);
            try {
                const res = await fetch('{{ route('tasks.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': _csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title,
                        category: cat,
                        priority: prio,
                        due_date: new Date().toISOString().split('T')[0],
                        status: 'todo'
                    }),
                });
                const data = await res.json();
                if (data.success || res.ok) {
                    document.getElementById('qt-title').value = '';
                    toast('Tugas ditambahkan!');
                    setTimeout(() => location.reload(), 700);
                } else {
                    toast(data.message || 'Gagal menambahkan.', false);
                }
            } catch (e) {
                toast('Gagal menghubungi server.', false);
            } finally {
                setLoading(btn, false);
            }
        }

        async function saveGTask(btn) {
            const title = document.getElementById('gta-title').value.trim();
            if (!title) {
                toast('Isi judul tugas dulu!', false);
                return;
            }
            setLoading(btn, true);
            try {
                const res = await fetch('{{ route('tasks.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': _csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title,
                        category: document.getElementById('gta-cat').value,
                        priority: document.getElementById('gta-priority').value,
                        estimated_time: document.getElementById('gta-time').value || null,
                        due_date: document.getElementById('gta-date').value,
                        status: 'todo',
                    }),
                });
                const data = await res.json();
                if (data.success || res.ok) {
                    closeModal('modal-add-gtask');
                    toast('Tugas berhasil ditambahkan!');
                    setTimeout(() => location.reload(), 700);
                } else {
                    toast(data.message || 'Gagal.', false);
                }
            } catch (e) {
                toast('Gagal menghubungi server.', false);
            } finally {
                setLoading(btn, false);
            }
        }
    </script>
@endpush
