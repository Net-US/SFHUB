@extends('layouts.app')

@section('title', 'Kelola Jadwal - StudentHub')

@section('content')
    <div class="min-h-screen bg-stone-50 dark:bg-stone-950 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-stone-900 dark:text-white">Kelola Jadwal</h1>
                <p class="text-stone-500 dark:text-stone-400">Atur jadwal rutin, mata kuliah, ujian, dan aktivitas lainnya
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Schedule Form -->
                <div class="lg:col-span-1">
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800 sticky top-8">
                        <h2 class="font-bold text-stone-800 dark:text-white mb-6">
                            {{ $editingId ? 'Edit Jadwal' : 'Tambah Jadwal' }}
                        </h2>

                        @livewire('schedule-manager')
                    </div>
                </div>

                <!-- Schedule Display -->
                <div class="lg:col-span-3">
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="font-bold text-stone-800 dark:text-white">Jadwal Mingguan</h2>
                            <div class="flex space-x-2">
                                <button onclick="exportSchedule()"
                                    class="px-4 py-2 bg-stone-800 dark:bg-stone-700 text-white rounded-lg hover:bg-stone-900 dark:hover:bg-stone-600 transition-colors text-sm">
                                    <i class="fa-solid fa-download mr-2"></i>Export
                                </button>
                                <button onclick="importSchedule()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                    <i class="fa-solid fa-upload mr-2"></i>Import
                                </button>
                            </div>
                        </div>

                        <!-- Weekly Schedule -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-stone-200 dark:border-stone-800">
                                        <th class="text-left py-3 text-stone-600 dark:text-stone-400 font-medium">Hari</th>
                                        <th class="text-left py-3 text-stone-600 dark:text-stone-400 font-medium">Pagi
                                            (08-12)</th>
                                        <th class="text-left py-3 text-stone-600 dark:text-stone-400 font-medium">Siang
                                            (12-17)</th>
                                        <th class="text-left py-3 text-stone-600 dark:text-stone-400 font-medium">Malam
                                            (17-22)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                                        <tr class="border-b border-stone-100 dark:border-stone-800">
                                            <td class="py-4 font-bold text-stone-800 dark:text-white">
                                                {{ $day }}
                                                <button onclick="showAddForm('{{ $day }}')"
                                                    class="ml-2 text-xs text-blue-500 hover:text-blue-700">
                                                    <i class="fa-solid fa-plus"></i>
                                                </button>
                                            </td>
                                            @foreach (['morning', 'afternoon', 'evening'] as $period)
                                                <td class="py-4">
                                                    <div class="space-y-2">
                                                        @php
                                                            $periodTimes = [
                                                                'morning' => ['08:00', '12:00'],
                                                                'afternoon' => ['12:00', '17:00'],
                                                                'evening' => ['17:00', '22:00'],
                                                            ];
                                                        @endphp

                                                        @foreach ($schedules[$day] ?? [] as $schedule)
                                                            @if ($schedule['start_time'] >= $periodTimes[$period][0] && $schedule['start_time'] < $periodTimes[$period][1])
                                                                <div
                                                                    class="p-2 rounded-lg {{ $schedule->getTypeColor() }} text-xs">
                                                                    <div class="flex justify-between items-center">
                                                                        <span
                                                                            class="font-medium">{{ $schedule['activity'] }}</span>
                                                                        <div class="flex space-x-1">
                                                                            <button
                                                                                onclick="editSchedule({{ $schedule['id'] }})"
                                                                                class="text-blue-500 hover:text-blue-700">
                                                                                <i class="fa-solid fa-pen text-xs"></i>
                                                                            </button>
                                                                            <button
                                                                                onclick="deleteSchedule({{ $schedule['id'] }})"
                                                                                class="text-red-500 hover:text-red-700">
                                                                                <i class="fa-solid fa-trash text-xs"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-xs opacity-75 mt-1">
                                                                        {{ $schedule['start_time'] }} -
                                                                        {{ $schedule['end_time'] }}
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Legend -->
                        <div class="mt-8 pt-6 border-t border-stone-200 dark:border-stone-800">
                            <h3 class="font-bold text-stone-800 dark:text-white mb-4">Kode Warna</h3>
                            <div class="flex flex-wrap gap-3">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full bg-blue-500 mr-2"></div>
                                    <span class="text-sm text-stone-600 dark:text-stone-400">Akademik</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full bg-orange-500 mr-2"></div>
                                    <span class="text-sm text-stone-600 dark:text-stone-400">Kreatif</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full bg-emerald-500 mr-2"></div>
                                    <span class="text-sm text-stone-600 dark:text-stone-400">PKL/Kerja</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full bg-red-500 mr-2"></div>
                                    <span class="text-sm text-stone-600 dark:text-stone-400">Ujian</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full bg-purple-500 mr-2"></div>
                                    <span class="text-sm text-stone-600 dark:text-stone-400">Personal</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div
                            class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                            <div class="flex items-center">
                                <div
                                    class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 mr-4">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-stone-500 dark:text-stone-400">Total Jam/Minggu</p>
                                    <h3 class="text-2xl font-bold text-stone-800 dark:text-white">
                                        {{ $totalWeeklyHours ?? 0 }} jam
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                            <div class="flex items-center">
                                <div
                                    class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 dark:text-orange-400 mr-4">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-stone-500 dark:text-stone-400">Mata Kuliah</p>
                                    <h3 class="text-2xl font-bold text-stone-800 dark:text-white">
                                        {{ $courseCount ?? 0 }}
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                            <div class="flex items-center">
                                <div
                                    class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mr-4">
                                    <i class="fa-solid fa-briefcase"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-stone-500 dark:text-stone-400">Jam PKL/Minggu</p>
                                    <h3 class="text-2xl font-bold text-stone-800 dark:text-white">
                                        {{ $pklHours ?? 0 }} jam
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function showAddForm(day) {
                Livewire.emit('showAddForm', day);
            }

            function editSchedule(id) {
                Livewire.emit('editSchedule', id);
            }

            function deleteSchedule(id) {
                if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
                    Livewire.emit('deleteSchedule', id);
                }
            }

            function exportSchedule() {
                // Implement export functionality
                alert('Fitur export akan segera tersedia!');
            }

            function importSchedule() {
                // Implement import functionality
                alert('Fitur import akan segera tersedia!');
            }
        </script>
    @endpush
@endsection
