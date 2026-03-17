<!-- resources/views/dashboard/calendar-day.blade.php -->
@extends('layouts.app-dashboard')

@section('title', 'Jadwal Harian')

@section('content')
    <div class="animate-fade-in-up">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Jadwal Harian</h2>
                    <p class="text-stone-500 dark:text-stone-400 text-sm">
                        {{ $dayNames[$carbonDate->format('l')] ?? $carbonDate->format('l') }},
                        {{ $carbonDate->format('d') }} {{ $monthNames[$carbonDate->month] }} {{ $carbonDate->format('Y') }}
                        <span
                            class="ml-2 px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded-full text-xs">
                            Minggu ke-{{ $carbonDate->weekOfMonth }}
                        </span>
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard.smartcalendar') }}"
                        class="px-4 py-2 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 rounded-lg transition-colors">
                        <i class="fa-solid fa-calendar mr-2"></i>Kembali ke Kalender
                    </a>

                    <button type="button" onclick="showEventModal()"
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">
                        <i class="fa-solid fa-plus mr-2"></i>Tambah Jadwal
                    </button>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div
                class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl">
                <div class="flex items-center">
                    <i class="fa-solid fa-check-circle text-emerald-500 mr-3"></i>
                    <span class="text-emerald-700 dark:text-emerald-300">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Hourly Schedule -->
            <div class="lg:col-span-2">
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200 dark:border-stone-800 overflow-hidden">
                    <!-- Day Navigation -->
                    <div class="flex items-center justify-between p-4 border-b border-stone-200 dark:border-stone-800">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('calendar.day', $carbonDate->copy()->subDay()->format('Y-m-d')) }}"
                                class="p-2 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-lg">
                                <i class="fa-solid fa-chevron-left"></i>
                            </a>

                            <span class="font-medium text-stone-800 dark:text-white">
                                {{ $carbonDate->format('d') }} {{ $monthNames[$carbonDate->month] }}
                                {{ $carbonDate->format('Y') }}
                            </span>

                            <a href="{{ route('calendar.day', $carbonDate->copy()->addDay()->format('Y-m-d')) }}"
                                class="p-2 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-lg">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </div>

                        <div class="text-sm text-stone-500 dark:text-stone-400">
                            {{ count($dayEvents) + count($dayTasks) }} kegiatan
                        </div>
                    </div>

                    <!-- Hourly Timeline -->
                    <div class="max-h-[600px] overflow-y-auto">
                        @foreach ($hourlySchedule as $slot)
                            <div
                                class="p-4 border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800/50
                        {{ $slot['is_current'] ? 'bg-orange-50 dark:bg-orange-900/20' : '' }}
                        transition-colors">
                                <div class="flex gap-4">
                                    <!-- Time Column -->
                                    <div class="w-24 flex-shrink-0">
                                        <div class="font-medium text-stone-800 dark:text-white">
                                            {{ $slot['time_display'] }}
                                        </div>
                                        <div class="text-xs text-stone-500 dark:text-stone-400 mt-1">
                                            {{ $slot['recommendation'] }}
                                        </div>
                                    </div>

                                    <!-- Events Column -->
                                    <div class="flex-1 min-w-0">
                                        @if (count($slot['items']) > 0)
                                            @foreach ($slot['items'] as $item)
                                                <div class="mb-3 last:mb-0 p-3 rounded-lg border"
                                                    style="border-color: {{ $item['color'] }}30; background-color: {{ $item['color'] }}10;">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center">
                                                            <div class="w-3 h-3 rounded-full mr-2"
                                                                style="background-color: {{ $item['color'] }}"></div>
                                                            <span class="font-medium text-stone-800 dark:text-white">
                                                                {{ $item['title'] }}
                                                            </span>
                                                            <span
                                                                class="ml-2 text-xs px-2 py-1 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300">
                                                                {{ $item['type'] == 'event' ? 'Event' : 'Task' }}
                                                            </span>
                                                        </div>
                                                        <span class="text-sm text-stone-500 dark:text-stone-400">
                                                            {{ $item['start'] }} - {{ $item['end'] }}
                                                        </span>
                                                    </div>

                                                    @if ($item['description'])
                                                        <p class="text-sm text-stone-600 dark:text-stone-300 mt-2">
                                                            {{ $item['description'] }}
                                                        </p>
                                                    @endif

                                                    <div class="flex gap-2 mt-3">
                                                        <button
                                                            onclick="editItem('{{ $item['type'] }}', '{{ $item['id'] }}')"
                                                            class="text-xs px-3 py-1 bg-white dark:bg-stone-800 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-700 rounded">
                                                            <i class="fa-solid fa-pen mr-1"></i>Edit
                                                        </button>

                                                        @if ($item['type'] == 'task')
                                                            <a href="{{ route('tasks.complete', $item['id']) }}"
                                                                onclick="return confirm('Tandai task sebagai selesai?')"
                                                                class="text-xs px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-200 dark:hover:bg-emerald-800/50 rounded">
                                                                <i class="fa-solid fa-check mr-1"></i>Selesai
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center py-6">
                                                <div class="text-stone-400 dark:text-stone-600 mb-2">
                                                    <i class="fa-regular fa-calendar text-2xl"></i>
                                                </div>
                                                <p class="text-stone-500 dark:text-stone-400 text-sm">
                                                    Tidak ada jadwal di jam ini
                                                </p>
                                                <button onclick="showEventModal('{{ $slot['time'] }}')"
                                                    class="mt-3 text-xs px-3 py-1 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 rounded">
                                                    <i class="fa-solid fa-plus mr-1"></i>Tambah Jadwal
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Quick Stats -->
                    <div class="p-4 bg-stone-50 dark:bg-stone-800/30 border-t border-stone-200 dark:border-stone-800">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-sm text-stone-500 dark:text-stone-400">Total Event</div>
                                <div class="text-lg font-bold text-stone-800 dark:text-white">{{ count($dayEvents) }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-stone-500 dark:text-stone-400">Total Task</div>
                                <div class="text-lg font-bold text-stone-800 dark:text-white">{{ count($dayTasks) }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-stone-500 dark:text-stone-400">Waktu Produktif</div>
                                <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $productiveHours }} jam
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Weekly Overview -->
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-4">Minggu Ini</h3>
                    <p class="text-sm text-stone-500 dark:text-stone-400 mb-4">
                        {{ $weekStart->format('d') }} {{ $monthNames[$weekStart->month] }} -
                        {{ $weekEnd->format('d') }} {{ $monthNames[$weekEnd->month] }} {{ $weekEnd->format('Y') }}
                    </p>

                    <div class="space-y-3">
                        @php
                            $currentWeek = $weekStart->copy();
                        @endphp
                        @for ($i = 0; $i < 7; $i++)
                            @php
                                $day = $currentWeek->copy();
                                $dayEventsCount = $weeklyEvents
                                    ->filter(function ($event) use ($day) {
                                        return $event->start_time->format('Y-m-d') === $day->format('Y-m-d');
                                    })
                                    ->count();
                            @endphp
                            <a href="{{ route('calendar.day', $day->format('Y-m-d')) }}"
                                class="block p-3 rounded-lg border border-stone-200 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800/50 transition-colors
                                  {{ $day->format('Y-m-d') === $carbonDate->format('Y-m-d') ? 'bg-orange-50 dark:bg-orange-900/20 border-orange-300 dark:border-orange-700' : '' }}">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-medium text-stone-800 dark:text-white">
                                            {{ $dayNames[$day->format('l')] ?? $day->format('l') }}
                                        </div>
                                        <div class="text-sm text-stone-500 dark:text-stone-400">
                                            {{ $day->format('d') }} {{ $monthNames[$day->month] }}
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="text-sm text-stone-500 dark:text-stone-400 mr-2">
                                            {{ $dayEventsCount }} jadwal
                                        </span>
                                        @if ($day->isToday())
                                            <span
                                                class="px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded-full text-xs">
                                                Hari Ini
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            @php
                                $currentWeek->addDay();
                            @endphp
                        @endfor
                    </div>
                </div>

                <!-- Today's Tasks -->
                @if (count($dayTasks) > 0)
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                        <h3 class="font-bold text-stone-800 dark:text-white mb-4">Task Hari Ini</h3>

                        <div class="space-y-3">
                            @foreach ($dayTasks as $task)
                                <div class="p-3 border border-stone-200 dark:border-stone-700 rounded-lg">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-1">
                                                @php
                                                    $taskColor = '';
                                                    if ($task->category == 'Skripsi') {
                                                        $taskColor = '#8b5cf6';
                                                    } elseif ($task->category == 'Creative') {
                                                        $taskColor = '#f97316';
                                                    } elseif ($task->category == 'PKL') {
                                                        $taskColor = '#10b981';
                                                    } elseif ($task->category == 'Akademik') {
                                                        $taskColor = '#3b82f6';
                                                    } else {
                                                        $taskColor = '#64748b';
                                                    }
                                                @endphp
                                                <div class="w-2 h-2 rounded-full mr-2"
                                                    style="background-color: {{ $taskColor }}"></div>
                                                <span class="font-medium text-stone-800 dark:text-white">
                                                    {{ $task->title }}
                                                </span>
                                            </div>

                                            <!-- Progress Bar -->
                                            <div class="mb-2">
                                                <div
                                                    class="flex justify-between text-xs text-stone-500 dark:text-stone-400 mb-1">
                                                    <span>Progress</span>
                                                    <span>{{ $task->progress }}%</span>
                                                </div>
                                                <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2">
                                                    <div class="bg-emerald-500 h-2 rounded-full"
                                                        style="width: {{ $task->progress }}%"></div>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($task->status != 'done')
                                            <a href="{{ route('tasks.complete', $task->id) }}"
                                                onclick="return confirm('Tandai task sebagai selesai?')"
                                                class="ml-2 p-2 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg">
                                                <i class="fa-solid fa-check"></i>
                                            </a>
                                        @endif
                                    </div>

                                    <div
                                        class="flex justify-between items-center mt-2 pt-2 border-t border-stone-100 dark:border-stone-800">
                                        <span class="text-xs text-stone-500 dark:text-stone-400">
                                            {{ $task->category }}
                                        </span>
                                        <span
                                            class="text-xs px-2 py-1 rounded-full
                                {{ $task->status == 'done'
                                    ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300'
                                    : ($task->is_overdue
                                        ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'
                                        : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300') }}">
                                            {{ $task->status == 'done' ? 'Selesai' : ($task->is_overdue ? 'Terlambat' : 'Berjalan') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Today's Events -->
                @if (count($dayEvents) > 0)
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                        <h3 class="font-bold text-stone-800 dark:text-white mb-4">Event Hari Ini</h3>

                        <div class="space-y-3">
                            @foreach ($dayEvents as $event)
                                <div class="p-3 border border-stone-200 dark:border-stone-700 rounded-lg"
                                    style="border-left: 4px solid {{ $event->color }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="font-medium text-stone-800 dark:text-white mb-1">
                                                {{ $event->title }}
                                            </div>
                                            <p class="text-sm text-stone-500 dark:text-stone-400 mb-2">
                                                <i class="fa-regular fa-clock mr-1"></i>
                                                {{ $event->start_time->format('H:i') }} -
                                                {{ $event->end_time->format('H:i') }}
                                            </p>
                                            @if ($event->description)
                                                <p class="text-sm text-stone-600 dark:text-stone-300">
                                                    {{ Str::limit($event->description, 100) }}
                                                </p>
                                            @endif
                                        </div>

                                        <form method="POST" action="{{ route('calendar.events.destroy', $event->id) }}"
                                            class="ml-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Hapus event ini?')"
                                                class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal untuk Tambah Event -->
    <div id="eventModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-stone-800 dark:text-white">Tambah Jadwal</h3>
                    <button onclick="hideEventModal()" class="p-2 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-lg">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('calendar.events.store') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Judul</label>
                            <input type="text" name="title" required
                                class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                                placeholder="Nama event atau task">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tipe</label>
                            <select name="type" required
                                class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white">
                                <option value="academic">Akademik</option>
                                <option value="creative">Creative</option>
                                <option value="pkl">PKL</option>
                                <option value="deadline">Deadline</option>
                                <option value="personal">Personal</option>
                                <option value="routine">Routine</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tanggal</label>
                            <input type="date" id="eventDate" name="date" required
                                class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                                value="{{ $carbonDate->format('Y-m-d') }}">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Mulai</label>
                                <input type="time" id="startTime" name="start_time" required
                                    class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                                    value="09:00">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Selesai</label>
                                <input type="time" id="endTime" name="end_time" required
                                    class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                                    value="12:00">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Deskripsi</label>
                        <textarea name="description" rows="3"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                            placeholder="Deskripsi detail..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="isRecurring" name="is_recurring" class="mr-2">
                            <label for="isRecurring" class="text-sm text-stone-700 dark:text-stone-300">Berulang</label>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Frekuensi</label>
                            <select id="recurringFrequency" name="recurring_frequency"
                                class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white">
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-stone-200 dark:border-stone-800">
                        <button type="button" onclick="hideEventModal()"
                            class="px-4 py-2 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 rounded-lg font-medium transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">
                            Tambah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let eventModal = document.getElementById('eventModal');

            function showEventModal(prefillTime = null) {
                if (prefillTime) {
                    document.getElementById('startTime').value = prefillTime;
                    let timeParts = prefillTime.split(':');
                    let hour = parseInt(timeParts[0]) + 1;
                    if (hour > 23) hour = 23;
                    document.getElementById('endTime').value = hour.toString().padStart(2, '0') + ':00';
                }

                eventModal.classList.remove('hidden');
                eventModal.classList.add('flex');
            }

            function hideEventModal() {
                eventModal.classList.remove('flex');
                eventModal.classList.add('hidden');
            }

            function editItem(type, id) {
                // TODO: Implement edit functionality
                alert('Fitur edit akan segera tersedia!');
            }

            // Close modal on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') hideEventModal();
            });

            // Close modal on background click
            eventModal.addEventListener('click', function(e) {
                if (e.target === eventModal) hideEventModal();
            });
        </script>
    @endpush
@endsection
