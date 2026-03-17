{{-- resources/views/dashboard/productivity.blade.php --}}
@extends('layouts.app-dashboard')
@section('title', 'Analytics | StudentHub')
@section('page-title', 'Analytics & Produktivitas')

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
    </style>
@endpush

@section('content')
    @php
        $weekly = $weekly ?? [];
        $categories = $categories ?? [];
        $totalDone = $totalDone ?? 0;
        $totalPlanned = $totalPlanned ?? 0;
        $avgFocus = $avgFocus ?? 0;
        $bestDay = $bestDay ?? ['day' => '-', 'focus' => 0];
        $completionPct = $completionPct ?? 0;
    @endphp

    <div class="fade-up space-y-5">

        {{-- Hero summary --}}
        <div class="bg-gradient-to-r from-indigo-600 to-violet-600 rounded-2xl p-6 text-white shadow-xl">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-5">
                <div>
                    <h2 class="text-xl font-bold mb-1">Ringkasan Produktivitas Minggu Ini</h2>
                    <p class="text-indigo-200 text-sm">{{ now()->startOfWeek()->isoFormat('D MMM') }} –
                        {{ now()->endOfWeek()->isoFormat('D MMM YYYY') }}</p>
                </div>
                <div class="flex gap-6 text-center flex-wrap">
                    @foreach ([[$completionPct . '%', 'Completion Rate'], [$avgFocus . '%', 'Avg Focus'], [ucfirst($bestDay['day']), 'Peak Day'], [$totalDone . ' / ' . $totalPlanned, 'Tasks Done']] as [$v, $l])
                        <div>
                            <p class="text-2xl font-bold">{{ $v }}</p>
                            <p class="text-xs text-indigo-200 uppercase tracking-wide">{{ $l }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 2-col charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
                <h3 class="font-bold text-stone-800 dark:text-white mb-4 text-sm">Tren Produktivitas Mingguan</h3>
                <div style="position:relative;height:220px">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
                <h3 class="font-bold text-stone-800 dark:text-white mb-4 text-sm">Komposisi Tugas per Kategori</h3>
                <div style="position:relative;height:220px">
                    <canvas id="catChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Category progress --}}
        <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
            <h3 class="font-bold text-stone-800 dark:text-white mb-5 text-sm">Progress per Kategori</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($categories as $c)
                    @php $pct = round(($c['done']/$c['total'])*100); @endphp
                    <div class="p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                        <div class="flex justify-between items-center mb-2">
                            <span
                                class="text-sm font-semibold text-stone-700 dark:text-stone-300">{{ $c['name'] }}</span>
                            <span class="text-sm font-bold text-stone-800 dark:text-white">{{ $pct }}%</span>
                        </div>
                        <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2 mb-2">
                            <div class="h-2 rounded-full"
                                style="width:{{ $pct }}%;background-color:{{ $c['color'] }}"></div>
                        </div>
                        <p class="text-xs text-stone-400">{{ $c['done'] }}/{{ $c['total'] }} tugas selesai</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Daily table --}}
        <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-6">
            <h3 class="font-bold text-stone-800 dark:text-white mb-4 text-sm">Detail Harian Minggu Ini</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-stone-200 dark:border-stone-700">
                            @foreach (['Hari', 'Selesai', 'Direncanakan', 'Completion', 'Focus Score', 'Status'] as $h)
                                <th class="text-left py-3 text-stone-500 dark:text-stone-400 font-medium">
                                    {{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($weekly as $i => $d)
                            @php
                                $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                $cr = $d['planned'] > 0 ? round(($d['done'] / $d['planned']) * 100) : 0;
                                $statusCls =
                                    $d['focus'] >= 80
                                        ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                                        : ($d['focus'] >= 65
                                            ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'
                                            : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400');
                                $statusLbl =
                                    $d['focus'] >= 80 ? 'Optimal' : ($d['focus'] >= 65 ? 'Cukup' : 'Perlu Perbaikan');
                            @endphp
                            <tr
                                class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800">
                                <td class="py-3 font-medium text-stone-800 dark:text-white">{{ $days[$i] }}</td>
                                <td class="py-3 text-stone-700 dark:text-stone-300">{{ $d['done'] }}</td>
                                <td class="py-3 text-stone-700 dark:text-stone-300">{{ $d['planned'] }}</td>
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-stone-100 dark:bg-stone-700 rounded-full h-1.5">
                                            <div class="bg-blue-500 h-1.5 rounded-full" style="width:{{ $cr }}%">
                                            </div>
                                        </div>
                                        <span class="text-stone-600 dark:text-stone-400">{{ $cr }}%</span>
                                    </div>
                                </td>
                                <td class="py-3 text-stone-700 dark:text-stone-300">{{ $d['focus'] }}%</td>
                                <td class="py-3"><span
                                        class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusCls }}">{{ $statusLbl }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Insights --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-800 flex items-center justify-center">
                        <i class="fa-solid fa-lightbulb text-blue-600 dark:text-blue-300"></i>
                    </div>
                    <h4 class="font-bold text-blue-800 dark:text-blue-300 text-sm">Pola Kerja Optimal</h4>
                </div>
                <p class="text-sm text-blue-700 dark:text-blue-400">Kamu paling produktif pada
                    <strong>{{ ucfirst($bestDay['day']) }} ({{ $bestDay['focus'] }}% focus score)</strong>. Coba terapkan
                    rutinitas yang sama di hari lain untuk meningkatkan konsistensi.
                </p>
            </div>
            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-2xl p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-800 flex items-center justify-center">
                        <i class="fa-solid fa-triangle-exclamation text-orange-600 dark:text-orange-300"></i>
                    </div>
                    <h4 class="font-bold text-orange-800 dark:text-orange-300 text-sm">Area Perbaikan</h4>
                </div>
                <p class="text-sm text-orange-700 dark:text-orange-400">Produktivitas turun di <strong>akhir pekan
                        (55–60%)</strong>. Pertimbangkan jadwal yang lebih ringan atau istirahat berkualitas di akhir pekan.
                </p>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const weekly = @json($weekly);
            const cats = @json($categories);
            const isDark = document.documentElement.classList.contains('dark');
            const gridC = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.05)';
            const tickC = isDark ? '#a8a29e' : '#78716c';

            // Weekly line chart
            const wCtx = document.getElementById('weeklyChart');
            if (wCtx) {
                new Chart(wCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: weekly.map(d => d.day),
                        datasets: [{
                                label: 'Focus Score (%)',
                                data: weekly.map(d => d.focus),
                                borderColor: '#8b5cf6',
                                backgroundColor: 'rgba(139,92,246,.1)',
                                fill: true,
                                tension: .4,
                                borderWidth: 2,
                                pointRadius: 4
                            },
                            {
                                label: 'Completion (%)',
                                data: weekly.map(d => Math.round(d.done / d.planned * 100)),
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,.08)',
                                fill: true,
                                tension: .4,
                                borderWidth: 2,
                                pointRadius: 4
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                grid: {
                                    color: gridC
                                },
                                ticks: {
                                    color: tickC,
                                    callback: v => v + '%'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: tickC
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: tickC,
                                    usePointStyle: true,
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Doughnut
            const cCtx = document.getElementById('catChart');
            if (cCtx) {
                new Chart(cCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: cats.map(c => c.name),
                        datasets: [{
                            data: cats.map(c => Math.round(c.done / c.total * 100)),
                            backgroundColor: cats.map(c => c.color),
                            borderWidth: 2,
                            borderColor: isDark ? '#1c1917' : '#fff'
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    color: tickC,
                                    usePointStyle: true,
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: c => `${c.label}: ${c.raw}% selesai`
                                }
                            }
                        }
                    }
                });
            }
        })();
    </script>
@endpush
