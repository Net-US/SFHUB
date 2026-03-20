@extends('layouts.app')

@section('title', 'SEO Settings | SFHUB Admin')

@section('page-title', 'SEO Settings')

@section('content')
<div class="animate-fade-in-up space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-stone-900 dark:text-white">SEO Settings</h2>
            <p class="text-stone-500 dark:text-stone-400 text-sm">Kelola SEO dan metadata website</p>
        </div>
        <div class="flex gap-2">
            <button onclick="generateSitemap()" class="flex items-center gap-2 px-4 py-2 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300 rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-sitemap"></i> Generate Sitemap
            </button>
            <button onclick="saveGlobalSEO()" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-save"></i> Save Changes
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl p-2 border border-stone-200 dark:border-stone-800">
        <div class="flex gap-2 overflow-x-auto">
            <button onclick="switchTab('global')" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400" data-tab="global">
                <i class="fa-solid fa-globe mr-1"></i> Global
            </button>
            <button onclick="switchTab('pages')" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800" data-tab="pages">
                <i class="fa-solid fa-file mr-1"></i> Pages
            </button>
            <button onclick="switchTab('metatags')" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800" data-tab="metatags">
                <i class="fa-solid fa-tags mr-1"></i> Meta Tags
            </button>
            <button onclick="switchTab('sitemap')" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800" data-tab="sitemap">
                <i class="fa-solid fa-map mr-1"></i> Sitemap
            </button>
        </div>
    </div>

    <!-- Global SEO -->
    <div id="tab-global" class="tab-content bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
        <h3 class="font-bold text-stone-900 dark:text-white mb-6">Global SEO Settings</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Site Title</label>
                    <input type="text" id="global-title" value="{{ $globalSeo?->default_title ?? 'Student-Freelancer Hub' }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Default Description</label>
                    <textarea id="global-description" rows="3" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">{{ $globalSeo?->default_description ?? 'Platform terpadu untuk mahasiswa dan freelancer Indonesia' }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Default Keywords</label>
                    <input type="text" id="global-keywords" value="{{ $globalSeo?->default_keywords ?? 'mahasiswa, freelancer, manajemen tugas, keuangan, kalender' }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                    <p class="text-xs text-stone-500 mt-1">Separate keywords with commas</p>
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Author</label>
                    <input type="text" id="global-author" value="{{ $globalSeo?->author ?? 'SFHUB Team' }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Robots</label>
                    <select id="global-robots" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        <option value="index, follow" {{ ($globalSeo?->robots ?? 'index, follow') === 'index, follow' ? 'selected' : '' }}>index, follow</option>
                        <option value="index, nofollow" {{ ($globalSeo?->robots ?? '') === 'index, nofollow' ? 'selected' : '' }}>index, nofollow</option>
                        <option value="noindex, follow" {{ ($globalSeo?->robots ?? '') === 'noindex, follow' ? 'selected' : '' }}>noindex, follow</option>
                        <option value="noindex, nofollow" {{ ($globalSeo?->robots ?? '') === 'noindex, nofollow' ? 'selected' : '' }}>noindex, nofollow</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Google Analytics ID</label>
                    <input type="text" id="global-analytics" value="{{ $globalSeo?->google_analytics_id ?? '' }}" placeholder="UA-12345678-1 or G-XXXXXXXXXX" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Facebook Pixel ID</label>
                    <input type="text" id="global-pixel" value="{{ $globalSeo?->facebook_pixel_id ?? '' }}" placeholder="FB-PIXEL-123" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="global-analytics-active" {{ $globalSeo?->analytics_active ? 'checked' : '' }} class="rounded border-stone-300 text-primary-600 focus:ring-primary-500">
                    <label for="global-analytics-active" class="text-sm text-stone-700 dark:text-stone-300">Enable Analytics Tracking</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Pages SEO -->
    <div id="tab-pages" class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-stone-900 dark:text-white">Page SEO Settings</h3>
            <button onclick="addPageSEO()" class="flex items-center gap-2 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm">
                <i class="fa-solid fa-plus"></i> Add Page
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-stone-200 dark:border-stone-700">
                        <th class="text-left py-3 text-stone-600 dark:text-stone-400 font-medium">Page</th>
                        <th class="text-left py-3 text-stone-600 dark:text-stone-400 font-medium">Title</th>
                        <th class="text-left py-3 text-stone-600 dark:text-stone-400 font-medium">Priority</th>
                        <th class="text-left py-3 text-stone-600 dark:text-stone-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pageSeo ?? [] as $seo)
                    <tr class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800 page-seo-row" data-id="{{ $seo->id }}">
                        <td class="py-3 font-medium text-stone-800 dark:text-white">{{ $seo->page }}</td>
                        <td class="py-3">{{ Str::limit($seo->title, 50) }}</td>
                        <td class="py-3">{{ $seo->priority }}</td>
                        <td class="py-3">
                            <div class="flex gap-2">
                                <button onclick="editPageSEO({{ $seo->id }})" class="text-primary-600 hover:text-primary-800 dark:text-primary-400">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <button onclick="deletePageSEO({{ $seo->id }})" class="text-rose-600 hover:text-rose-800 dark:text-rose-400">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-stone-500">No page SEO settings yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Meta Tags -->
    <div id="tab-metatags" class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-stone-900 dark:text-white">Custom Meta Tags</h3>
            <button onclick="addMetaTag()" class="flex items-center gap-2 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm">
                <i class="fa-solid fa-plus"></i> Add Tag
            </button>
        </div>
        <div class="space-y-3" id="metatags-list">
            @forelse($metaTags ?? [] as $tag)
            <div class="meta-tag-item flex items-center gap-4 p-4 border border-stone-200 dark:border-stone-700 rounded-lg" data-id="{{ $tag->id }}">
                <select class="tag-type w-32 border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">
                    <option value="name" {{ $tag->type === 'name' ? 'selected' : '' }}>name</option>
                    <option value="property" {{ $tag->type === 'property' ? 'selected' : '' }}>property</option>
                    <option value="http-equiv" {{ $tag->type === 'http-equiv' ? 'selected' : '' }}>http-equiv</option>
                </select>
                <input type="text" value="{{ $tag->name }}" placeholder="Tag name" class="tag-name flex-1 border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">
                <input type="text" value="{{ $tag->content }}" placeholder="Content" class="tag-content flex-1 border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">
                <label class="flex items-center gap-1">
                    <input type="checkbox" class="tag-active" {{ $tag->is_active ? 'checked' : '' }}>
                    <span class="text-sm">Active</span>
                </label>
                <button onclick="deleteMetaTag({{ $tag->id }})" class="text-rose-600 hover:text-rose-800">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
            @empty
            <p class="text-center text-stone-500 py-4">No custom meta tags yet</p>
            @endforelse
        </div>
    </div>

    <!-- Sitemap -->
    <div id="tab-sitemap" class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
        <h3 class="font-bold text-stone-900 dark:text-white mb-6">Sitemap Settings</h3>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                    <div>
                        <p class="font-medium text-stone-800 dark:text-white">Auto-generate Sitemap</p>
                        <p class="text-sm text-stone-500">Automatically generate sitemap on content changes</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="sitemap-auto" {{ $sitemap?->auto_generate ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-stone-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-stone-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Sitemap Path</label>
                        <input type="text" id="sitemap-path" value="{{ $sitemap?->sitemap_path ?? 'sitemap.xml' }}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Last Generated</label>
                        <input type="text" value="{{ $sitemap?->last_generated?->format('Y-m-d H:i:s') ?? 'Never' }}" readonly class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 bg-stone-100 dark:bg-stone-800 text-stone-500">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="generateSitemap()" class="flex-1 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium">
                        <i class="fa-solid fa-sync-alt mr-2"></i> Generate Now
                    </button>
                    <a href="{{ route('admin.seo.sitemap.download') }}" class="flex-1 py-3 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 rounded-xl font-medium text-center">
                        <i class="fa-solid fa-download mr-2"></i> Download
                    </a>
                </div>
            </div>
            <div class="bg-stone-50 dark:bg-stone-800 rounded-xl p-4">
                <h4 class="font-medium text-stone-800 dark:text-white mb-4">Sitemap Info</h4>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-stone-500">URL Count</span>
                        <span class="font-medium">{{ $sitemap?->url_count ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Status</span>
                        <span class="font-medium text-emerald-600">{{ $sitemap?->last_generated ? 'Generated' : 'Not Generated' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tab switching
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-primary-100', 'text-primary-700', 'dark:bg-primary-900/30', 'dark:text-primary-400');
            btn.classList.add('text-stone-600', 'dark:text-stone-400');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('bg-primary-100', 'text-primary-700', 'dark:bg-primary-900/30', 'dark:text-primary-400');
        document.querySelector(`[data-tab="${tabName}"]`).classList.remove('text-stone-600', 'dark:text-stone-400');
        
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById(`tab-${tabName}`).classList.remove('hidden');
    }

    // Save Global SEO
    function saveGlobalSEO() {
        const data = {
            default_title: document.getElementById('global-title').value,
            default_description: document.getElementById('global-description').value,
            default_keywords: document.getElementById('global-keywords').value,
            author: document.getElementById('global-author').value,
            robots: document.getElementById('global-robots').value,
            google_analytics_id: document.getElementById('global-analytics').value,
            facebook_pixel_id: document.getElementById('global-pixel').value,
            analytics_active: document.getElementById('global-analytics-active').checked,
        };

        fetch('{{ route("admin.seo.global.update") }}', {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification('Global SEO settings saved');
            } else {
                showNotification(data.message, 'error');
            }
        });
    }

    // Generate Sitemap
    function generateSitemap() {
        fetch('{{ route("admin.seo.sitemap.generate") }}', {
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
            } else {
                showNotification(data.message, 'error');
            }
        });
    }

    // Add Page SEO
    function addPageSEO() {
        const content = `
            <form id="add-page-seo-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Page Slug</label>
                    <input type="text" name="page" required placeholder="e.g., about, contact" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Title</label>
                    <input type="text" name="title" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Priority (0.0 - 1.0)</label>
                    <input type="number" name="priority" step="0.1" min="0" max="1" value="0.8" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
            </form>
        `;
        showModal('Add Page SEO', content, () => {
            const form = document.getElementById('add-page-seo-form');
            const formData = new FormData(form);
            
            fetch('{{ route("admin.seo.pages") }}', {
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

    // Edit Page SEO
    function editPageSEO(id) {
        fetch(`{{ url('admin/seo/pages') }}/${id}`)
            .then(r => r.json())
            .then(data => {
                const seo = data.seo;
                const content = `
                    <form id="edit-page-seo-form" class="space-y-4">
                        <input type="hidden" name="_method" value="PUT">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Title</label>
                            <input type="text" name="title" value="${seo.title}" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Description</label>
                            <textarea name="description" rows="3" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">${seo.description || ''}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Keywords</label>
                            <input type="text" name="keywords" value="${seo.keywords || ''}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Priority</label>
                            <input type="number" name="priority" step="0.1" min="0" max="1" value="${seo.priority}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        </div>
                    </form>
                `;
                showModal('Edit Page SEO', content, () => {
                    const form = document.getElementById('edit-page-seo-form');
                    const formData = new FormData(form);
                    
                    fetch(`{{ url('admin/seo/pages') }}/${id}`, {
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

    // Add Meta Tag
    function addMetaTag() {
        const id = 'new_' + Date.now();
        const div = document.createElement('div');
        div.className = 'meta-tag-item flex items-center gap-4 p-4 border border-stone-200 dark:border-stone-700 rounded-lg';
        div.dataset.id = id;
        div.innerHTML = `
            <select class="tag-type w-32 border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">
                <option value="name">name</option>
                <option value="property">property</option>
                <option value="http-equiv">http-equiv</option>
            </select>
            <input type="text" placeholder="Tag name" class="tag-name flex-1 border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">
            <input type="text" placeholder="Content" class="tag-content flex-1 border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">
            <label class="flex items-center gap-1">
                <input type="checkbox" class="tag-active" checked>
                <span class="text-sm">Active</span>
            </label>
            <button onclick="this.closest('.meta-tag-item').remove()" class="text-rose-600 hover:text-rose-800">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        document.getElementById('metatags-list').appendChild(div);
    }

    // Notification helper
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'} text-white px-4 py-2 rounded-lg shadow-lg z-50`;
        notification.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>${message}`;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
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
</script>
@endpush
