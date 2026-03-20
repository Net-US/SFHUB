@extends('layouts.app')

@section('title', 'Subscription Management | SFHUB Admin')

@section('page-title', 'Subscription Management')

@section('content')
    <div class="animate-fade-in-up space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Subscription Management</h2>
                <p class="text-stone-500 dark:text-stone-400 text-sm">Kelola paket dan langganan pengguna</p>
            </div>
            <div class="flex gap-2">
                <button onclick="showAddPlanModal()"
                    class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-plus"></i> Add Plan
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Total Subscriptions</p>
                <p class="text-2xl font-bold text-stone-900 dark:text-white">{{ $totalSubscriptions ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Active</p>
                <p class="text-2xl font-bold text-emerald-600">{{ $activeSubscriptions ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Monthly Revenue</p>
                <p class="text-2xl font-bold text-amber-600">Rp {{ number_format($monthlyRevenue ?? 0) }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <p class="text-sm text-stone-500 dark:text-stone-400">Churn Rate</p>
                <p class="text-2xl font-bold text-blue-600">{{ $churnRate ?? 0 }}%</p>
            </div>
        </div>

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($plans ?? [] as $plan)
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 relative overflow-hidden">
                    @if ($plan->is_popular)
                        <div class="absolute top-0 right-0 bg-primary-500 text-white text-xs px-3 py-1 rounded-bl-lg">
                            Popular</div>
                    @endif
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-lg text-stone-900 dark:text-white">{{ $plan->name }}</h3>
                            <p class="text-sm text-stone-500">{{ $plan->slug }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-2xl"
                            style="background-color: {{ $plan->color ?? '#f57223' }}20; color: {{ $plan->color ?? '#f57223' }}">
                            <i class="fa-solid fa-{{ $plan->icon ?? 'star' }}"></i>
                        </div>
                    </div>
                    <div class="mb-4">
                        <span class="text-3xl font-bold text-stone-900 dark:text-white">Rp
                            {{ number_format($plan->price, 0, ',', '.') }}</span>
                        <span class="text-stone-500">/{{ $plan->billing_period }}</span>
                    </div>
                    <p class="text-sm text-stone-600 dark:text-stone-400 mb-4">{{ Str::limit($plan->description, 100) }}
                    </p>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-stone-500">{{ $plan->users_count ?? 0 }} users</span>
                        <div class="flex gap-1">
                            <button onclick="editPlan({{ $plan->id }})"
                                class="p-2 text-primary-600 hover:text-primary-800 dark:text-primary-400">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <button onclick="deletePlan({{ $plan->id }})"
                                class="p-2 text-rose-600 hover:text-rose-800 dark:text-rose-400">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-4 text-center py-12">
                    <div
                        class="w-16 h-16 bg-stone-100 dark:bg-stone-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-crown text-2xl text-stone-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-stone-900 dark:text-white mb-2">No plans yet</h3>
                    <p class="text-stone-500 mb-4">Create your first subscription plan</p>
                    <button onclick="showAddPlanModal()"
                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                        Add Plan
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Subscriptions Table -->
        <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 overflow-hidden">
            <div class="p-6 border-b border-stone-200 dark:border-stone-700 flex justify-between items-center">
                <h3 class="font-bold text-stone-900 dark:text-white">Active Subscriptions</h3>
                <div class="flex gap-2">
                    <select id="status-filter" onchange="filterByStatus(this.value)"
                        class="px-3 py-2 border border-stone-300 dark:border-stone-700 rounded-lg text-sm dark:bg-stone-800 dark:text-white">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-stone-50 dark:bg-stone-800 border-b border-stone-200 dark:border-stone-700">
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">User</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Plan</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Amount</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Status</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Start Date</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">End Date</th>
                            <th class="text-left py-4 px-6 text-stone-600 dark:text-stone-400 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions ?? [] as $sub)
                            <tr
                                class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gradient-to-br from-stone-400 to-stone-600 flex items-center justify-center text-white font-bold text-xs">
                                            {{ strtoupper(substr($sub->user?->name, 0, 1)) }}
                                        </div>
                                        <span class="text-stone-800 dark:text-white">{{ $sub->user?->name }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">{{ $sub->plan?->name ?? '-' }}</td>
                                <td class="py-4 px-6">Rp {{ number_format($sub->amount, 0, ',', '.') }}</td>
                                <td class="py-4 px-6">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-medium
                                @if ($sub->status === 'active') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400
                                @elseif($sub->status === 'cancelled') bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400
                                @else bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 @endif">
                                        {{ $sub->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">{{ $sub->starts_at?->format('M d, Y') ?? '-' }}</td>
                                <td class="py-4 px-6">{{ $sub->ends_at?->format('M d, Y') ?? '-' }}</td>
                                <td class="py-4 px-6">
                                    <div class="flex gap-2">
                                        <button onclick="editSubscription({{ $sub->id }})"
                                            class="text-primary-600 hover:text-primary-800 dark:text-primary-400">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <button onclick="cancelSubscription({{ $sub->id }})"
                                            class="text-rose-600 hover:text-rose-800 dark:text-rose-400" title="Cancel">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-stone-500 dark:text-stone-400">
                                    <div
                                        class="w-16 h-16 bg-stone-100 dark:bg-stone-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fa-solid fa-users text-2xl text-stone-400"></i>
                                    </div>
                                    <p>No subscriptions found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-stone-200 dark:border-stone-700">
                {{ $subscriptions->links() ?? '' }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Filter by status
        function filterByStatus(status) {
            const url = new URL(window.location);
            if (status) url.searchParams.set('status', status);
            else url.searchParams.delete('status');
            window.location = url;
        }

        // Show Add Plan Modal
        function showAddPlanModal() {
            const content = `
            <form id="add-plan-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Plan Name</label>
                    <input type="text" name="name" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Slug</label>
                    <input type="text" name="slug" placeholder="e.g., basic, pro, premium" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Price (Rp)</label>
                        <input type="number" name="price" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Billing Period</label>
                        <select name="billing_period" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="lifetime">Lifetime</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Icon</label>
                        <input type="text" name="icon" placeholder="fontawesome icon name" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Color</label>
                        <input type="color" name="color" value="#f57223" class="w-full h-10 border border-stone-300 dark:border-stone-700 rounded-xl">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_popular" value="1" class="rounded border-stone-300 text-primary-600 focus:ring-primary-500">
                    <label class="text-sm text-stone-700 dark:text-stone-300">Mark as Popular</label>
                </div>
            </form>
        `;

            showModal('Add Subscription Plan', content, () => {
                const form = document.getElementById('add-plan-form');
                const formData = new FormData(form);

                fetch('{{ route('admin.subscriptions.store') }}', {
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

        // Edit Plan
        function editPlan(id) {
            fetch(`{{ url('admin/subscriptions/plans') }}/${id}`)
                .then(r => r.json())
                .then(data => {
                    const plan = data.plan;
                    const content = `
                    <form id="edit-plan-form" class="space-y-4">
                        <input type="hidden" name="_method" value="PUT">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Plan Name</label>
                            <input type="text" name="name" value="${plan.name}" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Price (Rp)</label>
                                <input type="number" name="price" value="${plan.price}" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Billing Period</label>
                                <select name="billing_period" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                                    <option value="monthly" ${plan.billing_period === 'monthly' ? 'selected' : ''}>Monthly</option>
                                    <option value="yearly" ${plan.billing_period === 'yearly' ? 'selected' : ''}>Yearly</option>
                                    <option value="lifetime" ${plan.billing_period === 'lifetime' ? 'selected' : ''}>Lifetime</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Description</label>
                            <textarea name="description" rows="3" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">${plan.description || ''}</textarea>
                        </div>
                    </form>
                `;

                    showModal('Edit Plan', content, () => {
                        const form = document.getElementById('edit-plan-form');
                        const formData = new FormData(form);

                        fetch(`{{ url('admin/subscriptions/plans') }}/${id}`, {
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

        // Delete Plan
        function deletePlan(id) {
            if (!confirm('Delete this plan? Active subscriptions may be affected.')) return;

            fetch(`{{ url('admin/subscriptions/plans') }}/${id}`, {
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

        // Edit Subscription
        function editSubscription(id) {
            // Implementation similar to editPlan
            showNotification('Edit subscription functionality coming soon');
        }

        // Cancel Subscription
        function cancelSubscription(id) {
            if (!confirm('Cancel this subscription?')) return;

            fetch(`{{ url('admin/subscriptions') }}/${id}/cancel`, {
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
