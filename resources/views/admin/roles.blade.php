@extends('layouts.app')

@section('title', 'Roles & Permissions | SFHUB Admin')

@section('page-title', 'Roles & Permissions')

@section('content')
<div class="animate-fade-in-up space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Roles & Permissions</h2>
            <p class="text-stone-500 dark:text-stone-400 text-sm">Kelola peran dan hak akses pengguna</p>
        </div>
        <div class="flex gap-2">
            <button onclick="seedDefaults()" class="flex items-center gap-2 px-4 py-2 bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-300 rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-seedling"></i> Seed Defaults
            </button>
            <button onclick="showAddRoleModal()" class="flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-plus"></i> Add New Role
            </button>
        </div>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" id="roles-grid">
        @forelse($roles as $role)
        <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 role-card" data-role-id="{{ $role->id }}">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-lg text-stone-900 dark:text-white">{{ $role->name }}</h3>
                    <p class="text-sm text-stone-500 dark:text-stone-400">{{ $role->users_count }} users</p>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-medium 
                    @if($role->slug === 'super-admin') bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400
                    @elseif($role->slug === 'admin') bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400
                    @elseif($role->slug === 'premium') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400
                    @else bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                    @endif">
                    {{ $role->slug }}
                </span>
            </div>
            <p class="text-sm text-stone-600 dark:text-stone-400 mb-4">{{ $role->description }}</p>

            <div class="space-y-2 mb-4">
                <p class="text-xs font-medium text-stone-500 dark:text-stone-400 uppercase">Permissions:</p>
                @if($role->permissions->count() > 0)
                    <div class="flex flex-wrap gap-1">
                        @foreach($role->permissions->take(5) as $permission)
                        <span class="px-2 py-0.5 bg-stone-100 dark:bg-stone-800 rounded text-xs text-stone-600 dark:text-stone-400">{{ $permission->name }}</span>
                        @endforeach
                        @if($role->permissions->count() > 5)
                        <span class="px-2 py-0.5 bg-stone-100 dark:bg-stone-800 rounded text-xs text-stone-600 dark:text-stone-400">+{{ $role->permissions->count() - 5 }} more</span>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-stone-400">No permissions assigned</p>
                @endif
            </div>

            <div class="flex gap-2">
                <button onclick="editRole({{ $role->id }})" class="flex-1 py-2 border border-stone-300 dark:border-stone-700 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 text-sm">
                    <i class="fa-solid fa-edit mr-1"></i> Edit
                </button>
                @if($role->slug !== 'super-admin')
                <button onclick="deleteRole({{ $role->id }})" class="flex-1 py-2 border border-rose-300 dark:border-rose-800 text-rose-700 dark:text-rose-400 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20 text-sm">
                    <i class="fa-solid fa-trash-can mr-1"></i> Delete
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-12">
            <div class="w-16 h-16 bg-stone-100 dark:bg-stone-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-user-tag text-2xl text-stone-400"></i>
            </div>
            <h3 class="text-lg font-medium text-stone-900 dark:text-white mb-2">No roles found</h3>
            <p class="text-stone-500 dark:text-stone-400 mb-4">Create your first role or seed default roles</p>
            <button onclick="seedDefaults()" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg">
                Seed Default Roles
            </button>
        </div>
        @endforelse
    </div>

    <!-- All Permissions Table -->
    <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-stone-900 dark:text-white">All Permissions</h3>
            <button onclick="showAddPermissionModal()" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                <i class="fa-solid fa-plus mr-1"></i> Add Permission
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-stone-200 dark:border-stone-700">
                        <th class="text-left py-3 text-stone-600 dark:text-stone-400 font-medium">Permission</th>
                        <th class="text-left py-3 text-stone-600 dark:text-stone-400 font-medium">Module</th>
                        <th class="text-left py-3 text-stone-600 dark:text-stone-400 font-medium">Slug</th>
                        <th class="text-left py-3 text-stone-600 dark:text-stone-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $permission)
                    <tr class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800 permission-row" data-permission-id="{{ $permission->id }}">
                        <td class="py-3 font-medium text-stone-800 dark:text-white">{{ $permission->name }}</td>
                        <td class="py-3">
                            <span class="px-2 py-1 bg-stone-100 dark:bg-stone-800 rounded-full text-xs">{{ $permission->module }}</span>
                        </td>
                        <td class="py-3 text-stone-500 dark:text-stone-400">{{ $permission->slug }}</td>
                        <td class="py-3">
                            <button onclick="editPermission({{ $permission->id }})" class="text-primary-600 hover:text-primary-800 dark:text-primary-400 mr-2">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <button onclick="deletePermission({{ $permission->id }})" class="text-rose-600 hover:text-rose-800 dark:text-rose-400">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-stone-500 dark:text-stone-400">
                            No permissions found. Seed defaults to create permissions.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add Role Modal
    function showAddRoleModal() {
        const content = `
            <form id="add-role-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Role Name</label>
                    <input type="text" name="name" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Slug (optional)</label>
                    <input type="text" name="slug" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                    <p class="text-xs text-stone-500 mt-1">Auto-generated if empty</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Permissions</label>
                    <div class="max-h-48 overflow-y-auto space-y-2 border border-stone-200 dark:border-stone-700 rounded-xl p-3">
                        @foreach($permissions->groupBy('module') as $module => $perms)
                        <div class="mb-3">
                            <p class="text-xs font-bold text-stone-500 uppercase mb-1">{{ $module }}</p>
                            @foreach($perms as $perm)
                            <label class="flex items-center space-x-2 mb-1">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="rounded border-stone-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-stone-700 dark:text-stone-300">{{ $perm->name }}</span>
                            </label>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
            </form>
        `;
        showModal('Add New Role', content, () => {
            const form = document.getElementById('add-role-form');
            const formData = new FormData(form);
            
            fetch('{{ route("admin.roles.store") }}', {
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

    // Add Permission Modal
    function showAddPermissionModal() {
        const content = `
            <form id="add-permission-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Permission Name</label>
                    <input type="text" name="name" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Slug (optional)</label>
                    <input type="text" name="slug" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Module</label>
                    <select name="module" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        @foreach($modules as $module)
                        <option value="{{ $module }}">{{ $module }}</option>
                        @endforeach
                        <option value="New Module">+ New Module</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Description</label>
                    <textarea name="description" rows="2" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white"></textarea>
                </div>
            </form>
        `;
        showModal('Add New Permission', content, () => {
            const form = document.getElementById('add-permission-form');
            const formData = new FormData(form);
            
            fetch('{{ route("admin.permissions.store") }}', {
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

    // Seed Defaults
    function seedDefaults() {
        if (!confirm('This will create default roles and permissions. Continue?')) return;
        
        fetch('{{ route("admin.roles.seed") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            showNotification(data.message);
            setTimeout(() => location.reload(), 1000);
        });
    }

    // Edit Role
    function editRole(id) {
        fetch(`{{ url('admin/roles/list') }}`)
            .then(r => r.json())
            .then(roles => {
                const role = roles.find(r => r.id === id);
                if (!role) return;

                const content = `
                    <form id="edit-role-form" class="space-y-4">
                        <input type="hidden" name="_method" value="PUT">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Role Name</label>
                            <input type="text" name="name" value="${role.name}" required class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Description</label>
                            <textarea name="description" rows="3" class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-stone-800 dark:text-white">${role.description || ''}</textarea>
                        </div>
                        <div>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="is_active" value="1" ${role.is_active ? 'checked' : ''} class="rounded border-stone-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-stone-700 dark:text-stone-300">Active</span>
                            </label>
                        </div>
                    </form>
                `;
                showModal('Edit Role', content, () => {
                    const form = document.getElementById('edit-role-form');
                    const formData = new FormData(form);
                    
                    fetch(`{{ url('admin/roles') }}/${id}`, {
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

    // Delete Role
    function deleteRole(id) {
        if (!confirm('Are you sure you want to delete this role?')) return;
        
        fetch(`{{ url('admin/roles') }}/${id}`, {
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
                document.querySelector(`[data-role-id="${id}"]`)?.remove();
            } else {
                showNotification(data.message, 'error');
            }
        });
    }

    // Delete Permission
    function deletePermission(id) {
        if (!confirm('Are you sure you want to delete this permission?')) return;
        
        fetch(`{{ url('admin/permissions') }}/${id}`, {
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
                document.querySelector(`[data-permission-id="${id}"]`)?.remove();
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
