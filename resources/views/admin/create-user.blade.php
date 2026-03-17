@extends('layouts.app-dashboard')
@section('title', 'Tambah User | Admin')
@section('page-title', 'Tambah User Baru')

@section('content')
<div class="max-w-lg">

    <div class="mb-5">
        <a href="{{ route('admin.users') }}" class="text-stone-400 hover:text-stone-700 dark:hover:text-white text-sm flex items-center gap-2">
            <i class="fa-solid fa-arrow-left text-xs"></i> Kembali ke Daftar User
        </a>
    </div>

    @if($errors->any())
        <div class="p-3 mb-4 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 rounded-xl text-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm">
        <div class="p-6 border-b border-stone-100 dark:border-stone-800">
            <h3 class="font-bold text-stone-900 dark:text-white">Tambah Pengguna Baru</h3>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Nama Lengkap *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                    placeholder="Nama lengkap pengguna">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="email@contoh.com">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Auto-generate jika kosong">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Password *</label>
                    <input type="password" name="password" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Min. 8 karakter">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Konfirmasi Password *</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Ulangi password">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Role</label>
                    <select name="role" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <option value="student">Mahasiswa</option>
                        <option value="freelancer">Freelancer</option>
                        <option value="both">Keduanya</option>
                        <option value="entrepreneur">Entrepreneur</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Plan</label>
                    <select name="plan" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <option value="free">Free</option>
                        <option value="pro">Pro</option>
                        <option value="team">Team</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <a href="{{ route('admin.users') }}"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 text-sm text-center hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">Batal</a>
                <button type="submit"
                    class="flex-1 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-user-plus mr-1.5"></i>Tambah User
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
