@extends('layouts.app-landing')

@section('title', 'StudentHub | Platform All-in-One untuk Mahasiswa & Freelancer')

@section('content')
    @include('home.hero')

    {{-- FEATURES SECTION - Like Image Design --}}
    <section class="landing-section bg-white dark:bg-stone-900 overflow-hidden" id="fitur">
        <div class="landing-container">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                {{-- Left Title --}}
                <div class="lg:col-span-4 lg:sticky lg:top-24">
                    <span class="brand-pill">Fitur Utama</span>
                    <h2 class="text-3xl lg:text-4xl font-bold text-stone-900 dark:text-white mt-4 mb-4">
                        Kenapa memilih <span class="text-orange-500">SFHUB?</span>
                    </h2>
                    <p class="text-stone-600 dark:text-stone-400 mb-6">
                        Platform all-in-one yang dirancang khusus untuk mahasiswa Indonesia. Kelola akademik, karir, dan
                        finansial dalam satu dashboard.
                    </p>
                    <img src="{{ asset('images/1974.jpg') }}" alt="Student Character"
                        class="w-full max-w-[280px] rounded-2xl shadow-lg hidden lg:block">
                </div>

                {{-- Right Features Grid --}}
                <div class="lg:col-span-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($features as $index => $feat)
                            @php
                                $iconColors = [
                                    'text-blue-500',
                                    'text-orange-500',
                                    'text-emerald-500',
                                    'text-purple-500',
                                ];
                                $bgColors = [
                                    'bg-blue-50 dark:bg-blue-900/20',
                                    'bg-orange-50 dark:bg-orange-900/20',
                                    'bg-emerald-50 dark:bg-emerald-900/20',
                                    'bg-purple-50 dark:bg-purple-900/20',
                                ];
                                $color = $iconColors[$index % 4];
                                $bg = $bgColors[$index % 4];
                            @endphp
                            <div
                                class="flex gap-4 p-5 bg-stone-50 dark:bg-stone-800 rounded-xl border border-stone-100 dark:border-stone-700 hover:shadow-lg transition-shadow group">
                                <div
                                    class="w-12 h-12 rounded-xl {{ $bg }} flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid {{ $feat->icon ?? 'fa-star' }} text-xl {{ $color }}"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-stone-900 dark:text-white text-lg mb-2">{{ $feat->title }}
                                    </h3>
                                    <p class="text-stone-600 dark:text-stone-400 text-sm leading-relaxed">
                                        {{ $feat->description }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- STATS SECTION - Responsive Horizontal Cards --}}
    <section class="py-12 sm:py-16 bg-stone-100 dark:bg-stone-800" id="stats">
        <div class="landing-container">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach ($stats as $index => $stat)
                    @php
                        $colors = [
                            [
                                'bg' => 'bg-amber-100 dark:bg-amber-900/30',
                                'icon' => 'text-amber-600 dark:text-amber-400',
                                'border' => 'border-amber-200 dark:border-amber-800',
                            ],
                            [
                                'bg' => 'bg-blue-100 dark:bg-blue-900/30',
                                'icon' => 'text-blue-600 dark:text-blue-400',
                                'border' => 'border-blue-200 dark:border-blue-800',
                            ],
                            [
                                'bg' => 'bg-purple-100 dark:bg-purple-900/30',
                                'icon' => 'text-purple-600 dark:text-purple-400',
                                'border' => 'border-purple-200 dark:border-purple-800',
                            ],
                            [
                                'bg' => 'bg-emerald-100 dark:bg-emerald-900/30',
                                'icon' => 'text-emerald-600 dark:text-emerald-400',
                                'border' => 'border-emerald-200 dark:border-emerald-800',
                            ],
                        ];
                        $color = $colors[$index % 4];
                    @endphp
                    <div
                        class="stat-card flex items-center gap-3 sm:gap-4 bg-white dark:bg-stone-800 rounded-xl p-4 sm:p-5 border {{ $color['border'] }}">
                        <div
                            class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg {{ $color['bg'] }} flex items-center justify-center flex-shrink-0">
                            <i
                                class="fa-solid {{ $stat->icon ?? 'fa-chart-line' }} text-xl sm:text-2xl {{ $color['icon'] }}"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-stone-900 dark:text-white truncate">
                                {{ $stat->value }}
                            </p>
                            <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400">{{ $stat->label }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- STUDENT FEEDBACK - Horizontal Scroll Carousel --}}
    <section class="landing-section bg-white dark:bg-stone-900 overflow-hidden" id="testimonials">
        <div class="landing-container">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <span class="brand-pill">Student Feedback</span>
                    <h2 class="landing-section-title mt-3">Apa kata pengguna SFHUB</h2>
                </div>
                <div class="flex gap-3">
                    <button id="scroll-left"
                        class="w-12 h-12 rounded-full border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:bg-orange-50 hover:border-orange-300 hover:text-orange-500 transition-all flex items-center justify-center">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button id="scroll-right"
                        class="w-12 h-12 rounded-full border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-600 dark:text-stone-400 hover:bg-orange-50 hover:border-orange-300 hover:text-orange-500 transition-all flex items-center justify-center">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div id="testimonials-scroll" class="flex justify-center gap-9 overflow-x-auto scrollbar-hide pb-4 snap-x snap-mandatory"
                style="scrollbar-width: none; -ms-overflow-style: none;">
                @foreach ($testimonials as $testimonial)
                    <div class="flex-shrink-0 w-[320px] md:w-[380px] snap-start">
                        <div
                            class="bg-stone-50 dark:bg-stone-800 rounded-2xl p-6 border border-stone-200 dark:border-stone-700 h-full hover:shadow-xl transition-shadow">
                            <div class="flex items-center gap-4 mb-4">
                                @if ($testimonial->avatar)
                                    <img src="{{ $testimonial->avatar }}" alt="{{ $testimonial->name }}"
                                        class="w-14 h-14 rounded-full object-cover">
                                @else
                                    <div
                                        class="w-14 h-14 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold text-lg">
                                        {{ substr($testimonial->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <h4 class="font-bold text-stone-900 dark:text-white">{{ $testimonial->name }}</h4>
                                    <p class="text-sm text-stone-500 dark:text-stone-400">{{ $testimonial->role }}</p>
                                </div>
                            </div>
                            <div class="flex gap-1 mb-3">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i
                                        class="fa-solid fa-star {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-stone-300 dark:text-stone-600' }} text-sm"></i>
                                @endfor
                            </div>
                            <p class="text-stone-600 dark:text-stone-300 text-sm leading-relaxed">
                                "{{ $testimonial->content }}"</p>
                        </div>
                    </div>
                @endforeach

                {{-- Duplicate for infinite scroll effect --}}
                @foreach ($testimonials->take(3) as $testimonial)
                    <div class="flex-shrink-0 w-[320px] md:w-[380px] snap-start">
                        <div
                            class="bg-stone-50 dark:bg-stone-800 rounded-2xl p-6 border border-stone-200 dark:border-stone-700 h-full hover:shadow-xl transition-shadow">
                            <div class="flex items-center gap-4 mb-4">
                                @if ($testimonial->avatar)
                                    <img src="{{ $testimonial->avatar }}" alt="{{ $testimonial->name }}"
                                        class="w-14 h-14 rounded-full object-cover">
                                @else
                                    <div
                                        class="w-14 h-14 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold text-lg">
                                        {{ substr($testimonial->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <h4 class="font-bold text-stone-900 dark:text-white">{{ $testimonial->name }}</h4>
                                    <p class="text-sm text-stone-500 dark:text-stone-400">{{ $testimonial->role }}</p>
                                </div>
                            </div>
                            <div class="flex gap-1 mb-3">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i
                                        class="fa-solid fa-star {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-stone-300 dark:text-stone-600' }} text-sm"></i>
                                @endfor
                            </div>
                            <p class="text-stone-600 dark:text-stone-300 text-sm leading-relaxed">
                                "{{ $testimonial->content }}"</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <script>
            (function() {
                const scrollContainer = document.getElementById('testimonials-scroll');
                const scrollLeftBtn = document.getElementById('scroll-left');
                const scrollRightBtn = document.getElementById('scroll-right');
                const cardWidth = 380 + 24; // card width + gap

                scrollLeftBtn.addEventListener('click', () => {
                    scrollContainer.scrollBy({
                        left: -cardWidth,
                        behavior: 'smooth'
                    });
                });

                scrollRightBtn.addEventListener('click', () => {
                    scrollContainer.scrollBy({
                        left: cardWidth,
                        behavior: 'smooth'
                    });
                });
            })();
        </script>
    </section>

    {{-- HOW IT WORKS - Improved Steps with Better Connectors --}}
    <section class="landing-section bg-stone-100 dark:bg-stone-800 overflow-hidden" id="cara-kerja">
        <div class="landing-container">
            {{-- Header Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 items-end mb-10 lg:mb-16">
                <div>
                    <span class="brand-pill">Cara Kerja</span>
                    <h2
                        class="text-2xl sm:text-3xl lg:text-4xl font-bold text-stone-900 dark:text-white mt-4 leading-tight">
                        Bagaimana kami membantu mahasiswa <span class="text-orange-500">sukses berkarir</span>
                    </h2>
                </div>
                <div class="lg:text-right">
                    <p class="text-stone-600 dark:text-stone-400 mb-4 text-responsive-sm">Platform yang dirancang untuk
                        memudahkan perjalanan
                        akademik dan profesionalmu.</p>
                    @guest
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition-colors text-sm sm:text-base">
                            Mulai Sekarang <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition-colors text-sm sm:text-base">
                            Dashboard <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endguest
                </div>
            </div>

            {{-- Steps with Connectors --}}
            <div class="relative">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                    @foreach ([['icon' => 'fa-user-graduate', 'title' => 'Dukungan Akademik', 'desc' => 'Kami peduli dengan perkembangan studimu. Dapatkan bantuan manajemen jadwal, skripsi, dan PKL.'], ['icon' => 'fa-lightbulb', 'title' => 'Pembangun Karir', 'desc' => 'Belajar sepanjang hayat dengan tools dan mentor yang membantumu berkembang setiap hari.'], ['icon' => 'fa-hands-helping', 'title' => 'Bimbingan Personal', 'desc' => 'Tidak sendirian! Dapatkan dukungan saat menghadapi tantangan akademik maupun karir.']] as $index => $step)
                        <div class="relative text-center group">
                            {{-- Step Number Badge --}}
                            <div
                                class="absolute -top-3 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-orange-500 text-white text-sm font-bold flex items-center justify-center z-10 shadow-lg">
                                {{ $index + 1 }}
                            </div>

                            {{-- Connector Arrow (hidden on mobile, visible on lg) --}}
                            @if ($index < 2)
                                <div class="hidden lg:block absolute top-12 left-full -translate-x-1/2 z-0">
                                    <svg width="60" height="24" viewBox="0 0 60 24" fill="none"
                                        class="text-orange-400">
                                        <path d="M0 12 Q 30 12, 50 12" stroke="currentColor" stroke-width="2" fill="none"
                                            stroke-dasharray="5 3" />
                                        <polygon points="52,8 60,12 52,16" fill="currentColor" />
                                    </svg>
                                </div>
                            @endif

                            {{-- Icon Circle --}}
                            <div class="relative inline-block mb-5 mt-4">
                                <div
                                    class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-white dark:bg-stone-700 shadow-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <div
                                        class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center">
                                        <i class="fa-solid {{ $step['icon'] }} text-xl sm:text-2xl text-white"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Content --}}
                            <h3 class="text-lg sm:text-xl font-bold text-stone-900 dark:text-white mb-2 sm:mb-3">
                                {{ $step['title'] }}</h3>
                            <p class="text-stone-600 dark:text-stone-400 text-sm leading-relaxed max-w-xs mx-auto">
                                {{ $step['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- LATEST BLOG POSTS --}}
    <section class="landing-section bg-white dark:bg-stone-900" id="blog">
        <div class="landing-container">
            <div class="text-center mb-14">
                <span class="brand-pill">Blog & Insight</span>
                <h2 class="landing-section-title mt-4">
                    Tips & <span class="text-orange-500">Inspirasi</span> Terbaru
                </h2>
                <p class="landing-section-subtitle mx-auto">
                    Artikel ringkas dan praktis untuk bantu kamu tetap konsisten membangun skill, karir, dan finansial.
                </p>
            </div>

            @php
                $latestPosts = \App\Models\BlogPost::with(['user', 'categories'])
                    ->where('status', 'published')
                    ->orderBy('published_at', 'desc')
                    ->take(3)
                    ->get();
            @endphp

            @if ($latestPosts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach ($latestPosts as $post)
                        <article
                            class="group bg-stone-50 dark:bg-stone-800 rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 border border-stone-200 dark:border-stone-700">
                            @if ($post->featured_image)
                                <div class="aspect-w-16 aspect-h-9 overflow-hidden">
                                    <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}"
                                        class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                            @else
                                <div
                                    class="w-full h-48 bg-gradient-to-br from-orange-400 to-rose-400 flex items-center justify-center">
                                    <i class="fa-solid fa-newspaper text-white text-4xl"></i>
                                </div>
                            @endif
                            <div class="p-6">
                                <div class="flex items-center gap-2 mb-3">
                                    @foreach ($post->categories->take(2) as $category)
                                        <span
                                            class="px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-xs font-medium rounded-full">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                    @if ($post->featured)
                                        <span
                                            class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 text-xs font-medium rounded-full">
                                            <i class="fa-solid fa-star mr-1"></i>Unggulan
                                        </span>
                                    @endif
                                </div>
                                <h3
                                    class="font-bold text-lg text-stone-900 dark:text-white mb-2 group-hover:text-orange-500 transition-colors">
                                    <a href="{{ route('blog.show', $post->slug) }}">
                                        {{ $post->title }}
                                    </a>
                                </h3>
                                <p class="text-stone-600 dark:text-stone-300 text-sm mb-4 line-clamp-2">
                                    {{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 100) }}
                                </p>
                                <div class="flex items-center justify-between text-xs text-stone-500 dark:text-stone-400">
                                    <div class="flex items-center gap-2">
                                        @if ($post->user->avatar)
                                            <img src="{{ asset($post->user->avatar) }}" alt="{{ $post->user->name }}"
                                                class="w-5 h-5 rounded-full">
                                        @else
                                            <div
                                                class="w-5 h-5 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                                <i
                                                    class="fa-solid fa-user text-orange-600 dark:text-orange-400 text-xs"></i>
                                            </div>
                                        @endif
                                        <span>{{ $post->user->name }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span><i class="fa-solid fa-eye mr-1"></i>{{ $post->views }}</span>
                                        <span>{{ $post->published_at->format('d M Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="text-center mt-12">
                    <a href="{{ route('blog.index') }}"
                        class="inline-flex items-center gap-2 px-8 py-4 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold shadow-lg shadow-orange-200 dark:shadow-none transition-all">
                        Lihat Semua Artikel
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fa-solid fa-newspaper text-6xl text-stone-300 dark:text-stone-600 mb-4"></i>
                    <h3 class="text-xl font-semibold text-stone-900 dark:text-white mb-2">Belum ada artikel</h3>
                    <p class="text-stone-600 dark:text-stone-300">
                        Nantikan artikel menarik dari kami segera!
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- FAQ SECTION - Dynamic from Database --}}
    @php
        $faqCategories = \App\Models\FaqCategory::with([
            'faqs' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            },
        ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    @endphp
    {{-- CTA SECTION --}}
    <section class="landing-section bg-white dark:bg-stone-900">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <div
                class="rounded-3xl bg-gradient-to-r from-orange-500 to-rose-500 text-white p-8 md:p-10 flex flex-col md:flex-row items-center justify-between gap-6 shadow-xl">
                <div class="text-left max-w-xl">
                    <h2 class="text-3xl md:text-4xl font-extrabold mb-2">Siap naik level bareng SFHUB?</h2>
                    <p class="text-orange-100 text-sm md:text-base">Atur ritme kuliah, proyek kerja, dan finansial jadi
                        satu
                        sistem yang rapi dan berkelanjutan.</p>
                </div>
                @guest
                    <a href="{{ route('register') }}"
                        class="px-6 py-3 bg-white text-stone-900 rounded-xl font-bold hover:bg-stone-100 transition-colors whitespace-nowrap">
                        Mulai Sekarang
                    </a>
                @else
                    <a href="{{ route('dashboard') }}"
                        class="px-6 py-3 bg-white text-stone-900 rounded-xl font-bold hover:bg-stone-100 transition-colors whitespace-nowrap">
                        Buka Dashboard
                    </a>
                @endguest
            </div>
        </div>
    </section>

    @if ($faqCategories->isNotEmpty() && $faqCategories->pluck('faqs')->flatten()->isNotEmpty())
        <section class="landing-section bg-stone-50 dark:bg-stone-800" id="faq">
            <div class="landing-container">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
                    {{-- Left: Title & Description --}}
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-stone-900 dark:text-white mb-4">
                            Pertanyaan yang <span class="text-orange-500">Sering Diajukan</span>
                        </h2>
                        <p class="text-stone-600 dark:text-stone-400 mb-6">
                            Kami menjawab pertanyaan yang paling sering ditanyakan. Jika tidak menemukan jawaban yang tepat,
                            hubungi tim support kami atau cari di halaman <a href="{{ route('blog.index') }}"
                                class="text-orange-500 hover:underline">Blog</a>.
                        </p>
                        <a href="{{ route('contact') }}"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition-colors">
                            <i class="fa-solid fa-headset"></i>
                            Hubungi Support
                        </a>
                    </div>

                    {{-- Right: Accordion --}}
                    <div class="space-y-3">
                        @foreach ($faqCategories as $category)
                            @foreach ($category->faqs as $faq)
                                <div
                                    class="faq-item bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 overflow-hidden">
                                    <button
                                        class="faq-trigger w-full flex items-center justify-between p-5 text-left hover:bg-stone-50 dark:hover:bg-stone-800/50 transition-colors"
                                        onclick="toggleFaq(this)">
                                        <span
                                            class="font-semibold text-stone-900 dark:text-white pr-4">{{ $faq->question }}</span>
                                        <span
                                            class="faq-icon w-8 h-8 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center flex-shrink-0 transition-transform">
                                            <i class="fa-solid fa-plus text-stone-500 dark:text-stone-400 text-sm"></i>
                                        </span>
                                    </button>
                                    <div class="faq-content hidden px-5 pb-5">
                                        <div class="pt-2 border-t border-stone-100 dark:border-stone-800">
                                            <p class="text-stone-600 dark:text-stone-400 text-sm leading-relaxed pt-3">
                                                {{ $faq->answer }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>

            <script>
                function toggleFaq(button) {
                    const item = button.closest('.faq-item');
                    const content = item.querySelector('.faq-content');
                    const icon = item.querySelector('.faq-icon');
                    const isOpen = !content.classList.contains('hidden');

                    // Close all other items
                    document.querySelectorAll('.faq-content').forEach(c => {
                        if (c !== content) c.classList.add('hidden');
                    });
                    document.querySelectorAll('.faq-icon').forEach(i => {
                        if (i !== icon) {
                            i.classList.remove('rotate-45');
                            i.querySelector('i').classList.replace('fa-minus', 'fa-plus');
                        }
                    });

                    // Toggle current
                    if (isOpen) {
                        content.classList.add('hidden');
                        icon.classList.remove('rotate-45');
                        icon.querySelector('i').classList.replace('fa-minus', 'fa-plus');
                    } else {
                        content.classList.remove('hidden');
                        icon.classList.add('rotate-45');
                        icon.querySelector('i').classList.replace('fa-plus', 'fa-minus');
                    }
                }
            </script>
        </section>
    @endif

@endsection
