<!-- resources/views/dashboard/general-tracker.blade.php -->
@extends('layouts.app-dashboard')

@section('title', 'General Task Tracker')

@section('content')
    <div class="animate-fade-in-up">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-stone-900 dark:text-white">General Task Tracker</h2>
            <p class="text-stone-500 dark:text-stone-400 text-sm">
                Kelola semua tugas non-akademik: kesehatan, pengembangan diri, organisasi, perawatan, dan lainnya.
            </p>
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div
                class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl">
                <div class="flex items-center">
                    <i class="fa-solid fa-check-circle text-emerald-500 mr-3"></i>
                    <span class="text-emerald-700 dark:text-emerald-300">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl">
                <div class="flex items-center">
                    <i class="fa-solid fa-exclamation-circle text-red-500 mr-3"></i>
                    <div>
                        <span class="font-medium text-red-700 dark:text-red-300">Ada kesalahan:</span>
                        <ul class="mt-1 text-sm text-red-600 dark:text-red-400">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mr-4">
                        <i class="fa-solid fa-list-check text-rose-600 dark:text-rose-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-stone-500 dark:text-stone-400">Total Tugas</p>
                        <h3 class="text-2xl font-bold text-stone-800 dark:text-white">{{ $stats['total_tasks'] }}</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mr-4">
                        <i class="fa-solid fa-check-circle text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-stone-500 dark:text-stone-400">Selesai</p>
                        <h3 class="text-2xl font-bold text-stone-800 dark:text-white">{{ $stats['completed_tasks'] }}</h3>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400">{{ $stats['completion_rate'] }}%
                            completion rate</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-4">
                        <i class="fa-solid fa-clock text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-stone-500 dark:text-stone-400">Sedang Dikerjakan</p>
                        <h3 class="text-2xl font-bold text-stone-800 dark:text-white">{{ $stats['in_progress_tasks'] }}</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mr-4">
                        <i class="fa-solid fa-layer-group text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-stone-500 dark:text-stone-400">Kategori</p>
                        <h3 class="text-2xl font-bold text-stone-800 dark:text-white">{{ $stats['categories_count'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Task List -->
            <div class="lg:col-span-2">
                <!-- Add Task Form (Simple) -->
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800 mb-6">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-4">Tambah Task Baru</h3>

                    <form method="POST" action="{{ route('tasks.store') }}" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Judul
                                    Task</label>
                                <input type="text" name="title" required
                                    class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:bg-stone-800 dark:text-white"
                                    placeholder="Apa yang perlu dikerjakan?">
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Kategori</label>
                                <select name="category" required
                                    class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:bg-stone-800 dark:text-white">
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Priority</label>
                                <select name="priority" required
                                    class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:bg-stone-800 dark:text-white">
                                    <option value="not-urgent-not-important">Not Urgent/Important</option>
                                    <option value="urgent-not-important">Urgent Not Important</option>
                                    <option value="important-not-urgent">Important Not Urgent</option>
                                    <option value="urgent-important">Urgent & Important</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Deadline</label>
                                <input type="date" name="due_date" required
                                    class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:bg-stone-800 dark:text-white"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Deskripsi
                                (Opsional)</label>
                            <textarea name="description" rows="2"
                                class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:bg-stone-800 dark:text-white"
                                placeholder="Detail task..."></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-medium transition-colors">
                                <i class="fa-solid fa-plus mr-2"></i>Tambah Task
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Task List -->
                <div class="space-y-3" id="task-list">
                    @if ($tasks->count() > 0)
                        <!-- resources/views/dashboard/general-tracker.blade.php -->
                        <!-- Dalam loop task -->
                        @foreach ($tasks as $task)
                            <div class="task-item bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-xl p-4 hover:shadow-md transition-shadow"
                                data-task-id="{{ $task->id }}">
                                <div class="flex items-start">
                                    <!-- Checkbox -->
                                    <div class="mr-4 mt-1">
                                        <input type="checkbox" onclick="toggleTaskStatus({{ $task->id }}, this)"
                                            {{ $task->status === 'done' ? 'checked' : '' }}
                                            class="task-checkbox w-5 h-5 rounded border-2 border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 checked:bg-emerald-500 checked:border-emerald-500 focus:ring-0 focus:ring-offset-0 cursor-pointer"
                                            id="task-checkbox-{{ $task->id }}">
                                    </div>

                                    <!-- Task Content -->
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4
                                                    class="font-medium text-stone-800 dark:text-white {{ $task->status === 'done' ? 'line-through text-stone-500' : '' }}">
                                                    {{ $task->title }}
                                                </h4>

                                                <!-- DEBUG: Tampilkan status dan ID -->
                                                <div class="text-xs text-gray-400 mt-1">
                                                    ID: {{ $task->id }} | Status: {{ $task->status }} | Progress:
                                                    {{ $task->progress }}%
                                                </div>

                                                @if ($task->description)
                                                    <p class="text-sm text-stone-600 dark:text-stone-400 mt-1">
                                                        {{ $task->description }}</p>
                                                @endif
                                            </div>

                                            <!-- Due Date Badge -->
                                            <span
                                                class="due-date-badge px-2 py-1 rounded-full text-xs
                        @if ($task->is_overdue && $task->status !== 'done') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300
                        @elseif($task->is_today && $task->status !== 'done')
                            bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300
                        @elseif($task->status === 'done')
                            bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300
                        @else
                            bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 @endif">
                                                <i class="fa-regular fa-calendar mr-1"></i>
                                                <span class="due-date-text">
                                                    @if ($task->status === 'done')
                                                        Selesai
                                                    @elseif($task->is_today)
                                                        Hari ini
                                                    @elseif($task->is_overdue)
                                                        Terlambat {{ $task->due_date->diffForHumans() }}
                                                    @else
                                                        {{ $task->due_date ? $task->due_date->diffForHumans() : 'Tanpa deadline' }}
                                                    @endif
                                                </span>
                                            </span>
                                        </div>

                                        <!-- Category -->
                                        <div class="mt-2">
                                            <span
                                                class="text-xs px-2 py-1 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400">
                                                <i
                                                    class="fa-solid fa-tag mr-1"></i>{{ $task->category ?? 'Uncategorized' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Pagination -->
                        @if ($tasks->hasPages())
                            <div class="mt-6">
                                {{ $tasks->withQueryString()->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <div
                                class="w-16 h-16 bg-stone-100 dark:bg-stone-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-clipboard-list text-2xl text-stone-400 dark:text-stone-500"></i>
                            </div>
                            <h3 class="text-lg font-bold text-stone-800 dark:text-white mb-2">Tidak ada tugas</h3>
                            <p class="text-stone-600 dark:text-stone-400 mb-4">Mulai dengan menambahkan tugas baru</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Category Breakdown -->
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-4">Breakdown per Kategori</h3>
                    <div class="space-y-4">
                        @foreach ($categoryBreakdown as $category)
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span
                                        class="text-sm font-medium text-stone-700 dark:text-stone-300">{{ $category['name'] }}</span>
                                    <span
                                        class="text-sm font-bold text-stone-800 dark:text-white">{{ $category['completion_rate'] }}%</span>
                                </div>
                                <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2">
                                    <div class="h-2 rounded-full"
                                        style="width: {{ $category['completion_rate'] }}%; background-color: {{ $category['color'] }}">
                                    </div>
                                </div>
                                <div class="text-xs text-stone-500 dark:text-stone-400">
                                    {{ $category['completed'] }}/{{ $category['total'] }} tugas selesai
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Quick Add -->
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-4">Tambah Task Cepat</h3>
                    <form method="POST" action="{{ route('tasks.quick-add') }}" class="space-y-3">
                        @csrf
                        <input type="text" name="title" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 dark:bg-stone-800 dark:text-white"
                            placeholder="Judul tugas">
                        <select name="category" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 dark:bg-stone-800 dark:text-white">
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="w-full px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm transition-colors">
                            Tambah Task
                        </button>
                    </form>
                </div>

                <!-- Recently Completed -->
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-4">Baru Selesai</h3>
                    <div class="space-y-3">
                        @if ($recentlyCompleted->count() > 0)
                            @foreach ($recentlyCompleted as $task)
                                <div
                                    class="p-3 border border-emerald-100 dark:border-emerald-800 rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                                    <div class="flex items-start">
                                        <div
                                            class="w-8 h-8 bg-emerald-100 dark:bg-emerald-800 rounded-full flex items-center justify-center text-emerald-600 dark:text-emerald-300 mr-3">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-emerald-900 dark:text-emerald-200 text-sm">
                                                {{ $task->title }}</h4>
                                            <div
                                                class="flex items-center text-xs text-emerald-700 dark:text-emerald-300 mt-1">
                                                <span class="mr-3">{{ $task->category }}</span>
                                                <span>{{ $task->updated_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-center py-4 text-stone-500 dark:text-stone-400">Belum ada tugas yang
                                diselesaikan</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Fungsi untuk toggle status task dengan debug
        async function toggleTaskStatus(taskId, checkbox) {
            console.log('toggleTaskStatus called for task:', taskId);
            console.log('Checkbox checked:', checkbox.checked);

            // Tentukan status baru berdasarkan checkbox
            const newStatus = checkbox.checked ? 'done' : 'todo';
            console.log('New status to send:', newStatus);

            try {
                // Kirim request ke server
                const response = await fetch(`/tasks/${taskId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                });

                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Response data:', data);

                if (data.success) {
                    // Update UI
                    const taskItem = checkbox.closest('.task-item');
                    const title = taskItem.querySelector('h4');
                    const dueDateSpan = taskItem.querySelector('.due-date-badge');

                    if (newStatus === 'done') {
                        title.classList.add('line-through', 'text-stone-500');
                        if (dueDateSpan) {
                            dueDateSpan.className =
                                'px-2 py-1 rounded-full text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300';
                            dueDateSpan.innerHTML = '<i class="fa-solid fa-check mr-1"></i>Selesai';
                        }

                        // Tampilkan notifikasi sukses
                        showNotification('🎉 Task berhasil diselesaikan!', 'success');

                    } else {
                        title.classList.remove('line-through', 'text-stone-500');
                        if (dueDateSpan) {
                            // Kembalikan ke tampilan normal
                            updateDueDateBadge(taskItem, dueDateSpan);
                        }
                        showNotification('Task dikembalikan ke belum selesai', 'info');
                    }

                    // Refresh halaman setelah 1.5 detik untuk update statistik
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);

                } else {
                    // Jika gagal, kembalikan checkbox
                    checkbox.checked = !checkbox.checked;
                    showNotification(data.message || 'Gagal mengubah status task', 'error');
                }

            } catch (error) {
                console.error('Error in toggleTaskStatus:', error);
                // Jika error, kembalikan checkbox
                checkbox.checked = !checkbox.checked;
                showNotification('Terjadi kesalahan koneksi', 'error');
            }
        }

        // Helper function untuk update badge tanggal
        function updateDueDateBadge(taskItem, dueDateSpan) {
            // Ambil data tanggal dari atribut data atau teks
            const dueDateText = taskItem.querySelector('.due-date-text')?.textContent;
            if (!dueDateText) return;

            // Parse tanggal (contoh: "2 hari lagi", "Hari ini", "Kemarin")
            if (dueDateText.includes('Hari ini')) {
                dueDateSpan.className =
                    'px-2 py-1 rounded-full text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300';
                dueDateSpan.innerHTML = '<i class="fa-regular fa-calendar mr-1"></i>Hari ini';
            } else if (dueDateText.includes('Terlambat') || dueDateText.includes('hari lalu')) {
                dueDateSpan.className =
                    'px-2 py-1 rounded-full text-xs bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300';
                dueDateSpan.innerHTML = '<i class="fa-regular fa-calendar mr-1"></i>' + dueDateText;
            } else {
                dueDateSpan.className =
                    'px-2 py-1 rounded-full text-xs bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400';
                dueDateSpan.innerHTML = '<i class="fa-regular fa-calendar mr-1"></i>' + dueDateText;
            }
        }

        // Fungsi notifikasi sederhana
        function showNotification(message, type = 'info') {
            // Hapus notifikasi lama
            const oldNotif = document.querySelector('.notification-toast');
            if (oldNotif) oldNotif.remove();

            // Buat notifikasi baru
            const notification = document.createElement('div');
            notification.className = `notification-toast fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-emerald-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'info' ? 'bg-blue-500 text-white' :
            'bg-stone-500 text-white'
        }`;

            notification.innerHTML = `
            <div class="flex items-center">
                <i class="fa-solid ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    'fa-info-circle'
                } mr-2"></i>
                <span>${message}</span>
            </div>
        `;

            document.body.appendChild(notification);

            // Auto hilang setelah 3 detik
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.3s';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Tambah event listener untuk debug
        document.addEventListener('DOMContentLoaded', function() {
            console.log('General Tracker loaded');

            // Debug: Cek semua checkbox
            const checkboxes = document.querySelectorAll('.task-checkbox');
            console.log('Found', checkboxes.length, 'checkboxes');

            checkboxes.forEach((checkbox, index) => {
                const taskId = checkbox.closest('.task-item')?.dataset?.taskId;
                console.log(`Checkbox ${index}: taskId=${taskId}, checked=${checkbox.checked}`);
            });
        });
    </script>
@endpush
