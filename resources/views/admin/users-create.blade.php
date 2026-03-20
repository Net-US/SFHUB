@extends('layouts.app')

@section('title', 'Tambah User | Admin')

@section('page-title', 'Tambah User')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users') }}" class="text-stone-500 hover:text-stone-700 dark:text-stone-400 dark:hover:text-stone-200">
            <i class="fa-solid fa-arrow-left mr-2"></i>Kembali
        </a>
        <div>
            <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Tambah User Baru</h2>
            <p class="text-stone-500 dark:text-stone-400 text-sm">Buat akun pengguna baru</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @csrf
            
            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Nama Lengkap *</label>
                    <input type="text" name="name" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400 dark:bg-stone-800 dark:text-white"
                        value="{{ old('name') }}">
                    @error('name')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Email *</label>
                    <input type="email" name="email" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400 dark:bg-stone-800 dark:text-white"
                        value="{{ old('email') }}">
                    @error('email')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Username & Password -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Username</label>
                    <input type="text" name="username"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400 dark:bg-stone-800 dark:text-white"
                        value="{{ old('username') }}">
                    @error('username')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Password *</label>
                    <input type="password" name="password" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400 dark:bg-stone-800 dark:text-white">
                    @error('password')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Role & Plan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Role *</label>
                    <select name="role" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400 dark:bg-stone-800 dark:text-white">
                        <option value="">Pilih Role</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="moderator" {{ old('role') == 'moderator' ? 'selected' : '' }}>Moderator</option>
                        <option value="premium" {{ old('role') == 'premium' ? 'selected' : '' }}>Premium</option>
                        <option value="free" {{ old('role') == 'free' ? 'selected' : '' }}>Free</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Subscription Plan</label>
                    <select name="plan"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-400 dark:bg-stone-800 dark:text-white">
                        <option value="">Pilih Plan</option>
                        <option value="free" {{ old('plan') == 'free' ? 'selected' : '' }}>Free</option>
                        <option value="basic" {{ old('plan') == 'basic' ? 'selected' : '' }}>Basic</option>
                        <option value="premium" {{ old('plan') == 'premium' ? 'selected' : '' }}>Premium</option>
                    </select>
                    @error('plan')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked
                        class="rounded border-stone-300 text-orange-500 focus:ring-orange-400">
                    <span class="text-sm text-stone-700 dark:text-stone-300">Aktifkan akun</span>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-stone-200 dark:border-stone-700">
                <a href="{{ route('admin.users') }}"
                    class="px-4 py-2 border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-300 rounded-xl hover:bg-stone-50 dark:hover:bg-stone-800">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-medium">
                    <i class="fa-solid fa-save mr-2"></i>Simpan User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
