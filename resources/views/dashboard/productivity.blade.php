@extends('layouts.app-dashboard')

@section('title', 'Analytics - StudentHub')

@push('styles')
    <style>
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }

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
    </style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto animate-fade-in-up">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-stone-800 dark:text-white mb-2">Analytics</h1>
            <p class="text-stone-500 dark:text-stone-400">Analisis produktivitas dan performa Anda</p>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                        <i class="fa-solid fa-clock text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                    <span class="text-xs font-medium text-stone-500 dark:text-stone-400">Total Hours</span>
                </div>
                <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $totalHours ?? 0 }}</div>
                <div class="text-sm text-stone-500 dark:text-stone-400 mt-1">Jam fokus</div>
            </div>

            <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl">
                        <i class="fa-solid fa-check-circle text-emerald-600 dark:text-emerald-400 text-xl"></i>
                    </div>
                    <span class="text-xs font-medium text-stone-500 dark:text-stone-400">Tasks Done</span>
                </div>
                <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $completedTasks ?? 0 }}</div>
                <div class="text-sm text-stone-500 dark:text-stone-400 mt-1">Tugas selesai</div>
            </div>

            <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-xl">
                        <i class="fa-solid fa-fire text-purple-600 dark:text-purple-400 text-xl"></i>
                    </div>
                    <span class="text-xs font-medium text-stone-500 dark:text-stone-400">Streak</span>
                </div>
                <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $currentStreak ?? 0 }}</div>
                <div class="text-sm text-stone-500 dark:text-stone-400 mt-1">Hari berturut-turut</div>
            </div>

            <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-xl">
                        <i class="fa-solid fa-chart-line text-orange-600 dark:text-orange-400 text-xl"></i>
                    </div>
                    <span class="text-xs font-medium text-stone-500 dark:text-stone-400">Productivity</span>
                </div>
                <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $productivityScore ?? 0 }}%</div>
                <div class="text-sm text-stone-500 dark:text-stone-400 mt-1">Skor minggu ini</div>
            </div>
        </div>

        {{-- Productivity Logs --}}
        <div
            class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-stone-800 dark:text-white">Log Produktivitas</h3>
                <button
                    class="px-3 py-1.5 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded-lg text-sm font-medium">
                    <i class="fa-solid fa-plus mr-1"></i> Catat Log
                </button>
            </div>

            @if (isset($productivityLogs) && $productivityLogs->count() > 0)
                <div class="space-y-3">
                    @foreach ($productivityLogs as $log)
                        <div class="flex items-center gap-4 p-4 bg-stone-50 dark:bg-stone-700/50 rounded-xl">
                            <div
                                class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-stopwatch text-xl"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-stone-800 dark:text-white">{{ $log->date->format('d M Y') }}
                                </h4>
                                <p class="text-sm text-stone-500 dark:text-stone-400">{{ $log->category }} •
                                    {{ $log->focus_level }}/10 fokus</p>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-stone-800 dark:text-white">{{ $log->hours }} jam</div>
                                <div class="text-xs text-stone-500 dark:text-stone-400">{{ $log->energy_level }}/10 energi
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-stone-500 dark:text-stone-400">
                    <i class="fa-solid fa-chart-bar text-4xl mb-3"></i>
                    <p>Belum ada log produktivitas</p>
                    <button class="mt-3 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm">
                        Catat Log Pertama
                    </button>
                </div>
            @endif
        </div>

        {{-- Weekly Chart Placeholder --}}
        <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-700">
            <h3 class="font-bold text-stone-800 dark:text-white mb-4">Produktivitas Mingguan</h3>
            <div class="h-64 bg-stone-100 dark:bg-stone-700/50 rounded-xl flex items-center justify-center">
                <div class="text-center text-stone-500 dark:text-stone-400">
                    <i class="fa-solid fa-chart-column text-3xl mb-2"></i>
                    <p>Chart produktivitas akan ditampilkan di sini</p>
                </div>
            </div>
        </div>
    </div>
@endsection
