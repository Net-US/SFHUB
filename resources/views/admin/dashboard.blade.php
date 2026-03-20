@extends('layouts.app')

@section('title', 'Admin Dashboard | SFHUB')

@section('page-title', 'Dashboard Overview')

@section('content')
    <div class="animate-fade-in-up space-y-8">
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-800 rounded-2xl p-8 text-white shadow-lg">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Selamat datang kembali, {{ auth()->user()->name }}!</h2>
                    <p class="text-primary-100">Berikut ringkasan aktivitas Student-Freelancer Hub hari ini.</p>
                </div>
                <div class="mt-4 md:mt-0 flex gap-3">
                    <span class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl text-sm">
                        <i class="fa-solid fa-calendar mr-2"></i>{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- System Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mr-4">
                        <i class="fa-solid fa-server text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-stone-500 dark:text-stone-400">System Status</p>
                        <h3 class="text-xl font-bold text-stone-800 dark:text-white">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>Online
                        </h3>
                        <p class="text-xs text-stone-400 mt-1">v{{ $stats['system']['version'] ?? '2.1.0' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-4">
                        <i class="fa-solid fa-users text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-stone-500 dark:text-stone-400">Total Users</p>
                        <h3 class="text-xl font-bold text-stone-800 dark:text-white">
                            {{ number_format($stats['users']['total'] ?? 0) }}</h3>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                            +{{ $stats['users']['new_today'] ?? 0 }} hari ini</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mr-4">
                        <i class="fa-solid fa-crown text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-stone-500 dark:text-stone-400">Premium Users</p>
                        <h3 class="text-xl font-bold text-stone-800 dark:text-white">
                            {{ number_format($stats['users']['by_plan']['pro'] ?? 0) }}</h3>
                        <p class="text-xs text-stone-400 mt-1">
                            {{ $stats['users']['total'] > 0 ? round(($stats['users']['by_plan']['pro'] / $stats['users']['total']) * 100, 1) : 0 }}%
                            of total</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mr-4">
                        <i class="fa-solid fa-chart-line text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div>
                        <p class="text-sm text-stone-500 dark:text-stone-400">Revenue (MTD)</p>
                        <h3 class="text-xl font-bold text-stone-800 dark:text-white">Rp
                            {{ number_format($stats['revenue']['month_to_date'] ?? 0, 0, ',', '.') }}</h3>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">+8.2% dari bulan lalu</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-stone-800 dark:text-white">User Growth</h3>
                    <span class="text-xs bg-stone-100 dark:bg-stone-800 px-2 py-1 rounded text-stone-500">Last 6
                        Months</span>
                </div>
                <div class="chart-container h-64">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-stone-800 dark:text-white">User Distribution by Plan</h3>
                    <span class="text-xs bg-stone-100 dark:bg-stone-800 px-2 py-1 rounded text-stone-500">Total:
                        {{ number_format($stats['users']['total'] ?? 0) }}</span>
                </div>
                <div class="chart-container h-64">
                    <canvas id="userDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-stone-800 dark:text-white">Recent Users</h3>
                <a href="{{ route('admin.users') }}"
                    class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">View All →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr
                            class="text-left text-xs text-stone-500 dark:text-stone-400 border-b border-stone-200 dark:border-stone-700">
                            <th class="pb-3 font-medium">User</th>
                            <th class="pb-3 font-medium">Role</th>
                            <th class="pb-3 font-medium">Joined</th>
                            <th class="pb-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($recentUsers as $user)
                            <tr class="border-b border-stone-100 dark:border-stone-800 last:border-0">
                                <td class="py-3">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gradient-to-tr from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-stone-900 dark:text-white">{{ $user->name }}</p>
                                            <p class="text-xs text-stone-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ ($user->subscriptions->first()?->plan->slug ?? 'free') === 'pro' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-400' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400' }}">
                                        {{ ucfirst($user->subscriptions->first()?->plan->slug ?? 'free') }}
                                    </span>
                                </td>
                                <td class="py-3 text-stone-600 dark:text-stone-400">
                                    {{ $user->created_at->format('Y-m-d') }}
                                </td>
                                <td class="py-3">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $user->status === 'active' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-400' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400' }}">
                                        {{ ucfirst($user->status ?? 'active') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-stone-500">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- System Logs -->
        <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-stone-800 dark:text-white">Recent System Activity</h3>
                <a href="{{ route('admin.logs.index') }}"
                    class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">View All Logs →</a>
            </div>
            <div class="space-y-3">
                @forelse($recentLogs as $log)
                    <div class="flex items-center space-x-3 p-3 bg-stone-50 dark:bg-stone-800/50 rounded-lg">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center
                    {{ $log->level === 'error' ? 'bg-red-100 dark:bg-red-900/30 text-red-600' : ($log->level === 'warning' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-600' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-600') }}">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-stone-900 dark:text-white">{{ $log->message }}</p>
                            <p class="text-xs text-stone-500">{{ $log->logged_at->diffForHumans() }}</p>
                        </div>
                        <span class="text-xs text-stone-400 uppercase">{{ $log->level }}</span>
                    </div>
                @empty
                    <p class="text-center text-stone-500 py-4">No recent activity</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Total Users',
                    data: [10000, 11000, 12500, 14000, 15000, {{ $stats['users']['total'] ?? 16000 }}],
                    borderColor: '#f57223',
                    backgroundColor: 'rgba(245, 114, 35, 0.1)',
                    tension: 0.4,
                    fill: true
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

        const userDistCtx = document.getElementById('userDistributionChart').getContext('2d');
        new Chart(userDistCtx, {
            type: 'doughnut',
            data: {
                labels: ['Free', 'Basic', 'Pro'],
                datasets: [{
                    data: [{{ $stats['users']['by_plan']['free'] ?? 0 }},
                        {{ $stats['users']['by_plan']['basic'] ?? 0 }},
                        {{ $stats['users']['by_plan']['pro'] ?? 0 }}
                    ],
                    backgroundColor: ['#d6d3d1', '#3b82f6', '#f57223'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            }
        });
    </script>
@endpush

