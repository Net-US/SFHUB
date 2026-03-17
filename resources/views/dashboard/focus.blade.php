@extends('layouts.app-dashboard')

@section('title', 'Focus - Smart Timeline - StudentHub')

@push('styles')
<style>
    .animate-fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .timeline-item {
        position: relative;
        padding-left: 2rem;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #f97316, #fb923c);
    }
    .timeline-item::after {
        content: '';
        position: absolute;
        left: -4px;
        top: 0.5rem;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #f97316;
        border: 2px solid white;
    }
    .timeline-item.priority-high::after { background: #ef4444; }
    .timeline-item.priority-medium::after { background: #f97316; }
    .timeline-item.priority-low::after { background: #22c55e; }
    .timeline-item.completed::before { background: #22c55e; }
    .timeline-item.completed::after { background: #22c55e; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto animate-fade-in-up">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-stone-800 dark:text-white mb-2">Focus</h1>
        <p class="text-stone-500 dark:text-stone-400">Smart Timeline - Prioritas harian Anda</p>
    </div>

    {{-- Current Time & Status --}}
    <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-stone-800 dark:text-white">{{ now()->format('l, d M Y') }}</h2>
                <p class="text-sm text-stone-500 dark:text-stone-400">Waktu sekarang: <span id="current-time" class="font-mono font-bold text-orange-500">{{ now()->format('H:i') }}</span></p>
            </div>
            <div class="text-right">
                <span class="px-4 py-2 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded-full text-sm font-medium">
                    <i class="fa-solid fa-clock mr-2"></i>Fokus Mode
                </span>
            </div>
        </div>
    </div>

    {{-- Today's Priority Schedule --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Fixed Schedule (Kuliah & PKL) --}}
        <div class="lg:col-span-2 bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
            <h3 class="font-bold text-stone-800 dark:text-white mb-4 flex items-center">
                <i class="fa-solid fa-calendar-check mr-2 text-blue-500"></i>
                Jadwal Rutin Hari Ini
            </h3>

            @php
                $today = now()->format('l');
                $todaySchedules = $schedules ?? collect();
                $fixedSchedules = $todaySchedules->whereIn('type', ['academic', 'pkl', 'work'])->sortBy('start_time');
                $remainingTimeSlots = collect();
                
                // Calculate remaining time slots
                $currentHour = now()->hour;
                $lastEndTime = $currentHour;
                
                foreach ($fixedSchedules as $schedule) {
                    $startHour = (int) substr($schedule->start_time, 0, 2);
                    if ($startHour > $lastEndTime) {
                        $remainingTimeSlots->push([
                            'start' => $lastEndTime,
                            'end' => $startHour,
                            'duration' => $startHour - $lastEndTime
                        ]);
                    }
                    $lastEndTime = max($lastEndTime, (int) substr($schedule->end_time, 0, 2));
                }
                
                // Add remaining time until evening
                if ($lastEndTime < 22) {
                    $remainingTimeSlots->push([
                        'start' => $lastEndTime,
                        'end' => 22,
                        'duration' => 22 - $lastEndTime
                    ]);
                }
            @endphp

            <div class="space-y-4">
                @forelse($fixedSchedules as $schedule)
                    <div class="timeline-item priority-high p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-medium text-red-600 dark:text-red-400 uppercase">
                                    {{ $schedule->type === 'academic' ? '🎓 Kuliah' : '💼 PKL/Kerja' }}
                                </span>
                                <h4 class="font-semibold text-stone-800 dark:text-white mt-1">{{ $schedule->activity }}</h4>
                                @if($schedule->location)
                                    <p class="text-sm text-stone-500 dark:text-stone-400">
                                        <i class="fa-solid fa-location-dot mr-1"></i>{{ $schedule->location }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-stone-800 dark:text-white">
                                    {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}
                                </div>
                                <span class="text-sm text-stone-500 dark:text-stone-400">
                                    {{ (int)substr($schedule->end_time, 0, 2) - (int)substr($schedule->start_time, 0, 2) }} jam
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-stone-500 dark:text-stone-400">
                        <i class="fa-solid fa-calendar-xmark text-4xl mb-3"></i>
                        <p>Tidak ada jadwal rutin hari ini</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Available Time Slots --}}
        <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
            <h3 class="font-bold text-stone-800 dark:text-white mb-4 flex items-center">
                <i class="fa-solid fa-hourglass-half mr-2 text-emerald-500"></i>
                Waktu Tersedia
            </h3>

            @if($remainingTimeSlots->count() > 0)
                <div class="space-y-3">
                    @foreach($remainingTimeSlots as $slot)
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-stone-800 dark:text-white">
                                    {{ sprintf('%02d:00', $slot['start']) }} - {{ sprintf('%02d:00', $slot['end']) }}
                                </span>
                                <span class="px-2 py-1 bg-emerald-100 dark:bg-emerald-800 text-emerald-700 dark:text-emerald-300 rounded text-xs font-medium">
                                    {{ $slot['duration'] }} jam
                                </span>
                            </div>
                            <p class="text-sm text-stone-500 dark:text-stone-400">
                                @if($slot['duration'] >= 3)
                                    💡 Ideal untuk tugas besar atau project
                                @elseif($slot['duration'] >= 2)
                                    💡 Cocok untuk tugas medium atau content creation
                                @else
                                    💡 Gunakan untuk tugas kecil atau review
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-stone-500 dark:text-stone-400">
                    <i class="fa-solid fa-clock text-4xl mb-3"></i>
                    <p>Semua waktu sudah terjadwal</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Smart Task Recommendations --}}
    <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700 mb-8">
        <h3 class="font-bold text-stone-800 dark:text-white mb-4 flex items-center">
            <i class="fa-solid fa-lightbulb mr-2 text-amber-500"></i>
            Rekomendasi Tugas Berdasarkan Waktu Tersedia
        </h3>

        @php
            $urgentTasks = $tasks ?? collect();
            $urgentTasks = $urgentTasks->where('priority', 'urgent')->whereNotIn('status', ['done', 'archived'])->take(3);
            $mediumTasks = $tasks ?? collect();
            $mediumTasks = $mediumTasks->where('priority', 'medium')->whereNotIn('status', ['done', 'archived'])->take(3);
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Urgent Tasks --}}
            <div>
                <h4 class="text-sm font-semibold text-red-600 dark:text-red-400 mb-3 flex items-center">
                    <i class="fa-solid fa-fire mr-2"></i>Tugas Urgent
                </h4>
                @forelse($urgentTasks as $task)
                    <div class="timeline-item priority-high mb-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <h5 class="font-medium text-stone-800 dark:text-white text-sm">{{ $task->title }}</h5>
                                <p class="text-xs text-stone-500 dark:text-stone-400">
                                    Deadline: {{ $task->due_date ? $task->due_date->format('d M') : 'Tidak ada' }}
                                </p>
                            </div>
                            <span class="px-2 py-1 bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-300 rounded text-xs">
                                Urgent
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-stone-500 dark:text-stone-400 italic">Tidak ada tugas urgent</p>
                @endforelse
            </div>

            {{-- Medium Priority Tasks --}}
            <div>
                <h4 class="text-sm font-semibold text-orange-600 dark:text-orange-400 mb-3 flex items-center">
                    <i class="fa-solid fa-star mr-2"></i>Tugas Medium (Content Creator)
                </h4>
                @forelse($mediumTasks as $task)
                    <div class="timeline-item priority-medium mb-3 p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <h5 class="font-medium text-stone-800 dark:text-white text-sm">{{ $task->title }}</h5>
                                <p class="text-xs text-stone-500 dark:text-stone-400">
                                    Estimasi: {{ $task->estimated_hours ?? 1 }} jam
                                </p>
                            </div>
                            <span class="px-2 py-1 bg-orange-100 dark:bg-orange-800 text-orange-700 dark:text-orange-300 rounded text-xs">
                                Medium
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-stone-500 dark:text-stone-400 italic">Tidak ada tugas medium</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Smart Suggestions --}}
    <div class="bg-gradient-to-r from-orange-500 to-amber-500 rounded-2xl p-6 text-white">
        <h3 class="font-bold mb-4 flex items-center">
            <i class="fa-solid fa-wand-magic-sparkles mr-2"></i>
            Saran Smart Focus
        </h3>
        <div class="space-y-3">
            @if($remainingTimeSlots->count() > 0)
                @php
                    $firstSlot = $remainingTimeSlots->first();
                    $totalAvailableHours = $remainingTimeSlots->sum('duration');
                @endphp

                @if($urgentTasks->count() > 0)
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-exclamation mt-1"></i>
                        <div>
                            <p class="font-medium">Prioritas Utama: Tugas Urgent</p>
                            <p class="text-sm text-white/80">
                                Anda memasukkan {{ $totalAvailableHours }} jam waktu tersedia. 
                                Fokus pada tugas urgent terlebih dahulu: <strong>{{ $urgentTasks->first()->title }}</strong>
                            </p>
                        </div>
                    </div>
                @elseif($totalAvailableHours >= 3 && $mediumTasks->count() > 0)
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-video mt-1"></i>
                        <div>
                            <p class="font-medium">Sesi Content Creator</p>
                            <p class="text-sm text-white/80">
                                Waktu {{ $totalAvailableHours }} jam ideal untuk content creation. 
                                Pilih tugas: <strong>{{ $mediumTasks->first()->title }}</strong>
                            </p>
                        </div>
                    </div>
                @elseif($totalAvailableHours >= 2)
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-book mt-1"></i>
                        <div>
                            <p class="font-medium">Waktu Review & Belajar</p>
                            <p class="text-sm text-white/80">
                                Gunakan {{ $totalAvailableHours }} jam untuk review materi kuliah atau belajar mandiri.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-mug-hot mt-1"></i>
                        <div>
                            <p class="font-medium">Waktu Istirahat & Refresh</p>
                            <p class="text-sm text-white/80">
                                Sisa waktu {{ $totalAvailableHours }} jam. Gunakan untuk istirahat atau tugas ringan.
                            </p>
                        </div>
                    </div>
                @endif
            @else
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-calendar-check mt-1"></i>
                    <div>
                        <p class="font-medium">Hari Penuh!</p>
                        <p class="text-sm text-white/80">
                            Semua waktu sudah terjadwal dengan baik. Fokus pada jadwal yang ada.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Update current time every minute
    setInterval(() => {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('current-time').textContent = `${hours}:${minutes}`;
    }, 60000);
</script>
@endsection
