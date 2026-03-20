@extends('layouts.app')

@section('title', 'Media Manager | SFHUB Admin')

@section('page-title', 'Media Manager')

@section('content')
<div class="animate-fade-in-up space-y-6">
    <!-- Header & Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-3">
            <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Media Manager</h2>
            <p class="text-stone-500 dark:text-stone-400 text-sm">Kelola file dan media upload</p>
        </div>
        <div class="flex justify-end">
            <button onclick="showUploadModal()" class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload Files
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <p class="text-sm text-stone-500 dark:text-stone-400">Total Files</p>
            <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <p class="text-sm text-stone-500 dark:text-stone-400">Images</p>
            <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $stats['images'] }}</p>
        </div>
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <p class="text-sm text-stone-500 dark:text-stone-400">Documents</p>
            <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $stats['documents'] }}</p>
        </div>
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <p class="text-sm text-stone-500 dark:text-stone-400">Total Size</p>
            <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $stats['total_size'] > 0 ? number_format($stats['total_size'] / 1024 / 1024, 2) : 0 }} MB</p>
        </div>
    </div>

    <!-- Filters & Folders -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200 dark:border-stone-800">
        <div class="flex flex-col md:flex-row gap-4 justify-between">
            <div class="flex gap-2 overflow-x-auto">
                <button onclick="filterByType('all')" class="type-filter px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'all' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800' }}" data-type="all">
                    All Files
                </button>
                <button onclick="filterByType('image')" class="type-filter px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'image' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800' }}" data-type="image">
                    <i class="fa-solid fa-image mr-1"></i> Images
                </button>
                <button onclick="filterByType('video')" class="type-filter px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'video' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800' }}" data-type="video">
                    <i class="fa-solid fa-video mr-1"></i> Videos
                </button>
                <button onclick="filterByType('document')" class="type-filter px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'document' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800' }}" data-type="document">
                    <i class="fa-solid fa-file-lines mr-1"></i> Documents
                </button>
            </div>
            <div class="flex gap-2">
                <select id="folder-select" onchange="filterByFolder(this.value)" class="px-4 py-2 border border-stone-300 dark:border-stone-700 rounded-lg text-sm bg-white dark:bg-stone-800 dark:text-white">
                    <option value="/">All Folders</option>
                    @foreach($folders as $f)
                    <option value="{{ $f }}" {{ $folder === $f ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
                <button onclick="showCreateFolderModal()" class="px-4 py-2 border border-stone-300 dark:border-stone-700 rounded-lg text-sm hover:bg-stone-50 dark:hover:bg-stone-800">
                    <i class="fa-solid fa-folder-plus"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Media Grid -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 p-6">
        @if($media->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($media as $item)
            <div class="media-item group relative bg-stone-50 dark:bg-stone-800 rounded-xl overflow-hidden border border-stone-200 dark:border-stone-700 hover:shadow-md transition-all cursor-pointer" data-media-id="{{ $item->id }}" onclick="viewMedia({{ $item->id }})">
                <div class="aspect-square flex items-center justify-center bg-stone-100 dark:bg-stone-800">
                    @if($item->type === 'image')
                        <img src="{{ $item->getUrl() }}" alt="{{ $item->alt_text ?? $item->filename }}" class="w-full h-full object-cover">
                    @elseif($item->type === 'video')
                        <div class="text-center p-4">
                            <i class="fa-solid fa-film text-4xl text-stone-400 mb-2"></i>
                            <p class="text-xs text-stone-500 truncate">{{ $item->filename }}</p>
                        </div>
                    @elseif($item->type === 'audio')
                        <div class="text-center p-4">
                            <i class="fa-solid fa-music text-4xl text-stone-400 mb-2"></i>
                            <p class="text-xs text-stone-500 truncate">{{ $item->filename }}</p>
                        </div>
                    @else
                        <div class="text-center p-4">
                            <i class="fa-solid fa-file text-4xl text-stone-400 mb-2"></i>
                            <p class="text-xs text-stone-500 truncate">{{ $item->filename }}</p>
                        </div>
                    @endif
                </div>
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                    <button onclick="event.stopPropagation(); editMedia({{ $item->id }})" class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-stone-700 hover:text-primary-600">
                        <i class="fa-solid fa-pen text-xs"></i>
                    </button>
                    <button onclick="event.stopPropagation(); copyUrl('{{ $item->getUrl() }}')" class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-stone-700 hover:text-primary-600">
                        <i class="fa-solid fa-link text-xs"></i>
                    </button>
                    <button onclick="event.stopPropagation(); deleteMedia({{ $item->id }})" class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-stone-700 hover:text-rose-600">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </div>
                <div class="p-2 bg-white dark:bg-stone-900">
                    <p class="text-xs text-stone-600 dark:text-stone-400 truncate">{{ $item->original_name }}</p>
                    <p class="text-xs text-stone-400">{{ $item->getSizeForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $media->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-stone-100 dark:bg-stone-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-image text-2xl text-stone-400"></i>
            </div>
            <h3 class="text-lg font-medium text-stone-900 dark:text-white mb-2">No media files</h3>
            <p class="text-stone-500 dark:text-stone-400 mb-4">Upload your first file to get started</p>
            <button onclick="showUploadModal()" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                Upload Files
            </button>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Filter by type
    function filterByType(type) {
        const url = new URL(window.location);
        url.searchParams.set('type', type);
        window.location = url;
    }

    // Filter by folder
    function filterByFolder(folder) {
        const url = new URL(window.location);
        url.searchParams.set('folder', folder);
        window.location = url;
    }

    // Copy URL to clipboard
    function copyUrl(url) {
        navigator.clipboard.writeText(url).then(() => {
            showNotification('URL copied to clipboard');
        });
    }

    // View Media
    function viewMedia(id) {
        fetch(`{{ url('admin/media/list') }}?per_page=100`)
            .then(r => r.json())
            .then(data => {
                const media = data.data.find(m => m.id === id);
                if (!media) return;

                const content = `
                    <div class="space-y-4">
                        <div class="aspect-video bg-stone-100 dark:bg-stone-800 rounded-xl flex items-center justify-center overflow-hidden">
                            ${media.type === 'image' 
                                ? `<img src="${media.url}" class="max-w-full max-h-full object-contain">`
                                : `<i class="fa-solid fa-file text-6xl text-stone-400"></i>`
                            }
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-stone-500">Filename</p>
                                <p class="font-medium">${media.filename}</p>
                            </div>
                            <div>
                                <p class="text-stone-500">Original Name</p>
                                <p class="font-medium">${media.original_name}</p>
                            </div>
                            <div>
                                <p class="text-stone-500">Type</p>
                                <p class="font-medium capitalize">${media.type}</p>
                            </div>
                            <div>
                                <p class="text-stone-500">Size</p>
                                <p class="font-medium">${media.size}</p>
                            </div>
                            <div>
                                <p class="text-stone-500">Extension</p>
                                <p class="font-medium">${media.extension}</p>
                            </div>
                            <div>
                                <p class="text-stone-500">Folder</p>
                                <p class="font-medium">${media.folder}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-stone-500 mb-1">URL</p>
                            <div class="flex gap-2">
                                <input type="text" value="${media.url}" readonly class="flex-1 px-3 py-2 border border-stone-300 dark:border-stone-700 rounded-lg text-sm bg-stone-50 dark:bg-stone-800">
                                <button onclick="copyUrl('${media.url}')" class="px-3 py-2 bg-primary-600 text-white rounded-lg text-sm">
                                    <i class="fa-solid fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                showModal('Media Details', content, null, false);
            });
    }

    // Edit Media
    function editMedia(id) {
        fetch(`{{ url('admin/media/list') }}?per_page=100`)
            .then(r => r.json())
            .then(data => {
                const media = data.data.find(m => m.id === id);
                if (!media) return;

                const content = `
                    <form id="edit-media-form" class="space-y-4">
                        <input type="hidden" name="_method" value="PUT">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Alt Text</label>
                            <input type="text" name="alt_text" value="${media.alt_text || ''}" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Description</label>
                            <textarea name="description" rows="3" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">${media.description || ''}</textarea>
                        </div>
                        <div>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="is_public" value="1" ${media.is_public ? 'checked' : ''} class="rounded border-stone-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-stone-700 dark:text-stone-300">Public Access</span>
                            </label>
                        </div>
                    </form>
                `;
                showModal('Edit Media', content, () => {
                    const form = document.getElementById('edit-media-form');
                    const formData = new FormData(form);
                    
                    fetch(`{{ url('admin/media') }}/${id}`, {
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

    // Delete Media
    function deleteMedia(id) {
        if (!confirm('Are you sure you want to delete this file?')) return;
        
        fetch(`{{ url('admin/media') }}/${id}`, {
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
                document.querySelector(`[data-media-id="${id}"]`)?.remove();
            } else {
                showNotification(data.message, 'error');
            }
        });
    }

    // Upload Modal
    function showUploadModal() {
        const content = `
            <form id="upload-form" class="space-y-4">
                <div class="border-2 border-dashed border-stone-300 dark:border-stone-700 rounded-xl p-8 text-center" id="drop-zone">
                    <i class="fa-solid fa-cloud-arrow-up text-4xl text-stone-400 mb-4"></i>
                    <p class="text-stone-600 dark:text-stone-400 mb-2">Drag and drop files here</p>
                    <p class="text-sm text-stone-500 mb-4">or</p>
                    <label class="px-4 py-2 bg-primary-600 text-white rounded-lg cursor-pointer hover:bg-primary-700">
                        Select Files
                        <input type="file" name="files[]" multiple class="hidden" id="file-input">
                    </label>
                    <p class="text-xs text-stone-500 mt-4">Max file size: 50MB</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Folder</label>
                    <select name="folder" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        <option value="/">Root</option>
                        @foreach($folders as $f)
                        <option value="{{ $f }}">{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="file-list" class="space-y-2 hidden">
                    <p class="text-sm font-medium text-stone-700 dark:text-stone-300">Selected files:</p>
                    <div id="file-list-content" class="max-h-32 overflow-y-auto"></div>
                </div>
            </form>
        `;
        
        showModal('Upload Files', content, () => {
            const form = document.getElementById('upload-form');
            const formData = new FormData();
            const files = document.getElementById('file-input').files;
            
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            formData.append('folder', form.querySelector('[name="folder"]').value);
            
            fetch('{{ route("admin.media.upload") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
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

        // File input change handler
        setTimeout(() => {
            const fileInput = document.getElementById('file-input');
            fileInput?.addEventListener('change', (e) => {
                const fileList = document.getElementById('file-list');
                const fileListContent = document.getElementById('file-list-content');
                fileListContent.innerHTML = '';
                
                Array.from(e.target.files).forEach(file => {
                    const div = document.createElement('div');
                    div.className = 'flex justify-between items-center text-sm p-2 bg-stone-50 dark:bg-stone-800 rounded';
                    div.innerHTML = `
                        <span class="truncate">${file.name}</span>
                        <span class="text-stone-500">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                    `;
                    fileListContent.appendChild(div);
                });
                
                fileList.classList.remove('hidden');
            });
        }, 100);
    }

    // Create Folder Modal
    function showCreateFolderModal() {
        const content = `
            <form id="create-folder-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Folder Name</label>
                    <input type="text" name="folder_name" required pattern="[a-zA-Z0-9_-]+" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                    <p class="text-xs text-stone-500 mt-1">Only letters, numbers, dashes and underscores</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Parent Folder</label>
                    <select name="parent_folder" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        <option value="">Root</option>
                        @foreach($folders as $f)
                        <option value="{{ $f }}">{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        `;
        
        showModal('Create Folder', content, () => {
            const form = document.getElementById('create-folder-form');
            const formData = new FormData(form);
            
            fetch('{{ route("admin.media.folders.store") }}', {
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

    // Show Modal Helper
    function showModal(title, content, onConfirm, showConfirm = true) {
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
                ${showConfirm ? `
                <div class="flex justify-end gap-2 p-6 border-t border-stone-200 dark:border-stone-800">
                    <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 border border-stone-300 dark:border-stone-700 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800">
                        Cancel
                    </button>
                    <button id="modal-confirm" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                        ${onConfirm ? 'Save' : 'Close'}
                    </button>
                </div>
                ` : `
                <div class="flex justify-end p-6 border-t border-stone-200 dark:border-stone-800">
                    <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                        Close
                    </button>
                </div>
                `}
            </div>
        `;
        document.body.appendChild(modal);
        
        if (onConfirm) {
            modal.querySelector('#modal-confirm').addEventListener('click', () => {
                onConfirm();
                modal.remove();
            });
        }
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
    }
</script>
@endpush
