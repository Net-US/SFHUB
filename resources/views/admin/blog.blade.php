@extends('layouts.app')

@section('title', 'Blog Management | SFHUB Admin')

@section('page-title', 'Blog Management')

@section('content')
    <div class="space-y-6">
        {{-- Header & Stats --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            {{-- Title & Actions --}}
            <div
                class="lg:col-span-1 bg-white dark:bg-stone-900 rounded-xl p-5 border border-stone-200 dark:border-stone-800">
                <h2 class="text-xl font-bold text-stone-900 dark:text-white mb-1">Blog</h2>
                <p class="text-stone-500 text-sm mb-4">Kelola artikel & kategori</p>
                <div class="flex gap-2">
                    <a href="{{ route('admin.blog.create') }}"
                        class="flex-1 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium text-center transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Post Baru
                    </a>
                    <a href="{{ route('admin.blog.categories.index') }}"
                        class="px-4 py-2 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300 rounded-lg text-sm font-medium transition-colors">
                        <i class="fa-solid fa-folder"></i>
                    </a>
                </div>
            </div>

            {{-- Stats --}}
            <div class="bg-white dark:bg-stone-900 rounded-xl p-5 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Total Posts</p>
                <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $posts->total() }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-xl p-5 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Published</p>
                <p class="text-2xl font-bold text-emerald-600">{{ $publishedCount ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-xl p-5 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Drafts</p>
                <p class="text-2xl font-bold text-amber-600">{{ $draftCount ?? 0 }}</p>
            </div>
        </div>

        {{-- Category Filter --}}
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-sm text-stone-500">Filter:</span>
                <a href="{{ route('admin.blog.index') }}"
                    class="px-3 py-1.5 rounded-full text-sm {{ !request('category') ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300' }}">
                    Semua
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('admin.blog.index', ['category' => $category->id]) }}"
                        class="px-3 py-1.5 rounded-full text-sm flex items-center gap-1 {{ request('category') == $category->id ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300' }}">
                        <span class="w-2 h-2 rounded-full"
                            style="background-color: {{ $category->color ?? '#f57223' }}"></span>
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Posts List --}}
        <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50 dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
                        <tr>
                            <th class="text-left py-3 px-4 text-stone-600 dark:text-stone-400 font-medium">Judul</th>
                            <th class="text-left py-3 px-4 text-stone-600 dark:text-stone-400 font-medium">Kategori</th>
                            <th class="text-left py-3 px-4 text-stone-600 dark:text-stone-400 font-medium">Status</th>
                            <th class="text-left py-3 px-4 text-stone-600 dark:text-stone-400 font-medium">Tanggal</th>
                            <th class="text-left py-3 px-4 text-stone-600 dark:text-stone-400 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                            <tr
                                class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800/50">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        @if ($post->featured_image)
                                            <img src="{{ asset($post->featured_image) }}"
                                                class="w-10 h-10 rounded-lg object-cover">
                                        @else
                                            <div
                                                class="w-10 h-10 rounded-lg bg-stone-200 dark:bg-stone-700 flex items-center justify-center">
                                                <i class="fa-solid fa-file-lines text-stone-400"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-stone-800 dark:text-white">{{ $post->title }}</p>
                                            <p class="text-xs text-stone-500">{{ $post->user?->name ?? 'Unknown' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($post->categories->first())
                                        <span class="px-2 py-1 rounded-full text-xs"
                                            style="background-color: {{ $post->categories->first()->color ?? '#f57223' }}20; color: {{ $post->categories->first()->color ?? '#f57223' }}">
                                            {{ $post->categories->first()->name }}
                                        </span>
                                    @else
                                        <span class="text-stone-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if ($post->status === 'published')
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">Published</span>
                                    @elseif($post->status === 'draft')
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Draft</span>
                                    @else
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-medium bg-stone-100 text-stone-800 dark:bg-stone-700 dark:text-stone-300">{{ $post->status }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-stone-600 dark:text-stone-400 text-xs">
                                    {{ $post->created_at->format('d M Y') }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.blog.edit', $post) }}"
                                            class="text-orange-500 hover:text-orange-600" title="Edit">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                                            class="text-stone-400 hover:text-stone-600" title="Preview">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.blog.destroy', $post) }}"
                                            class="inline" onsubmit="return confirm('Hapus post ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-600" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-stone-500">
                                    <i class="fa-solid fa-newspaper text-4xl mb-3"></i>
                                    <p>Belum ada artikel</p>
                                    <a href="{{ route('admin.blog.create') }}"
                                        class="text-orange-500 hover:underline text-sm mt-2 inline-block">Buat post
                                        pertama</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-stone-200 dark:border-stone-700">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
@endsection
