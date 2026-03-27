@extends('layouts.app-landing')

@section('title', $post->title . ' | Blog SFHUB')

@section('content')
    <!-- Reading Progress Bar -->
    <div id="reading-progress" class="fixed top-0 left-0 h-1 bg-orange-500 z-50 transition-all duration-150" style="width: 0%">
    </div>

    <!-- Article Header - Compact & Modern -->
    <header class="bg-white dark:bg-stone-900 border-b border-stone-200 dark:border-stone-800">
        <div class="landing-container">
            <!-- Breadcrumb -->
            <nav class="py-4">
                <a href="{{ route('blog.index') }}"
                    class="inline-flex items-center gap-2 text-sm text-stone-500 hover:text-orange-500 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali ke Blog</span>
                </a>
            </nav>

            <!-- Title Section -->
            <div class="pb-6 sm:pb-8">
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    @foreach ($post->categories as $category)
                        <a href="{{ route('blog.index', ['category' => $category->slug]) }}"
                            class="px-2.5 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-xs font-medium rounded-full hover:bg-orange-200 transition-colors">
                            {{ $category->name }}
                        </a>
                    @endforeach
                    @if ($post->featured)
                        <span
                            class="px-2.5 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 text-xs font-medium rounded-full flex items-center gap-1">
                            <i class="fa-solid fa-star text-[10px]"></i>
                            <span class="hidden sm:inline">Unggulan</span>
                        </span>
                    @endif
                </div>

                <h1
                    class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-stone-900 dark:text-white mb-4 leading-tight">
                    {{ $post->title }}
                </h1>

                <!-- Meta Info - Horizontal Scroll on Mobile -->
                <div
                    class="flex items-center gap-4 sm:gap-6 text-sm text-stone-500 dark:text-stone-400 overflow-x-auto pb-2 scrollbar-hide">
                    <div class="flex items-center gap-2 shrink-0">
                        @if ($post->user->avatar)
                            <img src="{{ asset($post->user->avatar) }}" alt="{{ $post->user->name }}"
                                class="w-8 h-8 rounded-full object-cover">
                        @else
                            <div
                                class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-user text-orange-600 dark:text-orange-400 text-xs"></i>
                            </div>
                        @endif
                        <span class="font-medium text-stone-700 dark:text-stone-300">{{ $post->user->name }}</span>
                    </div>
                    <span class="hidden sm:inline text-stone-300">|</span>
                    <span class="flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-calendar text-xs"></i>
                        {{ $post->published_at->format('d M Y') }}
                    </span>
                    <span class="flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-eye text-xs"></i>
                        {{ $post->views }} dibaca
                    </span>
                    <span class="flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-clock text-xs"></i>
                        {{ $post->published_at->diffForHumans() }}
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Featured Image - Full Width with Overlay -->
    @if ($post->featured_image)
        <section class="relative">
            <div class="landing-container">
                <div class="rounded-2xl overflow-hidden shadow-xl mt-4 sm:mt-6">
                    <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}"
                        class="w-full aspect-[16/9] object-cover">
                </div>
            </div>
        </section>
    @endif

    <!-- Article Content -->
    <article class="py-8 sm:py-12 bg-white dark:bg-stone-900">
        <div class="landing-container">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                <!-- Main Content -->
                <div class="lg:col-span-8 lg:col-start-3">
                    <!-- Excerpt Box -->
                    @if ($post->excerpt)
                        <div
                            class="bg-gradient-to-r from-orange-50 to-rose-50 dark:from-orange-900/20 dark:to-rose-900/20 border-l-4 border-orange-500 p-5 sm:p-6 mb-8 rounded-r-xl">
                            <p class="text-orange-900 dark:text-orange-200 text-sm sm:text-base leading-relaxed italic">
                                {{ $post->excerpt }}
                            </p>
                        </div>
                    @endif

                    <!-- Content Body -->
                    <div
                        class="prose prose-base sm:prose-lg max-w-none dark:prose-invert prose-stone prose-headings:font-bold prose-a:text-orange-500 prose-a:no-underline hover:prose-a:underline">
                        {!! $post->content !!}
                    </div>

                    <!-- Tags -->
                    @if ($post->tags->count() > 0)
                        <div class="mt-10 pt-6 border-t border-stone-200 dark:border-stone-800">
                            <h3 class="text-sm font-semibold text-stone-700 dark:text-stone-300 mb-3">Tag Terkait</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($post->tags as $tag)
                                    <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}"
                                        class="px-3 py-1.5 bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 text-sm rounded-full hover:bg-orange-100 hover:text-orange-700 dark:hover:bg-orange-900/30 dark:hover:text-orange-300 transition-colors">
                                        #{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Share Section -->
                    <div class="mt-8 pt-6 border-t border-stone-200 dark:border-stone-800">
                        <h3 class="text-sm font-semibold text-stone-700 dark:text-stone-300 mb-4">Bagikan artikel ini</h3>
                        <div class="flex flex-wrap gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                                target="_blank"
                                class="flex items-center gap-2 px-4 py-2 bg-[#1877f2] hover:bg-[#166fe5] text-white text-sm rounded-lg transition-colors">
                                <i class="fa-brands fa-facebook-f"></i>
                                <span class="hidden sm:inline">Facebook</span>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}"
                                target="_blank"
                                class="flex items-center gap-2 px-4 py-2 bg-[#1da1f2] hover:bg-[#1a91da] text-white text-sm rounded-lg transition-colors">
                                <i class="fa-brands fa-twitter"></i>
                                <span class="hidden sm:inline">Twitter</span>
                            </a>
                            <a href="https://wa.me/?text={{ urlencode($post->title . ' - ' . url()->current()) }}"
                                target="_blank"
                                class="flex items-center gap-2 px-4 py-2 bg-[#25d366] hover:bg-[#22bf5b] text-white text-sm rounded-lg transition-colors">
                                <i class="fa-brands fa-whatsapp"></i>
                                <span class="hidden sm:inline">WhatsApp</span>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
                                target="_blank"
                                class="flex items-center gap-2 px-4 py-2 bg-[#0a66c2] hover:bg-[#0958a8] text-white text-sm rounded-lg transition-colors">
                                <i class="fa-brands fa-linkedin-in"></i>
                                <span class="hidden sm:inline">LinkedIn</span>
                            </a>
                            <button onclick="copyLink()"
                                class="flex items-center gap-2 px-4 py-2 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-300 text-sm rounded-lg transition-colors">
                                <i class="fa-solid fa-link"></i>
                                <span class="hidden sm:inline">Salin Link</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </article>

    <!-- Author Bio Card -->
    <section class="py-8 sm:py-12 bg-stone-50 dark:bg-stone-950">
        <div class="landing-container">
            <div class="max-w-3xl mx-auto">
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 sm:p-8 shadow-sm border border-stone-200 dark:border-stone-800">
                    <div class="flex items-start gap-4 sm:gap-6">
                        @if ($post->user->avatar)
                            <img src="{{ asset($post->user->avatar) }}" alt="{{ $post->user->name }}"
                                class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover shrink-0">
                        @else
                            <div
                                class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-orange-400 to-rose-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-user text-white text-2xl sm:text-3xl"></i>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-stone-500 uppercase tracking-wider mb-1">Ditulis oleh</p>
                            <h3 class="text-lg sm:text-xl font-bold text-stone-900 dark:text-white mb-2">
                                {{ $post->user->name }}
                            </h3>
                            <p class="text-stone-600 dark:text-stone-400 text-sm mb-4">
                                Penulis SFHUB yang berdedikasi memberikan konten berkualitas untuk membantu mahasiswa dan
                                freelancer.
                            </p>
                            <a href="{{ route('blog.index', ['search' => $post->user->name]) }}"
                                class="inline-flex items-center gap-2 text-orange-500 hover:text-orange-600 text-sm font-medium transition-colors">
                                <i class="fa-solid fa-newspaper"></i>
                                Lihat artikel lainnya
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Navigation - Prev/Next -->
    <section class="py-6 sm:py-8 bg-white dark:bg-stone-900 border-t border-stone-200 dark:border-stone-800">
        <div class="landing-container">
            <div class="flex items-stretch justify-between gap-4">
                @if ($previousPost)
                    <a href="{{ route('blog.show', $previousPost->slug) }}"
                        class="flex-1 flex items-center gap-3 sm:gap-4 p-4 sm:p-5 bg-stone-50 dark:bg-stone-800 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-700 transition-colors group">
                        <i
                            class="fa-solid fa-arrow-left text-orange-500 group-hover:-translate-x-1 transition-transform shrink-0"></i>
                        <div class="text-left overflow-hidden">
                            <p class="text-xs text-stone-500 mb-0.5">Sebelumnya</p>
                            <p
                                class="text-sm font-semibold text-stone-900 dark:text-white group-hover:text-orange-500 transition-colors truncate">
                                {{ $previousPost->title }}
                            </p>
                        </div>
                    </a>
                @else
                    <div class="flex-1"></div>
                @endif

                @if ($nextPost)
                    <a href="{{ route('blog.show', $nextPost->slug) }}"
                        class="flex-1 flex items-center gap-3 sm:gap-4 p-4 sm:p-5 bg-stone-50 dark:bg-stone-800 rounded-xl hover:bg-stone-100 dark:hover:bg-stone-700 transition-colors group">
                        <div class="text-right overflow-hidden">
                            <p class="text-xs text-stone-500 mb-0.5">Selanjutnya</p>
                            <p
                                class="text-sm font-semibold text-stone-900 dark:text-white group-hover:text-orange-500 transition-colors truncate">
                                {{ $nextPost->title }}
                            </p>
                        </div>
                        <i
                            class="fa-solid fa-arrow-right text-orange-500 group-hover:translate-x-1 transition-transform shrink-0"></i>
                    </a>
                @else
                    <div class="flex-1"></div>
                @endif
            </div>
        </div>
    </section>

    <!-- Related Posts - Compact Grid -->
    @if ($relatedPosts->count() > 0)
        <section class="py-10 sm:py-14 bg-stone-50 dark:bg-stone-950">
            <div class="landing-container">
                <div class="text-center mb-8">
                    <h2 class="text-xl sm:text-2xl font-bold text-stone-900 dark:text-white">Artikel Terkait</h2>
                    <p class="text-stone-500 text-sm mt-1">Baca artikel lain yang mungkin menarik</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach ($relatedPosts as $related)
                        <article
                            class="group bg-white dark:bg-stone-900 rounded-xl sm:rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all border border-stone-200 dark:border-stone-800">
                            <a href="{{ route('blog.show', $related->slug) }}" class="block">
                                @if ($related->featured_image)
                                    <div class="aspect-[16/10] overflow-hidden">
                                        <img src="{{ asset($related->featured_image) }}" alt="{{ $related->title }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                @else
                                    <div
                                        class="aspect-[16/10] bg-gradient-to-br from-stone-200 to-stone-300 dark:from-stone-800 dark:to-stone-700 flex items-center justify-center">
                                        <i class="fa-solid fa-newspaper text-stone-400 dark:text-stone-600 text-2xl"></i>
                                    </div>
                                @endif
                            </a>
                            <div class="p-4 sm:p-5">
                                <div class="flex items-center gap-2 mb-2">
                                    @foreach ($related->categories->take(1) as $category)
                                        <span
                                            class="px-2 py-0.5 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-xs font-medium rounded-full">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                                <h3
                                    class="font-bold text-sm sm:text-base text-stone-900 dark:text-white mb-2 group-hover:text-orange-500 transition-colors line-clamp-2">
                                    <a href="{{ route('blog.show', $related->slug) }}">
                                        {{ $related->title }}
                                    </a>
                                </h3>
                                <div class="flex items-center justify-between text-xs text-stone-500 dark:text-stone-400">
                                    <span>{{ $related->published_at->format('d M Y') }}</span>
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-eye text-[10px]"></i>
                                        {{ $related->views }}
                                    </span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        // Copy link functionality
        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                showNotification('Link berhasil disalin!', 'success');
            }).catch(function(err) {
                console.error('Gagal menyalin link: ', err);
            });
        }

        // Simple notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className =
                `fixed bottom-4 right-4 ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'} text-white px-4 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2 animate-fade-in-up`;
            notification.innerHTML =
                `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i><span>${message}</span>`;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(10px)';
                setTimeout(() => notification.remove(), 300);
            }, 2500);
        }

        // Reading progress bar
        window.addEventListener('scroll', function() {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;

            const progressBar = document.getElementById('reading-progress');
            if (progressBar) {
                progressBar.style.width = scrolled + '%';
            }
        });
    </script>
@endpush
