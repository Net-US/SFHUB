@extends('layouts.app')

@section('title', 'Backup Management | SFHUB Admin')

@section('page-title', 'Backup Management')

@section('content')
    <div class="animate-fade-in-up space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Backup Management</h2>
                <p class="text-stone-500 dark:text-stone-400 text-sm">Kelola backup data aplikasi</p>
            </div>
            <div class="flex gap-2">
                <button onclick="showBackupConfigModal()"
                    class="flex items-center gap-2 px-4 py-2 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300 rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-gear"></i> Config
                </button>
                <button onclick="createBackup(this)"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-cloud-arrow-down"></i> Create Backup
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Total Backups</p>
                <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $totalBackups ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Total Size</p>
                <p class="text-2xl font-bold text-blue-600">{{ number_format(($totalSize ?? 0) / 1024 / 1024, 2) }} MB</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Last Backup</p>
                <p class="text-2xl font-bold text-emerald-600">
                    {{ $lastBackup ? $lastBackup->created_at->diffForHumans() : 'Never' }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Auto Backup</p>
                <p class="text-2xl font-bold {{ $autoBackup ?? false ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $autoBackup ?? false ? 'ON' : 'OFF' }}
                </p>
            </div>
        </div>

        <!-- Backup Info -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Storage Info -->
            <div
                class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white mb-4">Storage Usage</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-stone-600 dark:text-stone-400">Database Backups</span>
                        <span class="font-medium">{{ $dbBackupsCount ?? 0 }} files
                            ({{ number_format(($dbBackupsSize ?? 0) / 1024 / 1024, 2) }} MB)</span>
                    </div>
                    <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2">
                        <div class="bg-primary-500 h-2 rounded-full" style="width: {{ $dbUsagePercent ?? 0 }}%"></div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-stone-600 dark:text-stone-400">File Backups</span>
                        <span class="font-medium">{{ $fileBackupsCount ?? 0 }} files
                            ({{ number_format(($fileBackupsSize ?? 0) / 1024 / 1024, 2) }} MB)</span>
                    </div>
                    <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $fileUsagePercent ?? 0 }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
                <h3 class="font-bold text-stone-900 dark:text-white mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <button onclick="backupDatabase()"
                        class="w-full flex items-center gap-3 p-3 border border-stone-300 dark:border-stone-700 rounded-xl hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">
                        <div
                            class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600">
                            <i class="fa-solid fa-database"></i>
                        </div>
                        <span class="font-medium">Backup Database Only</span>
                    </button>
                    <button onclick="backupFiles()"
                        class="w-full flex items-center gap-3 p-3 border border-stone-300 dark:border-stone-700 rounded-xl hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">
                        <div
                            class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                            <i class="fa-solid fa-folder"></i>
                        </div>
                        <span class="font-medium">Backup Files Only</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Backups List -->
        <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden">
            <div class="p-4 border-b border-stone-200 dark:border-stone-700 flex justify-between items-center">
                <h3 class="font-bold text-stone-900 dark:text-white">Backup History</h3>
                <div class="flex gap-2">
                    <select id="type-filter" onchange="filterByType(this.value)"
                        class="px-3 py-2 border border-stone-300 dark:border-stone-700 rounded-lg text-sm dark:bg-stone-800 dark:text-white">
                        <option value="">All Types</option>
                        <option value="full" {{ request('type') == 'full' ? 'selected' : '' }}>Full</option>
                        <option value="database" {{ request('type') == 'database' ? 'selected' : '' }}>Database</option>
                        <option value="files" {{ request('type') == 'files' ? 'selected' : '' }}>Files</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-stone-50 dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Name</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Type</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Size</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Status</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Created</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups ?? [] as $backup)
                            <tr
                                class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-stone-100 dark:bg-stone-800 flex items-center justify-center">
                                            <i
                                                class="fa-solid fa-{{ $backup->type === 'database' ? 'database' : ($backup->type === 'files' ? 'folder' : 'archive') }} text-stone-400"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-stone-800 dark:text-white">{{ $backup->file_name }}
                                            </p>
                                            <p class="text-xs text-stone-500">{{ $backup->file_path }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-medium
                                @if ($backup->type === 'full') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400
                                @elseif($backup->type === 'database') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                @else bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 @endif">
                                        {{ $backup->type }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">{{ number_format(($backup->file_size ?? 0) / 1024 / 1024, 2) }} MB
                                </td>
                                <td class="py-4 px-6">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-medium
                                @if ($backup->status === 'completed') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400
                                @elseif($backup->status === 'failed') bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400
                                @else bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 @endif">
                                        {{ $backup->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-stone-600 dark:text-stone-400">
                                    {{ $backup->created_at->format('Y-m-d H:i') }}</td>
                                <td class="py-4 px-6">
                                    <div class="flex gap-2">
                                        <button onclick="downloadBackup({{ $backup->id }})"
                                            class="text-primary-600 hover:text-primary-800 dark:text-primary-400"
                                            title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </button>
                                        <button onclick="deleteBackup({{ $backup->id }})"
                                            class="text-rose-600 hover:text-rose-800 dark:text-rose-400" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-stone-500 dark:text-stone-400">
                                    <div
                                        class="w-16 h-16 bg-stone-100 dark:bg-stone-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fa-solid fa-box-archive text-2xl text-stone-400"></i>
                                    </div>
                                    <p>No backups found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-stone-200 dark:border-stone-700">
                {{ $backups->links() ?? '' }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Filter by type
        function filterByType(type) {
            const url = new URL(window.location);
            if (type) url.searchParams.set('type', type);
            else url.searchParams.delete('type');
            window.location = url;
        }

        // Create full backup
        function createBackup(buttonEl) {
            if (!confirm('Create a new full backup? This may take a few minutes.')) return;

            const btn = buttonEl;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Creating...';

            fetch('{{ route('admin.settings.backups.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        type: 'full'
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message);
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showNotification(data.message, 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-down mr-2"></i> Create Backup';
                    }
                });
        }

        // Backup database only
        function backupDatabase() {
            if (!confirm('Create database backup?')) return;

            fetch('{{ route('admin.settings.backups.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        type: 'database'
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message);
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showNotification(data.message, 'error');
                    }
                });
        }

        // Backup files only
        function backupFiles() {
            if (!confirm('Create files backup?')) return;

            fetch('{{ route('admin.settings.backups.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        type: 'files'
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message);
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showNotification(data.message, 'error');
                    }
                });
        }

        // Download backup
        function downloadBackup(id) {
            window.open(`{{ url('admin/settings/backups') }}/${id}/download`, '_blank');
        }

        // Delete backup
        function deleteBackup(id) {
            if (!confirm('Delete this backup? This action cannot be undone.')) return;

            fetch(`{{ url('admin/backups') }}/${id}`, {
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

        // Show Backup Config Modal
        function showBackupConfigModal() {
            const content = `
            <form id="backup-config-form" class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
                    <div>
                        <p class="font-medium text-stone-800 dark:text-white">Auto Backup</p>
                        <p class="text-sm text-stone-500">Automatically create backups</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="auto_backup" {{ $autoBackup ?? false ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-stone-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-stone-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Backup Frequency</label>
                    <select name="backup_frequency" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        <option value="daily" {{ ($backupFrequency ?? 'daily') === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ ($backupFrequency ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ ($backupFrequency ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Keep Backups (days)</label>
                    <input type="number" name="keep_days" value="{{ $keepDays ?? 30 }}" min="1" max="365" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Storage Limit (GB)</label>
                    <input type="number" name="storage_limit" value="{{ $storageLimit ?? 10 }}" min="1" max="100" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
            </form>
        `;

            showModal('Backup Configuration', content, () => {
                const form = document.getElementById('backup-config-form');
                const formData = new FormData(form);

                fetch('{{ route('admin.backups.config') }}', {
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
            modal.querySelector('#modal-confirm').addEventListener('click', () => {
                onConfirm();
                modal.remove();
            });
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });
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
