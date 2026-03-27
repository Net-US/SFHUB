@extends('layouts.app')

@section('title', 'Landing Page Editor | SFHUB Admin')

@section('page-title', 'Landing Page Editor')

@section('content')
    <div class="animate-fade-in-up space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Landing Page Editor</h2>
                <p class="text-stone-500 dark:text-stone-400 text-sm">Kelola konten halaman landing</p>
            </div>
            <button onclick="saveAllChanges()"
                class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-save"></i> Save All Changes
            </button>
        </div>

        <!-- Tabs -->
        <div class="bg-white dark:bg-stone-900 rounded-2xl p-2 border border-stone-200 dark:border-stone-800">
            <div class="flex gap-2 overflow-x-auto">
                <button onclick="switchTab('hero')"
                    class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400"
                    data-tab="hero">
                    <i class="fa-solid fa-image mr-1"></i> Hero
                </button>
                <button onclick="switchTab('features')"
                    class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800"
                    data-tab="features">
                    <i class="fa-solid fa-star mr-1"></i> Features
                </button>
                <button onclick="switchTab('testimonials')"
                    class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800"
                    data-tab="testimonials">
                    <i class="fa-solid fa-quote-left mr-1"></i> Testimonials
                </button>
                <button onclick="switchTab('stats')"
                    class="tab-btn px-4 py-2 rounded-lg text-sm font-medium text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800"
                    data-tab="stats">
                    <i class="fa-solid fa-chart-bar mr-1"></i> Stats
                </button>
            </div>
        </div>

        <!-- Hero Section -->
        <div id="tab-hero"
            class="tab-content bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
            <h3 class="font-bold text-stone-900 dark:text-white mb-4">Hero Section</h3>
            @php
                $hero = $heroes->first();
            @endphp
            <input type="hidden" id="hero-id" value="{{ $hero?->id ?? '' }}">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Title</label>
                        <input type="text" id="hero-title" value="{{ $hero?->title ?? '' }}"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Subtitle</label>
                        <textarea id="hero-subtitle" rows="3"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">{{ $hero?->subtitle ?? '' }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">CTA
                                Text</label>
                            <input type="text" id="hero-cta-text" value="{{ $hero?->cta_text ?? '' }}"
                                class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">CTA
                                Link</label>
                            <input type="text" id="hero-cta-link" value="{{ $hero?->cta_link ?? '' }}"
                                class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Hero Image</label>
                        <div class="border-2 border-dashed border-stone-300 dark:border-stone-700 rounded-xl p-6 text-center"
                            id="hero-image-dropzone">
                            <input type="file" id="hero-image-input" accept="image/*" class="hidden"
                                onchange="handleImageUpload(this)">
                            <input type="hidden" id="hero-image-url" value="{{ $hero?->hero_image ?? '' }}">
                            <div id="hero-image-preview" class="mb-4 relative {{ $hero?->hero_image ? '' : 'hidden' }}">
                                <img src="{{ $hero?->hero_image ? \App\Helpers\StorageHelper::getImageUrl(basename($hero->hero_image), 'landing') : '' }}"
                                    alt="Hero" class="max-h-40 mx-auto rounded-lg">
                                @if ($hero?->hero_image)
                                    <button onclick="deleteHeroImage()"
                                        class="absolute top-2 right-2 bg-rose-500 hover:bg-rose-600 text-white p-1.5 rounded-lg shadow-lg"
                                        title="Hapus gambar hero">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>
                                @endif
                            </div>
                            <button onclick="document.getElementById('hero-image-input').click()"
                                class="px-4 py-2 bg-stone-100 dark:bg-stone-800 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700">
                                <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Upload Image
                            </button>
                            <p class="text-xs text-stone-500 mt-2">Format: JPG, PNG, GIF, WebP (Max 5MB)</p>
                            <p class="text-xs text-stone-400">Atau drag and drop gambar di sini</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Logo</label>
                            <div
                                class="border-2 border-dashed border-stone-300 dark:border-stone-700 rounded-xl p-4 text-center">
                                <input type="file" id="logo-input" accept="image/*" class="hidden"
                                    onchange="handleLogoUpload(this)">
                                <input type="hidden" id="logo-url"
                                    value="{{ \App\Models\SiteSetting::getValue('site_logo') }}">
                                <div id="logo-preview"
                                    class="mb-3 relative {{ \App\Models\SiteSetting::getValue('site_logo') ? '' : 'hidden' }}">
                                    <img src="{{ \App\Helpers\StorageHelper::getImageUrl(\App\Models\SiteSetting::getValue('site_logo'), 'site') }}"
                                        alt="Logo" class="max-h-16 mx-auto">
                                    @if (\App\Models\SiteSetting::getValue('site_logo'))
                                        <button onclick="deleteLogo()"
                                            class="absolute -top-2 -right-2 bg-rose-500 hover:bg-rose-600 text-white p-1 rounded-lg shadow-lg"
                                            title="Hapus logo">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                                <button onclick="document.getElementById('logo-input').click()"
                                    class="px-3 py-2 bg-stone-100 dark:bg-stone-800 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700 text-sm">
                                    <i class="fa-solid fa-upload mr-2"></i> Upload Logo
                                </button>
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Favicon</label>
                            <div
                                class="border-2 border-dashed border-stone-300 dark:border-stone-700 rounded-xl p-4 text-center">
                                <input type="file" id="favicon-input" accept="image/*" class="hidden"
                                    onchange="handleFaviconUpload(this)">
                                <input type="hidden" id="favicon-url"
                                    value="{{ \App\Models\SiteSetting::getValue('site_favicon') }}">
                                <div id="favicon-preview"
                                    class="mb-3 relative {{ \App\Models\SiteSetting::getValue('site_favicon') ? '' : 'hidden' }}">
                                    <img src="{{ \App\Helpers\StorageHelper::getImageUrl(\App\Models\SiteSetting::getValue('site_favicon'), 'site') }}"
                                        alt="Favicon" class="max-h-8 mx-auto">
                                    @if (\App\Models\SiteSetting::getValue('site_favicon'))
                                        <button onclick="deleteFavicon()"
                                            class="absolute -top-2 -right-2 bg-rose-500 hover:bg-rose-600 text-white p-1 rounded-lg shadow-lg"
                                            title="Hapus favicon">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                                <button onclick="document.getElementById('favicon-input').click()"
                                    class="px-3 py-2 bg-stone-100 dark:bg-stone-800 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700 text-sm">
                                    <i class="fa-solid fa-upload mr-2"></i> Upload Favicon
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-stone-50 dark:bg-stone-800 rounded-xl p-6">
                    <p class="text-sm font-medium text-stone-700 dark:text-stone-300 mb-4">Preview</p>
                    <div class="bg-gradient-to-r from-primary-600 to-primary-800 rounded-xl p-6 text-white text-center">
                        <h2 class="text-2xl font-bold mb-2" id="preview-hero-title">{{ $hero?->title ?? 'Your Title' }}
                        </h2>
                        <p class="text-primary-100 mb-4" id="preview-hero-subtitle">
                            {{ $hero?->subtitle ?? 'Your subtitle here' }}</p>
                        <span class="inline-block bg-white text-primary-600 px-6 py-2 rounded-full font-medium"
                            id="preview-hero-cta">{{ $hero?->cta_text ?? 'Get Started' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="tab-features"
            class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-stone-900 dark:text-white">Features</h3>
                <button onclick="addFeature()"
                    class="flex items-center gap-2 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm">
                    <i class="fa-solid fa-plus"></i> Add Feature
                </button>
            </div>
            <div class="space-y-3" id="features-list">
                @forelse($features ?? [] as $feature)
                    <div class="feature-item flex items-center gap-4 p-4 border border-stone-200 dark:border-stone-700 rounded-lg"
                        data-id="{{ $feature->id }}">
                        <div
                            class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600">
                            <i class="fa-solid fa-{{ $feature->icon }}"></i>
                        </div>
                        <div class="flex-1 grid grid-cols-3 gap-4">
                            <input type="text" value="{{ $feature->title }}" placeholder="Title"
                                class="feature-title border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">
                            <input type="text" value="{{ $feature->description }}" placeholder="Description"
                                class="feature-desc border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">
                            <input type="text" value="{{ $feature->icon }}" placeholder="Icon (fontawesome)"
                                class="feature-icon border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">
                        </div>
                        <button onclick="deleteFeature({{ $feature->id }})" class="text-rose-600 hover:text-rose-800">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                @empty
                    <p class="text-center text-stone-500 py-4">No features yet. Add your first feature.</p>
                @endforelse
            </div>
        </div>

        <!-- Testimonials Section -->
        <div id="tab-testimonials"
            class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-stone-900 dark:text-white">Testimonials</h3>
                <button onclick="addTestimonial()"
                    class="flex items-center gap-2 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm">
                    <i class="fa-solid fa-plus"></i> Add Testimonial
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="testimonials-list">
                @forelse($testimonials ?? [] as $testimonial)
                    <div class="testimonial-item p-4 border border-stone-200 dark:border-stone-700 rounded-lg"
                        data-id="{{ $testimonial->id }}">
                        <div class="flex items-start gap-3 mb-3">
                            <div
                                class="w-12 h-12 rounded-full bg-gradient-to-br from-stone-400 to-stone-600 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($testimonial->name, 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <input type="text" value="{{ $testimonial->name }}" placeholder="Name"
                                    class="testimonial-name w-full border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-1 text-sm dark:bg-stone-800 dark:text-white mb-1">
                                <input type="text" value="{{ $testimonial->role }}" placeholder="Role"
                                    class="testimonial-role w-full border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-1 text-sm dark:bg-stone-800 dark:text-white">
                            </div>
                            <button onclick="deleteTestimonial({{ $testimonial->id }})"
                                class="text-rose-600 hover:text-rose-800">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                        <textarea placeholder="Testimonial content..." rows="3"
                            class="testimonial-content w-full border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">{{ $testimonial->content }}</textarea>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-sm text-stone-500">Rating:</span>
                            <select
                                class="testimonial-rating border border-stone-300 dark:border-stone-700 rounded-lg px-2 py-1 text-sm dark:bg-stone-800 dark:text-white">
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}"
                                        {{ $testimonial->rating == $i ? 'selected' : '' }}>{{ $i }}
                                        star{{ $i > 1 ? 's' : '' }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                @empty
                    <p class="col-span-2 text-center text-stone-500 py-4">No testimonials yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Stats Section -->
        <div id="tab-stats"
            class="tab-content hidden bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-stone-900 dark:text-white">Statistics</h3>
                <button onclick="addStat()"
                    class="flex items-center gap-2 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm">
                    <i class="fa-solid fa-plus"></i> Add Stat
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="stats-list">
                @forelse($stats ?? [] as $stat)
                    <div class="stat-item p-4 border border-stone-200 dark:border-stone-700 rounded-lg text-center"
                        data-id="{{ $stat->id }}">
                        <input type="text" value="{{ $stat->value }}" placeholder="Value"
                            class="stat-value w-full border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-center text-2xl font-bold dark:bg-stone-800 dark:text-white mb-2">
                        <input type="text" value="{{ $stat->label }}" placeholder="Label"
                            class="stat-label w-full border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-1 text-sm text-center dark:bg-stone-800 dark:text-white">
                        <input type="text" value="{{ $stat->icon }}" placeholder="Icon"
                            class="stat-icon w-full border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-1 text-sm text-center dark:bg-stone-800 dark:text-white mt-2">
                        <button onclick="deleteStat({{ $stat->id }})"
                            class="text-rose-600 hover:text-rose-800 text-sm mt-2">
                            <i class="fa-solid fa-trash-can"></i> Delete
                        </button>
                    </div>
                @empty
                    <p class="col-span-4 text-center text-stone-500 py-4">No stats yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Tab switching
        function switchTab(tabName) {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-primary-100', 'text-primary-700', 'dark:bg-primary-900/30',
                    'dark:text-primary-400');
                btn.classList.add('text-stone-600', 'dark:text-stone-400');
            });
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('bg-primary-100', 'text-primary-700',
                'dark:bg-primary-900/30', 'dark:text-primary-400');
            document.querySelector(`[data-tab="${tabName}"]`).classList.remove('text-stone-600', 'dark:text-stone-400');

            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById(`tab-${tabName}`).classList.remove('hidden');
        }

        // Hero preview update
        document.getElementById('hero-title')?.addEventListener('input', (e) => {
            document.getElementById('preview-hero-title').textContent = e.target.value;
        });
        document.getElementById('hero-subtitle')?.addEventListener('input', (e) => {
            document.getElementById('preview-hero-subtitle').textContent = e.target.value;
        });
        document.getElementById('hero-cta-text')?.addEventListener('input', (e) => {
            document.getElementById('preview-hero-cta').textContent = e.target.value;
        });

        // Save all changes
        function saveAllChanges() {
            const heroId = document.getElementById('hero-id')?.value;
            const heroTitle = document.getElementById('hero-title')?.value;
            const heroSubtitle = document.getElementById('hero-subtitle')?.value;

            const data = {
                hero: heroTitle ? {
                    id: heroId || null,
                    title: heroTitle,
                    subtitle: heroSubtitle,
                    cta_text: document.getElementById('hero-cta-text')?.value,
                    cta_link: document.getElementById('hero-cta-link')?.value,
                    hero_image: document.getElementById('hero-image-url')?.value,
                } : null,
                features: Array.from(document.querySelectorAll('.feature-item'))
                    .map(item => ({
                        id: item.dataset.id,
                        title: item.querySelector('.feature-title').value,
                        description: item.querySelector('.feature-desc').value,
                        icon: item.querySelector('.feature-icon').value,
                    }))
                    .filter(item => item.title && item.description),
                testimonials: Array.from(document.querySelectorAll('.testimonial-item'))
                    .map(item => ({
                        id: item.dataset.id,
                        name: item.querySelector('.testimonial-name').value,
                        role: item.querySelector('.testimonial-role').value,
                        content: item.querySelector('.testimonial-content').value,
                        rating: item.querySelector('.testimonial-rating').value,
                    }))
                    .filter(item => item.name && item.content),
                stats: Array.from(document.querySelectorAll('.stat-item'))
                    .map(item => ({
                        id: item.dataset.id,
                        value: item.querySelector('.stat-value').value,
                        label: item.querySelector('.stat-label').value,
                        icon: item.querySelector('.stat-icon').value,
                    }))
                    .filter(item => item.value && item.label)
            };

            fetch('{{ route('admin.landing.save') }}', {
                    method: 'POST',
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
                        showNotification('Changes saved successfully');
                        // Reload page to get new IDs for created items
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showNotification(data.message || 'Failed to save', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showNotification('Error saving changes', 'error');
                });
        }

        // Handle image upload
        function handleImageUpload(input) {
            const file = input.files[0];
            if (!file) return;
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const formData = new FormData();
                formData.append('image', file);

                fetch('{{ route('admin.landing.upload-image') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: formData
                    })
                    .then(response => {
                        // Check if response is JSON
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            return response.json();
                        } else {
                            // Handle HTML error response
                            return response.text().then(text => {
                                console.error('Server returned HTML instead of JSON:', text);
                                throw new Error('Server error: Invalid response format');
                            });
                        }
                    })
                    .then(data => {
                        if (data.success) {
                            document.getElementById('hero-image-url').value = data.filename; // Simpan hanya filename
                            document.getElementById('hero-image-preview').innerHTML =
                                `<img src="${data.url}" alt="Hero" class="max-h-40 mx-auto rounded-lg">`;
                            document.getElementById('hero-image-preview').classList.remove('hidden');
                            showNotification('Image uploaded successfully');
                        } else {
                            showNotification(data.message || 'Upload failed', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Upload error:', err);
                        showNotification('Error uploading image: ' + err.message, 'error');
                    });
            }
        }

        // Handle logo upload
        function handleLogoUpload(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const formData = new FormData();
                formData.append('image', file);

                fetch('{{ route('admin.landing.upload-logo') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: formData
                    })
                    .then(response => {
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            return response.json();
                        } else {
                            return response.text().then(text => {
                                console.error('Server returned HTML instead of JSON:', text);
                                throw new Error('Server error: Invalid response format');
                            });
                        }
                    })
                    .then(data => {
                        if (data.success) {
                            document.getElementById('logo-url').value = data.filename; // Simpan hanya filename
                            document.getElementById('logo-preview').innerHTML =
                                `<img src="${data.url}" alt="Logo" class="max-h-16 mx-auto">`;
                            document.getElementById('logo-preview').classList.remove('hidden');
                            showNotification('Logo uploaded successfully');
                        } else {
                            showNotification(data.message || 'Upload failed', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Upload error:', err);
                        showNotification('Error uploading logo: ' + err.message, 'error');
                    });
            }
        }

        // Handle favicon upload
        function handleFaviconUpload(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const formData = new FormData();
                formData.append('image', file);

                fetch('{{ route('admin.landing.upload-favicon') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: formData
                    })
                    .then(response => {
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            return response.json();
                        } else {
                            return response.text().then(text => {
                                console.error('Server returned HTML instead of JSON:', text);
                                throw new Error('Server error: Invalid response format');
                            });
                        }
                    })
                    .then(data => {
                        if (data.success) {
                            document.getElementById('favicon-url').value = data.filename; // Simpan hanya filename
                            document.getElementById('favicon-preview').innerHTML =
                                `<img src="${data.url}" alt="Favicon" class="max-h-8 mx-auto">`;
                            document.getElementById('favicon-preview').classList.remove('hidden');
                            showNotification('Favicon uploaded successfully');
                        } else {
                            showNotification(data.message || 'Upload failed', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Upload error:', err);
                        showNotification('Error uploading favicon: ' + err.message, 'error');
                    });
            }
        }

        // Delete hero image
        function deleteHeroImage() {
            if (!confirm('Hapus gambar hero? Gambar akan dihapus permanen.')) return;

            const heroId = document.getElementById('hero-id')?.value;
            if (!heroId) {
                // Just clear the preview if no hero ID
                document.getElementById('hero-image-url').value = '';
                document.getElementById('hero-image-preview').classList.add('hidden');
                showNotification('Gambar hero dihapus');
                return;
            }

            fetch('{{ route('admin.landing.delete-hero-image') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        hero_id: heroId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('hero-image-url').value = '';
                        document.getElementById('hero-image-preview').classList.add('hidden');
                        showNotification('Gambar hero berhasil dihapus');
                    } else {
                        showNotification(data.message || 'Gagal menghapus gambar', 'error');
                    }
                })
                .catch(err => {
                    console.error('Delete error:', err);
                    showNotification('Error menghapus gambar hero', 'error');
                });
        }

        // Delete logo
        function deleteLogo() {
            if (!confirm('Hapus logo? Logo akan kembali ke default.')) return;

            fetch('{{ route('admin.landing.delete-logo') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('logo-url').value = '';
                        document.getElementById('logo-preview').classList.add('hidden');
                        showNotification('Logo berhasil dihapus');
                    } else {
                        showNotification(data.message || 'Gagal menghapus logo', 'error');
                    }
                })
                .catch(err => {
                    console.error('Delete error:', err);
                    showNotification('Error menghapus logo', 'error');
                });
        }

        // Delete favicon
        function deleteFavicon() {
            if (!confirm('Hapus favicon? Favicon akan kembali ke default.')) return;

            fetch('{{ route('admin.landing.delete-favicon') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('favicon-url').value = '';
                        document.getElementById('favicon-preview').classList.add('hidden');
                        showNotification('Favicon berhasil dihapus');
                    } else {
                        showNotification(data.message || 'Gagal menghapus favicon', 'error');
                    }
                })
                .catch(err => {
                    console.error('Delete error:', err);
                    showNotification('Error menghapus favicon', 'error');
                });
        }

        // Drag and drop for hero image
        const dropzone = document.getElementById('hero-image-dropzone');
        if (dropzone) {
            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('border-primary-500', 'bg-primary-50');
            });
            dropzone.addEventListener('dragleave', () => {
                dropzone.classList.remove('border-primary-500', 'bg-primary-50');
            });
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-primary-500', 'bg-primary-50');
                const files = e.dataTransfer.files;
                if (files.length) {
                    const input = document.getElementById('hero-image-input');
                    input.files = files;
                    handleImageUpload(input);
                }
            });
        }

        // Add Feature
        function addFeature() {
            const id = 'new_' + Date.now();
            const div = document.createElement('div');
            div.className =
                'feature-item flex items-center gap-4 p-4 border border-stone-200 dark:border-stone-700 rounded-lg';
            div.dataset.id = id;
            div.innerHTML = `
            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600">
                <i class="fa-solid fa-star"></i>
            </div>
            <div class="flex-1 grid grid-cols-3 gap-4">
                <input type="text" placeholder="Title" class="feature-title border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">
                <input type="text" placeholder="Description" class="feature-desc border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">
                <input type="text" placeholder="Icon (fontawesome)" class="feature-icon border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white">
            </div>
            <button onclick="this.closest('.feature-item').remove()" class="text-rose-600 hover:text-rose-800">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
            document.getElementById('features-list').appendChild(div);
        }

        // Delete Testimonial
        function deleteTestimonial(id) {
            if (!confirm('Delete this testimonial?')) return;

            if (String(id).startsWith('new_')) {
                document.querySelector(`[data-id="${id}"]`)?.remove();
                showNotification('Testimonial removed');
                return;
            }

            fetch(`{{ route('admin.landing.testimonials.destroy', ':id') }}`.replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Delete failed');
                    }
                    return response.json();
                })
                .then(data => {
                    // Success - remove element and show success notification
                    showNotification('Testimonial deleted successfully');
                    const element = document.querySelector(`[data-id="${id}"]`);
                    if (element) {
                        element.remove();
                    }
                })
                .catch(err => {
                    console.error(err);
                    showNotification('Error deleting testimonial', 'error');
                });
        }

        // Delete Feature
        function deleteFeature(id) {
            if (!confirm('Delete this feature?')) return;

            if (String(id).startsWith('new_')) {
                document.querySelector(`[data-id="${id}"]`)?.remove();
                showNotification('Feature removed');
                return;
            }

            fetch(`{{ route('admin.landing.features.destroy', ':id') }}`.replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Delete failed');
                    }
                    return response.json();
                })
                .then(data => {
                    showNotification('Feature deleted successfully');
                    const element = document.querySelector(`[data-id="${id}"]`);
                    if (element) {
                        element.remove();
                    }
                })
                .catch(err => {
                    console.error(err);
                    showNotification('Error deleting feature', 'error');
                });
        }

        // Delete Stat
        function deleteStat(id) {
            if (!confirm('Delete this stat?')) return;

            if (String(id).startsWith('new_')) {
                document.querySelector(`[data-id="${id}"]`)?.remove();
                showNotification('Stat removed');
                return;
            }

            fetch(`{{ route('admin.landing.stats.destroy', ':id') }}`.replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Delete failed');
                    }
                    return response.json();
                })
                .then(data => {
                    showNotification('Stat deleted successfully');
                    const element = document.querySelector(`[data-id="${id}"]`);
                    if (element) {
                        element.remove();
                    }
                })
                .catch(err => {
                    console.error(err);
                    showNotification('Error deleting stat', 'error');
                });
        }

        // Add Testimonial
        function addTestimonial() {
            const id = 'new_' + Date.now();
            const div = document.createElement('div');
            div.className = 'testimonial-item p-4 border border-stone-200 dark:border-stone-700 rounded-lg';
            div.dataset.id = id;
            div.innerHTML = `
            <div class="flex items-start gap-3 mb-3">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-stone-400 to-stone-600 flex items-center justify-center text-white font-bold">N</div>
                <div class="flex-1">
                    <input type="text" placeholder="Name" class="testimonial-name w-full border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-1 text-sm dark:bg-stone-800 dark:text-white mb-1">
                    <input type="text" placeholder="Role" class="testimonial-role w-full border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-1 text-sm dark:bg-stone-800 dark:text-white">
                </div>
                <button onclick="this.closest('.testimonial-item').remove()" class="text-rose-600 hover:text-rose-800">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
            <textarea placeholder="Testimonial content..." rows="3" class="testimonial-content w-full border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm dark:bg-stone-800 dark:text-white"></textarea>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-sm text-stone-500">Rating:</span>
                <select class="testimonial-rating border border-stone-300 dark:border-stone-700 rounded-lg px-2 py-1 text-sm dark:bg-stone-800 dark:text-white">
                    <option value="5">5 stars</option>
                    <option value="4">4 stars</option>
                    <option value="3">3 stars</option>
                    <option value="2">2 stars</option>
                    <option value="1">1 star</option>
                </select>
            </div>
        `;
            document.getElementById('testimonials-list').appendChild(div);
        }

        // Add Stat
        function addStat() {
            const id = 'new_' + Date.now();
            const div = document.createElement('div');
            div.className =
                'stat-item p-4 border border-stone-200 dark:border-stone-700 rounded-lg text-center';
            div.dataset.id = id;
            div.innerHTML = `
            <input type="text" placeholder="Value" class="stat-value w-full border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-center text-2xl font-bold dark:bg-stone-800 dark:text-white mb-2">
            <input type="text" placeholder="Label" class="stat-label w-full border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-1 text-sm text-center dark:bg-stone-800 dark:text-white">
            <input type="text" placeholder="Icon" class="stat-icon w-full border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-1 text-sm text-center dark:bg-stone-800 dark:text-white mt-2">
            <button onclick="this.closest('.stat-item').remove()" class="text-rose-600 hover:text-rose-800 text-sm mt-2">
                <i class="fa-solid fa-trash-can"></i> Delete
            </button>
        `;
            document.getElementById('stats-list').appendChild(div);
        }

        // Image selection (placeholder)
        function selectImage(targetId) {
            showNotification('Image picker would open here');
        }

        // Notification helper
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className =
                `fixed bottom-4 right-4 ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'} text-white px-4 py-2 rounded-lg shadow-lg z-50`;
            notification.innerHTML =
                `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>${message}`;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
    </script>
@endpush
