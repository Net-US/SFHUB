@extends('layouts.app')

@section('title', 'Edit Postingan | SFHUB Admin')

@push('styles')
    <!-- Quill Editor CSS -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
@endpush

@section('page-title', 'Edit Postingan')

@section('content')
    <div class="animate-fade-in-up space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Edit Postingan</h2>
                <p class="text-stone-500 dark:text-stone-400 text-sm">Perbarui postingan blog yang ada</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.blog.index') }}"
                    class="flex items-center gap-2 px-4 py-2 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300 rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Blog
                </a>
            </div>
        </div>

        <!-- Form -->
        <form id="post-form" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Judul Postingan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="title" name="title" value="{{ $post->title }}" required
                            class="w-full px-4 py-3 border border-stone-300 dark:border-stone-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-stone-800 dark:text-white transition-colors"
                            placeholder="Masukkan judul postingan...">
                    </div>

                    <!-- URL Slug -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            URL Slug <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex">
                            <span
                                class="inline-flex items-center px-3 text-sm text-stone-500 dark:text-stone-400 bg-stone-50 dark:bg-stone-800 border border-r-0 border-stone-300 dark:border-stone-700 rounded-l-lg">
                                {{ url('/blog/') }}
                            </span>
                            <input type="text" id="slug" name="slug" value="{{ $post->slug }}" required
                                class="flex-1 px-4 py-3 border border-stone-300 dark:border-stone-700 rounded-r-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-stone-800 dark:text-white transition-colors"
                                placeholder="url-friendly-title">
                        </div>
                    </div>

                    <!-- Content Editor -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Konten <span class="text-rose-500">*</span>
                        </label>
                        <div id="editor" style="min-height: 300px;"></div>
                        <!-- Hidden input to store content -->
                        <input type="hidden" id="content" name="content">
                    </div>

                    <!-- Excerpt -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Ringkasan <span class="text-xs text-stone-500">(Opsional - deskripsi singkat)</span>
                        </label>
                        <textarea id="excerpt" name="excerpt" rows="3" placeholder="Ringkasan postingan (opsional)">{{ $post->excerpt }}</textarea>
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
                            @if ($post->featured_image)
                                <img src="{{ asset($post->featured_image) }}" alt="Gambar Utama"
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
                            value="{{ $post->featured_image }}">
                    </div>

                    <!-- Category -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Kategori <span class="text-rose-500">*</span>
                        </label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ $post->categories->contains($category->id) ? 'selected' : '' }}>{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tags -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
                            Tag <span class="text-xs text-stone-500">(Dipisahkan koma)</span>
                        </label>
                        <input type="text" id="tags" name="tags"
                            value="{{ $post->tags->pluck('name')->implode(', ') }}" placeholder="web, design, programming">
                    </div>

                    <!-- SEO Settings -->
                    <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
                        <h3 class="text-sm font-medium text-stone-700 dark:text-stone-300 mb-4">Pengaturan SEO</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-1">
                                    Meta Title
                                </label>
                                <input type="text" id="meta_title" name="meta_title" value="{{ $post->meta_title }}"
                                    placeholder="Judul SEO (maksimal 60 karakter)">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-1">
                                    Meta Description
                                </label>
                                <textarea id="meta_description" name="meta_description" rows="3"
                                    placeholder="Deskripsi SEO (maksimal 160 karakter)">{{ $post->meta_description }}</textarea>
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
                                <select id="status" name="status" required>
                                    <option value="draft" {{ $post->status === 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                    <option value="published" {{ $post->status === 'published' ? 'selected' : '' }}>
                                        Dipublikasikan</option>
                                    <option value="featured" {{ $post->status === 'featured' ? 'selected' : '' }}>Unggulan
                                    </option>
                                </select>
                                <p class="text-xs text-stone-500 mt-1">Draft: Disimpan tapi tidak
                                    dipublikasikan<br>Dipublikasikan: Muncul di blog<br>Unggulan: Ditampilkan di halaman
                                    utama</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-stone-600 dark:text-stone-400 mb-1">
                                    Waktu Publikasi
                                </label>
                                <input type="datetime-local" id="published-at" name="published_at"
                                    class="w-full px-3 py-2 border border-stone-300 dark:border-stone-700 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-stone-800 dark:text-white text-sm"
                                    value="{{ $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '' }}">
                                <p class="text-xs text-stone-500 mt-1">Kosongkan untuk menggunakan waktu saat ini</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition-colors">
                            <i class="fa-solid fa-save mr-2"></i> Update Postingan
                        </button>
                        <a href="{{ route('admin.blog.index') }}"
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
            placeholder: 'Tulis konten postingan di sini...',
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
        quill.root.innerHTML = `{!! $post->content !!}`;

        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slug = title.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.getElementById('slug').value = slug;
        });

        // Handle form submission
        document.getElementById('post-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Get content from Quill editor
            const content = quill.root.innerHTML;
            document.getElementById('content').value = content;

            // Client-side validation
            const title = document.getElementById('title').value.trim();
            const slug = document.getElementById('slug').value.trim();
            const status = document.getElementById('status').value;

            if (!title) {
                showNotification('Judul postingan wajib diisi.', 'error');
                return;
            }

            if (!slug) {
                showNotification('URL slug wajib diisi.', 'error');
                return;
            }

            if (!content || content.trim() === '' || content.trim() === '<p><br></p>') {
                showNotification('Konten postingan wajib diisi.', 'error');
                return;
            }

            if (!status) {
                showNotification('Status postingan wajib dipilih.', 'error');
                return;
            }

            const formData = new FormData(e.target);
            const postId = {{ $post->id }};

            // Always use update method
            const url = `{{ url('admin/blog') }}/${postId}`;
            formData.append('_method', 'PUT');

            // Debug: Log form data
            console.log('Submitting update:', {
                url: url,
                formData: Object.fromEntries(formData.entries())
            });

            fetch(url, {
                    method: 'POST', // Always use POST method, _method field handles PUT
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Response:', data);

                    if (data.success) {
                        showNotification(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = '{{ route('admin.blog.index') }}';
                        }, 1500);
                    } else {
                        console.error('Validation errors:', data.errors);

                        let errorMessage = data.message;
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
                    showNotification('Terjadi kesalahan saat memperbarui postingan. Silakan coba lagi.',
                        'error');
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
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 ${
                type === 'success'
                    ? 'bg-emerald-500 text-white'
                    : 'bg-rose-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transform = 'translateX(400px)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
@endpush
