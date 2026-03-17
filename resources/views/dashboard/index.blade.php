<!-- resources/views/dashboard/index.blade.php -->
@extends('layouts.app-dashboard')

@section('title', 'Dashboard - StudentHub')

@push('styles')
    <style>
        /* Custom styles from your template */
        .hero-gradient {
            background: linear-gradient(rgba(255, 247, 237, 0.95), rgba(245, 245, 244, 0.9));
            background-size: cover;
            background-position: center;
        }

        .dark .hero-gradient {
            background: linear-gradient(rgba(28, 25, 23, 0.9), rgba(12, 10, 9, 0.95));
        }

        .floating-card {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .hover-lift {
            transition: transform 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-8px);
        }

        /* Priority colors */
        .priority-urgent-important {
            border-left-color: #ef4444 !important;
            background-color: rgba(239, 68, 68, 0.05) !important;
        }

        .priority-important-not-urgent {
            border-left-color: #3b82f6 !important;
            background-color: rgba(59, 130, 246, 0.05) !important;
        }

        .priority-urgent-not-important {
            border-left-color: #f97316 !important;
            background-color: rgba(249, 115, 22, 0.05) !important;
        }

        .priority-not-urgent-not-important {
            border-left-color: #6b7280 !important;
            background-color: rgba(107, 114, 128, 0.05) !important;
        }

        /* Schedule colors */
        .schedule-academic {
            background-color: rgba(59, 130, 246, 0.05) !important;
            border-left-color: #3b82f6 !important;
        }

        .schedule-creative {
            background-color: rgba(249, 115, 22, 0.05) !important;
            border-left-color: #f97316 !important;
        }

        .schedule-pkl {
            background-color: rgba(16, 185, 129, 0.05) !important;
            border-left-color: #10b981 !important;
        }

        .schedule-exam {
            background-color: rgba(239, 68, 68, 0.05) !important;
            border-left-color: #ef4444 !important;
        }

        .schedule-personal {
            background-color: rgba(139, 92, 246, 0.05) !important;
            border-left-color: #8b5cf6 !important;
        }

        .schedule-routine {
            background-color: rgba(107, 114, 128, 0.05) !important;
            border-left-color: #6b7280 !important;
        }

        .time-block {
            transition: all 0.3s ease;
        }

        .time-block:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
@endpush

