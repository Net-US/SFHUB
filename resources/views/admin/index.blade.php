@extends('layouts.app-dashboard')
@section('title', 'Admin Dashboard | StudentHub')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Admin Dashboard</h2>
            <p class="text-stone-400 text-xs mt-0.5">Kelola pengguna dan konten platform StudentHub</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
            class="flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-medium transition-colors">
            <i class="fa-solid fa-plus text-xs"></i> Tambah User
        </a>
    </div>

    @if(session('success'))
        <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm">
            <i class="fa-solid fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            [$totalUsers,    'Total User',       'fa-users',       'bg-blue-100 dark:bg-blue-900/30 text-blue-600'],
            [$activeUsers,   'User Aktif',        'fa-user-check',  'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600'],
            [$inactiveUsers, 'User Nonaktif',     'fa-user-xmark',  'bg-rose-100 dark:bg-rose-900/30 text-rose-600'],
            [$newThisMonth,  'Baru Bulan Ini',    'fa-user-plus',   'bg-amber-100 dark:bg-amber-900/30 text-amber-600'],
        ] as [$v, $l, $ic, $cls])
        <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl {{ $cls }} flex items-center justify-center flex-shrink-0">
                <i class="fa-solid {{ $ic }} text-lg"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-stone-800 dark:text-white">{{ $v }}</p>
                <p class="text-xs text-stone-400">{{ $l }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Quick nav --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('admin.users') }}" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-users text-xl"></i>
            </div>
            <div>
                <p class="font-bold text-stone-800 dark:text-white">Manajemen User</p>
                <p class="text-xs text-stone-400">Tambah, nonaktifkan, atau hapus pengguna</p>
            </div>
            <i class="fa-solid fa-chevron-right text-stone-300 ml-auto"></i>
        </a>
        <a href="{{ route('admin.landing') }}" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 text-orange-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-newspaper text-xl"></i>
            </div>
            <div>
                <p class="font-bold text-stone-800 dark:text-white">Konten Landing Page</p>
                <p class="text-xs text-stone-400">Kelola fitur & informasi halaman utama</p>
            </div>
            <i class="fa-solid fa-chevron-right text-stone-300 ml-auto"></i>
        </a>
    </div>

    {{-- Recent users --}}
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100 dark:border-stone-800">
            <h3 class="font-bold text-stone-800 dark:text-white text-sm">User Terbaru</h3>
            <a href="{{ route('admin.users') }}" class="text-xs text-orange-500 hover:text-orange-600">Lihat semua</a>
        </div>
        <div class="divide-y divide-stone-100 dark:divide-stone-800">
            @forelse($recentUsers as $u)
            <div class="flex items-center gap-4 px-6 py-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-rose-500 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                    {{ strtoupper(substr($u->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-stone-800 dark:text-white truncate">{{ $u->name }}</p>
                    <p class="text-xs text-stone-400 truncate">{{ $u->email }} · {{ '@' }}{{ $u->username ?? '-' }}</p>
                </div>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold {{ $u->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' }}">
                    {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            @empty
            <div class="text-center py-8 text-stone-400 text-sm">Belum ada pengguna.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection
