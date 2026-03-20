@extends('layouts.app')

@section('title', 'Analytics | SFHUB Admin')

@section('page-title', 'Analytics Dashboard')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Analytics Dashboard</h2>
                <p class="text-stone-500 dark:text-stone-400 text-sm">Statistik dan analisis platform</p>
            </div>
            <div class="flex gap-2">
                <select id="date-range"
                    class="px-4 py-2 border border-stone-300 dark:border-stone-700 rounded-xl text-sm dark:bg-stone-800 dark:text-white">
                    <option value="7">Last 7 Days</option>
                    <option value="30">Last 30 Days</option>
                    <option value="90">Last 3 Months</option>
                    <option value="365">Last Year</option>
                </select>
                <button onclick="exportAnalytics()"
                    class="px-4 py-2 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300 rounded-xl text-sm">
                    <i class="fa-solid fa-download mr-2"></i>Export
                </button>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- User Growth Chart -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white mb-4">User Growth</h3>
                <div class="h-64">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>

            <!-- Revenue Chart -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white mb-4">Revenue</h3>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Daily Active Users -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white mb-4">Daily Active Users</h3>
                <div class="h-52">
                    <canvas id="dauChart"></canvas>
                </div>
            </div>

            <!-- User Distribution -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white mb-4">Users by Plan</h3>
                <div class="h-52">
                    <canvas id="planChart"></canvas>
                </div>
            </div>

            <!-- Content Stats -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white mb-4">Content Stats</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-stone-50 dark:bg-stone-800 rounded-lg">
                        <span class="text-stone-600 dark:text-stone-400">Blog Posts</span>
                        <span class="font-bold text-stone-900 dark:text-white">{{ $contentStats['posts'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-stone-50 dark:bg-stone-800 rounded-lg">
                        <span class="text-stone-600 dark:text-stone-400">Published</span>
                        <span class="font-bold text-emerald-600">{{ $contentStats['published'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-stone-50 dark:bg-stone-800 rounded-lg">
                        <span class="text-stone-600 dark:text-stone-400">Drafts</span>
                        <span class="font-bold text-amber-600">{{ $contentStats['drafts'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metrics Table -->
        <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden">
            <div class="p-4 border-b border-stone-200 dark:border-stone-700">
                <h3 class="font-bold text-stone-900 dark:text-white">Monthly Metrics</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-stone-50 dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Month</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">New Users</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Active Users</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Revenue</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Tasks Completed
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyMetrics ?? [] as $metric)
                            <tr class="border-b border-stone-100 dark:border-stone-800">
                                <td class="py-4 px-6 font-medium">{{ $metric['month'] }}</td>
                                <td class="py-4 px-6">{{ $metric['new_users'] }}</td>
                                <td class="py-4 px-6">{{ $metric['active_users'] }}</td>
                                <td class="py-4 px-6">Rp {{ number_format($metric['revenue'], 0, ',', '.') }}</td>
                                <td class="py-4 px-6">{{ $metric['tasks'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-stone-500">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($userGrowth ?? [], 'month')) !!},
                datasets: [{
                    label: 'Total Users',
                    data: {!! json_encode(array_column($userGrowth ?? [], 'users')) !!},
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($revenueData ?? [], 'month')) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode(array_column($revenueData ?? [], 'revenue')) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // DAU Chart
        const dauCtx = document.getElementById('dauChart').getContext('2d');
        new Chart(dauCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($dailyActive ?? [], 'date')) !!},
                datasets: [{
                    label: 'Active Users',
                    data: {!! json_encode(array_column($dailyActive ?? [], 'active')) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Plan Distribution Chart
        const planCtx = document.getElementById('planChart').getContext('2d');
        new Chart(planCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($planDistribution ?? [])) !!},
                datasets: [{
                    data: {!! json_encode(array_values($planDistribution ?? [])) !!},
                    backgroundColor: ['#f97316', '#10b981', '#3b82f6', '#8b5cf6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        function exportAnalytics() {
            window.open('{{ route('admin.analytics') }}?export=csv', '_blank');
        }
    </script>
@endpush
