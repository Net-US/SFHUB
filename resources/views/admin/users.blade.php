@extends('layouts.app')
@section('title', 'Manajemen User | Admin')
@section('page-title', 'Manajemen User')

@section('content')
    <div class="space-y-5">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Manajemen User</h2>
                <p class="text-stone-400 text-xs">Kelola akun pengguna platform StudentHub</p>
            </div>
            <a href="{{ route('admin.users.create') }}"
                class="flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-medium transition-colors self-start">
                <i class="fa-solid fa-plus text-xs"></i> Tambah User
            </a>
        </div>

        @if (session('success'))
            <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm">
                <i class="fa-solid fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-3 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 rounded-xl text-sm">
                <i class="fa-solid fa-circle-xmark mr-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- Search & Filter --}}
        <form method="GET" action="{{ route('admin.users') }}" class="flex gap-2 flex-wrap">
            <input type="text" name="search" value="{{ $search }}"
                placeholder="Cari nama, email, atau username..."
                class="flex-1 min-w-52 border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white">
            @foreach (['all' => 'Semua', 'active' => 'Aktif', 'inactive' => 'Nonaktif'] as $k => $v)
                <button type="submit" name="filter" value="{{ $k }}"
                    class="px-4 py-2 text-sm rounded-xl font-medium transition-colors {{ $filter === $k ? 'bg-stone-800 dark:bg-stone-700 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:bg-stone-200' }}">
                    {{ $v }}
                </button>
            @endforeach
        </form>

        {{-- Table --}}
        <div
            class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50 dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
                        <tr>
                            @foreach (['#', 'User', 'Email', 'Username', 'Role', 'Plan', 'Status', 'Bergabung', 'Aksi'] as $h)
                                <th
                                    class="text-left py-3 px-4 text-stone-500 dark:text-stone-400 font-medium whitespace-nowrap">
                                    {{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                        @forelse($users as $i => $u)
                            <tr class="hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">
                                <td class="py-3 px-4 text-stone-400">{{ $users->firstItem() + $i }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-rose-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <span class="font-medium text-stone-800 dark:text-white">{{ $u->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-stone-500 dark:text-stone-400">{{ $u->email }}</td>
                                <td class="py-3 px-4 text-stone-500 dark:text-stone-400">{{ $u->username ?? '-' }}</td>
                                <td class="py-3 px-4"><span
                                        class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">{{ ucfirst($u->role ?? 'user') }}</span>
                                </td>
                                <td class="py-3 px-4"><span
                                        class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-stone-100 dark:bg-stone-700 text-stone-600 dark:text-stone-300">{{ ucfirst($u->plan ?? 'free') }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $u->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400' }}">
                                        {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-stone-400 whitespace-nowrap">
                                    {{ $u->created_at->format('d M Y') }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1">
                                        <form method="POST" action="{{ route('admin.users.toggle', $u) }}">
                                            @csrf
                                            <button type="submit"
                                                class="px-2.5 py-1 rounded-lg text-[11px] font-medium transition-colors {{ $u->is_active ? 'bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 hover:bg-rose-200' : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-200' }}">
                                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        @if ($u->role !== 'admin')
                                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                                onsubmit="return confirm('Hapus user {{ $u->name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-stone-100 dark:bg-stone-700 text-stone-500 hover:bg-rose-100 dark:hover:bg-rose-900/30 hover:text-rose-600 transition-colors">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10 text-stone-400">Tidak ada pengguna ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-stone-100 dark:border-stone-800">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
