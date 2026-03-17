@extends('layouts.app')

@section('title', 'Masuk ke StudentHub')

@section('content')
    <section class="min-h-screen flex items-center justify-center py-20 hero-gradient">
        <div class="max-w-md w-full mx-4">
            <div class="form-glass p-8 md:p-10 rounded-3xl shadow-xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-stone-900 dark:text-white mb-2">Selamat Datang Kembali!</h2>
                    <p class="text-stone-600 dark:text-stone-400">
                        Masuk untuk melanjutkan ke dashboard produktivitasmu
                    </p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    @if (session('status'))
                        <div
                            class="p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-lg text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="p-3 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg text-sm">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Email
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-3 rounded-xl border border-stone-200 dark:border-stone-600 dark:bg-stone-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all"
                            placeholder="nama@email.com" />
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">
                                Password
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                    class="text-sm text-orange-500 hover:text-orange-600">
                                    Lupa password?
                                </a>
                            @endif
                        </div>
                        <input type="password" name="password" required
                            class="w-full px-4 py-3 rounded-xl border border-stone-200 dark:border-stone-600 dark:bg-stone-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all"
                            placeholder="••••••••" />
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="remember" id="remember"
                            class="rounded border-stone-300 text-orange-500 focus:ring-orange-500" />
                        <label for="remember" class="ml-2 text-sm text-stone-700 dark:text-stone-400">
                            Ingat saya
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-orange-500 text-white rounded-xl font-bold hover:bg-orange-600 transition-all shadow-lg">
                        Masuk ke Akun
                    </button>

                    <div class="text-center text-sm text-stone-600 dark:text-stone-400">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="text-orange-500 hover:text-orange-600 font-medium">
                            Daftar gratis
                        </a>
                    </div>
                </form>

                <div class="mt-8 pt-8 border-t border-stone-200 dark:border-stone-700">
                    <div class="text-center">
                        <a href="{{ route('home') }}"
                            class="text-stone-600 dark:text-stone-400 hover:text-orange-500 dark:hover:text-orange-400 transition-colors">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
