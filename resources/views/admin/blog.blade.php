@extends('layouts.app')

@section('title', 'Blog Management | SFHUB Admin')

@section('page-title', 'Blog Management')

@section('content')
    <div class="space-y-6">
        {{-- Header Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Posts --}}
            <div class="bg-white dark:bg-stone-900 rounded-xl p-5 border border-stone-200 dark:border-stone-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <i class="fa-solid fa-newspaper text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $posts->total() }}</p>
                        <p class="text-xs text-stone-500">Total Posts</p>
                    </div>
                </div>
            </div>

            {{-- Published --}}
            <div class="bg-white dark:bg-stone-900 rounded-xl p-5 border border-stone-200 dark:border-stone-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <i class="fa-solid fa-check-circle text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-emerald-600">{{ $publishedCount ?? 0 }}</p>
                        <p class="text-xs text-stone-500">Published</p>
                    </div>
                </div>
            </div>

            {{-- Drafts --}}
            <div class="bg-white dark:bg-stone-900 rounded-xl p-5 border border-stone-200 dark:border-stone-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <i class="fa-solid fa-pen-ruler text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-amber-600">{{ $draftCount ?? 0 }}</p>
                        <p class="text-xs text-stone-500">Drafts</p>
                    </div>
                </div>
            </div>

            {{-- Quick Action --}}
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-5 text-white">
                <a href="{{ route('admin.blog.create') }}" class="flex items-center gap-3 h-full">
                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-plus text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-sm">Buat Post</p>
                        <p class="text-xs text-orange-100">Baru</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                {{-- Category Pills --}}
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-sm text-stone-500 whitespace-nowrap">Filter:</span>
                    <a href="{{ route('admin.blog.index') }}"
                        class="px-3 py-1.5 rounded-full text-sm transition-colors {{ !request('category') ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700' }}">
                        Semua
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('admin.blog.index', ['category' => $category->id]) }}"
                            class="px-3 py-1.5 rounded-full text-sm flex items-center gap-1.5 transition-colors {{ request('category') == $category->id ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700' }}">
                            <span class="w-2 h-2 rounded-full"
                                style="background-color: {{ $category->color ?? '#f57223' }}"></span>
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>

                {{-- Actions --}}
                <div class="flex gap-2">
                    <a href="{{ route('admin.blog.categories') }}"
                        class="px-4 py-2 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-folder"></i>
                        <span class="hidden sm:inline">Kategori</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Posts Grid --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            @forelse($posts as $post)
                <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-800 overflow-hidden hover:shadow-lg transition-shadow group">
                    <div class="flex">
                        {{-- Thumbnail --}}
                        <div class="w-32 sm:w-40 flex-shrink-0">
                            @if ($post->featured_image)
                                <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}"
                                    class="w-full h-full object-cover min-h-[140px]">
                            @else
                                <div class="w-full h-full min-h-[140px] bg-gradient-to-br from-stone-200 to-stone-300 dark:from-stone-800 dark:to-stone-700 flex items-center justify-center">
                                    <i class="fa-solid fa-newspaper text-3xl text-stone-400 dark:text-stone-600"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 p-4 flex flex-col justify-between min-w-0">
                            <div>
                                {{-- Status Badge --}}
                                <div class="flex items-center gap-2 mb-2">
                                    @if ($post->status === 'published')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 flex items-center gap-1">
                                            <i class="fa-solid fa-circle text-[6px]"></i>
                                            Published
                                        </span>
                                    @elseif($post->status === 'draft')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 flex items-center gap-1">
                                            <i class="fa-solid fa-circle text-[6px]"></i>
                                            Draft
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-stone-100 text-stone-800 dark:bg-stone-700 dark:text-stone-300">{{ $post->status }}</span>
                                    @endif

                                    @if ($post->featured)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                            <i class="fa-solid fa-star text-[10px]"></i>
                                        </span>
                                    @endif
                                </div>

                                {{-- Title --}}
                                <h3 class="font-semibold text-stone-900 dark:text-white text-base mb-1 line-clamp-2 group-hover:text-orange-500 transition-colors">
                                    {{ $post->title }}
                                </h3>

                                {{-- Meta --}}
                                <div class="flex items-center gap-3 text-xs text-stone-500 dark:text-stone-400 mb-2">
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-user text-[10px]"></i>
                                        {{ $post->user?->name ?? 'Unknown' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-calendar text-[10px]"></i>
                                        {{ $post->created_at->format('d M Y') }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-eye text-[10px]"></i>
                                        {{ $post->views }}
                                    </span>
                                </div>

                                {{-- Category Tags --}}
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($post->categories->take(2) as $category)
                                        <span class="px-2 py-0.5 rounded text-xs"
                                            style="background-color: {{ $category->color ?? '#f57223' }}20; color: {{ $category->color ?? '#f57223' }}">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                    @if ($post->categories->count() > 2)
                                        <span class="px-2 py-0.5 rounded text-xs bg-stone-100 dark:bg-stone-800 text-stone-500">
                                            +{{ $post->categories->count() - 2 }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-1 mt-3 pt-3 border-t border-stone-100 dark:border-stone-800">
                                <a href="{{ route('admin.blog.edit', $post) }}"
                                    class="p-2 text-stone-400 hover:text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-all"
                                    title="Edit">
                                    <i class="fa-solid fa-pen text-sm"></i>
                                </a>
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                                    class="p-2 text-stone-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all"
                                    title="Preview">
                                    <i class="fa-solid fa-eye text-sm"></i>
                                </a>
                                <button type="button"
                                    onclick="confirmDelete('{{ route('admin.blog.destroy', $post) }}', '{{ $post->title }}')"
                                    class="p-2 text-stone-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-all"
                                    title="Hapus">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-800 p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-stone-100 dark:bg-stone-800 flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-newspaper text-2xl text-stone-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-stone-900 dark:text-white mb-2">Belum ada artikel</h3>
                        <p class="text-stone-500 text-sm mb-4 max-w-md mx-auto">Mulai buat artikel pertama Anda untuk membagikan informasi dan insight kepada pengguna.</p>
                        <a href="{{ route('admin.blog.create') }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium text-sm transition-colors">
                            <i class="fa-solid fa-plus"></i>
                            Buat Post Pertama
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($posts->hasPages())
            <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-800 p-4">
                {{ $posts->links() }}
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center">
        <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 max-w-md mx-4 shadow-2xl">
            <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash text-rose-600 dark:text-rose-400 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-stone-900 dark:text-white text-center mb-2">Hapus Artikel?</h3>
            <p class="text-stone-500 text-sm text-center mb-6">Artikel "<span id="deletePostTitle" class="font-medium text-stone-700 dark:text-stone-300"></span>" akan dihapus secara permanen.</p>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()"
                    class="flex-1 px-4 py-2.5 border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-300 rounded-xl font-medium text-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">
                    Batal
                </button>
                <form id="deleteForm" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full px-4 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-medium text-sm transition-colors">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(url, title) {
            document.getElementById('deleteForm').action = url;
            document.getElementById('deletePostTitle').textContent = title;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }

        // Close modal on backdrop click
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
@endsection
