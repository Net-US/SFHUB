@extends('layouts.app')

@section('title', 'Admin Dashboard | SFHUB')

@section('content')
<div class="min-h-screen bg-stone-50 dark:bg-stone-900">
    <!-- Admin Header -->
    <div class="bg-white dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-stone-900 dark:text-white">Admin Dashboard</h1>
                    <p class="text-stone-600 dark:text-stone-400 mt-1">Kelola sistem dan pantau performa platform SFHUB</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm text-stone-600 dark:text-stone-400">Welcome back,</p>
                        <p class="font-semibold text-stone-900 dark:text-white">{{ Auth::user()->name }}</p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center text-white font-bold">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-100 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl">
                <div class="flex items-center">
                    <i class="fa-solid fa-check-circle text-emerald-600 dark:text-emerald-400 mr-3"></i>
                    <p class="text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="mb-8 flex flex-wrap gap-3">
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium transition-colors">
                <i class="fa-solid fa-user-plus mr-2"></i>
                Tambah User Baru
            </a>
            <a href="{{ route('admin.landing') }}" class="inline-flex items-center px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg font-medium transition-colors">
                <i class="fa-solid fa-palette mr-2"></i>
                Kelola Landing
            </a>
            <a href="{{ route('admin.users') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition-colors">
                <i class="fa-solid fa-users mr-2"></i>
                Manajemen User
            </a>
        </div>

        <!-- Statistics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white dark:bg-stone-800 rounded-xl p-6 border border-stone-200 dark:border-stone-700 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $totalUsers }}</p>
                        <p class="text-sm text-stone-600 dark:text-stone-400">Total Users</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">+{{ $newThisMonth }} bulan ini</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-users text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white dark:bg-stone-800 rounded-xl p-6 border border-stone-200 dark:border-stone-700 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $activeUsers }}</p>
                        <p class="text-sm text-stone-600 dark:text-stone-400">Active Users</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">{{ $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0 }}% dari total</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-user-check text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                </div>
            </div>

            <!-- Total Tasks -->
            <div class="bg-white dark:bg-stone-800 rounded-xl p-6 border border-stone-200 dark:border-stone-700 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $totalTasks }}</p>
                        <p class="text-sm text-stone-600 dark:text-stone-400">Total Tasks</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">{{ $completedTasks }} selesai</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-tasks text-purple-600 dark:text-purple-400"></i>
                    </div>
                </div>
            </div>

            <!-- Workspaces -->
            <div class="bg-white dark:bg-stone-800 rounded-xl p-6 border border-stone-200 dark:border-stone-700 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $totalWorkspaces }}</p>
                        <p class="text-sm text-stone-600 dark:text-stone-400">Workspaces</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">{{ $totalFinanceAccounts }} akun keuangan</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-layer-group text-orange-600 dark:text-orange-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- User Growth Chart -->
            <div class="bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700 p-6">
                <h3 class="text-lg font-semibold text-stone-900 dark:text-white mb-4">Pertumbuhan Pengguna</h3>
                <div class="h-64 flex items-end justify-between space-x-2">
                    @foreach($monthlyGrowth as $growth)
                    <div class="flex flex-col items-center flex-1">
                        <div class="w-full bg-blue-500 dark:bg-blue-400 rounded-t" style="height: {{ $growth['users'] > 0 ? min(($growth['users'] / max(array_column($monthlyGrowth, 'users'))) * 100, 100) : 0 }}%; min-height: 20px;"></div>
                        <span class="text-xs text-stone-600 dark:text-stone-400 mt-2">{{ $growth['month'] }}</span>
                        <span class="text-xs font-medium text-stone-900 dark:text-white">{{ $growth['users'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Task Distribution Chart -->
            <div class="bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700 p-6">
                <h3 class="text-lg font-semibold text-stone-900 dark:text-white mb-4">Distribusi Tasks</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-emerald-500 rounded-full"></div>
                            <span class="text-sm text-stone-700 dark:text-stone-300">Selesai</span>
                        </div>
                        <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">{{ $completedTasks }}</span>
                    </div>
                    <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0 }}%"></div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <span class="text-sm text-stone-700 dark:text-stone-300">In Progress</span>
                        </div>
                        <span class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ $doingTasks }}</span>
                    </div>
                    <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $totalTasks > 0 ? ($doingTasks / $totalTasks) * 100 : 0 }}%"></div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                            <span class="text-sm text-stone-700 dark:text-stone-300">Pending</span>
                        </div>
                        <span class="text-sm font-medium text-orange-600 dark:text-orange-400">{{ $pendingTasks }}</span>
                    </div>
                    <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2">
                        <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $totalTasks > 0 ? ($pendingTasks / $totalTasks) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Statistics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- User Distribution by Role -->
            <div class="bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700 p-6">
                <h3 class="text-lg font-semibold text-stone-900 dark:text-white mb-4">Distribusi User per Role</h3>
                <div class="space-y-3">
                    @foreach([
                        ['Admin', $adminCount, 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400'],
                        ['Student', $studentCount, 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'],
                        ['Freelancer', $freelanceCount, 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400']
                    ] as $role)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-stone-600 dark:text-stone-400">{{ $role[0] }}</span>
                        <span class="text-sm font-medium {{ $role[2] }}">{{ $role[1] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- User Distribution by Plan -->
            <div class="bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700 p-6">
                <h3 class="text-lg font-semibold text-stone-900 dark:text-white mb-4">Distribusi User per Plan</h3>
                <div class="space-y-3">
                    @foreach([
                        ['Free', $freeCount, 'bg-gray-100 dark:bg-gray-900/30 text-gray-600 dark:text-gray-400'],
                        ['Pro', $proCount, 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400'],
                        ['Team', $teamCount, 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400']
                    ] as $plan)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-stone-600 dark:text-stone-400">{{ $plan[0] }}</span>
                        <span class="text-sm font-medium {{ $plan[2] }}">{{ $plan[1] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Platform Statistics -->
            <div class="bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700 p-6">
                <h3 class="text-lg font-semibold text-stone-900 dark:text-white mb-4">Statistik Platform</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-stone-600 dark:text-stone-400">Subjects</span>
                        <span class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ $totalSubjects }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-stone-600 dark:text-stone-400">Events</span>
                        <span class="text-sm font-medium text-purple-600 dark:text-purple-400">{{ $totalEvents }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-stone-600 dark:text-stone-400">Transactions</span>
                        <span class="text-sm font-medium text-orange-600 dark:text-orange-400">{{ $totalTransactions }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Recent Users -->
            <div class="bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700">
                <div class="p-6 border-b border-stone-200 dark:border-stone-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-stone-900 dark:text-white">User Terbaru</h3>
                        <a href="{{ route('admin.users') }}" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            View All →
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($recentUsers as $recentUser)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-stone-400 to-stone-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    {{ substr($recentUser->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-stone-900 dark:text-white">{{ $recentUser->name }}</p>
                                    <p class="text-sm text-stone-600 dark:text-stone-400">{{ $recentUser->email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $recentUser->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-400' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400' }}">
                                    {{ $recentUser->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">
                                    {{ $recentUser->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Tasks -->
            <div class="bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700">
                <div class="p-6 border-b border-stone-200 dark:border-stone-700">
                    <h3 class="text-lg font-semibold text-stone-900 dark:text-white">Task Terbaru</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($recentTasks as $task)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-tasks text-purple-600 dark:text-purple-400 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-stone-900 dark:text-white">{{ $task->title }}</p>
                                    <p class="text-sm text-stone-600 dark:text-stone-400">{{ $task->category }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($task->status === 'done') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-400
                                    @elseif($task->status === 'doing') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400
                                    @else bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400 @endif">
                                    {{ ucfirst($task->status) }}
                                </span>
                                <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">
                                    {{ $task->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="bg-white dark:bg-stone-800 rounded-xl border border-stone-200 dark:border-stone-700">
            <div class="p-6 border-b border-stone-200 dark:border-stone-700">
                <h3 class="text-lg font-semibold text-stone-900 dark:text-white">Informasi Sistem</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Environment</h4>
                        <dl class="space-y-1">
                            <div class="flex justify-between">
                                <dt class="text-sm text-stone-600 dark:text-stone-400">Application:</dt>
                                <dd class="text-sm font-medium text-stone-900 dark:text-white">SFHUB</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-stone-600 dark:text-stone-400">Version:</dt>
                                <dd class="text-sm font-medium text-stone-900 dark:text-white">1.0.0</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-stone-600 dark:text-stone-400">Environment:</dt>
                                <dd class="text-sm font-medium text-stone-900 dark:text-white">{{ config('app.env') }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Database</h4>
                        <dl class="space-y-1">
                            <div class="flex justify-between">
                                <dt class="text-sm text-stone-600 dark:text-stone-400">Connection:</dt>
                                <dd class="text-sm font-medium text-stone-900 dark:text-white">{{ config('database.default') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-stone-600 dark:text-stone-400">Database:</dt>
                                <dd class="text-sm font-medium text-stone-900 dark:text-white">{{ config('database.connections.mysql.database') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-stone-600 dark:text-stone-400">Status:</dt>
                                <dd class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Connected</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Server</h4>
                        <dl class="space-y-1">
                            <div class="flex justify-between">
                                <dt class="text-sm text-stone-600 dark:text-stone-400">PHP Version:</dt>
                                <dd class="text-sm font-medium text-stone-900 dark:text-white">{{ PHP_VERSION }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-stone-600 dark:text-stone-400">Laravel:</dt>
                                <dd class="text-sm font-medium text-stone-900 dark:text-white">{{ app()->version() }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-stone-600 dark:text-stone-400">Server Time:</dt>
                                <dd class="text-sm font-medium text-stone-900 dark:text-white">{{ now()->format('Y-m-d H:i:s') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
