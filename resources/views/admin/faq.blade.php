@extends('layouts.app')

@section('title', 'FAQ Management | SFHUB Admin')

@section('page-title', 'FAQ Management')

@section('content')
<div class="animate-fade-in-up space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-stone-900 dark:text-white">FAQ Management</h2>
            <p class="text-stone-500 dark:text-stone-400 text-sm">Kelola pertanyaan yang sering diajukan</p>
        </div>
        <div class="flex gap-2">
            <button onclick="showAddCategoryModal()" class="flex items-center gap-2 px-4 py-2 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300 rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-folder-plus"></i> Category
            </button>
            <button onclick="showAddFaqModal()" class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-plus"></i> New FAQ
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <p class="text-sm text-stone-500 dark:text-stone-400">Total FAQs</p>
            <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $totalFaqs ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <p class="text-sm text-stone-500 dark:text-stone-400">Published</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $publishedFaqs ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <p class="text-sm text-stone-500 dark:text-stone-400">Categories</p>
            <p class="text-2xl font-bold text-amber-600">{{ $categories->count() ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <p class="text-sm text-stone-500 dark:text-stone-400">Total Views</p>
            <p class="text-2xl font-bold text-blue-600">{{ $totalViews ?? 0 }}</p>
        </div>
    </div>

    <!-- Categories Tabs -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200 dark:border-stone-800">
        <div class="flex flex-wrap gap-2 items-center">
            <span class="text-sm text-stone-500 mr-2">Categories:</span>
            <button onclick="filterByCategory('all')" class="category-filter px-3 py-1.5 rounded-full text-sm {{ !request('category') ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300' }}">All</button>
            @foreach($categories as $category)
            <button onclick="filterByCategory({{ $category->id }})" class="category-filter px-3 py-1.5 rounded-full text-sm flex items-center gap-1 {{ request('category') == $category->id ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300' }}">
                <span class="w-2 h-2 rounded-full" style="background-color: {{ $category->color ?? '#f57223' }}"></span>
                {{ $category->name }}
            </button>
            @endforeach
        </div>
    </div>

    <!-- FAQs List -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-50 dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
                        <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Question</th>
                        <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Category</th>
                        <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Status</th>
                        <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Views</th>
                        <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Order</th>
                        <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faqs as $faq)
                    <tr class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800">
                        <td class="py-4 px-6">
                            <div>
                                <p class="font-medium text-stone-800 dark:text-white">{{ $faq->question }}</p>
                                <p class="text-xs text-stone-500 mt-1">{{ Str::limit(strip_tags($faq->answer), 80) }}</p>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            @if($faq->category)
                            <span class="px-2 py-1 rounded-full text-xs" style="background-color: {{ $faq->category->color ?? '#f57223' }}20; color: {{ $faq->category->color ?? '#f57223' }}">
                                {{ $faq->category->name }}
                            </span>
                            @else
                            <span class="text-stone-400">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $faq->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-stone-100 text-stone-800 dark:bg-stone-700 dark:text-stone-300' }}">
                                {{ $faq->is_active ? 'Published' : 'Draft' }}
                            </span>
                        </td>
                        <td class="py-4 px-6">{{ $faq->view_count ?? 0 }}</td>
                        <td class="py-4 px-6">{{ $faq->sort_order }}</td>
                        <td class="py-4 px-6">
                            <div class="flex gap-2">
                                <button onclick="editFaq({{ $faq->id }})" class="text-primary-600 hover:text-primary-800 dark:text-primary-400" title="Edit">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <button onclick="toggleFaqStatus({{ $faq->id }})" class="text-amber-600 hover:text-amber-800 dark:text-amber-400" title="Toggle Status">
                                    <i class="fa-solid {{ $faq->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                </button>
                                <button onclick="deleteFaq({{ $faq->id }})" class="text-rose-600 hover:text-rose-800 dark:text-rose-400" title="Delete">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-stone-500 dark:text-stone-400">
                            <div class="w-16 h-16 bg-stone-100 dark:bg-stone-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-circle-question text-2xl text-stone-400"></i>
                            </div>
                            <p>No FAQs found. Create your first FAQ.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-stone-200 dark:border-stone-700">
            {{ $faqs->links() }}
        </div>
    </div>

    <!-- Categories Management -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
        <h3 class="font-bold text-stone-900 dark:text-white mb-6">Categories</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($categories as $category)
            <div class="p-4 border border-stone-200 dark:border-stone-700 rounded-lg flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $category->color ?? '#f57223' }}"></span>
                    <div>
                        <p class="font-medium text-stone-800 dark:text-white">{{ $category->name }}</p>
                        <p class="text-xs text-stone-500">{{ $category->faqs_count ?? 0 }} FAQs</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="editCategory({{ $category->id }})" class="text-primary-600 hover:text-primary-800 dark:text-primary-400">
                        <i class="fa-solid fa-edit"></i>
                    </button>
                    <button onclick="deleteCategory({{ $category->id }})" class="text-rose-600 hover:text-rose-800 dark:text-rose-400">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
            @endforeach
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

    // Show Add FAQ Modal
    function showAddFaqModal() {
        const content = `
            <form id="add-faq-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Question</label>
                    <input type="text" name="question" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Answer</label>
                    <textarea name="answer" rows="5" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Category</label>
                        <select name="faq_category_id" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Sort Order</label>
                        <input type="number" name="sort_order" value="0" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-stone-300 text-primary-600 focus:ring-primary-500">
                    <label class="text-sm text-stone-700 dark:text-stone-300">Publish immediately</label>
                </div>
            </form>
        `;
        
        showModal('Add New FAQ', content, () => {
            const form = document.getElementById('add-faq-form');
            const formData = new FormData(form);
            
            fetch('{{ route("admin.faq.store") }}', {
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
                    showNotification(data.message);
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
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Color</label>
                    <input type="color" name="color" value="#f57223" class="w-full h-10 border border-stone-300 dark:border-stone-700 rounded-xl">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-stone-300 text-primary-600 focus:ring-primary-500">
                    <label class="text-sm text-stone-700 dark:text-stone-300">Active</label>
                </div>
            </form>
        `;
        
        showModal('Add FAQ Category', content, () => {
            const form = document.getElementById('add-category-form');
            const formData = new FormData(form);
            
            fetch('{{ route("admin.faq.categories.store") }}', {
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
                    showNotification(data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            });
        });
    }

    // Edit FAQ
    function editFaq(id) {
        fetch(`{{ url('admin/faq') }}/${id}`)
            .then(r => r.json())
            .then(data => {
                const faq = data.faq;
                const content = `
                    <form id="edit-faq-form" class="space-y-4">
                        <input type="hidden" name="_method" value="PUT">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Question</label>
                            <input type="text" name="question" value="${faq.question}" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Answer</label>
                            <textarea name="answer" rows="5" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">${faq.answer}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Category</label>
                                <select name="faq_category_id" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" ${faq.faq_category_id == {{ $cat->id }} ? 'selected' : ''}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Sort Order</label>
                                <input type="number" name="sort_order" value="${faq.sort_order}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                            </div>
                        </div>
                    </form>
                `;
                
                showModal('Edit FAQ', content, () => {
                    const form = document.getElementById('edit-faq-form');
                    const formData = new FormData(form);
                    
                    fetch(`{{ url('admin/faq') }}/${id}`, {
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
                            showNotification(data.message);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showNotification(data.message, 'error');
                        }
                    });
                });
            });
    }

    // Toggle FAQ Status
    function toggleFaqStatus(id) {
        fetch(`{{ url('admin/faq') }}/${id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.message, 'error');
            }
        });
    }

    // Delete FAQ
    function deleteFaq(id) {
        if (!confirm('Delete this FAQ?')) return;
        
        fetch(`{{ url('admin/faq') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message);
                location.reload();
            } else {
                showNotification(data.message, 'error');
            }
        });
    }

    // Edit Category
    function editCategory(id) {
        fetch(`{{ url('admin/faq/categories') }}/${id}`)
            .then(r => r.json())
            .then(data => {
                const cat = data.category;
                const content = `
                    <form id="edit-category-form" class="space-y-4">
                        <input type="hidden" name="_method" value="PUT">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Name</label>
                            <input type="text" name="name" value="${cat.name}" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Color</label>
                            <input type="color" name="color" value="${cat.color || '#f57223'}" class="w-full h-10 border border-stone-300 dark:border-stone-700 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Description</label>
                            <textarea name="description" rows="3" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">${cat.description || ''}</textarea>
                        </div>
                    </form>
                `;
                
                showModal('Edit Category', content, () => {
                    const form = document.getElementById('edit-category-form');
                    const formData = new FormData(form);
                    
                    fetch(`{{ url('admin/faq/categories') }}/${id}`, {
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
                            showNotification(data.message);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showNotification(data.message, 'error');
                        }
                    });
                });
            });
    }

    // Delete Category
    function deleteCategory(id) {
        if (!confirm('Delete this category? FAQs will be moved to no category.')) return;
        
        fetch(`{{ url('admin/faq/categories') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message);
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
            <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                    <h3 class="text-lg font-bold text-stone-900 dark:text-white">${title}</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-stone-500 hover:text-stone-800 dark:hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                <div class="p-6">${content}</div>
                <div class="flex justify-end gap-2 p-6 border-t border-stone-200 dark:border-stone-800">
                    <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 border border-stone-300 dark:border-stone-700 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800">Cancel</button>
                    <button id="modal-confirm" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">Save</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.querySelector('#modal-confirm').addEventListener('click', () => { onConfirm(); modal.remove(); });
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.remove(); });
    }

    // Notification helper
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'} text-white px-4 py-2 rounded-lg shadow-lg z-50`;
        notification.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>${message}`;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
</script>
@endpush
