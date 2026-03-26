<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    // ────────────────────────────────────────────────────────────────────
    // VIEW — GET /admin/subscriptions
    // ────────────────────────────────────────────────────────────────────
    public function getSubscriptions(Request $request)
    {
        $query = UserSubscription::with(['user', 'plan']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('plan_id')) {
            $query->where('subscription_plan_id', $request->plan_id);
        }

        $subscriptions = $query->latest()->paginate($request->get('per_page', 20));
        $plans         = SubscriptionPlan::orderBy('sort_order')->get();

        $totalSubscriptions = UserSubscription::count();
        $activeSubscriptions = UserSubscription::where('status', 'active')->where('ends_at', '>', now())->count();
        $monthlyRevenue     = UserSubscription::where('status', 'active')->where('billing_cycle', 'monthly')->sum('amount_paid');
        $churnRate          = $totalSubscriptions > 0
            ? round((($totalSubscriptions - $activeSubscriptions) / $totalSubscriptions) * 100, 1)
            : 0;

        return view('admin.subscriptions', compact(
            'subscriptions',
            'plans',
            'totalSubscriptions',
            'activeSubscriptions',
            'monthlyRevenue',
            'churnRate'
        ));
    }

    // ── GET plan by ID (untuk edit modal) ────────────────────────────────
    public function getPlan(SubscriptionPlan $plan): JsonResponse
    {
        return response()->json(['success' => true, 'plan' => $plan]);
    }

    // ── GET all plans ────────────────────────────────────────────────────
    public function getPlans(): JsonResponse
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        return response()->json(['success' => true, 'plans' => $plans]);
    }

    // ────────────────────────────────────────────────────────────────────
    // STORE PLAN — POST /admin/subscriptions/plans
    // ────────────────────────────────────────────────────────────────────
    public function storePlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'slug'          => 'nullable|string|max:100|unique:subscription_plans,slug',
            'description'   => 'nullable|string|max:1000',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly'  => 'nullable|numeric|min:0',
            'currency'      => 'nullable|string|size:3',
            'features'      => 'nullable|array',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        $validated['slug']         = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['price_yearly'] = $validated['price_yearly'] ?? 0;
        $validated['currency']     = $validated['currency'] ?? 'IDR';
        $validated['is_active']    = true;
        $validated['sort_order']   = $validated['sort_order'] ?? SubscriptionPlan::max('sort_order') + 1;

        $plan = SubscriptionPlan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Paket berhasil dibuat.',
            'plan'    => $plan,
        ], 201);
    }

    // ────────────────────────────────────────────────────────────────────
    // UPDATE PLAN — PUT /admin/subscriptions/plans/{plan}
    // ────────────────────────────────────────────────────────────────────
    public function updatePlan(Request $request, SubscriptionPlan $plan): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'slug'          => 'sometimes|string|max:100|unique:subscription_plans,slug,' . $plan->id,
            'description'   => 'nullable|string|max:1000',
            'price_monthly' => 'sometimes|numeric|min:0',
            'price_yearly'  => 'nullable|numeric|min:0',
            'currency'      => 'nullable|string|size:3',
            'features'      => 'nullable|array',
            'is_active'     => 'sometimes|boolean',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        // Auto-update slug jika nama berubah dan slug tidak di-override
        if (isset($validated['name']) && !isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $plan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Paket berhasil diperbarui.',
            'plan'    => $plan->fresh(),
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // TOGGLE ACTIVE — PATCH /admin/subscriptions/plans/{plan}/toggle
    // ────────────────────────────────────────────────────────────────────
    public function togglePlan(SubscriptionPlan $plan): JsonResponse
    {
        $plan->update(['is_active' => !$plan->is_active]);

        return response()->json([
            'success'   => true,
            'message'   => 'Status paket diperbarui.',
            'is_active' => $plan->is_active,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // DESTROY PLAN — DELETE /admin/subscriptions/plans/{plan}
    // ────────────────────────────────────────────────────────────────────
    public function destroyPlan(SubscriptionPlan $plan): JsonResponse
    {
        $activeCount = UserSubscription::where('subscription_plan_id', $plan->id)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->count();

        if ($activeCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Tidak bisa menghapus paket yang masih memiliki {$activeCount} subscriber aktif.",
            ], 422);
        }

        $plan->delete();

        return response()->json(['success' => true, 'message' => 'Paket berhasil dihapus.']);
    }

    // ────────────────────────────────────────────────────────────────────
    // CANCEL SUBSCRIPTION — POST /admin/subscriptions/{subscription}/cancel
    // ────────────────────────────────────────────────────────────────────
    public function cancelSubscription(UserSubscription $subscription): JsonResponse
    {
        if (!in_array($subscription->status, ['active', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription sudah tidak aktif.',
            ], 422);
        }

        $subscription->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Downgrade user jika tidak ada active subscription lain
        $hasOtherActive = UserSubscription::where('user_id', $subscription->user_id)
            ->where('id', '!=', $subscription->id)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->exists();

        if (!$hasOtherActive) {
            \App\Models\User::where('id', $subscription->user_id)->update(['plan' => 'free']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription berhasil dibatalkan.',
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // EXTEND SUBSCRIPTION — POST /admin/subscriptions/{subscription}/extend
    // ────────────────────────────────────────────────────────────────────
    public function extendSubscription(Request $request, UserSubscription $subscription): JsonResponse
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $newEndsAt = ($subscription->ends_at ?? now())->addDays($validated['days']);

        $subscription->update([
            'ends_at' => $newEndsAt,
            'status'  => 'active',
        ]);

        // Sync user plan
        if ($subscription->plan) {
            $slug = strtolower($subscription->plan->slug ?? 'pro');
            \App\Models\User::where('id', $subscription->user_id)->update(['plan' => $slug]);
        }

        return response()->json([
            'success'      => true,
            'message'      => "Subscription diperpanjang {$validated['days']} hari.",
            'new_ends_at'  => $newEndsAt->format('Y-m-d'),
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // STATS — GET /admin/subscriptions/stats
    // ────────────────────────────────────────────────────────────────────
    public function getStats(): JsonResponse
    {
        $stats = [
            'total_active' => UserSubscription::where('status', 'active')->where('ends_at', '>', now())->count(),
            'total_pending' => UserSubscription::where('status', 'pending')->count(),
            'total_revenue' => UserSubscription::where('status', 'active')->sum('amount_paid'),
            'monthly_recurring_revenue' => UserSubscription::where('status', 'active')->where('billing_cycle', 'monthly')->sum('amount_paid'),
            'yearly_recurring_revenue'  => UserSubscription::where('status', 'active')->where('billing_cycle', 'yearly')->sum('amount_paid'),
            'by_plan' => SubscriptionPlan::withCount([
                'subscriptions as active_count' => fn($q) => $q->where('status', 'active')->where('ends_at', '>', now()),
            ])->get()->map(fn($p) => [
                'id'     => $p->id,
                'name'   => $p->name,
                'slug'   => $p->slug,
                'count'  => $p->active_count,
                'active' => $p->is_active,
            ]),
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }
}
