@extends('layouts.app-landing')

@section('title', 'Blog SFHUB | Tips & Artikel untuk Mahasiswa')

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-orange-50 to-rose-50 dark:from-stone-900 dark:to-stone-800 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-stone-900 dark:text-white mb-4">
                    Blog <span class="text-orange-500">SFHUB</span>
                </h1>
                <p class="text-lg text-stone-600 dark:text-stone-300 max-w-2xl mx-auto mb-8">
                    Tips, tutorial, dan inspirasi untuk mahasiswa yang ingin produktif dan sukses di dunia digital.
                </p>
                
                <!-- Search Bar -->
                <div class="max-w-2xl mx-auto">
                    <form method="GET" action="{{ route('blog.index') }}" class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Cari artikel..." 
                            class="w-full px-6 py-4 pr-12 text-stone-900 dark:text-white bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-xl shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        >
                        <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-orange-500 hover:bg-orange-600 text-white p-3 rounded-lg transition-colors">
                            <i class="fa-solid fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Posts -->
    @if ($featuredPosts->count() > 0)
        <section class="py-12 bg-white dark:bg-stone-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white mb-8 flex items-center">
                    <i class="fa-solid fa-star text-orange-500 mr-3"></i>
                    Artikel Unggulan
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($featuredPosts as $post)
                        <div class="group bg-white dark:bg-stone-800 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 border border-stone-200 dark:border-stone-700">
                            @if ($post->featured_image)
                                <div class="aspect-w-16 aspect-h-9 overflow-hidden">
                                    <img 
                                        src="{{ asset($post->featured_image) }}" 
                                        alt="{{ $post->title }}"
                                        class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                                    >
                                </div>
                            @else
                                <div class="w-full h-48 bg-gradient-to-br from-orange-400 to-rose-400 flex items-center justify-center">
                                    <i class="fa-solid fa-newspaper text-white text-4xl"></i>
                                </div>
                            @endif
                            <div class="p-6">
                                <div class="flex items-center gap-2 mb-3">
                                    @foreach ($post->categories->take(2) as $category)
                                        <span class="px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-xs font-medium rounded-full">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                                <h3 class="font-bold text-lg text-stone-900 dark:text-white mb-2 group-hover:text-orange-500 transition-colors">
                                    <a href="{{ route('blog.show', $post->slug) }}">
                                        {{ Str::limit($post->title, 60) }}
                                    </a>
                                </h3>
                                <p class="text-stone-600 dark:text-stone-300 text-sm mb-4 line-clamp-2">
                                    {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 100) }}
                                </p>
                                <div class="flex items-center justify-between text-xs text-stone-500 dark:text-stone-400">
                                    <div class="flex items-center gap-2">
                                        @if ($post->user->avatar)
                                            <img src="{{ asset($post->user->avatar) }}" alt="{{ $post->user->name }}" class="w-5 h-5 rounded-full">
                                        @else
                                            <div class="w-5 h-5 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                                <i class="fa-solid fa-user text-orange-600 dark:text-orange-400 text-xs"></i>
                                            </div>
                                        @endif
                                        <span>{{ $post->user->name }}</span>
                                    </div>
                                    <span>{{ $post->published_at->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Main Content -->
    <section class="py-12 bg-stone-50 dark:bg-stone-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Blog Posts -->
                <div class="lg:col-span-3">
                    @if ($posts->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ($posts as $post)
                                <article class="group bg-white dark:bg-stone-900 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 border border-stone-200 dark:border-stone-700">
                                    @if ($post->featured_image)
                                        <div class="aspect-w-16 aspect-h-9 overflow-hidden">
                                            <img 
                                                src="{{ asset($post->featured_image) }}" 
                                                alt="{{ $post->title }}"
                                                class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                                            >
                                        </div>
                                    @else
                                        <div class="w-full h-48 bg-gradient-to-br from-stone-200 to-stone-300 dark:from-stone-700 dark:to-stone-800 flex items-center justify-center">
                                            <i class="fa-solid fa-newspaper text-stone-400 dark:text-stone-500 text-4xl"></i>
                                        </div>
                                    @endif
                                    <div class="p-6">
                                        <div class="flex items-center gap-2 mb-3">
                                            @foreach ($post->categories->take(2) as $category)
                                                <span class="px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-xs font-medium rounded-full">
                                                    {{ $category->name }}
                                                </span>
                                            @endforeach
                                            @if ($post->featured)
                                                <span class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 text-xs font-medium rounded-full">
                                                    <i class="fa-solid fa-star mr-1"></i>Unggulan
                                                </span>
                                            @endif
                                        </div>
                                        <h3 class="font-bold text-lg text-stone-900 dark:text-white mb-2 group-hover:text-orange-500 transition-colors">
                                            <a href="{{ route('blog.show', $post->slug) }}">
                                                {{ $post->title }}
                                            </a>
                                        </h3>
                                        <p class="text-stone-600 dark:text-stone-300 text-sm mb-4 line-clamp-2">
                                            {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 100) }}
                                        </p>
                                        <div class="flex items-center justify-between text-xs text-stone-500 dark:text-stone-400">
                                            <div class="flex items-center gap-2">
                                                @if ($post->user->avatar)
                                                    <img src="{{ asset($post->user->avatar) }}" alt="{{ $post->user->name }}" class="w-5 h-5 rounded-full">
                                                @else
                                                    <div class="w-5 h-5 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                                        <i class="fa-solid fa-user text-orange-600 dark:text-orange-400 text-xs"></i>
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

                        <!-- Pagination -->
                        <div class="mt-12">
                            {{ $posts->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fa-solid fa-search text-6xl text-stone-300 dark:text-stone-600 mb-4"></i>
                            <h3 class="text-xl font-semibold text-stone-900 dark:text-white mb-2">Belum ada artikel</h3>
                            <p class="text-stone-600 dark:text-stone-300">
                                @if (request('search'))
                                    Tidak ada artikel yang cocok dengan pencarian "{{ request('search') }}"
                                @else
                                    Belum ada artikel yang dipublikasikan. Nantikan artikel menarik dari kami!
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Categories -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 shadow-lg border border-stone-200 dark:border-stone-700 mb-6">
                        <h3 class="font-bold text-lg text-stone-900 dark:text-white mb-4 flex items-center">
                            <i class="fa-solid fa-folder text-orange-500 mr-2"></i>
                            Kategori
                        </h3>
                        <div class="space-y-2">
                            @foreach ($categories as $category)
                                <a href="{{ route('blog.index', ['category' => $category->slug]) }}" 
                                   class="flex items-center justify-between p-2 rounded-lg hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors {{ request('category') == $category->slug ? 'bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'text-stone-700 dark:text-stone-300' }}">
                                    <span>{{ $category->name }}</span>
                                    <span class="text-xs bg-stone-100 dark:bg-stone-700 px-2 py-1 rounded-full">
                                        {{ $category->posts_count ?? 0 }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Recent Posts -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 shadow-lg border border-stone-200 dark:border-stone-700">
                        <h3 class="font-bold text-lg text-stone-900 dark:text-white mb-4 flex items-center">
                            <i class="fa-solid fa-clock text-orange-500 mr-2"></i>
                            Terbaru
                        </h3>
                        <div class="space-y-4">
                            @php
                                $recentPosts = \App\Models\BlogPost::with(['user'])
                                    ->where('status', 'published')
                                    ->orderBy('published_at', 'desc')
                                    ->take(5)
                                    ->get();
                            @endphp
                            @foreach ($recentPosts as $recent)
                                <div class="flex items-start gap-3">
                                    @if ($recent->featured_image)
                                        <img src="{{ asset($recent->featured_image) }}" alt="{{ $recent->title }}" class="w-12 h-12 rounded-lg object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-400 to-rose-400 flex items-center justify-center">
                                            <i class="fa-solid fa-newspaper text-white text-sm"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <h4 class="font-medium text-sm text-stone-900 dark:text-white hover:text-orange-500 transition-colors">
                                            <a href="{{ route('blog.show', $recent->slug) }}">
                                                {{ Str::limit($recent->title, 40) }}
                                            </a>
                                        </h4>
                                        <p class="text-xs text-stone-500 dark:text-stone-400">
                                            {{ $recent->published_at->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
