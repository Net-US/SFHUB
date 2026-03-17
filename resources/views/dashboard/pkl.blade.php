@extends('layouts.app-dashboard')

@section('title', 'PKL / Work Log - StudentHub')

@section('content')
    <div class="max-w-7xl mx-auto animate-fade-in-up">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-stone-800 dark:text-white mb-2">PKL / Work Log</h1>
            <p class="text-stone-500 dark:text-stone-400">Catatan dan log kegiatan PKL/kerja Anda</p>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                        <i class="fa-solid fa-briefcase text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                    <span class="text-xs font-medium text-stone-500 dark:text-stone-400">Total Hari</span>
                </div>
                <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $totalDays ?? 0 }}</div>
                <div class="text-sm text-stone-500 dark:text-stone-400 mt-1">Hari kerja</div>
            </div>

            <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl">
                        <i class="fa-solid fa-clock text-emerald-600 dark:text-emerald-400 text-xl"></i>
                    </div>
                    <span class="text-xs font-medium text-stone-500 dark:text-stone-400">Total Jam</span>
                </div>
                <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $totalHours ?? 0 }}</div>
                <div class="text-sm text-stone-500 dark:text-stone-400 mt-1">Jam kerja</div>
            </div>

            <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-xl">
                        <i class="fa-solid fa-tasks text-purple-600 dark:text-purple-400 text-xl"></i>
                    </div>
                    <span class="text-xs font-medium text-stone-500 dark:text-stone-400">Aktivitas</span>
                </div>
                <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $pklLogs->count() ?? 0 }}</div>
                <div class="text-sm text-stone-500 dark:text-stone-400 mt-1">Log tercatat</div>
            </div>

            <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-xl">
                        <i class="fa-solid fa-calendar-check text-orange-600 dark:text-orange-400 text-xl"></i>
                    </div>
                    <span class="text-xs font-medium text-stone-500 dark:text-stone-400">Progress</span>
                </div>
                <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $progressPercentage ?? 0 }}%</div>
                <div class="text-sm text-stone-500 dark:text-stone-400 mt-1">Target {{ $targetDays ?? 120 }} hari</div>
            </div>
        </div>

        {{-- Work Logs List --}}
        <div
            class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-stone-800 dark:text-white">Log Kegiatan</h3>
                <button
                    class="px-3 py-1.5 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded-lg text-sm font-medium">
                    <i class="fa-solid fa-plus mr-1"></i> Tambah Log
                </button>
            </div>

            @if (isset($pklLogs) && $pklLogs->count() > 0)
                <div class="space-y-4">
                    @foreach ($pklLogs as $log)
                        <div
                            class="p-4 bg-stone-50 dark:bg-stone-700/50 rounded-xl border border-stone-200 dark:border-stone-700">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h4 class="font-semibold text-stone-800 dark:text-white">
                                        {{ $log->date->format('d M Y') }}</h4>
                                    <p class="text-sm text-stone-500 dark:text-stone-400">{{ $log->division ?? '-' }} •
                                        {{ $log->supervisor ?? '-' }}</p>
                                </div>
                                <span
                                    class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full text-sm font-medium">
                                    {{ $log->hours }} jam
                                </span>
                            </div>
                            <div class="text-sm text-stone-600 dark:text-stone-300 mb-3">
                                <strong>Aktivitas:</strong> {{ $log->activities }}
                            </div>
                            @if ($log->notes)
                                <div
                                    class="text-sm text-stone-500 dark:text-stone-400 bg-stone-100 dark:bg-stone-600/30 p-3 rounded-lg">
                                    <i class="fa-solid fa-note-sticky mr-2"></i>{{ $log->notes }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-stone-500 dark:text-stone-400">
                    <i class="fa-solid fa-clipboard-list text-4xl mb-3"></i>
                    <p>Belum ada log kegiatan</p>
                    <button class="mt-3 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm">
                        Catat Log Pertama
                    </button>
                </div>
            @endif
        </div>

        {{-- Progress Bar --}}
        <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
            <h3 class="font-bold text-stone-800 dark:text-white mb-4">Progress PKL</h3>
            @php
                $percentage = ($targetDays ?? 120) > 0 ? (($totalDays ?? 0) / ($targetDays ?? 120)) * 100 : 0;
            @endphp
            <div class="mb-2 flex justify-between text-sm">
                <span class="text-stone-600 dark:text-stone-400">{{ $totalDays ?? 0 }} dari {{ $targetDays ?? 120 }}
                    hari</span>
                <span class="font-medium text-stone-800 dark:text-white">{{ number_format($percentage, 1) }}%</span>
            </div>
            <div class="w-full h-3 bg-stone-200 dark:bg-stone-600 rounded-full overflow-hidden">
                <div class="h-full bg-orange-500 rounded-full transition-all duration-500"
                    style="width: {{ min($percentage, 100) }}%"></div>
            </div>
            <p class="text-sm text-stone-500 dark:text-stone-400 mt-3">
                @if ($percentage >= 100)
                    <i class="fa-solid fa-trophy text-amber-500 mr-1"></i> Selamat! Anda telah menyelesaikan target PKL!
                @else
                    <i class="fa-solid fa-info-circle mr-1"></i> Sisa {{ ($targetDays ?? 120) - ($totalDays ?? 0) }} hari
                    lagi untuk menyelesaikan target.
                @endif
            </p>
        </div>
    </div>
@endsection
