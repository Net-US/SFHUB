<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LandingContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ── DASHBOARD ─────────────────────────────────────────────────────────
    public function index()
    {
        // User Statistics
        $totalUsers    = User::count();
        $activeUsers   = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();
        $newThisMonth  = User::whereMonth('created_at', now()->month)->count();

        // Role Distribution
        $adminCount     = User::where('role', 'admin')->count();
        $studentCount   = User::where('role', 'student')->count();
        $freelanceCount = User::where('role', 'freelancer')->count();

        // Plan Distribution
        $freeCount  = User::where('plan', 'free')->count();
        $proCount   = User::where('plan', 'pro')->count();
        $teamCount  = User::where('plan', 'team')->count();

        // Platform Statistics
        $totalWorkspaces = \App\Models\Workspace::count();
        $totalTasks      = \App\Models\Task::count();
        $totalSubjects   = \App\Models\Subject::count();
        $totalEvents     = \App\Models\CalendarEvent::count();
        $totalFinanceAccounts = \App\Models\FinanceAccount::count();
        $totalTransactions   = \App\Models\Transaction::count();

        // Task Statistics
        $completedTasks = \App\Models\Task::where('status', 'done')->count();
        $pendingTasks   = \App\Models\Task::where('status', 'todo')->count();
        $doingTasks     = \App\Models\Task::where('status', 'doing')->count();

        // Recent Activity
        $recentUsers = User::latest()->take(5)->get();
        $recentTasks = \App\Models\Task::latest()->take(5)->get();
        $recentTransactions = \App\Models\Transaction::latest()->take(5)->get();

        // Growth Data (Last 6 months)
        $monthlyGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyGrowth[] = [
                'month' => $month->format('M Y'),
                'users' => User::whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->count(),
                'tasks' => \App\Models\Task::whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->count(),
            ];
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'newThisMonth',
            'adminCount',
            'studentCount',
            'freelanceCount',
            'freeCount',
            'proCount',
            'teamCount',
            'totalWorkspaces',
            'totalTasks',
            'totalSubjects',
            'totalEvents',
            'totalFinanceAccounts',
            'totalTransactions',
            'completedTasks',
            'pendingTasks',
            'doingTasks',
            'recentUsers',
            'recentTasks',
            'recentTransactions',
            'monthlyGrowth'
        ));
    }

    // ── USERS ─────────────────────────────────────────────────────────────
    public function users(Request $request)
    {
        $search = $request->get('search');
        $filter = $request->get('filter', 'all');

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%");
            });
        }

        if ($filter === 'active') {
            $query->where('is_active', true);
        } elseif ($filter === 'inactive') {
            $query->where('is_active', false);
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users', compact('users', 'search', 'filter'));
    }

    public function createUser()
    {
        return view('admin.create-user');
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'username' => 'nullable|string|unique:users|max:50',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|in:student,freelancer,both,entrepreneur',
            'plan'     => 'required|in:free,pro,team',
        ]);

        if (empty($data['username'])) {
            $base    = strtolower(str_replace(' ', '', $data['name']));
            $uname   = $base;
            $counter = 1;
            while (User::where('username', $uname)->exists()) {
                $uname = $base . $counter++;
            }
            $data['username'] = $uname;
        }

        $data['password']  = Hash::make($data['password']);
        $data['is_active'] = true;

        User::create($data);

        return redirect()->route('admin.users')->with('success', 'User berhasil ditambahkan.');
    }

    public function toggleUserActive(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Tidak dapat menonaktifkan admin.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User {$user->name} berhasil {$status}.");
    }

    public function destroyUser(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Tidak dapat menghapus admin.');
        }
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    // ── LANDING CONTENT ────────────────────────────────────────────────────
    public function landingContent()
    {
        $features     = LandingContent::bySection('features')->active()->orderBy('sort_order')->get();
        $stats        = LandingContent::bySection('stats')->active()->orderBy('sort_order')->get();
        $heroContent  = LandingContent::where('key', 'hero_title')->first();
        $heroSubtitle = LandingContent::where('key', 'hero_subtitle')->first();
        $heroDesc     = LandingContent::where('key', 'hero_description')->first();
        $seoTitle     = LandingContent::where('key', 'seo_title')->first();
        $seoDesc      = LandingContent::where('key', 'seo_description')->first();
        $seoKeywords  = LandingContent::where('key', 'seo_keywords')->first();
        $allContent   = LandingContent::orderBy('section')->orderBy('sort_order')->get();

        return view('admin.landing', compact(
            'features',
            'stats',
            'heroContent',
            'heroSubtitle',
            'heroDesc',
            'seoTitle',
            'seoDesc',
            'seoKeywords',
            'allContent'
        ));
    }

    public function storeLandingContent(Request $request)
    {
        $data = $request->validate([
            'key'        => 'required|string|unique:landing_contents,key',
            'section'    => 'required|string',
            'title'      => 'nullable|string|max:255',
            'content'    => 'nullable|string',
            'icon'       => 'nullable|string|max:100',
            'color'      => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $data['is_active'] = true;
        LandingContent::create($data);

        return back()->with('success', 'Konten berhasil ditambahkan.');
    }

    public function updateLandingContent(Request $request, LandingContent $content)
    {
        $data = $request->validate([
            'title'      => 'nullable|string|max:255',
            'content'    => 'nullable|string',
            'icon'       => 'nullable|string|max:100',
            'color'      => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
        ]);

        $content->update($data);

        return back()->with('success', 'Konten berhasil diperbarui.');
    }

    public function destroyLandingContent(LandingContent $content)
    {
        $content->delete();
        return back()->with('success', 'Konten berhasil dihapus.');
    }
}
