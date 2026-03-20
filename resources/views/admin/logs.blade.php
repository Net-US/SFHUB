@extends('layouts.app')

@section('title', 'System Logs | SFHUB Admin')

@section('page-title', 'System Logs')

@section('content')
<div class="animate-fade-in-up space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-stone-900 dark:text-white">System Logs</h2>
            <p class="text-stone-500 dark:text-stone-400 text-sm">Monitor aktivitas dan log sistem</p>
        </div>
        <div class="flex gap-2">
            <button onclick="exportLogs()" class="flex items-center gap-2 px-4 py-2 border border-stone-300 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300 rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-download"></i> Export
            </button>
            <button onclick="clearLogs()" class="flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-trash-can"></i> Clear Logs
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <p class="text-sm text-stone-500 dark:text-stone-400">Total Logs</p>
            <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $totalLogs ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <p class="text-sm text-stone-500 dark:text-stone-400">Errors</p>
            <p class="text-2xl font-bold text-rose-600">{{ $errorLogs ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <p class="text-sm text-stone-500 dark:text-stone-400">Warnings</p>
            <p class="text-2xl font-bold text-amber-600">{{ $warningLogs ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <p class="text-sm text-stone-500 dark:text-stone-400">Today</p>
            <p class="text-2xl font-bold text-blue-600">{{ $todayLogs ?? 0 }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl p-4 border border-stone-200 dark:border-stone-800">
        <div class="flex flex-col md:flex-row gap-4 justify-between">
            <div class="flex gap-2 overflow-x-auto">
                <button onclick="filterByLevel('all')" class="level-filter px-4 py-2 rounded-lg text-sm font-medium {{ $level === 'all' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800' }}" data-level="all">All</button>
                <button onclick="filterByLevel('info')" class="level-filter px-4 py-2 rounded-lg text-sm font-medium {{ $level === 'info' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800' }}" data-level="info">Info</button>
                <button onclick="filterByLevel('warning')" class="level-filter px-4 py-2 rounded-lg text-sm font-medium {{ $level === 'warning' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800' }}" data-level="warning">Warning</button>
                <button onclick="filterByLevel('error')" class="level-filter px-4 py-2 rounded-lg text-sm font-medium {{ $level === 'error' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800' }}" data-level="error">Error</button>
                <button onclick="filterByLevel('critical')" class="level-filter px-4 py-2 rounded-lg text-sm font-medium {{ $level === 'critical' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'text-stone-600 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800' }}" data-level="critical">Critical</button>
            </div>
            <div class="flex gap-2">
                <input type="date" id="date-filter" value="{{ request('date') }}" onchange="filterByDate(this.value)" class="px-4 py-2 border border-stone-300 dark:border-stone-700 rounded-xl text-sm dark:bg-stone-800 dark:text-white">
                <input type="text" id="search-input" value="{{ request('search') }}" placeholder="Search logs..." onkeyup="if(event.key === 'Enter') searchLogs()" class="px-4 py-2 border border-stone-300 dark:border-stone-700 rounded-xl text-sm dark:bg-stone-800 dark:text-white">
            </div>
        </div>
    </div>

    <!-- Activity Logs -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden">
        <div class="p-4 border-b border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800">
            <h3 class="font-bold text-stone-900 dark:text-white">Activity Logs</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-50 dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
                        <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Time</th>
                        <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">User</th>
                        <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Action</th>
                        <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Entity</th>
                        <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">IP Address</th>
                        <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activityLogs ?? [] as $log)
                    <tr class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800">
                        <td class="py-4 px-6 text-stone-600 dark:text-stone-400 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-stone-400 to-stone-600 flex items-center justify-center text-white text-xs font-bold">
                                    {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                </div>
                                <span class="text-stone-800 dark:text-white">{{ $log->user?->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 rounded-full text-xs font-medium 
                                @if($log->action === 'created') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400
                                @elseif($log->action === 'updated') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                @elseif($log->action === 'deleted') bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400
                                @else bg-stone-100 text-stone-800 dark:bg-stone-700 dark:text-stone-300
                                @endif">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-stone-600 dark:text-stone-400">{{ $log->entity_type }} #{{ $log->entity_id }}</td>
                        <td class="py-4 px-6 text-stone-600 dark:text-stone-400">{{ $log->ip_address }}</td>
                        <td class="py-4 px-6">
                            <button onclick="viewLogDetails({{ $log->id }})" class="text-primary-600 hover:text-primary-800 dark:text-primary-400">
                                <i class="fa-solid fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-stone-500 dark:text-stone-400">
                            <div class="w-16 h-16 bg-stone-100 dark:bg-stone-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-clipboard-list text-2xl text-stone-400"></i>
                            </div>
                            <p>No activity logs found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-stone-200 dark:border-stone-700">
            {{ $activityLogs->links() ?? '' }}
        </div>
    </div>

    <!-- System Logs -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden">
        <div class="p-4 border-b border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800">
            <h3 class="font-bold text-stone-900 dark:text-white">System Logs</h3>
        </div>
        <div class="p-4 max-h-96 overflow-y-auto font-mono text-sm">
            @forelse($systemLogs ?? [] as $log)
            <div class="mb-2 p-3 rounded-lg 
                @if($log->level === 'error') bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800
                @elseif($log->level === 'warning') bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800
                @elseif($log->level === 'info') bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800
                @else bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700
                @endif">
                <div class="flex justify-between items-start mb-1">
                    <span class="font-bold 
                        @if($log->level === 'error') text-rose-700 dark:text-rose-400
                        @elseif($log->level === 'warning') text-amber-700 dark:text-amber-400
                        @elseif($log->level === 'info') text-blue-700 dark:text-blue-400
                        @else text-stone-700 dark:text-stone-400
                        @endif">
                        [{{ strtoupper($log->level) }}]
                    </span>
                    <span class="text-xs text-stone-500">{{ $log->created_at->format('Y-m-d H:i:s') }}</span>
                </div>
                <p class="text-stone-800 dark:text-stone-300">{{ $log->message }}</p>
                @if($log->context)
                <pre class="mt-2 text-xs text-stone-600 dark:text-stone-400 overflow-x-auto">{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre>
                @endif
            </div>
            @empty
            <p class="text-center text-stone-500 py-8">No system logs found</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Filter by level
    function filterByLevel(level) {
        const url = new URL(window.location);
        if (level === 'all') url.searchParams.delete('level');
        else url.searchParams.set('level', level);
        window.location = url;
    }

    // Filter by date
    function filterByDate(date) {
        const url = new URL(window.location);
        if (date) url.searchParams.set('date', date);
        else url.searchParams.delete('date');
        window.location = url;
    }

    // Search logs
    function searchLogs() {
        const search = document.getElementById('search-input').value;
        const url = new URL(window.location);
        if (search) url.searchParams.set('search', search);
        else url.searchParams.delete('search');
        window.location = url;
    }

    // View log details
    function viewLogDetails(id) {
        fetch(`{{ url('admin/logs/activity') }}/${id}`)
            .then(r => r.json())
            .then(data => {
                const log = data.log;
                const content = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-stone-500">ID</p>
                                <p class="font-medium">${log.id}</p>
                            </div>
                            <div>
                                <p class="text-stone-500">Time</p>
                                <p class="font-medium">${log.created_at}</p>
                            </div>
                            <div>
                                <p class="text-stone-500">User</p>
                                <p class="font-medium">${log.user?.name || 'System'}</p>
                            </div>
                            <div>
                                <p class="text-stone-500">Action</p>
                                <p class="font-medium">${log.action}</p>
                            </div>
                            <div>
                                <p class="text-stone-500">Entity</p>
                                <p class="font-medium">${log.entity_type} #${log.entity_id}</p>
                            </div>
                            <div>
                                <p class="text-stone-500">IP Address</p>
                                <p class="font-medium">${log.ip_address || '-'}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-stone-500 mb-2">Changes</p>
                            <pre class="bg-stone-100 dark:bg-stone-800 p-3 rounded-lg text-xs overflow-x-auto">${JSON.stringify(log.changes, null, 2)}</pre>
                        </div>
                    </div>
                `;
                showModal('Log Details', content, null, false);
            });
    }

    // Export logs
    function exportLogs() {
        window.open('{{ route("admin.logs.export") }}', '_blank');
    }

    // Clear logs
    function clearLogs() {
        if (!confirm('Are you sure you want to clear all logs? This action cannot be undone.')) return;
        
        fetch('{{ route("admin.logs.clear") }}', {
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
                <div class="p-6">${content}</div>
                ${showConfirm ? `
                <div class="flex justify-end gap-2 p-6 border-t border-stone-200 dark:border-stone-800">
                    <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 border border-stone-300 dark:border-stone-700 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800">Close</button>
                </div>
                ` : ''}
            </div>
        `;
        document.body.appendChild(modal);
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