@section('content')
    <!-- Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Welcome Card with Priority Engine -->
            <!-- Welcome Card with Priority Engine -->
            <div
                class="bg-gradient-to-br from-stone-800 to-stone-900 dark:from-stone-800 dark:to-black text-white rounded-3xl p-6 shadow-xl relative overflow-hidden">
                <!-- Decorations -->
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-orange-500 rounded-full mix-blend-overlay filter blur-3xl opacity-20 transform translate-x-1/2 -translate-y-1/2">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-40 h-40 bg-blue-500 rounded-full mix-blend-overlay filter blur-3xl opacity-20 transform -translate-x-1/2 translate-y-1/2">
                </div>

                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-medium">Mode:
                                    {{ ucfirst(auth()->user()->role ?? 'student_freelancer') }}</span>
                                <span id="current-schedule-badge"
                                    class="bg-{{ isset($currentActivity['type'])
                                        ? ($currentActivity['type'] == 'pkl'
                                            ? 'emerald'
                                            : ($currentActivity['type'] == 'academic'
                                                ? 'blue'
                                                : ($currentActivity['type'] == 'creative'
                                                    ? 'orange'
                                                    : ($currentActivity['type'] == 'break'
                                                        ? 'yellow'
                                                        : 'gray'))))
                                        : 'gray' }}-500 backdrop-blur-md px-3 py-1 rounded-full text-xs font-medium">
                                    <i class="fa-solid fa-clock mr-1"></i>
                                    @if (isset($currentActivity['title']))
                                        {{ $currentActivity['title'] }}
                                    @else
                                        {{ $timeOfDay }}
                                    @endif
                                </span>
                            </div>
                            <h2 class="text-3xl font-bold mt-2 mb-2">Selamat {{ $timeOfDay }},
                                {{ auth()->user()->name }}!</h2>
                            <p class="text-stone-300">
                                @if ($currentSchedule)
                                    {{ $currentSchedule->location ? '📍 ' . $currentSchedule->location : '' }}
                                    {{ $currentSchedule->instructor ? '👤 ' . $currentSchedule->instructor : '' }}
                                @else
                                    {{ $currentActivity['recommendation'] ?? 'Rekomendasi sistem berdasarkan jadwal dan deadline Anda' }}
                                @endif
                            </p>
                        </div>
                        <div class="bg-white/10 p-3 rounded-full backdrop-blur-sm">
                            <i class="fa-solid fa-bolt text-2xl text-yellow-400"></i>
                        </div>
                    </div>

                    <!-- Current Schedule (from database) -->
                    @if ($currentSchedule)
                        <div id="current-schedule" class="mb-6">
                            <div
                                class="mb-4 p-4 rounded-xl bg-{{ $currentSchedule->type == 'pkl'
                                    ? 'emerald'
                                    : ($currentSchedule->type == 'academic'
                                        ? 'blue'
                                        : ($currentSchedule->type == 'creative'
                                            ? 'orange'
                                            : 'gray')) }}-500 border border-{{ $currentSchedule->type == 'pkl'
                                    ? 'emerald'
                                    : ($currentSchedule->type == 'academic'
                                        ? 'blue'
                                        : ($currentSchedule->type == 'creative'
                                            ? 'orange'
                                            : 'gray')) }}-400">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="font-bold text-white">{{ $currentSchedule->activity }}</h3>
                                        <p class="text-white/80 text-sm">
                                            <i
                                                class="fa-solid fa-clock mr-1"></i>{{ $currentSchedule->start_time->format('H:i') }}
                                            - {{ $currentSchedule->end_time->format('H:i') }}
                                            @if ($currentSchedule->location)
                                                • <i
                                                    class="fa-solid fa-location-dot ml-2 mr-1"></i>{{ $currentSchedule->location }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-white">{{ $currentActivity['time_remaining'] }}</p>
                                        <p class="text-white/80 text-xs">Jadwal rutin</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($currentActivity['is_scheduled'] == false)
                        <!-- Waktu bebas (tidak ada jadwal di database) -->
                        <div id="current-schedule" class="mb-6">
                            <div
                                class="mb-4 p-4 rounded-xl
                                    @if ($hour >= 0 && $hour < 5) bg-indigo-500 border-indigo-400
                                    @elseif($hour >= 5 && $hour < 12) bg-blue-500 border-blue-400
                                    @elseif($hour >= 12 && $hour < 17) bg-orange-500 border-orange-400
                                    @else bg-gray-500 border-gray-400 @endif">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="font-bold text-white">{{ $currentActivity['title'] }}</h3>
                                        <p class="text-white/80 text-sm">
                                            <i class="fa-solid fa-clock mr-1"></i>{{ sprintf('%02d:00', $hour) }}
                                            • {{ $currentActivity['recommendation'] }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-white">
                                            @if ($hour >= 0 && $hour < 5)
                                                💤 Waktu Tidur
                                            @elseif($hour >= 5 && $hour < 8)
                                                🌅 Persiapan Pagi
                                            @elseif($hour >= 12 && $hour < 13)
                                                🍽️ Istirahat Siang
                                            @elseif($hour >= 17 && $hour < 19)
                                                🏃 Waktu Bebas
                                            @elseif($hour >= 19)
                                                🌙 Waktu Produktif
                                            @else
                                                ⏳ Waktu Kosong
                                            @endif
                                        </p>
                                        <p class="text-white/80 text-xs">
                                            @if ($isSimulation)
                                                Simulasi: {{ $hour }}:00
                                            @else
                                                Waktu Nyata
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <!-- Smart Recommendations -->
                    <!-- Smart Recommendations -->
                    <div class="bg-white/10 border border-white/10 rounded-2xl p-4 backdrop-blur-md">
                        <h3 class="text-sm font-bold text-orange-300 uppercase mb-3">Rekomendasi Sistem (AI Priority)</h3>
                        <div id="priority-recommendations" class="space-y-3">
                            @if (isset($smartRecommendations['recommendations']) && count($smartRecommendations['recommendations']) > 0)
                                @foreach ($smartRecommendations['recommendations'] as $index => $recommendation)
                                    @php
                                        // Tentukan icon dan warna berdasarkan jenis rekomendasi
                                        $icon = match ($recommendation['type']) {
                                            'overdue_alert' => '🚨',
                                            'deadline_today' => '🔥',
                                            'deadline_tomorrow' => '⏰',
                                            'late_night' => '💤',
                                            'early_morning' => '🌅',
                                            'morning_preparation' => '☕',
                                            'lunch_break' => '🍽️',
                                            'productive_night' => '🌙',
                                            'wind_down' => '😴',
                                            default => '📝',
                                        };

                                        $color = match ($recommendation['priority'] ?? 'normal') {
                                            'critical' => 'text-red-400',
                                            'high' => 'text-orange-400',
                                            'medium' => 'text-yellow-400',
                                            'low' => 'text-blue-400',
                                            default => 'text-gray-400',
                                        };

                                        $bgColor = match ($recommendation['priority'] ?? 'normal') {
                                            'critical' => 'bg-red-500/20 border-red-500/30',
                                            'high' => 'bg-orange-500/20 border-orange-500/30',
                                            'medium' => 'bg-yellow-500/20 border-yellow-500/30',
                                            'low' => 'bg-blue-500/20 border-blue-500/30',
                                            default => 'bg-gray-500/20 border-gray-500/30',
                                        };
                                    @endphp

                                    <div class="p-3 {{ $bgColor }} border rounded-lg">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex items-center">
                                                <span class="text-lg mr-2">{{ $icon }}</span>
                                                <h4 class="font-medium text-white text-sm">{{ $recommendation['title'] }}
                                                </h4>
                                            </div>
                                            @if (isset($recommendation['time_left']))
                                                <span class="text-xs px-2 py-0.5 bg-white/10 rounded">
                                                    {{ $recommendation['time_left'] }}
                                                </span>
                                            @endif
                                        </div>

                                        <p class="text-xs text-stone-300 mb-2">{{ $recommendation['message'] }}</p>

                                        @if (isset($recommendation['suggestion']))
                                            <p class="text-xs text-orange-300 mb-2">
                                                <i
                                                    class="fa-solid fa-lightbulb mr-1"></i>{{ $recommendation['suggestion'] }}
                                            </p>
                                        @endif

                                        <!-- Tampilkan tasks jika ada -->
                                        @if (isset($recommendation['tasks']) && count($recommendation['tasks']) > 0)
                                            <div class="space-y-2 mt-2">
                                                @foreach ($recommendation['tasks'] as $task)
                                                    <div class="flex items-center justify-between p-2 bg-black/20 rounded">
                                                        <div>
                                                            <p class="text-xs text-white">{{ $task['title'] }}</p>
                                                            <p class="text-[10px] text-stone-400">
                                                                Deadline:
                                                                {{ \Carbon\Carbon::parse($task['due_date'])->translatedFormat('d M') }}
                                                                •
                                                                {{ $task['urgency_level'] == 'critical' ? 'TERLAMBAT ' . abs($task['days_until_due']) . ' HARI' : $task['days_until_due'] . ' hari lagi' }}
                                                            </p>
                                                        </div>
                                                        <button onclick="startTask({{ $task['id'] }})"
                                                            class="text-xs px-2 py-1 bg-orange-500 hover:bg-orange-600 rounded">
                                                            Kerjakan
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Tampilkan ideal tasks jika ada -->
                                        @if (isset($recommendation['ideal_tasks']) && count($recommendation['ideal_tasks']) > 0)
                                            <div class="mt-2">
                                                <p class="text-xs text-stone-400 mb-1">Tugas yang cocok:</p>
                                                <div class="space-y-1">
                                                    @foreach ($recommendation['ideal_tasks'] as $task)
                                                        <div class="flex items-center justify-between text-xs">
                                                            <span class="text-stone-300">{{ $task['title'] }}</span>
                                                            <button onclick="startTask({{ $task['id'] }})"
                                                                class="text-[10px] px-2 py-0.5 bg-orange-500/30 hover:bg-orange-500/50 rounded">
                                                                Mulai
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Time slots untuk perencanaan -->
                                        @if (isset($recommendation['time_slots']))
                                            <div class="mt-2">
                                                <p class="text-xs text-stone-400 mb-1">Saran pembagian waktu:</p>
                                                <div class="space-y-1">
                                                    @foreach ($recommendation['time_slots'] as $slot)
                                                        <div class="flex items-center text-xs">
                                                            <span class="w-16 text-orange-300">{{ $slot['time'] }}</span>
                                                            <span class="text-stone-300">: {{ $slot['activity'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <!-- Fallback jika tidak ada rekomendasi -->
                                <div class="p-3 bg-white/5 border border-white/5 rounded-lg">
                                    <div class="flex items-center mb-2">
                                        <span class="text-lg mr-2">📝</span>
                                        <h4 class="font-medium text-white text-sm">Tidak ada rekomendasi spesifik</h4>
                                    </div>
                                    <p class="text-xs text-stone-300 mb-2">
                                        Cek tugas yang deadline-nya paling dekat atau tambahkan jadwal untuk mendapatkan
                                        rekomendasi yang lebih spesifik.
                                    </p>
                                    <div class="flex gap-2">
                                        <button onclick="showAddTaskModal()"
                                            class="flex-1 py-1 bg-orange-500 hover:bg-orange-600 text-white rounded text-xs">
                                            Tambah Tugas
                                        </button>
                                        <button onclick="showAddScheduleModal()"
                                            class="flex-1 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs">
                                            Tambah Jadwal
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Time Slider Simulation (untuk testing) -->
                    <div class="mt-6">
                        <label class="text-xs text-stone-400 mb-1 block">
                            @if ($isSimulation)
                                ⚡ SIMULASI WAKTU: {{ sprintf('%02d:00', $hour) }}
                            @else
                                ⌚ WAKTU NYATA: {{ now()->format('H:i') }}
                            @endif
                        </label>
                        <form id="time-simulation-form" method="GET" action="{{ url()->current() }}">
                            <input type="range" id="time-slider" name="simulated_hour" min="0" max="23"
                                value="{{ $isSimulation ? $hour : date('H') }}"
                                class="w-full h-2 bg-white/20 rounded-lg appearance-none cursor-pointer accent-orange-500"
                                oninput="updateTimeDisplay(this.value)">
                            <div class="flex justify-between text-[10px] text-stone-500 mt-1">
                                <span>00:00</span>
                                <span>06:00</span>
                                <span>12:00</span>
                                <span>18:00</span>
                                <span>23:00</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <div class="text-xs text-stone-300">
                                    <i class="fa-solid fa-clock mr-1"></i>
                                    <span
                                        id="selected-time">{{ sprintf('%02d:00', $isSimulation ? $hour : date('H')) }}</span>
                                    <span
                                        class="ml-2 px-2 py-0.5 rounded text-xs
                    @if ($isSimulation) bg-orange-500/30 text-orange-300
                    @else bg-emerald-500/30 text-emerald-300 @endif">
                                        @if ($isSimulation)
                                            ⚡ Simulasi
                                        @else
                                            ✅ Waktu Nyata
                                        @endif
                                    </span>
                                </div>
                                <div class="flex gap-2">
                                    @if ($isSimulation)
                                        <a href="{{ url()->current() }}"
                                            class="text-xs px-3 py-1 bg-stone-700 hover:bg-stone-600 rounded transition-colors">
                                            Reset ke Waktu Nyata
                                        </a>
                                    @endif
                                    <button type="submit"
                                        class="text-xs px-3 py-1 bg-orange-500 hover:bg-orange-600 rounded transition-colors">
                                        @if ($isSimulation)
                                            Update Simulasi
                                        @else
                                            Mulai Simulasi
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="mt-1 text-[10px] text-stone-500">
                            <i class="fa-solid fa-circle-info mr-1"></i>
                            Geser slider untuk melihat bagaimana sistem bereaksi pada jam berbeda
                        </div>
                    </div>

                    <script>
                        function updateTimeDisplay(value) {
                            const hour = String(value).padStart(2, '0');
                            document.getElementById('selected-time').textContent = hour + ':00';

                            // Update label juga
                            const label = document.querySelector('label.text-xs');
                            if (label) {
                                label.innerHTML = `⚡ SIMULASI WAKTU: ${hour}:00`;
                            }
                        }
                    </script>
                </div>
            </div>

            <script>
                function updateTimeDisplay(value) {
                    document.getElementById('selected-time').textContent =
                        String(value).padStart(2, '0') + ':00';
                }

                function resetToRealTime() {
                    document.getElementById('time-slider').value = {{ date('H') }};
                    updateTimeDisplay({{ date('H') }});
                    document.getElementById('time-simulation-form').submit();
                }
            </script>
        </div>


        <!-- Sidebar (1/3 width) -->
        <div class="space-y-6">
            <!-- Priority Summary -->
            <div
                class="bg-white dark:bg-stone-900 rounded-3xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-stone-900 dark:text-white">Prioritas Hari Ini</h3>
                    <span class="text-xs bg-stone-100 dark:bg-stone-800 px-2 py-1 rounded text-stone-500">
                        {{ $prioritySummary['total_tasks'] ?? 0 }} tugas diprioritaskan
                    </span>
                </div>

                <div class="space-y-3">
                    <!-- Urgent & Important (Do First) -->
                    @if (isset($prioritySummary['urgent_important']) && count($prioritySummary['urgent_important']) > 0)
                        @foreach ($prioritySummary['urgent_important'] as $task)
                            <div
                                class="p-3 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-r-xl priority-urgent-important">
                                <span class="text-[10px] font-bold text-red-600 dark:text-red-400 uppercase">Do
                                    First</span>
                                <p class="font-medium text-stone-800 dark:text-stone-200 text-sm mt-1">{{ $task->title }}
                                </p>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-xs text-stone-500 dark:text-stone-400">
                                        @if ($task->due_date)
                                            Deadline: {{ \Carbon\Carbon::parse($task->due_date)->translatedFormat('d M') }}
                                            @if ($task->due_date < now())
                                                <span class="text-red-500 font-bold"> (TERLAMBAT)</span>
                                            @endif
                                        @endif
                                    </span>
                                    @if ($task->estimated_time)
                                        <span class="text-xs px-2 py-0.5 bg-red-100 dark:bg-red-900/30 rounded">
                                            {{ $task->estimated_time }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="p-3 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-r-xl">
                            <span class="text-[10px] font-bold text-red-600 dark:text-red-400 uppercase">Do First</span>
                            <p class="font-medium text-stone-800 dark:text-stone-200 text-sm mt-1">Tidak ada tugas urgent
                            </p>
                            <span class="text-xs text-stone-500 dark:text-stone-400">Semua tugas terkendali</span>
                        </div>
                    @endif

                    <!-- Important Not Urgent (Schedule) -->
                    @if (isset($prioritySummary['important_not_urgent']) && count($prioritySummary['important_not_urgent']) > 0)
                        @foreach ($prioritySummary['important_not_urgent'] as $task)
                            <div
                                class="p-3 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 rounded-r-xl priority-important-not-urgent">
                                <span
                                    class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase">Schedule</span>
                                <p class="font-medium text-stone-800 dark:text-stone-200 text-sm mt-1">{{ $task->title }}
                                </p>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-xs text-stone-500 dark:text-stone-400">
                                        @if ($task->due_date)
                                            Deadline: {{ \Carbon\Carbon::parse($task->due_date)->translatedFormat('d M') }}
                                            ({{ \Carbon\Carbon::parse($task->due_date)->diffForHumans() }})
                                        @endif
                                    </span>
                                    @if ($task->estimated_time)
                                        <span class="text-xs px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 rounded">
                                            {{ $task->estimated_time }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 rounded-r-xl">
                            <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase">Schedule</span>
                            <p class="font-medium text-stone-800 dark:text-stone-200 text-sm mt-1">Tidak ada tugas penting
                            </p>
                            <span class="text-xs text-stone-500 dark:text-stone-400">Tambahkan tugas dengan deadline minggu
                                depan</span>
                        </div>
                    @endif

                    <!-- Routine Tasks -->
                    @if (isset($prioritySummary['routine']) && count($prioritySummary['routine']) > 0)
                        @foreach ($prioritySummary['routine'] as $task)
                            <div class="p-3 bg-stone-50 dark:bg-stone-800 border-l-4 border-stone-400 rounded-r-xl">
                                <span
                                    class="text-[10px] font-bold text-stone-500 dark:text-stone-400 uppercase">Routine</span>
                                <p class="font-medium text-stone-800 dark:text-stone-200 text-sm mt-1">{{ $task->title }}
                                </p>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-xs text-stone-500 dark:text-stone-400 capitalize">
                                        {{ $task->category }}
                                    </span>
                                    @if ($task->estimated_time)
                                        <span class="text-xs px-2 py-0.5 bg-stone-100 dark:bg-stone-700 rounded">
                                            {{ $task->estimated_time }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <!-- Jika tidak ada data sama sekali -->
                    @if (
                        (!isset($prioritySummary['urgent_important']) || count($prioritySummary['urgent_important']) == 0) &&
                            (!isset($prioritySummary['important_not_urgent']) || count($prioritySummary['important_not_urgent']) == 0))
                        <div class="text-center py-4 text-stone-500 dark:text-stone-400">
                            <i class="fa-solid fa-tasks text-xl mb-2 opacity-50"></i>
                            <p class="text-sm">Belum ada tugas yang diprioritaskan</p>
                            <button onclick="showAddTaskModal()"
                                class="mt-2 text-sm text-orange-500 hover:text-orange-600">
                                <i class="fa-solid fa-plus mr-1"></i>Tambah tugas pertama
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Progress Bar Overall -->
                @if (isset($prioritySummary['total_tasks']) && $prioritySummary['total_tasks'] > 0)
                    <div class="mt-4 pt-4 border-t border-stone-200 dark:border-stone-800">
                        <div class="flex justify-between text-xs text-stone-500 dark:text-stone-400 mb-1">
                            <span>Progress Prioritas</span>
                            <span>{{ $prioritySummary['completed_tasks'] ?? 0 }}/{{ $prioritySummary['total_tasks'] ?? 0 }}</span>
                        </div>
                        <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2">
                            <div class="bg-gradient-to-r from-red-500 via-orange-500 to-emerald-500 h-2 rounded-full"
                                style="width: {{ min(100, (($prioritySummary['completed_tasks'] ?? 0) / max(1, $prioritySummary['total_tasks'])) * 100) }}%">
                            </div>
                        </div>
                        <div class="flex justify-between text-[10px] text-stone-400 dark:text-stone-500 mt-1">
                            <span>Urgent</span>
                            <span>Important</span>
                            <span>Selesai</span>
                        </div>
                    </div>
                @endif
            </div>
            <!-- Quick Stats -->
            <div
                class="bg-white dark:bg-stone-900 rounded-3xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white mb-4">Statistik Cepat</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-orange-50 dark:bg-orange-900/20 rounded-xl hover-lift">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-400 mb-1">
                            {{ $stats['tasks']['pending'] ?? 0 }}
                        </div>
                        <p class="text-xs text-orange-700 dark:text-orange-300">Task Pending</p>
                    </div>

                    <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl hover-lift">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                            {{ $stats['tasks']['overdue'] ?? 0 }}
                        </div>
                        <p class="text-xs text-blue-700 dark:text-blue-300">Task Terlambat</p>
                    </div>

                    <div class="text-center p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl hover-lift">
                        <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mb-1">
                            {{ $stats['creative']['active_projects'] ?? 0 }}
                        </div>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300">Proyek Aktif</p>
                    </div>

                    <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl hover-lift">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-1">
                            {{ $stats['events']['today'] ?? 0 }}
                        </div>
                        <p class="text-xs text-purple-700 dark:text-purple-300">Event Hari Ini</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Schedule Timeline -->
        <div
            class="lg:col-span-3 bg-white dark:bg-stone-900 rounded-3xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white">Jadwal (Time Blocking)</h3>
                <span class="text-xs bg-stone-100 dark:bg-stone-800 px-2 py-1 rounded text-stone-500">
                    {{ \Carbon\Carbon::now()->translatedFormat('l') }}
                    @if (count($timeBlockingData['blocks']) > 0)
                        • {{ count($timeBlockingData['blocks']) }} aktivitas
                    @endif
                </span>
            </div>

            <!-- Dynamic Time Blocks -->
            <div class="h-12 flex w-full rounded-full overflow-hidden text-xs font-medium text-white shadow-inner">
                @foreach ($timeBlockingData['blocks'] as $block)
                    @php
                        $bgColor = match ($block['color']) {
                            'emerald' => 'bg-emerald-500',
                            'blue' => 'bg-blue-500',
                            'orange' => 'bg-orange-500',
                            'purple' => 'bg-purple-500',
                            'stone' => 'bg-stone-400',
                            'gray' => 'bg-gray-500',
                            default => 'bg-gray-500',
                        };

                        $hoverColor = match ($block['color']) {
                            'emerald' => 'hover:bg-emerald-600',
                            'blue' => 'hover:bg-blue-600',
                            'orange' => 'hover:bg-orange-600',
                            'purple' => 'hover:bg-purple-600',
                            'stone' => 'hover:bg-stone-500',
                            'gray' => 'hover:bg-gray-600',
                            default => 'hover:bg-gray-600',
                        };

                        $isNow = $block['is_now'] ?? false;
                        $borderClass = $isNow ? 'ring-2 ring-white ring-opacity-50' : '';
                    @endphp

                    <div class="{{ $bgColor }} {{ $hoverColor }} {{ $borderClass }} flex items-center justify-center transition-colors cursor-pointer time-block"
                        style="width: {{ $block['percentage'] }}%"
                        title="{{ $block['title'] }} ({{ $block['start_time'] }}-{{ $block['end_time'] }}) - {{ $block['description'] }}"
                        onclick="showScheduleDetails('{{ $block['title'] }}', '{{ $block['start_time'] }}', '{{ $block['end_time'] }}', '{{ $block['description'] }}')">

                        @if ($isNow)
                            <div class="animate-pulse">
                                {{ $block['title'] }}
                            </div>
                        @else
                            {{ $block['title'] }}
                        @endif

                        @if ($block['is_scheduled'])
                            <span class="ml-1 text-[8px] opacity-75">✓</span>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Time markers -->
            <div class="flex justify-between text-xs text-stone-400 mt-2 px-1">
                <span>{{ $timeBlockingData['day_start'] }}</span>
                @php
                    // Tampilkan 3-4 marker waktu penting
                    $totalHours =
                        (strtotime($timeBlockingData['day_end']) - strtotime($timeBlockingData['day_start'])) / 3600;
                    $markerCount = min(5, $totalHours);
                    $markerInterval = $totalHours / ($markerCount - 1);
                @endphp

                @for ($i = 1; $i < $markerCount - 1; $i++)
                    @php
                        $markerTime = strtotime($timeBlockingData['day_start']) + $i * $markerInterval * 3600;
                    @endphp
                    <span>{{ date('H:i', $markerTime) }}</span>
                @endfor
                <span>{{ $timeBlockingData['day_end'] }}</span>
            </div>

            <!-- Today's Schedule Details -->
            <div class="mt-6 space-y-3" id="today-schedule">
                @foreach ($todaySchedule as $schedule)
                    <div class="p-4 border-l-4 schedule-{{ $schedule['type'] }} rounded-r-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <i
                                        class="fa-solid {{ $schedule['icon'] }} text-{{ $schedule['type'] == 'academic' ? 'blue' : ($schedule['type'] == 'pkl' ? 'emerald' : 'orange') }}-500"></i>
                                    <h4 class="font-bold text-stone-800 dark:text-white">
                                        {{ $schedule['activity'] }}</h4>
                                    @if ($schedule['is_now'])
                                        <span
                                            class="px-2 py-0.5 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded-full text-xs font-medium">
                                            <i class="fa-solid fa-clock mr-1"></i>Sekarang
                                        </span>
                                    @endif
                                </div>
                                @if ($schedule['location'])
                                    <p class="text-sm text-stone-600 dark:text-stone-400">
                                        <i class="fa-solid fa-location-dot mr-1"></i>{{ $schedule['location'] }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-stone-800 dark:text-white">
                                    {{ $schedule['start_time'] }} - {{ $schedule['end_time'] }}</p>
                                <p class="text-xs text-stone-500 dark:text-stone-400 capitalize">
                                    {{ $schedule['type'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if ($todaySchedule->isEmpty())
                    <div class="text-center py-8 text-stone-500 dark:text-stone-400">
                        <i class="fa-solid fa-calendar-plus text-3xl mb-2 opacity-50"></i>
                        <p>Tidak ada jadwal untuk hari ini.</p>
                        <button onclick="showAddScheduleModal()" class="mt-3 text-orange-500 hover:text-orange-600">
                            <i class="fa-solid fa-plus mr-1"></i>Tambah jadwal
                        </button>
                    </div>
                @endif
            </div>
        </div>
        <!-- Upcoming Deadlines -->
        <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
            <h3 class="text-lg font-bold text-stone-900 dark:text-white mb-4">Deadline Mendatang</h3>

            <div class="space-y-3" id="upcoming-deadlines">
                @foreach ($upcomingDeadlines as $task)
                    <div
                        class="p-3 border border-stone-200 dark:border-stone-700 rounded-lg hover:shadow-sm transition-shadow">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-medium text-stone-800 dark:text-white text-sm">{{ $task->title }}
                            </h4>
                            <span
                                class="text-xs px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                {{ \Carbon\Carbon::parse($task->due_date)->diffForHumans() }}
                            </span>
                        </div>
                        <div class="flex items-center text-xs text-stone-500 dark:text-stone-400">
                            <i class="fa-solid fa-tag mr-1"></i>
                            {{ $task->category }}
                        </div>
                    </div>
                @endforeach

                @if ($upcomingDeadlines->isEmpty())
                    <div class="text-center py-4 text-stone-500 dark:text-stone-400">
                        <i class="fa-solid fa-calendar-check text-xl mb-2 opacity-50"></i>
                        <p class="text-sm">Tidak ada deadline mendatang</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Active Projects -->
        <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
            <h3 class="text-lg font-bold text-stone-900 dark:text-white mb-4">Proyek Aktif</h3>

            <div class="space-y-4" id="active-projects">
                @foreach ($activeProjects as $project)
                    <div
                        class="p-3 border border-stone-200 dark:border-stone-700 rounded-lg hover:shadow-sm transition-shadow">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-medium text-stone-800 dark:text-white">{{ $project->title }}</h4>
                            <span
                                class="text-xs px-2 py-0.5 rounded-full {{ $project->stage == 'editing'
                                    ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300'
                                    : ($project->stage == 'script'
                                        ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300'
                                        : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300') }}">
                                {{ $project->stage }}
                            </span>
                        </div>

                        <div class="mb-2">
                            <div class="flex justify-between text-xs text-stone-500 dark:text-stone-400 mb-1">
                                <span>Progress</span>
                                <span>{{ $project->progress }}%</span>
                            </div>
                            <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2">
                                <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $project->progress }}%">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between text-xs text-stone-500 dark:text-stone-400">
                            <span>
                                <i class="fa-solid fa-calendar-day mr-1"></i>
                                {{ \Carbon\Carbon::parse($project->deadline)->translatedFormat('d M') }}
                            </span>
                            @if ($project->client)
                                <span>
                                    <i class="fa-solid fa-user mr-1"></i>
                                    {{ $project->client }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if ($activeProjects->isEmpty())
                    <div class="text-center py-4 text-stone-500 dark:text-stone-400">
                        <i class="fa-solid fa-folder-open text-xl mb-2 opacity-50"></i>
                        <p class="text-sm">Tidak ada proyek aktif</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
            <h3 class="text-lg font-bold text-stone-900 dark:text-white mb-4">Aksi Cepat</h3>

            <div class="grid grid-cols-2 gap-3">
                <button onclick="showAddTaskModal()"
                    class="p-3 bg-orange-50 dark:bg-orange-900/20 hover:bg-orange-100 dark:hover:bg-orange-900/30 rounded-lg transition-colors hover-lift">
                    <i class="fa-solid fa-plus text-orange-600 dark:text-orange-400 mb-1 text-lg"></i>
                    <p class="text-xs font-medium text-orange-800 dark:text-orange-300">Tambah Task</p>
                </button>

                <button onclick="showAddScheduleModal()"
                    class="p-3 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors hover-lift">
                    <i class="fa-solid fa-calendar-plus text-blue-600 dark:text-blue-400 mb-1 text-lg"></i>
                    <p class="text-xs font-medium text-blue-800 dark:text-blue-300">Tambah Jadwal</p>
                </button>

                <button onclick="window.location.href='/'"
                    class="p-3 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 rounded-lg transition-colors hover-lift">
                    <i class="fa-solid fa-palette text-emerald-600 dark:text-emerald-400 mb-1 text-lg"></i>
                    <p class="text-xs font-medium text-emerald-800 dark:text-emerald-300">Creative Studio</p>
                </button>

                <button onclick="window.location.href='/'"
                    class="p-3 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 rounded-lg transition-colors hover-lift">
                    <i class="fa-solid fa-wallet text-purple-600 dark:text-purple-400 mb-1 text-lg"></i>
                    <p class="text-xs font-medium text-purple-800 dark:text-purple-300">Finance Manager</p>
                </button>
            </div>
        </div>

        <!-- JavaScript untuk show schedule details -->
        <script>
            function showScheduleDetails(title, startTime, endTime, description) {
                // Buat modal atau tooltip dengan detail schedule
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                modal.innerHTML = `
        <div class="bg-white dark:bg-stone-800 rounded-xl p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white">${title}</h3>
                <button onclick="this.parentElement.parentElement.remove()"
                        class="text-stone-400 hover:text-stone-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="space-y-3">
                <div class="flex items-center text-sm">
                    <i class="fa-solid fa-clock mr-2 text-orange-500"></i>
                    <span class="font-medium">${startTime} - ${endTime}</span>
                </div>
                <div class="text-sm text-stone-600 dark:text-stone-300">
                    <i class="fa-solid fa-info-circle mr-2 text-blue-500"></i>
                    ${description}
                </div>
                <div class="pt-4 border-t border-stone-200 dark:border-stone-700">
                    <button onclick="addToCalendar('${title}', '${startTime}', '${endTime}')"
                            class="w-full py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium">
                        <i class="fa-solid fa-calendar-plus mr-2"></i>Tambah ke Kalender
                    </button>
                </div>
            </div>
        </div>
    `;
                document.body.appendChild(modal);
            }

            function addToCalendar(title, startTime, endTime) {
                // Logic untuk menambahkan ke kalender
                alert(`"${title}" berhasil ditambahkan ke kalender!`);
                document.querySelector('.fixed.inset-0').remove();
            }
        </script>
    </div>
@endsection

@push('scripts')
    <script>
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initDashboard();
            loadPriorityRecommendations();
        });

        function initDashboard() {
            // Time simulation
            initTimeSimulation();

            // Update clock display
            updateClockDisplay();
        }

        function loadPriorityRecommendations() {
            // In a real app, this would fetch from API
            // For now, we'll use the static data from the template
            console.log('Loading priority recommendations...');
        }

        function updateClockDisplay() {
            const timeInput = document.getElementById('time-simulation');
            const clockDisplay = document.querySelector('#current-schedule-badge');
            if (timeInput && clockDisplay) {
                const time = timeInput.value;
                const hour = parseInt(time.split(':')[0]);

                // Update schedule badge based on time
                if (hour >= 8 && hour < 12) {
                    clockDisplay.innerHTML = '<i class="fa-solid fa-clock mr-1"></i>PKL Half Day';
                    clockDisplay.className = 'bg-emerald-500 backdrop-blur-md px-3 py-1 rounded-full text-xs font-medium';
                } else if (hour >= 13 && hour < 17) {
                    clockDisplay.innerHTML = '<i class="fa-solid fa-clock mr-1"></i>Kuliah & Praktikum';
                    clockDisplay.className = 'bg-blue-500 backdrop-blur-md px-3 py-1 rounded-full text-xs font-medium';
                } else if (hour >= 19 && hour < 23) {
                    clockDisplay.innerHTML = '<i class="fa-solid fa-clock mr-1"></i>Skripsi/Freelance';
                    clockDisplay.className = 'bg-orange-500 backdrop-blur-md px-3 py-1 rounded-full text-xs font-medium';
                } else {
                    clockDisplay.innerHTML = '<i class="fa-solid fa-clock mr-1"></i>Waktu Bebas';
                    clockDisplay.className = 'bg-gray-500 backdrop-blur-md px-3 py-1 rounded-full text-xs font-medium';
                }
            }
        }

        function updateDashboardTime(val) {
            // Update time display and schedule
            const hours = String(val).padStart(2, '0');
            document.querySelector('input#time-simulation').value = hours + ':00';
            updateClockDisplay();

            // You could also update other time-based elements here
            console.log('Time updated to:', hours + ':00');
        }

        function startTask(taskId) {
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Memulai...';
            button.disabled = true;

            // In a real app, this would make an API call
            setTimeout(() => {
                button.innerHTML = '<i class="fa-solid fa-check mr-1"></i>Dimulai';
                button.className =
                    'mt-2 w-full py-1 bg-emerald-500 text-white rounded text-xs font-medium transition-colors';

                // Show notification
                showNotification('Task berhasil dimulai! Fokus dan semangat!', 'success');

                // Reset button after 2 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    button.className =
                        'mt-2 w-full py-1 bg-orange-500 hover:bg-orange-600 text-white rounded text-xs font-medium transition-colors';
                }, 2000);
            }, 1000);
        }

        function toggleTaskComplete(taskId) {
            const taskItem = document.querySelector(`[data-task-id="${taskId}"]`);
            if (!taskItem) return;

            const checkbox = taskItem.querySelector('.w-6.h-6');
            const title = taskItem.querySelector('h4');
            const isCompleted = checkbox.classList.contains('bg-emerald-500');

            if (isCompleted) {
                // Mark as incomplete
                checkbox.className =
                    'w-6 h-6 rounded-full border-2 border-stone-300 dark:border-stone-600 flex items-center justify-center';
                checkbox.innerHTML = '';
                title.classList.remove('line-through');
                title.classList.add('text-stone-800', 'dark:text-white');
            } else {
                // Mark as complete
                checkbox.className =
                    'w-6 h-6 rounded-full border-2 bg-emerald-500 border-emerald-500 flex items-center justify-center';
                checkbox.innerHTML = '<i class="fa-solid fa-check text-white text-xs"></i>';
                title.classList.add('line-through');
                title.classList.remove('text-stone-800', 'dark:text-white');
                title.classList.add('text-stone-500', 'dark:text-stone-400');

                // Show notification
                showNotification('Task selesai! Bagus!', 'success');
            }

            // In a real app, make API call to update task status
            fetch(`/dashboard/tasks/${taskId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        // Revert on error
                        if (isCompleted) {
                            checkbox.className =
                                'w-6 h-6 rounded-full border-2 bg-emerald-500 border-emerald-500 flex items-center justify-center';
                            checkbox.innerHTML = '<i class="fa-solid fa-check text-white text-xs"></i>';
                            title.classList.add('line-through');
                        } else {
                            checkbox.className =
                                'w-6 h-6 rounded-full border-2 border-stone-300 dark:border-stone-600 flex items-center justify-center';
                            checkbox.innerHTML = '';
                            title.classList.remove('line-through');
                        }
                    }
                });
        }

        function editTask(taskId) {
            // In a real app, this would open an edit modal
            alert(`Edit task ${taskId} - Fitur ini akan dibuka di modal edit`);
            console.log('Edit task:', taskId);
        }


        function initTimeSimulation() {
            const timeInput = document.getElementById('time-simulation');
            if (timeInput) {
                // Set initial time to current time
                const now = new Date();
                timeInput.value = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2,
                    '0');

                timeInput.addEventListener('change', function() {
                    updateClockDisplay();
                    // You could also trigger a re-render of time-sensitive content here
                });
            }
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 flex items-center ${
            type === 'success' ? 'bg-emerald-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-orange-500 text-white'
        } animate-fade-in-up`;
            notification.innerHTML = `
            <i class="fa-solid ${type === 'success' ? 'fa-check' : 'fa-info'} mr-2"></i>
            <span>${message}</span>
        `;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Add CSS for animation
        const style = document.createElement('style');
        style.textContent = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }
    `;
        document.head.appendChild(style);
    </script>
@endpush
