@extends('layouts.app-landing')

@section('title', 'Daftar StudentHub')

@section('content')
    <section class="min-h-screen flex items-center justify-center py-20 hero-gradient">
        <div class="max-w-2xl w-full mx-4">
            <div class="form-glass p-8 md:p-10 rounded-3xl shadow-xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-stone-900 dark:text-white mb-2">Mulai Perjalanan Produktifmu</h2>
                    <p class="text-stone-600 dark:text-stone-400">
                        Bergabung dengan ribuan mahasiswa kreatif lainnya
                    </p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6"
                    enctype="multipart/form-data">
                    @csrf
                    <!-- Avatar Upload -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-stone-700 dark:text-stone-300 mb-2">Foto Profil
                            (Opsional)</label>
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 rounded-full bg-stone-200 dark:bg-stone-700 overflow-hidden">
                                <img id="avatar-preview" src="#" alt="Preview"
                                    class="hidden w-full h-full object-cover">
                                <div id="avatar-placeholder"
                                    class="w-full h-full flex items-center justify-center text-stone-400">
                                    <i class="fa-solid fa-user text-xl"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="avatar" id="avatar" accept="image/*"
                                    class="w-full px-4 py-3 rounded-xl border border-stone-200 dark:border-stone-600 dark:bg-stone-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all text-sm"
                                    onchange="previewAvatar(event)">
                                <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Ukuran maksimal 2MB</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-stone-700 dark:text-stone-300 mb-2">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Masukkan nama kamu"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-stone-200 dark:border-stone-600 dark:bg-stone-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all" />
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-stone-700 dark:text-stone-300 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-stone-200 dark:border-stone-600 dark:bg-stone-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all" />
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-stone-700 dark:text-stone-300 mb-2">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required
                                class="w-full px-4 py-3 pr-12 rounded-xl border border-stone-200 dark:border-stone-600 dark:bg-stone-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all" />

                            <button type="button" onclick="togglePassword('password')"
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-stone-500 hover:text-stone-700 dark:hover:text-stone-300 focus:outline-none">
                                <i id="icon-password" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-stone-700 dark:text-stone-300 mb-2">Konfirmasi
                            Password</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                class="w-full px-4 py-3 pr-12 rounded-xl border border-stone-200 dark:border-stone-600 dark:bg-stone-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all" />

                            <button type="button" onclick="togglePassword('password_confirmation')"
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-stone-500 hover:text-stone-700 dark:hover:text-stone-300 focus:outline-none">
                                <i id="icon-password_confirmation" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-stone-700 dark:text-stone-300 mb-2">Status Utama</label>
                        <select name="role"
                            class="w-full px-4 py-3 rounded-xl border border-stone-200 dark:border-stone-600 dark:bg-stone-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all appearance-none">
                            <option value="both" {{ old('role') == 'both' ? 'selected' : '' }}>Mahasiswa & Freelancer
                            </option>
                            <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Mahasiswa Full-time
                            </option>
                            <option value="freelancer" {{ old('role') == 'freelancer' ? 'selected' : '' }}>
                                Freelancer/Content Creator</option>
                            <option value="entrepreneur" {{ old('role') == 'entrepreneur' ? 'selected' : '' }}>Mahasiswa &
                                Wirausaha</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-stone-700 dark:text-stone-300 mb-2">Pilih Paket
                            Awal</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <label
                                class="border-2 border-stone-200 dark:border-stone-600 dark:hover:border-orange-500 rounded-xl p-4 cursor-pointer hover:border-orange-300 transition-colors">
                                <input type="radio" name="plan" value="free" class="sr-only"
                                    {{ old('plan') == 'free' ? 'checked' : '' }} />
                                <div class="font-bold text-stone-800 dark:text-white mb-1">Mahasiswa</div>
                                <div class="text-2xl font-bold text-stone-900 dark:text-white mb-2">Gratis</div>
                                <p class="text-xs text-stone-500 dark:text-stone-400">Untuk memulai</p>
                            </label>
                            <label
                                class="border-2 border-orange-300 bg-orange-50 dark:bg-orange-900/40 dark:border-orange-500 rounded-xl p-4 cursor-pointer">
                                <input type="radio" name="plan" value="pro" class="sr-only"
                                    {{ old('plan') == 'pro' ? 'checked' : 'checked' }} />
                                <div class="font-bold text-stone-800 dark:text-white mb-1">Kreator</div>
                                <div class="text-2xl font-bold text-stone-900 dark:text-white mb-2">Donasi Rp 49k</div>
                                <p class="text-xs text-stone-500 dark:text-stone-400">30 hari uji coba</p>
                            </label>
                            <label
                                class="border-2 border-stone-200 dark:border-stone-600 dark:hover:border-orange-500 rounded-xl p-4 cursor-pointer hover:border-orange-300 transition-colors">
                                <input type="radio" name="plan" value="team" class="sr-only"
                                    {{ old('plan') == 'team' ? 'checked' : '' }} />
                                <div class="font-bold text-stone-800 dark:text-white mb-1">Tim</div>
                                <div class="text-2xl font-bold text-stone-900 dark:text-white mb-2">Donasi Rp 99k</div>
                                <p class="text-xs text-stone-500 dark:text-stone-400">Untuk kolaborasi</p>
                            </label>
                        </div>

                        <!-- Informasi Donasi -->
                        <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-xl mb-4">
                            <p class="text-sm text-stone-700 dark:text-stone-300 mb-2">
                                <i class="fa-solid fa-heart text-orange-500 mr-2"></i>
                                <strong>Catatan Penting:</strong> Untuk paket Kreator dan Tim, kami mengadopsi model donasi
                                sukarela.
                            </p>
                            <p class="text-xs text-stone-600 dark:text-stone-400">
                                Donasi Anda akan digunakan untuk pengembangan platform dan memberikan akses premium selama
                                30 hari.
                                Setelah masa uji coba, Anda dapat memilih untuk melanjutkan donasi atau beralih ke paket
                                gratis.
                            </p>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="terms"
                                class="rounded border-stone-300 text-orange-500 focus:ring-orange-500" required />
                            <span class="ml-2 text-sm text-stone-700 dark:text-stone-400">
                                Saya menyetujui <a href="#" class="text-orange-500 hover:text-orange-600">Ketentuan
                                    Layanan</a> dan
                                <a href="#" class="text-orange-500 hover:text-orange-600">Kebijakan Privasi</a>
                            </span>
                        </label>
                        @error('terms')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit"
                            class="w-full py-4 bg-stone-900 text-white dark:bg-orange-500 dark:hover:bg-orange-600 rounded-xl font-bold hover:bg-stone-800 transition-all shadow-lg text-lg">
                            Buat Akun StudentHub Saya <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </div>

                    <div class="md:col-span-2 text-center">
                        <p class="text-sm text-stone-600 dark:text-stone-400">
                            Sudah punya akun?
                            <a href="{{ route('login') }}"
                                class="text-orange-500 hover:text-orange-600 font-medium">Masuk
                                di sini</a>
                        </p>
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
