<!-- Hero Section -->
<header class="pt-32 pb-20 hero-gradient">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div>
            <span
                class="inline-block px-4 py-1.5 mb-6 text-xs font-bold tracking-widest text-orange-600 dark:text-orange-400 uppercase bg-orange-100 dark:bg-orange-900/30 rounded-full">
                Platform All-in-One untuk Mahasiswa Kreatif
            </span>
            <h1 class="text-5xl md:text-6xl font-extrabold text-stone-900 dark:text-white leading-[1.1] mb-6">
                Seimbangkan <span class="text-orange-500">Kuliah</span> dan
                <span class="text-orange-500">Karir Kreatif</span> Tanpa Stress
            </h1>
            <p class="text-lg text-stone-600 dark:text-stone-300 mb-8 leading-relaxed max-w-lg">
                Sistem manajemen tugas pintar yang memahami jadwal sibuk mahasiswa.
                Atur proyek kreatif, tugas kampus, freelance, dan kehidupan pribadi dalam satu dashboard terintegrasi.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                @guest
                    <a href="{{ route('register') }}"
                        class="px-8 py-4 bg-orange-500 text-white rounded-xl font-bold shadow-lg shadow-orange-200 dark:shadow-none hover:bg-orange-600 transition-all text-center">
                        Mulai Gratis 30 Hari
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-outline btn-primary">
                        Lihat Dashboard
                    </a>
                    <button class="btn btn-outline btn-primary"> <span class="icon-[tabler--star] size-4.5 shrink-0"></span>
                        Primary </button>
                @endguest
                <div
                    class="flex items-center gap-3 px-6 py-4 bg-white/50 dark:bg-stone-800/50 rounded-xl backdrop-blur-sm">
                    <div class="flex -space-x-2">
                        <div class="w-8 h-8 rounded-full border-2 border-white dark:border-stone-800 bg-blue-400"></div>
                        <div class="w-8 h-8 rounded-full border-2 border-white dark:border-stone-800 bg-emerald-400">
                        </div>
                        <div class="w-8 h-8 rounded-full border-2 border-white dark:border-stone-800 bg-orange-400">
                        </div>
                        <div class="w-8 h-8 rounded-full border-2 border-white dark:border-stone-800 bg-purple-400">
                        </div>
                    </div>
                    <span class="text-sm font-medium text-stone-600 dark:text-stone-300">5,000+ Mahasiswa</span>
                </div>
            </div>
        </div>

        <!-- Visual Preview -->
        <div class="relative">
            <div
                class="bg-white dark:bg-stone-800 rounded-2xl shadow-2xl border border-stone-200 dark:border-stone-700 overflow-hidden floating-card transition-colors duration-300">
                <div
                    class="bg-stone-100 dark:bg-stone-900 p-3 border-b border-stone-200 dark:border-stone-700 flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                </div>
                <div class="p-6">
                    <div class="flex justify-between mb-6">
                        <div>
                            <p class="font-bold text-stone-800 dark:text-stone-100">Dashboard Harian</p>
                            <p class="text-xs text-stone-500 dark:text-stone-400">Rekomendasi Prioritas Otomatis</p>
                        </div>
                        <div
                            class="h-8 w-8 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-bolt text-orange-500"></i>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div
                            class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 rounded-xl">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase">
                                        Deadline Mendesak</p>
                                    <p class="font-bold text-stone-800 dark:text-stone-200">Skripsi Bab 3</p>
                                </div>
                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">⏰ Hari Ini</span>
                            </div>
                        </div>
                        <div
                            class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase">Proyek
                                        Kreatif</p>
                                    <p class="font-bold text-stone-800 dark:text-stone-200">Edit Video Klien</p>
                                </div>
                                <span class="text-xs font-bold text-blue-600 dark:text-blue-400">📅 2 Hari Lagi</span>
                            </div>
                        </div>
                        <div
                            class="p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800 rounded-xl">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-[10px] font-bold text-orange-600 dark:text-orange-400 uppercase">
                                        Konten Rutin</p>
                                    <p class="font-bold text-stone-800 dark:text-stone-200">Upload Instagram</p>
                                </div>
                                <span class="text-xs font-bold text-orange-600 dark:text-orange-400">🔄 Setiap
                                    Rabu</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Abstract elements -->
            <div
                class="absolute -z-10 -bottom-10 -left-10 w-32 h-32 bg-orange-200 dark:bg-orange-600 rounded-full mix-blend-multiply dark:mix-blend-screen filter blur-xl opacity-70 dark:opacity-20">
            </div>
            <div
                class="absolute -z-10 -top-10 -right-10 w-40 h-40 bg-blue-200 dark:bg-blue-600 rounded-full mix-blend-multiply dark:mix-blend-screen filter blur-xl opacity-70 dark:opacity-20">
            </div>
        </div>
    </div>
</header>
