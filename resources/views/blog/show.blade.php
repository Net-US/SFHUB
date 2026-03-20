@extends('layouts.app-landing')

@section('title', $post->title . ' | Blog SFHUB')

@section('content')
    <!-- Hero Header -->
    <section class="bg-gradient-to-br from-orange-50 to-rose-50 dark:from-stone-900 dark:to-stone-800 py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center text-sm text-stone-600 dark:text-stone-400 mb-6">
                <a href="{{ route('blog.index') }}" class="hover:text-orange-500 transition-colors">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Kembali ke Blog
                </a>
            </nav>
            
            <div class="text-center">
                <div class="flex items-center justify-center gap-2 mb-4">
                    @foreach ($post->categories as $category)
                        <span class="px-3 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-sm font-medium rounded-full">
                            {{ $category->name }}
                        </span>
                    @endforeach
                    @if ($post->featured)
                        <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 text-sm font-medium rounded-full">
                            <i class="fa-solid fa-star mr-1"></i>Artikel Unggulan
                        </span>
                    @endif
                </div>
                
                <h1 class="text-3xl md:text-4xl font-bold text-stone-900 dark:text-white mb-6">
                    {{ $post->title }}
                </h1>
                
                <div class="flex items-center justify-center gap-6 text-sm text-stone-600 dark:text-stone-400">
                    <div class="flex items-center gap-2">
                        @if ($post->user->avatar)
                            <img src="{{ asset($post->user->avatar) }}" alt="{{ $post->user->name }}" class="w-8 h-8 rounded-full">
                        @else
                            <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                <i class="fa-solid fa-user text-orange-600 dark:text-orange-400 text-sm"></i>
                            </div>
                        @endif
                        <span>{{ $post->user->name }}</span>
                    </div>
                    <span><i class="fa-solid fa-calendar mr-1"></i>{{ $post->published_at->format('d F Y') }}</span>
                    <span><i class="fa-solid fa-eye mr-1"></i>{{ $post->views }} dibaca</span>
                    <span><i class="fa-solid fa-clock mr-1"></i>{{ $post->published_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Image -->
    @if ($post->featured_image)
        <section class="bg-white dark:bg-stone-900">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-xl overflow-hidden shadow-xl">
                    <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-auto">
                </div>
            </div>
        </section>
    @endif

    <!-- Article Content -->
    <section class="py-12 bg-white dark:bg-stone-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="prose prose-lg max-w-none dark:prose-invert">
                @if ($post->excerpt)
                    <div class="bg-orange-50 dark:bg-orange-900/20 border-l-4 border-orange-500 p-6 mb-8 rounded-r-lg">
                        <p class="text-orange-800 dark:text-orange-200 italic">{{ $post->excerpt }}</p>
                    </div>
                @endif
                
                <div class="text-stone-800 dark:text-stone-200 leading-relaxed">
                    {!! $post->content !!}
                </div>
            </div>

            <!-- Tags -->
            @if ($post->tags->count() > 0)
                <div class="mt-8 pt-8 border-t border-stone-200 dark:border-stone-700">
                    <h3 class="text-sm font-medium text-stone-700 dark:text-stone-300 mb-3">Tag</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($post->tags as $tag)
                            <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" 
                               class="px-3 py-1 bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 text-sm rounded-full hover:bg-orange-100 dark:hover:bg-orange-900/30 hover:text-orange-700 dark:hover:text-orange-300 transition-colors">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Share Buttons -->
            <div class="mt-8 pt-8 border-t border-stone-200 dark:border-stone-700">
                <h3 class="text-sm font-medium text-stone-700 dark:text-stone-300 mb-3">Bagikan artikel</h3>
                <div class="flex gap-3">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ url()->current() }}" 
                       target="_blank"
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors">
                        <i class="fa-brands fa-facebook mr-2"></i>Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ url()->current() }}&text={{ $post->title }}" 
                       target="_blank"
                       class="px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white text-sm rounded-lg transition-colors">
                        <i class="fa-brands fa-twitter mr-2"></i>Twitter
                    </a>
                    <a href="https://wa.me/?text={{ $post->title }}%20-%20{{ url()->current() }}" 
                       target="_blank"
                       class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition-colors">
                        <i class="fa-brands fa-whatsapp mr-2"></i>WhatsApp
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ url()->current() }}" 
                       target="_blank"
                       class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm rounded-lg transition-colors">
                        <i class="fa-brands fa-linkedin mr-2"></i>LinkedIn
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Navigation -->
    <section class="py-8 bg-stone-50 dark:bg-stone-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                @if ($previousPost)
                    <a href="{{ route('blog.show', $previousPost->slug) }}" 
                       class="flex items-center gap-3 px-6 py-3 bg-white dark:bg-stone-900 rounded-lg shadow hover:shadow-lg transition-all group">
                        <i class="fa-solid fa-arrow-left text-orange-500 group-hover:-translate-x-1 transition-transform"></i>
                        <div class="text-left">
                            <p class="text-xs text-stone-500 dark:text-stone-400">Sebelumnya</p>
                            <p class="text-sm font-medium text-stone-900 dark:text-white group-hover:text-orange-500 transition-colors">
                                {{ Str::limit($previousPost->title, 30) }}
                            </p>
                        </div>
                    </a>
                @else
                    <div></div>
                @endif

                @if ($nextPost)
                    <a href="{{ route('blog.show', $nextPost->slug) }}" 
                       class="flex items-center gap-3 px-6 py-3 bg-white dark:bg-stone-900 rounded-lg shadow hover:shadow-lg transition-all group">
                        <div class="text-right">
                            <p class="text-xs text-stone-500 dark:text-stone-400">Selanjutnya</p>
                            <p class="text-sm font-medium text-stone-900 dark:text-white group-hover:text-orange-500 transition-colors">
                                {{ Str::limit($nextPost->title, 30) }}
                            </p>
                        </div>
                        <i class="fa-solid fa-arrow-right text-orange-500 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- Related Posts -->
    @if ($relatedPosts->count() > 0)
        <section class="py-16 bg-white dark:bg-stone-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white mb-8 text-center">
                    Artikel Terkait
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($relatedPosts as $related)
                        <article class="group bg-stone-50 dark:bg-stone-800 rounded-xl overflow-hidden shadow hover:shadow-lg transition-all border border-stone-200 dark:border-stone-700">
                            @if ($related->featured_image)
                                <div class="aspect-w-16 aspect-h-9 overflow-hidden">
                                    <img 
                                        src="{{ asset($related->featured_image) }}" 
                                        alt="{{ $related->title }}"
                                        class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300"
                                    >
                                </div>
                            @else
                                <div class="w-full h-40 bg-gradient-to-br from-stone-200 to-stone-300 dark:from-stone-700 dark:to-stone-800 flex items-center justify-center">
                                    <i class="fa-solid fa-newspaper text-stone-400 dark:text-stone-500 text-3xl"></i>
                                </div>
                            @endif
                            <div class="p-6">
                                <div class="flex items-center gap-2 mb-3">
                                    @foreach ($related->categories->take(1) as $category)
                                        <span class="px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-xs font-medium rounded-full">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                                <h3 class="font-bold text-lg text-stone-900 dark:text-white mb-2 group-hover:text-orange-500 transition-colors">
                                    <a href="{{ route('blog.show', $related->slug) }}">
                                        {{ Str::limit($related->title, 50) }}
                                    </a>
                                </h3>
                                <p class="text-stone-600 dark:text-stone-300 text-sm mb-4 line-clamp-2">
                                    {{ $related->excerpt ?: Str::limit(strip_tags($related->content), 80) }}
                                </p>
                                <div class="flex items-center justify-between text-xs text-stone-500 dark:text-stone-400">
                                    <span>{{ $related->published_at->format('d M Y') }}</span>
                                    <span><i class="fa-solid fa-eye mr-1"></i>{{ $related->views }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Author Bio -->
    <section class="py-12 bg-stone-50 dark:bg-stone-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-stone-900 rounded-xl p-8 shadow-lg border border-stone-200 dark:border-stone-700">
                <div class="flex items-start gap-6">
                    @if ($post->user->avatar)
                        <img src="{{ asset($post->user->avatar) }}" alt="{{ $post->user->name }}" 
                             class="w-20 h-20 rounded-full object-cover">
                    @else
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-orange-400 to-rose-400 flex items-center justify-center">
                            <i class="fa-solid fa-user text-white text-3xl"></i>
                        </div>
                    @endif
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-stone-900 dark:text-white mb-2">
                            {{ $post->user->name }}
                        </h3>
                        <p class="text-stone-600 dark:text-stone-300 mb-4">
                            Penulis artikel ini adalah bagian dari tim SFHUB yang berdedikasi untuk memberikan konten berkualitas 
                            untuk membantu mahasiswa dan freelancer dalam perjalanan karir mereka.
                        </p>
                        <div class="flex gap-4">
                            <a href="{{ route('blog.index', ['search' => $post->user->name]) }}" 
                               class="text-orange-500 hover:text-orange-600 text-sm font-medium transition-colors">
                                <i class="fa-solid fa-newspaper mr-1"></i>Lihat artikel lainnya
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
            notification.className = `fixed bottom-4 right-4 ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'} text-white px-4 py-2 rounded-lg shadow-lg z-50`;
            notification.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>${message}`;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        // Reading progress bar
        window.addEventListener('scroll', function() {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            
            let progressBar = document.getElementById('reading-progress');
            if (!progressBar) {
                progressBar = document.createElement('div');
                progressBar.id = 'reading-progress';
                progressBar.className = 'fixed top-0 left-0 h-1 bg-orange-500 z-50 transition-all duration-150';
                document.body.appendChild(progressBar);
            }
            progressBar.style.width = scrolled + '%';
        });
    </script>
@endpush
