<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Profile;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    // ────────────────────────────────────────────────────────────────────
    // LOGIN
    // ────────────────────────────────────────────────────────────────────
    public function showLogin()
    {
        return view('auth.portal-morph', ['mode' => 'login']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required'],
        ]);

        // Rate-limiting
        $maxAttempts = (int) (\App\Models\SystemSetting::get('max_login_attempts', 5));
        $key         = 'login_attempts_' . $request->ip();
        $attempts    = cache()->get($key, 0);
        if ($attempts >= $maxAttempts) {
            return back()
                ->withErrors(['login' => 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.'])
                ->onlyInput('login');
        }

        $loginField  = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$loginField => $request->login, 'password' => $request->password];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            cache()->forget($key);
            $user = Auth::user();

            if (isset($user->is_active) && !$user->is_active) {
                Auth::logout();
                return back()
                    ->withErrors(['login' => 'Akun Anda telah dinonaktifkan.'])
                    ->onlyInput('login');
            }

            $request->session()->regenerate();

            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.index'));
            }

            // ── Cek pending subscription → arahkan ke payment ──────────
            if ($this->shouldRedirectToPremiumCheckout($user)) {
                $pending = UserSubscription::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->latest()
                    ->with('plan')
                    ->first();

                $plan = $pending?->plan ?? $this->resolvePlanBySlug((string) $user->plan);

                if ($plan) {
                    return redirect()
                        ->route('auth.onboarding-payment', ['plan' => $plan->id])
                        ->with('info', 'Akun premium kamu belum aktif. Selesaikan pembayaran untuk melanjutkan.');
                }
            }

            return redirect()->intended(route('dashboard'));
        }

        cache()->put($key, $attempts + 1, now()->addMinutes(15));

        return back()->withErrors([
            'login' => 'Email/username atau password salah.',
        ])->onlyInput('login');
    }

    // ────────────────────────────────────────────────────────────────────
    // REGISTER
    // ────────────────────────────────────────────────────────────────────
    public function showRegister()
    {
        return view('auth.portal-morph', ['mode' => 'register']);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'plan'     => ['nullable', 'in:free,pro,team'],
            'terms'    => ['required', 'accepted'],
        ]);

        if (!\App\Models\SystemSetting::get('user_registration', true)) {
            return back()->withErrors(['email' => 'Pendaftaran user sedang ditutup.']);
        }

        $selectedPlan = $request->input('plan', 'free');
        $isPaidPlan   = in_array($selectedPlan, ['pro', 'team'], true);

        // ── Auto-generate username ────────────────────────────────────
        $base = strtolower(preg_replace('/[^a-z0-9]/', '', strtolower($request->name)));
        if (empty($base)) $base = 'user';
        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }

        $user = User::create([
            'name'        => $request->name,
            'username'    => $username,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'avatar'      => null,
            'role'        => 'student',
            'is_active'   => true,
            'plan'        => 'free', // selalu free dulu sampai pembayaran selesai
            'preferences' => json_encode([
                'theme'         => 'light',
                'notifications' => true,
                'language'      => 'id',
                'timezone'      => 'Asia/Jakarta',
            ]),
        ]);

        Profile::create(['user_id' => $user->id]);
        $this->createDefaultWorkspace($user);
        event(new Registered($user));
        Auth::login($user);

        // ── Jika plan berbayar dipilih ─────────────────────────────────
        if ($isPaidPlan) {
            // Cek Midtrans siap
            $midtransReady = !empty(\App\Models\SystemSetting::get('midtrans_server_key'))
                && !empty(\App\Models\SystemSetting::get('midtrans_client_key'));

            if (!$midtransReady) {
                // Midtrans belum dikonfigurasi — beri tahu user dengan jelas
                return redirect()->route('dashboard')
                    ->with('warning',
                        '⚠️ Akun berhasil dibuat, tetapi paket ' . strtoupper($selectedPlan) .
                        ' belum dapat diaktifkan karena sistem pembayaran belum dikonfigurasi. ' .
                        'Kamu masuk dengan paket Gratis. Coba upgrade nanti dari halaman Profil.'
                    );
            }

            // Cari plan di database
            $plan = $this->resolvePlanBySlug($selectedPlan);

            if (!$plan) {
                // Plan ada di DB tapi slug tidak cocok — beri tahu user
                return redirect()->route('dashboard')
                    ->with('warning',
                        '⚠️ Akun berhasil dibuat, tetapi paket ' . strtoupper($selectedPlan) .
                        ' tidak ditemukan di sistem. Kamu masuk dengan paket Gratis. ' .
                        'Hubungi admin untuk mengaktifkan paket premium.'
                    );
            }

            // Buat pending subscription
            $pendingSub = UserSubscription::create([
                'user_id'              => $user->id,
                'subscription_plan_id' => $plan->id,
                'status'               => 'pending',
                'billing_cycle'        => 'monthly',
                'amount_paid'          => $plan->price_monthly,
                'starts_at'            => now(),
                'ends_at'              => now()->addMonth(),
            ]);

            return redirect()
                ->route('auth.onboarding-payment', ['plan' => $plan->id])
                ->with('success', 'Akun berhasil dibuat! Selesaikan pembayaran untuk mengaktifkan paket ' . $plan->name . '.');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Selamat datang di SFHUB, ' . $user->name . '! 🎉');
    }

    // ────────────────────────────────────────────────────────────────────
    // ONBOARDING PAYMENT PAGE
    // ────────────────────────────────────────────────────────────────────
    public function showOnboardingPayment(Request $request)
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        if ($plans->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('warning', 'Belum ada paket premium yang tersedia. Hubungi admin.');
        }

        $selectedPlan = $plans->firstWhere('id', (int) $request->query('plan'))
            ?? $plans->where('price_monthly', '>', 0)->first()
            ?? $plans->first();

        // Cek apakah user sudah punya active subscription
        $activeSubscription = UserSubscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->with('plan')
            ->first();

        return view('auth.onboarding-payment', compact('plans', 'selectedPlan', 'activeSubscription'));
    }

    // ────────────────────────────────────────────────────────────────────
    // LOGOUT
    // ────────────────────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // ────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ────────────────────────────────────────────────────────────────────
    private function createDefaultWorkspace(User $user): void
    {
        $workspaces = [
            ['name' => 'Creative Studio', 'type' => 'creative', 'color' => '#f97316', 'icon' => 'palette',        'order' => 1, 'is_active' => true],
            ['name' => 'Academic Hub',    'type' => 'academic', 'color' => '#3b82f6', 'icon' => 'graduation-cap', 'order' => 2, 'is_active' => true],
            ['name' => 'PKL / Work',      'type' => 'pkl',      'color' => '#10b981', 'icon' => 'briefcase',      'order' => 3, 'is_active' => true],
            ['name' => 'Personal',        'type' => 'personal', 'color' => '#8b5cf6', 'icon' => 'user',           'order' => 4, 'is_active' => true],
        ];
        foreach ($workspaces as $ws) {
            $user->workspaces()->create($ws);
        }
    }

    private function shouldRedirectToPremiumCheckout(User $user): bool
    {
        // Jika sudah punya active subscription → tidak redirect
        $hasActive = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->exists();
        if ($hasActive) return false;

        // Jika ada pending subscription → redirect
        return UserSubscription::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }

    private function resolvePlanBySlug(string $slug): ?SubscriptionPlan
    {
        if (empty($slug) || $slug === 'free') return null;

        return SubscriptionPlan::where('is_active', true)
            ->where(fn($q) =>
                $q->whereRaw('LOWER(slug) = ?', [strtolower($slug)])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($slug) . '%'])
            )
            ->orderBy('sort_order')
            ->first();
    }
}
