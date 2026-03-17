<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('profile')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:student,freelancer,both,entrepreneur'],
            'plan' => ['required', 'in:free,pro,premium,team'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'plan' => $request->plan,
            'preferences' => json_encode([
                'theme' => 'light',
                'notifications' => true,
                'language' => 'id',
                'timezone' => 'Asia/Jakarta',
            ]),
        ]);

        // Create user profile
        \App\Models\Profile::create([
            'user_id' => $user->id,
        ]);

        // Create default workspace
        $this->createDefaultWorkspace($user);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['profile', 'workspaces', 'tasks', 'financeAccounts', 'academicCourses']);

        $stats = [
            'tasks' => $user->tasks()->count(),
            'completed_tasks' => $user->tasks()->where('status', 'done')->count(),
            'active_projects' => $user->projectStages()->where('status', 'active')->count(),
            'total_assets' => $user->getTotalAssets(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:student,freelancer,both,entrepreneur'],
            'plan' => ['required', 'in:free,pro,premium,team'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'plan' => $request->plan,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);

            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('users.show', $user)
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Create default workspace for user
     */
    private function createDefaultWorkspace(User $user)
    {
        $workspaces = [
            [
                'name' => 'Creative Studio',
                'type' => 'creative',
                'color' => '#f97316',
                'icon' => 'palette',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Academic Hub',
                'type' => 'academic',
                'color' => '#3b82f6',
                'icon' => 'graduation-cap',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'PKL / Work',
                'type' => 'pkl',
                'color' => '#10b981',
                'icon' => 'briefcase',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Personal',
                'type' => 'personal',
                'color' => '#8b5cf6',
                'icon' => 'user',
                'order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($workspaces as $workspaceData) {
            $user->workspaces()->create($workspaceData);
        }
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request, User $user)
    {
        $request->validate([
            'theme' => ['required', 'in:light,dark,system'],
            'notifications' => ['required', 'boolean'],
            'language' => ['required', 'in:id,en'],
            'timezone' => ['required', 'timezone'],
        ]);

        $user->update([
            'preferences' => json_encode([
                'theme' => $request->theme,
                'notifications' => $request->notifications,
                'language' => $request->language,
                'timezone' => $request->timezone,
            ]),
        ]);

        return back()->with('success', 'Preferensi berhasil diperbarui.');
    }
}
