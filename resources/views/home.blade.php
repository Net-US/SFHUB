@extends('layouts.app')

@section('title', 'StudentHub | Platform All-in-One untuk Mahasiswa & Freelancer')

@section('content')
    @include('home.hero')

    {{-- FEATURES SECTION --}}
    <section class="py-20 bg-white dark:bg-stone-900" id="fitur">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <span
                    class="inline-block px-4 py-1.5 mb-4 text-xs font-bold tracking-widest text-orange-600 dark:text-orange-400 uppercase bg-orange-100 dark:bg-orange-900/30 rounded-full">
                    Fitur Lengkap
                </span>
                <h2 class="text-4xl font-extrabold text-stone-900 dark:text-white mb-4">
                    Semua yang Kamu Butuhkan, <span class="text-orange-500">Satu Platform</span>
                </h2>
                <p class="text-lg text-stone-500 dark:text-stone-400 max-w-2xl mx-auto">
                    Dirancang khusus untuk mahasiswa aktif yang juga menjalani kehidupan sebagai freelancer, konten kreator,
                    atau magang.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($features as $feat)
                    <div
                        class="group bg-stone-50 dark:bg-stone-800 rounded-2xl p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-200 border border-stone-100 dark:border-stone-700">
                        <div
                            class="w-12 h-12 rounded-xl bg-white dark:bg-stone-700 shadow-sm flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i
                                class="fa-solid {{ $feat->icon ?? 'fa-star' }} text-xl {{ $feat->color ?? 'text-orange-500' }}"></i>
                        </div>
                        <h3 class="font-bold text-stone-900 dark:text-white text-base mb-2">{{ $feat->title }}</h3>
                        <p class="text-stone-500 dark:text-stone-400 text-sm leading-relaxed">{{ $feat->content }}</p>
                    </div>
                @empty
                    @foreach ([['fa-graduation-cap', 'text-blue-600', 'Academic Hub', 'Kelola mata kuliah, tugas, deadline, dan progres skripsi.'], ['fa-film', 'text-orange-600', 'Creative Studio', 'Kanban board proyek freelance, konten, dan Shutterstock.'], ['fa-briefcase', 'text-emerald-600', 'PKL Manager', 'Catat aktivitas harian magang dan laporan mingguan.'], ['fa-wallet', 'text-amber-600', 'Finance Tracker', 'Monitor pemasukan, pengeluaran, dan investasi.'], ['fa-calendar-days', 'text-violet-600', 'Smart Calendar', 'Kalender terintegrasi untuk semua jadwal dan deadline.'], ['fa-list-check', 'text-rose-600', 'General Tracker', 'Lacak aktivitas kesehatan, personal, dan pengembangan diri.'], ['fa-timeline', 'text-sky-600', 'Focus Today (Gantt)', 'Timeline harian + Eisenhower Matrix untuk prioritas cerdas.'], ['fa-chart-line', 'text-indigo-600', 'Analytics & Produktivitas', 'Insight produktivitas mingguan dari data nyata aktivitasmu.']] as [$ic, $color, $title, $desc])
                        <div
                            class="group bg-stone-50 dark:bg-stone-800 rounded-2xl p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-200 border border-stone-100 dark:border-stone-700">
                            <div
                                class="w-12 h-12 rounded-xl bg-white dark:bg-stone-700 shadow-sm flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <i class="fa-solid {{ $ic }} text-xl {{ $color }}"></i>
                            </div>
                            <h3 class="font-bold text-stone-900 dark:text-white text-base mb-2">{{ $title }}</h3>
                            <p class="text-stone-500 dark:text-stone-400 text-sm leading-relaxed">{{ $desc }}</p>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    {{-- STATS SECTION --}}
    <section class="py-16 bg-gradient-to-r from-orange-500 to-rose-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center text-white">
                @forelse($stats as $stat)
                    <div>
                        <p class="text-4xl font-extrabold mb-1">{{ $stat->title }}</p>
                        <p class="text-orange-100 text-sm font-medium">{{ $stat->content }}</p>
                    </div>
                @empty
                    @foreach ([['500+', 'Mahasiswa Aktif'], ['10.000+', 'Tugas Diselesaikan'], ['2.500+', 'Proyek Freelance'], ['4.9/5', 'Rating Kepuasan']] as [$v, $l])
                        <div>
                            <p class="text-4xl font-extrabold mb-1">{{ $v }}</p>
                            <p class="text-orange-100 text-sm font-medium">{{ $l }}</p>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="py-20 bg-stone-50 dark:bg-stone-800" id="cara-kerja">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-4xl font-extrabold text-stone-900 dark:text-white mb-4">Mulai dalam 3 Langkah</h2>
                <p class="text-stone-500 dark:text-stone-400 max-w-xl mx-auto">Tidak perlu setup rumit. Langsung produktif
                    dari hari pertama.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ([['1', 'Daftar Akun', 'Buat akun gratis dalam 30 detik. Tidak perlu kartu kredit.', 'fa-user-plus', 'bg-blue-500'], ['2', 'Setup Dashboard', 'Isi jadwal kuliah, info PKL, dan proyek aktifmu.', 'fa-sliders', 'bg-orange-500'], ['3', 'Mulai Produktif', 'Gunakan semua fitur untuk mengelola aktivitas harian.', 'fa-rocket', 'bg-emerald-500']] as [$num, $title, $desc, $ic, $bg])
                    <div class="text-center">
                        <div
                            class="w-16 h-16 {{ $bg }} rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg">
                            <i class="fa-solid {{ $ic }} text-2xl text-white"></i>
                        </div>
                        <div
                            class="w-8 h-8 rounded-full bg-stone-200 dark:bg-stone-700 text-stone-600 dark:text-stone-300 text-sm font-bold flex items-center justify-center mx-auto mb-3">
                            {{ $num }}</div>
                        <h3 class="font-bold text-stone-900 dark:text-white text-lg mb-2">{{ $title }}</h3>
                        <p class="text-stone-500 dark:text-stone-400 text-sm">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA SECTION --}}
    <section class="py-20 bg-white dark:bg-stone-900">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-4xl font-extrabold text-stone-900 dark:text-white mb-4">
                Siap Lebih Produktif Mulai Hari Ini?
            </h2>
            <p class="text-lg text-stone-500 dark:text-stone-400 mb-8">
                Bergabung dengan ribuan mahasiswa yang sudah mengelola kuliah dan karir mereka lebih efektif.
            </p>
            @guest
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}"
                        class="px-8 py-4 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold shadow-lg shadow-orange-200 dark:shadow-none transition-all">
                        Daftar Gratis Sekarang
                    </a>
                    <a href="{{ route('login') }}"
                        class="px-8 py-4 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-200 rounded-xl font-bold transition-all">
                        Sudah Punya Akun?
                    </a>
                </div>
            @else
                <a href="{{ route('dashboard') }}"
                    class="px-8 py-4 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold shadow-lg shadow-orange-200 dark:shadow-none transition-all">
                    Ke Dashboard Saya
                </a>
            @endguest
        </div>
    </section>

    @include('home.registration')

@endsection

@push('scripts')
    <script>
        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const email = this.querySelector('input[type="email"]');
                if (email && !email.value.includes("@")) {
                    e.preventDefault();
                    alert("Silakan masukkan email yang valid.");
                }
            });
        });

        // Plan selection styling
        document.querySelectorAll('input[name="plan"]').forEach((radio) => {
            radio.addEventListener("change", function() {
                document.querySelectorAll("label").forEach((label) => {
                    label.classList.remove("border-orange-300", "bg-orange-50",
                        "dark:bg-orange-900/40", "dark:border-orange-500");
                    label.classList.add("dark:hover:border-orange-500");
                });

                if (this.checked) {
                    const label = this.closest("label");
                    label.classList.remove("dark:hover:border-orange-500");
                    label.classList.add("border-orange-300", "bg-orange-50", "dark:bg-orange-900/40",
                        "dark:border-orange-500");
                }
            });
        });
    </script>
@endpush
