<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminRoleController extends Controller
{
    /**
     * Display roles and permissions management page
     */
    public function index(): Response
    {
        $roles = Role::withCount('users')->get();
        $permissions = Permission::orderBy('module')->get();
        $modules = Permission::select('module')->distinct()->pluck('module');

        return response()->view('admin.roles', compact('roles', 'permissions', 'modules'));
    }

    /**
     * Get all roles with their permissions
     */
    public function getRoles(): JsonResponse
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->get()
            ->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'users_count' => $role->users_count,
                'is_active' => $role->is_active,
                'permissions' => $role->permissions->pluck('slug'),
            ]);

        return response()->json($roles);
    }

    /**
     * Get all permissions grouped by module
     */
    public function getPermissions(): JsonResponse
    {
        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        return response()->json($permissions);
    }

    /**
     * Create a new role
     */
    public function storeRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->attach($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Update a role
     */
    public function updateRole(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'is_active' => 'boolean',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? $role->description,
            'is_active' => $validated['is_active'] ?? $role->is_active,
        ]);

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Delete a role
     */
    public function deleteRole(Role $role): JsonResponse
    {
        if ($role->slug === 'super-admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete super admin role',
            ], 403);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully',
        ]);
    }

    /**
     * Create a new permission
     */
    public function storePermission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:permissions',
            'module' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'module' => $validated['module'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully',
            'permission' => $permission,
        ]);
    }

    /**
     * Update a permission
     */
    public function updatePermission(Request $request, Permission $permission): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $permission->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Permission updated successfully',
            'permission' => $permission,
        ]);
    }

    /**
     * Delete a permission
     */
    public function deletePermission(Permission $permission): JsonResponse
    {
        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully',
        ]);
    }

    /**
     * Assign role to user
     */
    public function assignRoleToUser(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user->roles()->sync($validated['role_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Roles assigned successfully',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Get user's roles
     */
    public function getUserRoles(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user->load('roles'),
            'roles' => $user->roles,
        ]);
    }

    /**
     * Seed default roles and permissions
     */
    public function seedDefaults(): JsonResponse
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full access to all features'],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Manage users and content'],
            ['name' => 'Moderator', 'slug' => 'moderator', 'description' => 'Moderate content and users'],
            ['name' => 'Premium User', 'slug' => 'premium', 'description' => 'Premium features access'],
            ['name' => 'Free User', 'slug' => 'free', 'description' => 'Basic features access'],
        ];

        $permissions = [
            // Users module
            ['name' => 'Manage Users', 'slug' => 'manage_users', 'module' => 'Users'],
            ['name' => 'Edit Users', 'slug' => 'edit_users', 'module' => 'Users'],
            ['name' => 'View Users', 'slug' => 'view_users', 'module' => 'Users'],
            ['name' => 'Delete Users', 'slug' => 'delete_users', 'module' => 'Users'],

            // Content module
            ['name' => 'Manage Content', 'slug' => 'manage_content', 'module' => 'Content'],
            ['name' => 'Manage Blog', 'slug' => 'manage_blog', 'module' => 'Content'],
            ['name' => 'Manage FAQ', 'slug' => 'manage_faq', 'module' => 'Content'],
            ['name' => 'Manage Landing Page', 'slug' => 'manage_landing', 'module' => 'Content'],

            // Reports module
            ['name' => 'View Reports', 'slug' => 'view_reports', 'module' => 'Reports'],
            ['name' => 'View Analytics', 'slug' => 'view_analytics', 'module' => 'Reports'],

            // Settings module
            ['name' => 'Manage Settings', 'slug' => 'manage_settings', 'module' => 'Settings'],
            ['name' => 'Manage SEO', 'slug' => 'manage_seo', 'module' => 'Settings'],
            ['name' => 'View Logs', 'slug' => 'view_logs', 'module' => 'Settings'],

            // Features module
            ['name' => 'Access Premium Features', 'slug' => 'access_premium_features', 'module' => 'Features'],
            ['name' => 'Unlimited Tasks', 'slug' => 'unlimited_tasks', 'module' => 'Features'],
            ['name' => 'Priority Support', 'slug' => 'priority_support', 'module' => 'Features'],
            ['name' => 'Export Data', 'slug' => 'export_data', 'module' => 'Features'],
            ['name' => 'Basic Features', 'slug' => 'basic_features', 'module' => 'Features'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }

        // Assign all permissions to super admin
        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $allPermissionIds = Permission::pluck('id');
            $superAdmin->permissions()->sync($allPermissionIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'Default roles and permissions seeded successfully',
        ]);
    }
}
