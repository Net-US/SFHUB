<!-- resources/views/dashboard/smartcalendar.blade.php -->
@extends('layouts.app-dashboard')

@section('title', 'Smart Calendar')

@section('content')
    <div class="animate-fade-in-up">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Smart Calendar</h2>
            <p class="text-stone-500 dark:text-stone-400 text-sm">Kalender pintar yang mengintegrasikan semua jadwal: PKL,
                Kuliah, Deadline, dan proyek kreatif.</p>
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div
                class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl">
                <div class="flex items-center">
                    <i class="fa-solid fa-check-circle text-emerald-500 mr-3"></i>
                    <span class="text-emerald-700 dark:text-emerald-300">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl">
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Calendar Controls -->
            <div
                class="lg:col-span-3 bg-white dark:bg-stone-900 p-4 rounded-xl shadow-sm border border-stone-200 dark:border-stone-800">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-center md:text-left">
                        <h3 class="text-xl font-bold text-stone-800 dark:text-white">{{ $monthNames[$month] }}
                            {{ $year }}</h3>
                        <p class="text-sm text-stone-500 dark:text-stone-400">{{ count($events) }} event di bulan ini</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('dashboard.smartcalendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
                            class="px-4 py-2 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 rounded-lg transition-colors">
                            <i class="fa-solid fa-chevron-left mr-2"></i>Bulan Sebelumnya
                        </a>
                        <a href="{{ route('dashboard.smartcalendar', ['month' => date('m'), 'year' => date('Y')]) }}"
                            class="px-4 py-2 bg-orange-100 dark:bg-orange-900/30 hover:bg-orange-200 dark:hover:bg-orange-800/50 text-orange-700 dark:text-orange-300 rounded-lg transition-colors">
                            <i class="fa-solid fa-calendar-day mr-2"></i>Bulan Ini
                        </a>
                        <a href="{{ route('dashboard.smartcalendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
                            class="px-4 py-2 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 rounded-lg transition-colors">
                            Bulan Berikutnya<i class="fa-solid fa-chevron-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Calendar -->
            <div
                class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                <!-- Days Header -->
                <div class="grid grid-cols-7 gap-2 mb-4">
                    @foreach (['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day)
                        <div class="text-center font-medium text-stone-500 dark:text-stone-400 p-2">{{ $day }}
                        </div>
                    @endforeach
                </div>

                <!-- Calendar Days -->
                <div class="grid grid-cols-7 gap-2">
                    @foreach ($calendarData as $day)
                        <div
                            class="min-h-32 p-2 border border-stone-200 dark:border-stone-700 rounded-lg
                    {{ $day['is_today'] ? 'bg-orange-50 dark:bg-orange-900/20 border-orange-300 dark:border-orange-700' : '' }}
                    {{ $day['is_weekend'] && !$day['is_today'] ? 'bg-stone-50 dark:bg-stone-800/50' : 'bg-white dark:bg-stone-900' }}
                    hover:shadow-sm transition-shadow">

                            @if ($day['day'])
                                <div class="mb-2">
                                    <div class="flex justify-between items-center">
                                        <span
                                            class="font-bold text-stone-800 dark:text-white {{ $day['is_today'] ? 'text-orange-600 dark:text-orange-400' : '' }}">
                                            {{ $day['day'] }}
                                        </span>
                                        @if ($day['is_today'])
                                            <span
                                                class="text-xs px-2 py-0.5 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded-full">
                                                Hari Ini
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-stone-500 dark:text-stone-400">{{ $day['day_name'] }}</span>
                                </div>

                                <!-- Events for this day -->
                                <div class="space-y-1 max-h-20 overflow-y-auto">
                                    @foreach ($day['events'] as $event)
                                        <a href="{{ route('calendar.day', $day['date']) }}"
                                            class="block text-xs p-1.5 rounded truncate hover:opacity-90 transition-opacity"
                                            style="background-color: {{ $event['color'] }}20; color: {{ $event['color'] }}; border-left: 3px solid {{ $event['color'] }}"
                                            title="{{ $event['title'] }} ({{ $event['start_time'] }} - {{ $event['end_time'] }})">
                                            <div class="flex items-center">
                                                <div class="w-2 h-2 rounded-full mr-1"
                                                    style="background-color: {{ $event['color'] }}"></div>
                                                <span class="truncate">{{ $event['title'] }}</span>
                                            </div>
                                            <div class="text-[10px] opacity-75 mt-0.5">{{ $event['start_time'] }}</div>
                                        </a>
                                    @endforeach
                                </div>

                                <!-- Click to view day details -->
                                <div class="mt-2 pt-2 border-t border-stone-100 dark:border-stone-800">
                                    <a href="{{ route('calendar.day', $day['date']) }}"
                                        class="block w-full text-center text-xs px-2 py-1 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-600 dark:text-stone-300 rounded transition-colors">
                                        <i class="fa-solid fa-eye mr-1"></i>Lihat Jadwal
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Color Legend -->
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-4">Kode Warna</h3>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full bg-red-500 mr-3"></div>
                            <span class="text-sm text-stone-600 dark:text-stone-300">Deadline Penting</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full bg-blue-500 mr-3"></div>
                            <span class="text-sm text-stone-600 dark:text-stone-300">Akademik (UTS/UAS)</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full bg-orange-500 mr-3"></div>
                            <span class="text-sm text-stone-600 dark:text-stone-300">Proyek Kreatif</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full bg-emerald-500 mr-3"></div>
                            <span class="text-sm text-stone-600 dark:text-stone-300">PKL & Kerja</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full bg-purple-500 mr-3"></div>
                            <span class="text-sm text-stone-600 dark:text-stone-300">Skripsi</span>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Deadlines -->
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-4">Deadline Mendekat</h3>
                    <div class="space-y-3">
                        @foreach ($upcomingDeadlines as $deadline)
                            <a href="{{ route('calendar.day', $deadline['date']) }}"
                                class="block p-3 rounded-lg border border-stone-200 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800/50 transition-colors">
                                <div class="flex items-start">
                                    <div class="w-2 h-2 rounded-full mr-3 mt-1.5 flex-shrink-0"
                                        style="background-color: {{ $deadline['color'] }}"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-stone-800 dark:text-white truncate">
                                            {{ $deadline['title'] }}
                                        </div>
                                        <div class="flex items-center justify-between mt-1">
                                            <span class="text-xs text-stone-500 dark:text-stone-400">
                                                {{ $deadline['formatted_date'] }}
                                            </span>
                                            @if ($deadline['days_remaining'] >= 0)
                                                <span
                                                    class="text-xs px-2 py-1 rounded-full
                                        {{ $deadline['days_remaining'] < 3 ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' }}">
                                                    {{ $deadline['days_remaining'] }} hari lagi
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach

                        @if (count($upcomingDeadlines) == 0)
                            <div class="text-center py-4">
                                <p class="text-stone-500 dark:text-stone-400 text-sm">Tidak ada deadline mendekat</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recurring Schedule -->
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-stone-800 dark:text-white">Jadwal Rutin Mingguan</h3>
                <button type="button" onclick="showScheduleModal()"
                    class="px-3 py-1.5 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fa-solid fa-plus mr-1"></i>Tambah Jadwal
                </button>
            </div>

            @if (count($recurringSchedules) > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    @foreach ($recurringSchedules as $schedule)
                        <div
                            class="bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl p-4 text-center relative group">
                            <form method="POST" action="{{ route('calendar.schedules.destroy', $schedule['id']) }}"
                                class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus jadwal ini?')"
                                    class="w-6 h-6 flex items-center justify-center bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full hover:bg-red-200 dark:hover:bg-red-800/50">
                                    <i class="fa-solid fa-times text-xs"></i>
                                </button>
                            </form>

                            <div class="font-bold text-stone-800 dark:text-white text-lg mb-1">{{ $schedule['day'] }}
                            </div>
                            <div class="text-sm text-stone-600 dark:text-stone-300 mb-2 truncate"
                                title="{{ $schedule['activity'] }}">
                                {{ $schedule['activity'] }}
                            </div>
                            <div class="text-xs text-stone-500 dark:text-stone-400">{{ $schedule['time'] }}</div>
                            <div class="mt-2">
                                <span class="inline-block w-3 h-3 rounded-full"
                                    style="background-color: {{ $schedule['color'] }}"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-stone-400 dark:text-stone-600 mb-3">
                        <i class="fa-regular fa-calendar text-3xl"></i>
                    </div>
                    <p class="text-stone-500 dark:text-stone-400">Belum ada jadwal rutin</p>
                </div>
            @endif
        </div>

        <!-- Add Event Form -->
        <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
            <h3 class="font-bold text-stone-800 dark:text-white mb-4">Tambah Event Baru</h3>

            <form method="POST" action="{{ route('calendar.events.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Judul
                            Event</label>
                        <input type="text" name="title" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                            placeholder="Nama event">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tipe Event</label>
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
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tanggal</label>
                        <input type="date" name="date" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                            value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Mulai</label>
                            <input type="time" name="start_time" required
                                class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                                value="09:00">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Selesai</label>
                            <input type="time" name="end_time" required
                                class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                                value="12:00">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                        placeholder="Deskripsi event..."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_recurring" id="is_recurring" class="mr-2">
                        <label for="is_recurring" class="text-sm text-stone-700 dark:text-stone-300">Event
                            berulang</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Frekuensi</label>
                        <select name="recurring_frequency"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white">
                            <option value="weekly">Mingguan</option>
                            <option value="monthly">Bulanan</option>
                            <option value="daily">Harian</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">
                        <i class="fa-solid fa-calendar-plus mr-2"></i>Tambah Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal untuk Tambah Jadwal Rutin -->
    <div id="scheduleModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-stone-800 dark:text-white">Tambah Jadwal Rutin</h3>
                    <button onclick="hideScheduleModal()"
                        class="p-2 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-lg">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('calendar.schedules.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Hari</label>
                        <select name="day" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white">
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Aktivitas</label>
                        <input type="text" name="activity" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                            placeholder="Contoh: Kuliah Metodologi Penelitian">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tipe</label>
                            <select name="type" required
                                class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white">
                                <option value="academic">Akademik</option>
                                <option value="creative">Creative</option>
                                <option value="pkl">PKL</option>
                                <option value="personal">Personal</option>
                                <option value="routine">Routine</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Lokasi</label>
                            <input type="text" name="location"
                                class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                                placeholder="Contoh: Ruang 301">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Mulai</label>
                            <input type="time" name="start_time" required
                                class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                                value="08:00">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Selesai</label>
                            <input type="time" name="end_time" required
                                class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                                value="10:00">
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Instruktur/Dosen</label>
                        <input type="text" name="instructor"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-stone-800 dark:text-white"
                            placeholder="Nama instruktur/dosen">
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-stone-200 dark:border-stone-800">
                        <button type="button" onclick="hideScheduleModal()"
                            class="px-4 py-2 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 rounded-lg font-medium transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">
                            Tambah Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let scheduleModal = document.getElementById('scheduleModal');

            function showScheduleModal() {
                scheduleModal.classList.remove('hidden');
                scheduleModal.classList.add('flex');
            }

            function hideScheduleModal() {
                scheduleModal.classList.remove('flex');
                scheduleModal.classList.add('hidden');
            }

            // Close modal on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') hideScheduleModal();
            });

            // Close modal on background click
            scheduleModal.addEventListener('click', function(e) {
                if (e.target === scheduleModal) hideScheduleModal();
            });
        </script>
    @endpush
@endsection
