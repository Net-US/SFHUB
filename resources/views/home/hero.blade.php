<!-- Hero Section -->
<header class="pt-28 pb-16 hero-gradient relative overflow-hidden border-b border-stone-200/70 dark:border-stone-800">
    @if ($hero && $hero->hero_image)
        <div class="absolute inset-0 z-0">
            <img src="{{ $hero->hero_image }}" alt="Hero Background" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/45"></div>
        </div>
    @endif

    <div class="landing-container relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
            <div>
                <span class="brand-pill mb-5">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    Student & Freelancer OS
                </span>

                @if ($hero)
                    <h1 class="text-4xl md:text-6xl font-extrabold text-stone-900 dark:text-white leading-[1.05] mb-5">
                        {!! $hero->title !!}
                    </h1>
                    <p class="text-lg text-stone-600 dark:text-stone-300 leading-relaxed max-w-xl mb-7">
                        {!! $hero->subtitle !!}
                    </p>
                @else
                    <h1 class="text-4xl md:text-6xl font-extrabold text-stone-900 dark:text-white leading-[1.05] mb-5">
                        Develop skillset & bangun
                        <span class="text-orange-500">masa depan</span>
                        tanpa chaos jadwal.
                    </h1>
                    <p class="text-lg text-stone-600 dark:text-stone-300 leading-relaxed max-w-xl mb-7">
                        SFHUB membantu kamu mengatur kuliah, kerja freelance, dan investasi dalam satu alur yang rapi,
                        jelas, dan bisa ditindaklanjuti setiap hari.
                    </p>
                @endif

                <div class="flex flex-col sm:flex-row gap-3 mb-7">
                    @guest
                        <a href="{{ route('register') }}"
                            class="px-7 py-3.5 bg-orange-500 text-white rounded-xl font-bold shadow-lg shadow-orange-200 dark:shadow-none hover:bg-orange-600 transition-all text-center">
                            {{ $hero?->cta_text ?: 'Mulai Gratis Sekarang' }}
                        </a>
                        <a href="{{ route('login') }}"
                            class="px-7 py-3.5 bg-white/90 dark:bg-stone-900 border border-stone-200 dark:border-stone-700 text-stone-700 dark:text-stone-200 rounded-xl font-semibold hover:bg-stone-50 dark:hover:bg-stone-800 transition-all text-center">
                            Sudah punya akun
                        </a>
                    @else
                        <a href="{{ $hero?->cta_link ?: route('dashboard') }}"
                            class="px-7 py-3.5 bg-orange-500 text-white rounded-xl font-bold shadow-lg shadow-orange-200 dark:shadow-none hover:bg-orange-600 transition-all text-center">
                            {{ $hero?->cta_text ?: 'Lihat Dashboard' }}
                        </a>
                    @endguest
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach ([['5K+', 'Mahasiswa Aktif'], ['2.5K+', 'Project Freelance'], ['10K+', 'Task Selesai'], ['4.9/5', 'Rating Pengguna']] as [$value, $label])
                        <div
                            class="bg-white/75 dark:bg-stone-900/70 backdrop-blur-sm rounded-xl border border-stone-200/70 dark:border-stone-700 p-3">
                            <p class="font-extrabold text-stone-900 dark:text-white">{{ $value }}</p>
                            <p class="text-[11px] text-stone-500 dark:text-stone-400">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative">
                <div
                    class="bg-white dark:bg-stone-900 rounded-3xl border border-stone-200 dark:border-stone-700 shadow-2xl overflow-hidden floating-card">
                    <div
                        class="bg-stone-100 dark:bg-stone-950 px-4 py-3 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-400"></div>
                        </div>
                        <span class="text-[11px] text-stone-500 dark:text-stone-400">Daily Smart Board</span>
                    </div>

                    <div class="p-6 space-y-4">
                        @foreach ([['fa-user-graduate', 'Academic Focus', 'Prioritaskan tugas kuliah + deadline terdekat', 'bg-orange-50 dark:bg-orange-900/20', 'text-orange-500'], ['fa-briefcase', 'Freelance Tasks', 'Kelola revisi klien dan progress deliverables', 'bg-blue-50 dark:bg-blue-900/20', 'text-blue-500'], ['fa-coins', 'Investment Snapshot', 'Pantau aset crypto dan performa portofolio', 'bg-emerald-50 dark:bg-emerald-900/20', 'text-emerald-500']] as [$icon, $title, $desc, $bg, $color])
                            <div
                                class="p-4 rounded-2xl border border-stone-200 dark:border-stone-700 {{ $bg }}">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-white dark:bg-stone-900 flex items-center justify-center border border-stone-200 dark:border-stone-700 flex-shrink-0">
                                        <i class="fa-solid {{ $icon }} {{ $color }}"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-stone-900 dark:text-white text-sm">
                                            {{ $title }}</h3>
                                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">{{ $desc }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div
                    class="absolute -top-6 -right-4 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-700 shadow-lg p-3 w-44">
                    <p class="text-[11px] text-stone-400 mb-1">Next Priority</p>
                    <p class="text-sm font-semibold text-stone-900 dark:text-white">Deadline PKL Report</p>
                    <p class="text-[11px] text-stone-500">Besok, 10:00</p>
                </div>
            </div>
        </div>
    </div>
</header>
