@extends('layouts.app-landing')

@section('title', 'Blog SFHUB | Tips & Artikel untuk Mahasiswa')

@section('content')
    <!-- Hero Section - Modern & Clean -->
    <section
        class="relative bg-gradient-to-br from-orange-50 via-white to-rose-50 dark:from-stone-900 dark:via-stone-900 dark:to-stone-800 py-12 sm:py-16 lg:py-20 overflow-hidden">
        <div class="landing-container" style="margin-top: 2rem">
            <div class="text-center max-w-5xl mx-auto">
                <span class="brand-pill mb-4 sm:mb-6 inline-flex">
                    <i class="fa-solid fa-book-open"></i>
                    Blog & Insight
                </span>
                <h1
                    class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-stone-900 dark:text-white mb-4 sm:mb-6 leading-tight">
                    Tips & Inspirasi untuk <span class="text-orange-500">Mahasiswa</span>
                </h1>
                <p
                    class="text-base sm:text-lg text-stone-600 dark:text-stone-300 mb-8 sm:mb-10 max-w-2xl mx-auto leading-relaxed">
                    Artikel praktis seputar produktivitas, karir, dan pengembangan diri untuk membantu kamu sukses.
                </p>

                <!-- Modern Search Bar -->
                <form method="GET" action="{{ route('blog.index') }}" class="relative max-w-xl mx-auto">
                    <div class="relative group">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari artikel, tips, atau tutorial..."
                            class="w-full px-5 sm:px-6 py-3.5 sm:py-4 pr-14 text-stone-900 dark:text-white bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-2xl shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all text-sm sm:text-base">
                        <button type="submit"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-orange-500 hover:bg-orange-600 text-white p-2.5 sm:p-3 rounded-xl transition-all hover:scale-105 shadow-md">
                            <i class="fa-solid fa-search text-sm sm:text-base"></i>
                        </button>
                    </div>
                </form>

                <!-- Quick Tags -->
                @if (isset($categories) && $categories->count() > 0)
                    <div class="flex flex-wrap items-center justify-center gap-2 mt-6">
                        <span class="text-xs sm:text-sm text-stone-500 dark:text-stone-400">Populer:</span>
                        @foreach ($categories->take(4) as $cat)
                            <a href="{{ route('blog.index', ['category' => $cat->slug]) }}"
                                class="px-3 py-1 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-full text-xs sm:text-sm text-stone-600 dark:text-stone-300 hover:border-orange-300 hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
                                {{ $cat->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Featured Posts - Horizontal Scroll on Mobile -->
    @if ($featuredPosts->count() > 0)
        <section class="py-10 sm:py-12 bg-white dark:bg-stone-900">
            <div class="landing-container">
                <div class="flex items-center gap-3 mb-6 sm:mb-8">
                    <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                        <i class="fa-solid fa-star text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-stone-900 dark:text-white">Artikel Unggulan</h2>
                </div>

                <!-- Desktop Grid / Mobile Scroll -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach ($featuredPosts as $post)
                        <article
                            class="group bg-stone-50 dark:bg-stone-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-stone-200 dark:border-stone-700">
                            <a href="{{ route('blog.show', $post->slug) }}" class="block">
                                @if ($post->featured_image)
                                    <div class="aspect-[16/10] overflow-hidden">
                                        <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    </div>
                                @else
                                    <div
                                        class="aspect-[16/10] bg-gradient-to-br from-orange-400 to-rose-500 flex items-center justify-center">
                                        <i class="fa-solid fa-newspaper text-white text-3xl sm:text-4xl"></i>
                                    </div>
                                @endif
                            </a>
                            <div class="p-4 sm:p-5">
                                <div class="flex items-center gap-2 mb-3">
                                    @foreach ($post->categories->take(1) as $category)
                                        <span
                                            class="px-2.5 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-xs font-semibold rounded-full">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                    <span class="flex items-center gap-1 text-xs text-stone-500 dark:text-stone-400">
                                        <i class="fa-solid fa-eye text-[10px]"></i>
                                        {{ $post->views }}
                                    </span>
                                </div>
                                <h3
                                    class="font-bold text-base sm:text-lg text-stone-900 dark:text-white mb-2 group-hover:text-orange-500 transition-colors line-clamp-2">
                                    <a href="{{ route('blog.show', $post->slug) }}">
                                        {{ $post->title }}
                                    </a>
                                </h3>
                                <p class="text-stone-600 dark:text-stone-400 text-xs sm:text-sm mb-3 line-clamp-2">
                                    {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 80) }}
                                </p>
                                <div class="flex items-center gap-2 text-xs text-stone-500 dark:text-stone-400">
                                    <div class="flex items-center gap-1.5">
                                        @if ($post->user->avatar)
                                            <img src="{{ asset($post->user->avatar) }}" alt="{{ $post->user->name }}"
                                                class="w-5 h-5 rounded-full object-cover">
                                        @else
                                            <div
                                                class="w-5 h-5 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                                <i
                                                    class="fa-solid fa-user text-orange-600 dark:text-orange-400 text-[10px]"></i>
                                            </div>
                                        @endif
                                        <span class="truncate max-w-[100px]">{{ $post->user->name }}</span>
                                    </div>
                                    <span class="text-stone-300">•</span>
                                    <span>{{ $post->published_at->format('d M') }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Main Content - Improved Layout -->
    <section class="py-10 sm:py-12 bg-stone-50 dark:bg-stone-950">
        <div class="landing-container">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
                <!-- Blog Posts - Main Column -->
                <div class="lg:col-span-8">
                    @if ($posts->count() > 0)
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg sm:text-xl font-bold text-stone-900 dark:text-white">
                                @if (request('search'))
                                    Hasil Pencarian "{{ request('search') }}"
                                @elseif(request('category'))
                                    Kategori:
                                    {{ $categories->firstWhere('slug', request('category'))?->name ?? request('category') }}
                                @else
                                    Artikel Terbaru
                                @endif
                            </h2>
                            <span class="text-xs sm:text-sm text-stone-500">{{ $posts->total() }} artikel</span>
                        </div>

                        <div class="space-y-4 sm:space-y-5">
                            @foreach ($posts as $post)
                                <article
                                    class="group bg-white dark:bg-stone-900 rounded-xl sm:rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 border border-stone-200 dark:border-stone-800">
                                    <div class="flex flex-col sm:flex-row">
                                        {{-- Thumbnail --}}
                                        <a href="{{ route('blog.show', $post->slug) }}"
                                            class="sm:w-48 md:w-56 flex-shrink-0">
                                            @if ($post->featured_image)
                                                <div class="aspect-[16/9] sm:aspect-square sm:h-full overflow-hidden">
                                                    <img src="{{ asset($post->featured_image) }}"
                                                        alt="{{ $post->title }}"
                                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                                </div>
                                            @else
                                                <div
                                                    class="aspect-[16/9] sm:aspect-square sm:h-full bg-gradient-to-br from-stone-200 to-stone-300 dark:from-stone-800 dark:to-stone-700 flex items-center justify-center">
                                                    <i
                                                        class="fa-solid fa-newspaper text-stone-400 dark:text-stone-600 text-2xl sm:text-3xl"></i>
                                                </div>
                                            @endif
                                        </a>

                                        {{-- Content --}}
                                        <div class="flex-1 p-4 sm:p-5 flex flex-col justify-between">
                                            <div>
                                                <div class="flex items-center gap-2 mb-2">
                                                    @foreach ($post->categories->take(2) as $category)
                                                        <a href="{{ route('blog.index', ['category' => $category->slug]) }}"
                                                            class="px-2 py-0.5 bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400 text-xs font-medium rounded hover:bg-orange-100 hover:text-orange-600 dark:hover:bg-orange-900/30 dark:hover:text-orange-400 transition-colors">
                                                            {{ $category->name }}
                                                        </a>
                                                    @endforeach
                                                    @if ($post->featured)
                                                        <span
                                                            class="px-2 py-0.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 text-xs font-medium rounded flex items-center gap-1">
                                                            <i class="fa-solid fa-star text-[10px]"></i>
                                                            <span class="hidden sm:inline">Unggulan</span>
                                                        </span>
                                                    @endif
                                                </div>

                                                <h3
                                                    class="font-bold text-base sm:text-lg text-stone-900 dark:text-white mb-2 group-hover:text-orange-500 transition-colors line-clamp-2">
                                                    <a href="{{ route('blog.show', $post->slug) }}">
                                                        {{ $post->title }}
                                                    </a>
                                                </h3>

                                                <p
                                                    class="text-stone-600 dark:text-stone-400 text-xs sm:text-sm mb-3 line-clamp-2">
                                                    {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 120) }}
                                                </p>
                                            </div>

                                            <div class="flex items-center justify-between">
                                                <div
                                                    class="flex items-center gap-2 text-xs text-stone-500 dark:text-stone-400">
                                                    <div class="flex items-center gap-1.5">
                                                        @if ($post->user->avatar)
                                                            <img src="{{ asset($post->user->avatar) }}"
                                                                alt="{{ $post->user->name }}"
                                                                class="w-5 h-5 rounded-full object-cover">
                                                        @else
                                                            <div
                                                                class="w-5 h-5 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                                                <i
                                                                    class="fa-solid fa-user text-orange-600 dark:text-orange-400 text-[10px]"></i>
                                                            </div>
                                                        @endif
                                                        <span
                                                            class="truncate max-w-[80px] sm:max-w-[120px]">{{ $post->user->name }}</span>
                                                    </div>
                                                    <span class="text-stone-300">•</span>
                                                    <span>{{ $post->published_at->format('d M Y') }}</span>
                                                </div>
                                                <span class="flex items-center gap-1 text-xs text-stone-400">
                                                    <i class="fa-solid fa-eye text-[10px]"></i>
                                                    {{ $post->views }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if ($posts->hasPages())
                            <div class="mt-8 sm:mt-10">
                                {{ $posts->links() }}
                            </div>
                        @endif
                    @else
                        <div
                            class="text-center py-12 sm:py-16 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800">
                            <div
                                class="w-16 h-16 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-search text-2xl text-stone-400"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-stone-900 dark:text-white mb-2">
                                @if (request('search'))
                                    Tidak ada hasil untuk "{{ request('search') }}"
                                @else
                                    Belum ada artikel
                                @endif
                            </h3>
                            <p class="text-stone-500 text-sm max-w-md mx-auto">
                                @if (request('search'))
                                    Coba kata kunci lain atau <a href="{{ route('blog.index') }}"
                                        class="text-orange-500 hover:underline">lihat semua artikel</a>
                                @else
                                    Nantikan artikel menarik dari kami segera!
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Sidebar - Compact & Modern -->
                <div class="lg:col-span-4 space-y-4 sm:space-y-6">
                    <!-- Categories Card -->
                    <div
                        class="bg-white dark:bg-stone-900 rounded-xl sm:rounded-2xl p-5 sm:p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-folder-open text-orange-500"></i>
                            <h3 class="font-bold text-stone-900 dark:text-white">Kategori</h3>
                        </div>
                        <div class="space-y-1">
                            @foreach ($categories as $category)
                                <a href="{{ route('blog.index', ['category' => $category->slug]) }}"
                                    class="flex items-center justify-between p-2.5 rounded-lg transition-colors {{ request('category') == $category->slug ? 'bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 font-medium' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800' }}">
                                    <span class="flex items-center gap-2 text-sm">
                                        <span class="w-2 h-2 rounded-full"
                                            style="background-color: {{ $category->color ?? '#f97316' }}"></span>
                                        {{ $category->name }}
                                    </span>
                                    <span class="text-xs bg-stone-100 dark:bg-stone-800 px-2 py-0.5 rounded-full">
                                        {{ $category->posts_count ?? 0 }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Recent Posts - Compact List -->
                    <div
                        class="bg-white dark:bg-stone-900 rounded-xl sm:rounded-2xl p-5 sm:p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-clock-rotate-left text-orange-500"></i>
                            <h3 class="font-bold text-stone-900 dark:text-white">Terbaru</h3>
                        </div>
                        <div class="space-y-3">
                            @php
                                $recentPosts = \App\Models\BlogPost::with(['user'])
                                    ->where('status', 'published')
                                    ->orderBy('published_at', 'desc')
                                    ->take(4)
                                    ->get();
                            @endphp
                            @foreach ($recentPosts as $recent)
                                <a href="{{ route('blog.show', $recent->slug) }}" class="flex items-start gap-3 group">
                                    @if ($recent->featured_image)
                                        <img src="{{ asset($recent->featured_image) }}" alt="{{ $recent->title }}"
                                            class="w-14 h-14 rounded-lg object-cover flex-shrink-0">
                                    @else
                                        <div
                                            class="w-14 h-14 rounded-lg bg-gradient-to-br from-orange-400 to-rose-500 flex items-center justify-center flex-shrink-0">
                                            <i class="fa-solid fa-newspaper text-white text-sm"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h4
                                            class="font-medium text-sm text-stone-900 dark:text-white group-hover:text-orange-500 transition-colors line-clamp-2">
                                            {{ $recent->title }}
                                        </h4>
                                        <p class="text-xs text-stone-500 mt-0.5">
                                            {{ $recent->published_at->format('d M Y') }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Newsletter CTA (Optional) -->
                    <div
                        class="bg-gradient-to-br from-orange-500 to-rose-500 rounded-xl sm:rounded-2xl p-5 sm:p-6 text-white">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fa-solid fa-bell"></i>
                            <h3 class="font-bold">Update Terbaru</h3>
                        </div>
                        <p class="text-orange-100 text-sm mb-3">Dapatkan artikel terbaru langsung di email Anda.</p>
                        <a href="#"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white text-orange-600 rounded-lg text-sm font-semibold hover:bg-orange-50 transition-colors">
                            <i class="fa-solid fa-envelope"></i>
                            Berlangganan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
