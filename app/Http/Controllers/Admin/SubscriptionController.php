<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function getPlans(): JsonResponse
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();

        return response()->json($plans);
    }

    public function storePlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'features' => 'nullable|array',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = true;

        $plan = SubscriptionPlan::create($validated);

        return response()->json([
            'message' => 'Subscription plan created successfully',
            'plan' => $plan,
        ], 201);
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price_monthly' => 'sometimes|numeric|min:0',
            'price_yearly' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'features' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $plan->update($validated);

        return response()->json([
            'message' => 'Subscription plan updated successfully',
            'plan' => $plan,
        ]);
    }

    public function destroyPlan(SubscriptionPlan $plan): JsonResponse
    {
        // Check if plan has active subscriptions
        $activeCount = UserSubscription::where('subscription_plan_id', $plan->id)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->count();

        if ($activeCount > 0) {
            return response()->json([
                'message' => 'Cannot delete plan with active subscriptions',
            ], 422);
        }

        $plan->delete();

        return response()->json([
            'message' => 'Subscription plan deleted successfully',
        ]);
    }

    public function getSubscriptions(Request $request)
    {
        $query = UserSubscription::with(['user', 'plan']);

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('plan_id')) {
            $query->where('subscription_plan_id', $request->get('plan_id'));
        }

        $subscriptions = $query->latest()->paginate($request->get('per_page', 20));
        $plans = SubscriptionPlan::orderBy('sort_order')->get();

        // Calculate stats
        $totalSubscriptions = UserSubscription::count();
        $activeSubscriptions = UserSubscription::where('status', 'active')->where('ends_at', '>', now())->count();
        $monthlyRevenue = UserSubscription::where('status', 'active')->where('billing_cycle', 'monthly')->sum('amount_paid');
        $churnRate = $totalSubscriptions > 0 ? round((($totalSubscriptions - $activeSubscriptions) / $totalSubscriptions) * 100, 1) : 0;

        return view('admin.subscriptions', compact('subscriptions', 'plans', 'totalSubscriptions', 'activeSubscriptions', 'monthlyRevenue', 'churnRate'));
    }

    public function cancelSubscription(UserSubscription $subscription): JsonResponse
    {
        if ($subscription->status !== 'active') {
            return response()->json([
                'message' => 'Subscription is not active',
            ], 422);
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Subscription cancelled successfully',
        ]);
    }

    public function extendSubscription(Request $request, UserSubscription $subscription): JsonResponse
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1',
        ]);

        $newEndsAt = $subscription->ends_at->addDays($validated['days']);

        $subscription->update([
            'ends_at' => $newEndsAt,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Subscription extended successfully',
            'new_ends_at' => $newEndsAt->format('Y-m-d'),
        ]);
    }

    public function getStats(): JsonResponse
    {
        $stats = [
            'total_active' => UserSubscription::where('status', 'active')
                ->where('ends_at', '>', now())
                ->count(),
            'total_revenue' => UserSubscription::where('status', 'active')->sum('amount_paid'),
            'by_plan' => SubscriptionPlan::withCount(['subscriptions' => fn($q) => $q->where('status', 'active')])
                ->get()
                ->map(fn($p) => [
                    'name' => $p->name,
                    'count' => $p->subscriptions_count,
                ]),
            'monthly_recurring_revenue' => UserSubscription::where('status', 'active')
                ->where('billing_cycle', 'monthly')
                ->sum('amount_paid'),
            'yearly_recurring_revenue' => UserSubscription::where('status', 'active')
                ->where('billing_cycle', 'yearly')
                ->sum('amount_paid'),
        ];

        return response()->json($stats);
    }
}
