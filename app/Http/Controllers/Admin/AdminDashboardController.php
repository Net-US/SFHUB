<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsSnapshot;
use App\Models\BlogPost;
use App\Models\SubscriptionPlan;
use App\Models\SystemLog;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $stats = [
            'system' => [
                'version' => config('app.version', '2.1.0'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'last_update' => now()->format('Y-m-d H:i'),
                'environment' => app()->environment(),
                'debug_mode' => config('app.debug'),
                'maintenance_mode' => app()->isDownForMaintenance(),
            ],
            'users' => [
                'total' => User::count(),
                'active' => User::where('is_active', 1)->count(),
                'suspended' => User::where('is_active', 0)->count(),
                'pending_verification' => User::whereNull('email_verified_at')->count(),
                'new_today' => User::whereDate('created_at', $today)->count(),
                'by_role' => User::select('role', DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->pluck('count', 'role')
                    ->toArray(),
                'by_plan' => [
                    'pro' => UserSubscription::whereHas('plan', fn($q) => $q->where('slug', 'pro'))
                        ->where('status', 'active')
                        ->where('ends_at', '>', now())
                        ->count(),
                    'basic' => UserSubscription::whereHas('plan', fn($q) => $q->where('slug', 'basic'))
                        ->where('status', 'active')
                        ->where('ends_at', '>', now())
                        ->count(),
                    'free' => User::doesntHave('subscriptions')->count(),
                ],
            ],
            'content' => [
                'total_posts' => BlogPost::count(),
                'published_posts' => BlogPost::published()->count(),
                'draft_posts' => BlogPost::draft()->count(),
            ],
            'revenue' => [
                'month_to_date' => UserSubscription::where('status', 'active')
                    ->where('created_at', '>=', $thisMonth)
                    ->sum('amount_paid'),
                'total_revenue' => UserSubscription::where('status', 'active')->sum('amount_paid'),
            ],
        ];

        $recentUsers = User::with('subscriptions.plan')
            ->latest()
            ->take(10)
            ->get();

        $recentLogs = SystemLog::latest('logged_at')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentLogs'));
    }

    public function analytics()
    {
        // Get analytics data for the view
        $userGrowth = AnalyticsSnapshot::monthly()
            ->latest('snapshot_date')
            ->take(12)
            ->get()
            ->sortBy('snapshot_date')
            ->map(fn($s) => [
                'month' => $s->snapshot_date->format('M Y'),
                'users' => $s->total_users,
            ])
            ->values()
            ->toArray();

        $revenueData = AnalyticsSnapshot::monthly()
            ->latest('snapshot_date')
            ->take(12)
            ->get()
            ->sortBy('snapshot_date')
            ->map(fn($s) => [
                'month' => $s->snapshot_date->format('M Y'),
                'revenue' => $s->revenue,
            ])
            ->values()
            ->toArray();

        $dailyActive = AnalyticsSnapshot::daily()
            ->latest('snapshot_date')
            ->take(30)
            ->get()
            ->sortBy('snapshot_date')
            ->map(fn($s) => [
                'date' => $s->snapshot_date->format('d M'),
                'active' => $s->active_users,
            ])
            ->values()
            ->toArray();

        // Additional data for view
        $contentStats = [
            'posts' => \App\Models\BlogPost::count(),
            'published' => \App\Models\BlogPost::published()->count(),
            'drafts' => \App\Models\BlogPost::draft()->count(),
        ];

        $planDistribution = \App\Models\User::selectRaw('COALESCE(plan, "free") as plan_name, COUNT(*) as count')
            ->groupBy('plan_name')
            ->pluck('count', 'plan_name')
            ->toArray();

        $monthlyMetrics = AnalyticsSnapshot::monthly()
            ->latest('snapshot_date')
            ->take(6)
            ->get()
            ->sortByDesc('snapshot_date')
            ->map(fn($s) => [
                'month' => $s->snapshot_date->format('M Y'),
                'new_users' => $s->new_users ?? 0,
                'active_users' => $s->active_users ?? 0,
                'revenue' => $s->revenue ?? 0,
                'tasks' => $s->tasks_completed ?? 0,
            ])
            ->values()
            ->toArray();

        return view('admin.analytics', compact('userGrowth', 'revenueData', 'dailyActive', 'contentStats', 'planDistribution', 'monthlyMetrics'));
    }

    public function getStats(): JsonResponse
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $stats = [
            'system' => [
                'version' => config('app.version', '2.1.0'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'last_update' => now()->format('Y-m-d H:i'),
                'environment' => app()->environment(),
                'debug_mode' => config('app.debug'),
                'maintenance_mode' => app()->isDownForMaintenance(),
            ],
            'users' => [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'suspended' => User::where('status', 'suspended')->count(),
                'pending_verification' => User::whereNull('email_verified_at')->count(),
                'new_today' => User::whereDate('created_at', $today)->count(),
                'by_role' => User::select('role', DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->pluck('count', 'role')
                    ->toArray(),
                'by_plan' => [
                    'pro' => UserSubscription::whereHas('plan', fn($q) => $q->where('slug', 'pro'))
                        ->where('status', 'active')
                        ->where('ends_at', '>', now())
                        ->count(),
                    'basic' => UserSubscription::whereHas('plan', fn($q) => $q->where('slug', 'basic'))
                        ->where('status', 'active')
                        ->where('ends_at', '>', now())
                        ->count(),
                    'free' => User::doesntHave('subscriptions')->count(),
                ],
            ],
            'content' => [
                'total_posts' => BlogPost::count(),
                'published_posts' => BlogPost::published()->count(),
                'draft_posts' => BlogPost::draft()->count(),
            ],
            'revenue' => [
                'month_to_date' => UserSubscription::where('status', 'active')
                    ->where('created_at', '>=', $thisMonth)
                    ->sum('amount_paid'),
                'total_revenue' => UserSubscription::where('status', 'active')->sum('amount_paid'),
            ],
        ];

        return response()->json($stats);
    }

    public function getRecentUsers(): JsonResponse
    {
        $users = User::with('subscriptions.plan')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->subscriptions->first()?->plan->slug ?? 'free',
                'joined' => $user->created_at->format('Y-m-d'),
                'status' => $user->status ?? 'active',
                'avatar' => $user->avatar,
            ]);

        return response()->json($users);
    }

    public function getAnalytics(): JsonResponse
    {
        $dailyActive = AnalyticsSnapshot::daily()
            ->latest('snapshot_date')
            ->take(7)
            ->get()
            ->sortBy('snapshot_date')
            ->map(fn($s) => $s->active_users)
            ->values()
            ->toArray();

        $monthlyGrowth = AnalyticsSnapshot::monthly()
            ->latest('snapshot_date')
            ->take(12)
            ->get()
            ->sortBy('snapshot_date')
            ->map(fn($s) => $s->total_users)
            ->values()
            ->toArray();

        $revenue = AnalyticsSnapshot::monthly()
            ->latest('snapshot_date')
            ->take(12)
            ->get()
            ->sortBy('snapshot_date')
            ->map(fn($s) => $s->revenue)
            ->values()
            ->toArray();

        return response()->json([
            'daily_active_users' => $dailyActive,
            'monthly_active_users' => AnalyticsSnapshot::monthly()
                ->latest('snapshot_date')
                ->take(12)
                ->get()
                ->sortBy('snapshot_date')
                ->map(fn($s) => $s->active_users)
                ->values()
                ->toArray(),
            'user_growth' => $monthlyGrowth,
            'revenue' => $revenue,
            'task_completion' => AnalyticsSnapshot::daily()
                ->latest('snapshot_date')
                ->take(7)
                ->get()
                ->sortBy('snapshot_date')
                ->map(fn($s) => $s->tasks_completed)
                ->values()
                ->toArray(),
        ]);
    }

    public function getRecentActivity(): JsonResponse
    {
        $logs = SystemLog::latest('logged_at')
            ->take(10)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'level' => $log->level,
                'message' => $log->message,
                'timestamp' => $log->logged_at->format('Y-m-d H:i:s'),
            ]);

        return response()->json($logs);
    }
}
