{{-- resources/views/dashboard/general-tracker.blade.php --}}
@extends('layouts.app-dashboard')
@section('title', 'General Tracker | StudentHub')
@section('page-title', 'General Tracker')

@push('styles')
    <style>
        @keyframes fadeUp { from { opacity: 0; transform: translateY(12px) } to { opacity: 1; transform: translateY(0) } }
        .fade-up { animation: fadeUp .4s ease-out both }
        .task-row { transition: background .12s }
        .task-row:hover { background: #fafaf9 }
        .dark .task-row:hover { background: #292524 }
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
                <p class="text-stone-400 text-xs">Semua tugas di luar akademik: kesehatan, personal, perawatan, dan lainnya</p>
            </div>
            <button onclick="openModal('modal-add-gtask')" class="flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-medium transition-colors self-start sm:self-auto">
                <i class="fa-solid fa-plus text-xs"></i> Tambah Tugas
            </button>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([[$completedCount, 'Selesai', 'fa-circle-check', 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600'], [$totalCount - $completedCount, 'Belum', 'fa-clock', 'bg-rose-100 dark:bg-rose-900/30 text-rose-600'], [$totalCount, 'Total', 'fa-list-check', 'bg-blue-100 dark:bg-blue-900/30 text-blue-600'], [$catCounts->count(), 'Kategori', 'fa-tags', 'bg-amber-100 dark:bg-amber-900/30 text-amber-600']] as [$v, $l, $ic, $cls])
                <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5 flex items-center gap-4">
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
            {{-- Task list --}}
            <div class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800">
                    <h3 class="font-bold text-stone-800 dark:text-white text-sm">Semua Tugas</h3>
                    <div class="flex gap-1">
                        @foreach (['all' => 'Semua', 'pending' => 'Belum', 'done' => 'Selesai', 'today' => 'Hari Ini'] as $k => $v)
                            <button onclick="filterGT('{{ $k }}')" id="gtf-{{ $k }}" class="px-2.5 py-1 text-[11px] rounded-lg font-medium transition-colors {{ $k === 'all' ? 'bg-stone-800 dark:bg-stone-700 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500 hover:bg-stone-200' }}">{{ $v }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="divide-y divide-stone-100 dark:divide-stone-800" id="gtask-list">
                    @forelse($allTasks as $t)
                        @php $isToday = $t['date'] === now()->format('Y-m-d'); @endphp
                        <div class="task-row flex items-center gap-4 px-5 py-3.5 {{ $t['done'] ? 'opacity-60' : '' }}" data-status="{{ $t['done'] ? 'done' : 'pending' }}" data-today="{{ $isToday ? '1' : '0' }}">
                            <button onclick="toggleGT({{ $t['id'] }}, this)" class="w-5 h-5 rounded-full border-2 {{ $t['done'] ? 'bg-emerald-500 border-emerald-500' : 'border-stone-300 dark:border-stone-600 hover:border-emerald-400' }} flex items-center justify-center shrink-0 transition-colors">
                                @if ($t['done'])<i class="fa-solid fa-check text-white text-[9px]"></i>@endif
                            </button>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-stone-800 dark:text-white {{ $t['done'] ? 'line-through' : '' }}">{{ $t['title'] }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] bg-stone-100 dark:bg-stone-700 text-stone-500 dark:text-stone-400 px-2 py-0.5 rounded-full">{{ $t['category'] }}</span>
                                    <span class="text-[10px] text-stone-400"><i class="fa-regular fa-clock text-[9px] mr-0.5"></i>{{ $t['time'] }}</span>
                                    @if ($isToday)<span class="text-[10px] text-orange-600 dark:text-orange-400 font-semibold">Hari ini</span>@endif
                                </div>
                            </div>
                            <button class="w-7 h-7 rounded-lg bg-stone-100 dark:bg-stone-700 text-stone-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center justify-center transition-colors shrink-0">
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
                <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                    <h4 class="font-bold text-stone-800 dark:text-white text-sm mb-4">Progress per Kategori</h4>
                    <div class="space-y-3">
                        @foreach ($catCounts as $cat => $total)
                            @php $done = $doneByCat->get($cat, 0); $pct = $total > 0 ? round(($done / $total) * 100) : 0; @endphp
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-stone-600 dark:text-stone-400">{{ $cat }}</span>
                                    <span class="font-bold text-stone-700 dark:text-stone-300">{{ $pct }}%</span>
                                </div>
                                <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-1.5">
                                    <div class="bg-rose-500 h-1.5 rounded-full" style="width:{{ $pct }}%"></div>
                                </div>
                                <p class="text-[10px] text-stone-400 mt-0.5">{{ $done }}/{{ $total }} selesai</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Quick add form with Priority --}}
                <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                    <h4 class="font-bold text-stone-800 dark:text-white text-sm mb-3">Tambah Cepat</h4>
                    <div class="space-y-2">
                        <input type="text" id="qt-title" placeholder="Judul tugas..." class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-rose-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <select id="qt-cat" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-rose-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                            @foreach (['Kesehatan', 'Pengembangan Diri', 'Personal', 'Organisasi', 'Perawatan', 'Freelance', 'Shutterstock'] as $c)<option>{{ $c }}</option>@endforeach
                        </select>
                        <select id="qt-priority" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-rose-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="urgent-important">🔴 Urgent & Penting (Do First)</option>
                            <option value="important-not-urgent" selected>🔵 Penting, Tidak Urgent (Schedule)</option>
                            <option value="urgent-not-important">🟠 Urgent, Tidak Penting (Delegate)</option>
                            <option value="not-urgent-not-important">⚪ Tidak Urgent & Tidak Penting (Eliminate)</option>
                        </select>
                        <button onclick="quickAddGT()" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-medium transition-colors"><i class="fa-solid fa-plus mr-1.5"></i>Tambah</button>
                    </div>
                </div>

                {{-- Recently completed --}}
                <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5">
                    <h4 class="font-bold text-stone-800 dark:text-white text-sm mb-3">Baru Selesai</h4>
                    @forelse(collect($allTasks)->where('done',true)->take(3) as $t)
                        <div class="flex items-center gap-3 p-2.5 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl mb-2">
                            <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-check text-emerald-600 text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-stone-800 dark:text-white truncate">{{ $t['title'] }}</p>
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
    <div id="modal-add-gtask" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl border border-stone-200 dark:border-stone-800">
            <div class="flex items-center justify-between p-6 border-b border-stone-100 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white">Tambah Tugas Baru</h3>
                <button onclick="closeModal('modal-add-gtask')" class="text-stone-400 hover:text-stone-700"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Judul *</label>
                    <input type="text" id="gta-title" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-rose-400 focus:outline-none dark:bg-stone-800 dark:text-white" placeholder="Apa yang akan dikerjakan?">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Kategori</label>
                        <select id="gta-cat" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-rose-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                            @foreach (['Kesehatan', 'Pengembangan Diri', 'Personal', 'Organisasi', 'Perawatan', 'Freelance', 'Shutterstock', 'Lainnya'] as $c)<option>{{ $c }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Estimasi</label>
                        <input type="text" id="gta-time" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-rose-400 focus:outline-none dark:bg-stone-800 dark:text-white" placeholder="30m, 1j, dll">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Prioritas</label>
                    <select id="gta-priority" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-rose-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <option value="urgent-important">🔴 Urgent & Penting</option>
                        <option value="important-not-urgent" selected>🔵 Penting, Tidak Urgent</option>
                        <option value="urgent-not-important">🟠 Urgent, Tidak Penting</option>
                        <option value="not-urgent-not-important">⚪ Tidak Urgent & Tidak Penting</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Tanggal Target</label>
                    <input type="date" id="gta-date" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-rose-400 focus:outline-none dark:bg-stone-800 dark:text-white" value="{{ now()->format('Y-m-d') }}">
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-gtask')" class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</button>
                <button onclick="saveGTask()" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold transition-colors"><i class="fa-solid fa-plus mr-1.5"></i>Tambah</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openModal(id) { document.getElementById(id)?.classList.remove('hidden'); document.body.classList.add('modal-open'); }
        function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); document.body.classList.remove('modal-open'); }

        function toggleGT(id, btn) {
            const isChecked = btn.classList.contains('bg-emerald-500');
            btn.classList.toggle('bg-emerald-500', !isChecked);
            btn.classList.toggle('border-emerald-500', !isChecked);
            btn.classList.toggle('border-stone-300', isChecked);
            btn.innerHTML = !isChecked ? '<i class="fa-solid fa-check text-white text-[9px]"></i>' : '';
            const row = btn.closest('[data-status]');
            if (row) {
                row.dataset.status = !isChecked ? 'done' : 'pending';
                row.classList.toggle('opacity-60', !isChecked);
                const title = row.querySelector('p');
                if (title) title.classList.toggle('line-through', !isChecked);
            }
        }

        function filterGT(type) {
            ['all', 'pending', 'done', 'today'].forEach(k => {
                const b = document.getElementById('gtf-' + k);
                b.className = k === type ? 'px-2.5 py-1 text-[11px] rounded-lg font-medium bg-stone-800 dark:bg-stone-700 text-white transition-colors' : 'px-2.5 py-1 text-[11px] rounded-lg font-medium bg-stone-100 dark:bg-stone-800 text-stone-500 hover:bg-stone-200 transition-colors';
            });
            document.querySelectorAll('#gtask-list [data-status]').forEach(el => {
                if (type === 'all') { el.style.display = ''; return; }
                if (type === 'today') { el.style.display = el.dataset.today === '1' ? '' : 'none'; return; }
                el.style.display = el.dataset.status === type ? '' : 'none';
            });
        }

        const _csrf = document.querySelector('meta[name=csrf-token]')?.content || '';

        function quickAddGT() {
            const title = document.getElementById('qt-title').value.trim();
            const cat = document.getElementById('qt-cat').value;
            if (!title) return;
            fetch('{{ route('tasks.store') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ title, category: cat, priority: document.getElementById('qt-priority').value, due_date: new Date().toISOString().split('T')[0], status: 'todo' })
            }).then(r => r.json()).then(() => { document.getElementById('qt-title').value = ''; location.reload(); });
        }

        function saveGTask() {
            const title = document.getElementById('gta-title').value.trim();
            if (!title) { alert('Isi judul dulu'); return; }
            fetch('{{ route('tasks.store') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ title, category: document.getElementById('gta-cat').value, priority: document.getElementById('gta-priority').value, estimated_time: document.getElementById('gta-time').value, due_date: document.getElementById('gta-date').value, status: 'todo' })
            }).then(r => r.json()).then(() => { closeModal('modal-add-gtask'); location.reload(); });
        }

        function deleteGTask(id) {
            if (!confirm('Hapus tugas ini?')) return;
            fetch('{{ url('/tasks') }}/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' } }).then(() => location.reload());
        }

        function toast(msg, ok = true) {
            const t = document.createElement('div');
            t.className = `fixed bottom-6 right-6 z-[9999] flex items-center gap-2 px-4 py-3 ${ok ? 'bg-emerald-500' : 'bg-rose-500'} text-white text-sm font-medium rounded-2xl shadow-xl`;
            t.innerHTML = `<i class="fa-solid ${ok ? 'fa-check-circle' : 'fa-circle-xmark'}"></i> ${msg}`;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 2500);
        }
    </script>
@endpush
