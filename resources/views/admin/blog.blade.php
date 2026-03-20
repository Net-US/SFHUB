@extends('layouts.app')

@section('title', 'Blog Management | SFHUB Admin')

@section('page-title', 'Blog Management')

@section('content')
    <div class="animate-fade-in-up space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Blog Management</h2>
                <p class="text-stone-500 dark:text-stone-400 text-sm">Kelola artikel dan konten blog</p>
            </div>
            <div class="flex gap-2">
                <button onclick="showAddCategoryModal()"
                    class="flex items-center gap-2 px-4 py-2 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300 rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-folder-plus"></i> Category
                </button>
                <a href="{{ route('admin.blog.create') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-plus"></i> New Post
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Total Posts</p>
                <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $posts->total() }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Published</p>
                <p class="text-2xl font-bold text-emerald-600">{{ $publishedCount ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Drafts</p>
                <p class="text-2xl font-bold text-amber-600">{{ $draftCount ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Comments</p>
                <p class="text-2xl font-bold text-blue-600">{{ $commentsCount ?? 0 }}</p>
            </div>
        </div>

        <!-- Categories & Filters -->
        <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200 dark:border-stone-800">
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-sm text-stone-500 mr-2">Categories:</span>
                <button onclick="filterByCategory('all')"
                    class="px-3 py-1.5 rounded-full text-sm {{ !request('category') ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300' }}">All</button>
                @foreach ($categories as $category)
                    <button onclick="filterByCategory({{ $category->id }})"
                        class="px-3 py-1.5 rounded-full text-sm flex items-center gap-1 {{ request('category') == $category->id ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300' }}">
                        <span class="w-2 h-2 rounded-full"
                            style="background-color: {{ $category->color ?? '#f57223' }}"></span>
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Posts Table -->
        <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-stone-50 dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Title</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Category</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Author</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Status</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Views</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Comments</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Date</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                            <tr
                                class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800">
                                <td class="py-4 px-6">
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
                                            <p class="text-xs text-stone-500">{{ $post->slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    @if ($post->categories->first())
                                        <span class="px-2 py-1 rounded-full text-xs"
                                            style="background-color: {{ $post->categories->first()->color ?? '#f57223' }}20; color: {{ $post->categories->first()->color ?? '#f57223' }}">
                                            {{ $post->categories->first()->name }}
                                        </span>
                                    @else
                                        <span class="text-stone-400">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">{{ $post->user?->name ?? 'Unknown' }}</td>
                                <td class="py-4 px-6">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-medium
                                @if ($post->status === 'published') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400
                                @elseif($post->status === 'draft') bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400
                                @else bg-stone-100 text-stone-800 dark:bg-stone-700 dark:text-stone-300 @endif">
                                        {{ $post->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">{{ $post->views }}</td>
                                <td class="py-4 px-6">{{ $post->comments_count ?? 0 }}</td>
                                <td class="py-4 px-6 text-stone-600 dark:text-stone-400">
                                    {{ $post->created_at->format('M d, Y') }}</td>
                                <td class="py-4 px-6">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.blog.edit', $post) }}"
                                            class="text-primary-600 hover:text-primary-800 dark:text-primary-400"
                                            title="Edit">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                                            class="text-stone-500 hover:text-stone-700 dark:text-stone-400" title="Preview">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <button onclick="deletePost({{ $post->id }})"
                                            class="text-rose-500 hover:text-rose-700 dark:text-rose-400" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-stone-500 dark:text-stone-400">
                                    <div
                                        class="w-16 h-16 bg-stone-100 dark:bg-stone-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fa-solid fa-newspaper text-2xl text-stone-400"></i>
                                    </div>
                                    <p>No blog posts found</p>
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

        <!-- Recent Comments -->
        <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-stone-900 dark:text-white">Recent Comments</h3>
                <a href="{{ route('admin.blog.comments') }}" class="text-sm text-primary-600 hover:text-primary-700">View
                    All</a>
            </div>

            <div class="space-y-4">
                @forelse($recentComments ?? [] as $comment)
                    <div class="flex items-start gap-4 p-4 border border-stone-200 dark:border-stone-700 rounded-lg">
                        <div
                            class="w-10 h-10 rounded-full bg-stone-200 dark:bg-stone-700 flex items-center justify-center text-stone-500 font-medium">
                            {{ substr($comment->author_name ?? $comment->user?->name, 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-1">
                                <div>
                                    <span
                                        class="font-medium text-stone-800 dark:text-white">{{ $comment->author_name ?? $comment->user?->name }}</span>
                                    <span class="text-xs text-stone-500 ml-2">on "{{ $comment->post?->title }}"</span>
                                </div>
                                <span class="text-xs text-stone-500">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-stone-600 dark:text-stone-400 mb-2">
                                {{ Str::limit($comment->content, 100) }}</p>
                            <div class="flex gap-2">
                                <span
                                    class="px-2 py-1 rounded-full text-xs
                            @if ($comment->status === 'approved') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400
                            @elseif($comment->status === 'pending') bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400
                            @else bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400 @endif">
                                    {{ $comment->status }}
                                </span>
                                @if ($comment->status === 'pending')
                                    <button onclick="approveComment({{ $comment->id }})"
                                        class="text-xs text-primary-600 hover:text-primary-700">Approve</button>
                                @endif
                                <button onclick="deleteComment({{ $comment->id }})"
                                    class="text-xs text-rose-600 hover:text-rose-700">Delete</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-stone-500 py-4">No recent comments</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Filter by category
        function filterByCategory(categoryId) {
            const url = new URL(window.location);
            if (categoryId === 'all') {
                url.searchParams.delete('category');
            } else {
                url.searchParams.set('category', categoryId);
            }
            window.location = url;
        }

        // Show Add Post Modal
        function showAddPostModal() {
            const content = `
            <form id="add-post-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Title</label>
                    <input type="text" name="title" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Slug</label>
                    <input type="text" name="slug" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                    <p class="text-xs text-stone-500 mt-1">Auto-generated from title if empty</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Category</label>
                        <select name="category_id" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                            <option value="">Select Category</option>
                            @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Status</label>
                        <select name="status" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Excerpt</label>
                    <textarea name="excerpt" rows="3" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Content</label>
                    <textarea name="content" rows="6" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white"></textarea>
                </div>
            </form>
        `;

            showModal('New Blog Post', content, () => {
                const form = document.getElementById('add-post-form');
                const formData = new FormData(form);

                fetch('{{ route('admin.blog.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message, 'success');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showNotification(data.message, 'error');
                        }
                    });
            });
        }

        // Show Add Category Modal
        function showAddCategoryModal() {
            const content = `
            <form id="add-category-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Category Name</label>
                    <input type="text" name="name" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Slug</label>
                    <input type="text" name="slug" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                    <p class="text-xs text-stone-500 mt-1">Auto-generated if empty</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white"></textarea>
                </div>
            </form>
        `;

            showModal('New Category', content, () => {
                const form = document.getElementById('add-category-form');
                const formData = new FormData(form);

                fetch('{{ route('admin.blog.categories.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message, 'success');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showNotification(data.message, 'error');
                        }
                    });
            });
        }

        // Edit Post
        function editPost(id) {
            fetch(`{{ url('admin/blog') }}/${id}`)
                .then(r => r.json())
                .then(data => {
                    const post = data.post;
                    const content = `
                    <form id="edit-post-form" class="space-y-4">
                        <input type="hidden" name="_method" value="PUT">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Title</label>
                            <input type="text" name="title" value="${post.title}" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Slug</label>
                            <input type="text" name="slug" value="${post.slug}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Content</label>
                            <textarea name="content" rows="8" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">${post.content}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Status</label>
                            <select name="status" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                                <option value="draft" ${post.status === 'draft' ? 'selected' : ''}>Draft</option>
                                <option value="published" ${post.status === 'published' ? 'selected' : ''}>Published</option>
                                <option value="archived" ${post.status === 'archived' ? 'selected' : ''}>Archived</option>
                            </select>
                        </div>
                    </form>
                `;

                    showModal('Edit Post', content, () => {
                        const form = document.getElementById('edit-post-form');
                        const formData = new FormData(form);

                        fetch(`{{ url('admin/blog') }}/${id}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: formData
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    showNotification(data.message, 'success');
                                    setTimeout(() => location.reload(), 1000);
                                } else {
                                    showNotification(data.message, 'error');
                                }
                            });
                    });
                });
        }

        // Delete Post
        function deletePost(id) {
            if (!confirm('Are you sure you want to delete this post?')) return;

            fetch(`{{ url('admin/blog') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        location.reload();
                    } else {
                        showNotification(data.message, 'error');
                    }
                });
        }

        // Approve Comment
        function approveComment(id) {
            fetch(`{{ url('admin/blog/comments') }}/${id}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        location.reload();
                    } else {
                        showNotification(data.message, 'error');
                    }
                });
        }

        // Delete Comment
        function deleteComment(id) {
            if (!confirm('Delete this comment?')) return;

            fetch(`{{ url('admin/blog/comments') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        location.reload();
                    } else {
                        showNotification(data.message, 'error');
                    }
                });
        }

        // Show Modal Helper
        function showModal(title, content, onConfirm) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4';
            modal.innerHTML = `
            <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                    <h3 class="text-lg font-bold text-stone-900 dark:text-white">${title}</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-stone-500 hover:text-stone-800 dark:hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                <div class="p-6">
                    ${content}
                </div>
                <div class="flex justify-end gap-2 p-6 border-t border-stone-200 dark:border-stone-800">
                    <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 border border-stone-300 dark:border-stone-700 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800">
                        Cancel
                    </button>
                    <button id="modal-confirm" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                        Save
                    </button>
                </div>
            </div>
        `;
            document.body.appendChild(modal);

            modal.querySelector('#modal-confirm').addEventListener('click', () => {
                onConfirm();
                modal.remove();
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });
        }
    </script>
@endpush
