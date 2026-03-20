@extends('layouts.app')

@section('title', 'Edit Halaman | SFHUB Admin')

@push('styles')
    <!-- Quill Editor CSS -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
@endpush

@section('page-title', 'Edit Halaman')

@section('content')
    <div class="animate-fade-in-up space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Edit Halaman</h2>
                <p class="text-stone-500 dark:text-stone-400 text-sm">Perbarui halaman statis yang ada</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.seo.index') }}"
                    class="flex items-center gap-2 px-4 py-2 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300 rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke SEO
                </a>
            </div>
        </div>

        <!-- Form -->
        <form id="page-form" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Judul Halaman <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="title" name="title" value="{{ $page->title }}" required
                            class="w-full px-4 py-3 border border-stone-300 dark:border-stone-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-stone-800 dark:text-white transition-colors"
                            placeholder="Contoh: Tentang Kami, Hubungi Kami, Kebijakan Privasi">
                    </div>

                    <!-- URL Slug -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            URL Slug <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex">
                            <span
                                class="inline-flex items-center px-3 text-sm text-stone-500 dark:text-stone-400 bg-stone-50 dark:bg-stone-800 border border-r-0 border-stone-300 dark:border-stone-700 rounded-l-lg">
                                {{ url('/page/') }}
                            </span>
                            <input type="text" id="slug" name="slug" value="{{ $page->slug }}" required
                                class="flex-1 px-4 py-3 border border-stone-300 dark:border-stone-700 rounded-r-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-stone-800 dark:text-white transition-colors"
                                placeholder="tentang-kami">
                        </div>
                    </div>

                    <!-- Content Editor -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Konten Halaman <span class="text-rose-500">*</span>
                        </label>
                        <div id="editor" style="min-height: 300px;" class="bg-white dark:bg-stone-800 rounded-lg"></div>
                        <!-- Hidden input to store content -->
                        <input type="hidden" id="content" name="content">
                    </div>

                    <!-- Excerpt -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Ringkasan <span class="text-xs text-stone-500">(Opsional - deskripsi singkat)</span>
                        </label>
                        <textarea id="excerpt" name="excerpt" rows="3" placeholder="Ringkasan halaman (opsional)">{{ $page->excerpt }}</textarea>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Featured Image -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Gambar Utama
                        </label>
                        <div id="image-preview" class="mb-3">
                            @if ($page->featured_image)
                                <img src="{{ asset($page->featured_image) }}" alt="Gambar Utama"
                                    class="max-h-32 mx-auto rounded-lg">
                            @endif
                        </div>
                        <div
                            class="border-2 border-dashed border-stone-300 dark:border-stone-700 rounded-xl p-4 text-center">
                            <input type="file" id="featured-image" accept="image/*" class="hidden"
                                onchange="handleImageUpload(this)">
                            <button type="button" onclick="document.getElementById('featured-image').click()"
                                class="px-3 py-2 bg-stone-100 dark:bg-stone-800 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700 text-sm">
                                <i class="fa-solid fa-upload mr-2"></i> Ganti Gambar
                            </button>
                            <p class="text-xs text-stone-400 mt-2">Atau drag and drop</p>
                        </div>
                        <input type="hidden" id="featured-image-url" name="featured_image"
                            value="{{ $page->featured_image }}">
                    </div>

                    <!-- Page Type -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Tipe Halaman
                        </label>
                        <select id="page_type" name="page_type"
                            class="w-full px-3 py-2 border border-stone-300 dark:border-stone-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-stone-800 dark:text-white">
                            <option value="">Pilih Tipe Halaman</option>
                            <option value="about" {{ $page->page_type === 'about' ? 'selected' : '' }}>Tentang Kami
                            </option>
                            <option value="contact" {{ $page->page_type === 'contact' ? 'selected' : '' }}>Hubungi Kami
                            </option>
                            <option value="privacy" {{ $page->page_type === 'privacy' ? 'selected' : '' }}>Kebijakan Privasi
                            </option>
                            <option value="terms" {{ $page->page_type === 'terms' ? 'selected' : '' }}>Syarat & Ketentuan
                            </option>
                            <option value="faq" {{ $page->page_type === 'faq' ? 'selected' : '' }}>FAQ</option>
                            <option value="custom" {{ $page->page_type === 'custom' ? 'selected' : '' }}>Kustom</option>
                        </select>
                    </div>

                    <!-- SEO Settings -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <h3 class="text-sm font-medium text-stone-700 dark:text-stone-300 mb-4">Pengaturan SEO</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-1">
                                    Meta Title
                                </label>
                                <input type="text" id="meta_title" name="meta_title" value="{{ $page->meta_title }}"
                                    class="w-full px-3 py-2 border border-stone-300 dark:border-stone-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-stone-800 dark:text-white text-sm"
                                    placeholder="Judul SEO (maksimal 60 karakter)">
                                <p class="text-xs text-stone-500 mt-1">
                                    <span id="meta-title-count">{{ strlen($page->meta_title) }}</span>/60 karakter
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-1">
                                    Meta Description
                                </label>
                                <textarea id="meta_description" name="meta_description" rows="3"
                                    class="w-full px-3 py-2 border border-stone-300 dark:border-stone-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-stone-800 dark:text-white text-sm"
                                    placeholder="Deskripsi SEO (maksimal 160 karakter)">{{ $page->meta_description }}</textarea>
                                <p class="text-xs text-stone-500 mt-1">
                                    <span id="meta-description-count">{{ strlen($page->meta_description) }}</span>/160
                                    karakter
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-1">
                                    Meta Keywords
                                </label>
                                <input type="text" id="meta_keywords" name="meta_keywords"
                                    value="{{ $page->meta_keywords }}"
                                    class="w-full px-3 py-2 border border-stone-300 dark:border-stone-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-stone-800 dark:text-white text-sm"
                                    placeholder="keyword1, keyword2, keyword3">
                            </div>
                        </div>
                    </div>

                    <!-- Publishing Options -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <h3 class="text-sm font-medium text-stone-700 dark:text-stone-300 mb-4">Opsi Publikasi</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-1">
                                    Status <span class="text-rose-500">*</span>
                                </label>
                                <select id="status" name="status" required
                                    class="w-full px-3 py-2 border border-stone-300 dark:border-stone-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-stone-800 dark:text-white text-sm">
                                    <option value="draft" {{ $page->status === 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                    <option value="published" {{ $page->status === 'published' ? 'selected' : '' }}>
                                        Dipublikasikan</option>
                                </select>
                                <p class="text-xs text-stone-500 mt-1">Draft: Disimpan tapi tidak
                                    dipublikasikan<br>Dipublikasikan: Muncul di website</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-1">
                                    Waktu Publikasi
                                </label>
                                <input type="datetime-local" id="published_at" name="published_at"
                                    class="w-full px-3 py-2 border border-stone-300 dark:border-stone-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-stone-800 dark:text-white text-sm"
                                    value="{{ $page->published_at ? $page->published_at->format('Y-m-d\TH:i') : '' }}">
                                <p class="text-xs text-stone-500 mt-1">Kosongkan untuk menggunakan waktu saat ini</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition-colors">
                            <i class="fa-solid fa-save mr-2"></i> Update Halaman
                        </button>
                        <a href="{{ route('admin.seo.index') }}"
                            class="px-4 py-3 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300 rounded-xl font-medium transition-colors">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <!-- Include the Quill library -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <script>
        // Initialize Quill editor
        const quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Tulis konten halaman di sini...',
            modules: {
                toolbar: [
                    [{
                        'header': [1, 2, 3, false]
                    }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    [{
                        'script': 'sub'
                    }, {
                        'script': 'super'
                    }],
                    [{
                        'indent': '-1'
                    }, {
                        'indent': '+1'
                    }],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            }
        });

        // Set content if editing
        quill.root.innerHTML = `{!! $page->content !!}`;

        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slug = title.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.getElementById('slug').value = slug;
        });

        // Character counters
        document.getElementById('meta_title').addEventListener('input', function() {
            document.getElementById('meta-title-count').textContent = this.value.length;
        });

        document.getElementById('meta_description').addEventListener('input', function() {
            document.getElementById('meta-description-count').textContent = this.value.length;
        });

        // Handle form submission
        document.getElementById('page-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Get content from Quill editor
            const content = quill.root.innerHTML;
            document.getElementById('content').value = content;

            // Client-side validation
            const title = document.getElementById('title').value.trim();
            const slug = document.getElementById('slug').value.trim();
            const status = document.getElementById('status').value;

            if (!title) {
                showNotification('Judul halaman wajib diisi.', 'error');
                return;
            }

            if (!slug) {
                showNotification('URL slug wajib diisi.', 'error');
                return;
            }

            if (!content || content.trim() === '' || content.trim() === '<p><br></p>') {
                showNotification('Konten halaman wajib diisi.', 'error');
                return;
            }

            if (!status) {
                showNotification('Status halaman wajib dipilih.', 'error');
                return;
            }

            const formData = new FormData(e.target);
            const pageId = {{ $page->id }};

            formData.append('_method', 'PUT');

            fetch(`{{ url('admin/seo/pages') }}/${pageId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw data;
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = '{{ route('admin.seo.index') }}';
                        }, 1500);
                    } else {
                        let errorMessage = data.message || 'Terjadi kesalahan.';
                        if (data.errors) {
                            const errorMessages = Object.values(data.errors).flat();
                            if (errorMessages.length > 0) {
                                errorMessage = errorMessages.join('\n');
                            }
                        }
                        showNotification(errorMessage, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    let errorMessage = 'Terjadi kesalahan saat memperbarui halaman. Silakan coba lagi.';

                    if (error.errors) {
                        const errorMessages = Object.values(error.errors).flat();
                        if (errorMessages.length > 0) {
                            errorMessage = errorMessages.join('\n');
                        }
                    } else if (error.message) {
                        errorMessage = error.message;
                    }

                    showNotification(errorMessage, 'error');
                });
        });

        // Handle image upload
        function handleImageUpload(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    showNotification('Format gambar tidak didukung. Gunakan JPEG, PNG, GIF, atau WebP.', 'error');
                    input.value = '';
                    return;
                }

                // Validate file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    showNotification('Ukuran gambar terlalu besar. Maksimal 10MB.', 'error');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('image-preview').innerHTML =
                        `<img src="${e.target.result}" alt="Gambar Utama" class="max-h-32 mx-auto rounded-lg">`;
                    document.getElementById('featured-image-url').value = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        // Notification function
        function showNotification(message, type = 'success') {
            document.querySelectorAll('.notification').forEach(el => el.remove());

            const notification = document.createElement('div');
            notification.className =
                `notification fixed bottom-4 right-4 ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'} text-white px-4 py-3 rounded-lg shadow-lg z-50 max-w-md`;

            if (message.includes('\n')) {
                const lines = message.split('\n');
                notification.innerHTML = `
                    <div class="flex items-start">
                        <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2 mt-0.5"></i>
                        <div class="text-left">
                            ${lines.map(line => `<div class="${line.startsWith('•') ? 'ml-2' : ''}">${line}</div>`).join('')}
                        </div>
                    </div>
                `;
            } else {
                notification.innerHTML = `
                    <div class="flex items-center">
                        <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                        <span>${message}</span>
                    </div>
                `;
            }

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, type === 'error' ? 5000 : 3000);
        }
    </script>
@endpush
