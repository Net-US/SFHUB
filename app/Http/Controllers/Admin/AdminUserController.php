<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['subscriptions.plan']);
        $search = $request->get('search');
        $filter = $request->get('filter', 'all');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($filter !== 'all') {
            $query->where('is_active', $filter === 'active');
        }

        if ($request->has('role')) {
            $query->where('role', $request->get('role'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        $users = $query->latest()->paginate($request->get('per_page', 20));

        return view('admin.users', compact('users', 'search', 'filter'));
    }

    public function create()
    {
        return view('admin.users-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,moderator,premium,free',
            'password' => 'required|string|min:8',
            'plan' => 'nullable|string|exists:subscription_plans,slug',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
            'email_verified_at' => now(),
        ]);

        if (!empty($validated['plan'])) {
            $plan = \App\Models\SubscriptionPlan::where('slug', $validated['plan'])->first();
            if ($plan) {
                UserSubscription::create([
                    'user_id' => $user->id,
                    'subscription_plan_id' => $plan->id,
                    'status' => 'active',
                    'billing_cycle' => 'monthly',
                    'amount_paid' => $plan->price_monthly,
                    'starts_at' => now(),
                    'ends_at' => now()->addMonth(),
                ]);
            }
        }

        return redirect()->route('admin.users')->with('success', 'User created successfully');
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status ?? 'active',
            'plan' => $user->subscriptions->first()?->plan->slug ?? 'free',
            'subscriptions' => $user->subscriptions->map(fn($sub) => [
                'plan' => $sub->plan->name,
                'status' => $sub->status,
                'starts_at' => $sub->starts_at->format('Y-m-d'),
                'ends_at' => $sub->ends_at->format('Y-m-d'),
            ]),
            'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|in:admin,moderator,premium,free',
            'is_active' => 'sometimes|boolean',
            'password' => 'sometimes|string|min:8',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    public function toggleStatus(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return redirect()->back()->with('success', 'User status updated');
    }

    public function export(Request $request)
    {
        $users = User::all();

        $csv = "ID,Name,Email,Role,Status,Joined\n";
        foreach ($users as $user) {
            $csv .= "{$user->id},\"{$user->name}\",{$user->email},{$user->role},{$user->status},{$user->created_at->format('Y-m-d')}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="users-' . date('Y-m-d') . '.csv"');
    }
}
