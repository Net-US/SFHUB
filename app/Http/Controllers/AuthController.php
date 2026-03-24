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

        // Throttle: cek max_login_attempts dari settings
        $maxAttempts = (int) (\App\Models\SystemSetting::get('max_login_attempts', 5));
        $key = 'login_attempts_' . $request->ip();
        $attempts = cache()->get($key, 0);
        if ($attempts >= $maxAttempts) {
            return back()->withErrors(['login' => 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.'])->onlyInput('login');
        }

        $loginField  = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$loginField => $request->login, 'password' => $request->password];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Reset attempts on success
            cache()->forget($key);

            $user = Auth::user();

            if (isset($user->is_active) && !$user->is_active) {
                Auth::logout();
                return back()->withErrors(['login' => 'Akun Anda telah dinonaktifkan.'])->onlyInput('login');
            }

            $request->session()->regenerate();

            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.index'));
            }

            // Jika pilih plan premium saat daftar tapi belum bayar, arahkan ke checkout
            if ($this->shouldRedirectToPremiumCheckout($user)) {
                $plan = $this->resolvePlanBySlug((string) $user->plan);
                if ($plan) {
                    return redirect()->route('auth.onboarding-payment', ['plan' => $plan->id])
                        ->with('info', 'Akun premium kamu belum aktif. Selesaikan pembayaran untuk melanjutkan.');
                }
            }

            return redirect()->intended(route('dashboard'));
        }

        // Increment failed attempts with 15-minute TTL
        cache()->put($key, $attempts + 1, now()->addMinutes(15));

        return back()->withErrors([
            'login' => 'Email/username atau password salah.',
        ])->onlyInput('login');
    }

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

        // Check if user registration is enabled
        if (!\App\Models\SystemSetting::get('user_registration', true)) {
            return back()->withErrors(['email' => 'Pendaftaran user sedang ditutup.']);
        }

        $selectedPlan = $request->input('plan', 'free');

        // Auto-generate unique username
        $base = strtolower(preg_replace('/[^a-z0-9]/', '', strtolower($request->name)));
        if (empty($base)) $base = 'user';
        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }

        $user = User::create([
            'name'      => $request->name,
            'username'  => $username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'avatar'    => null,
            'role'      => 'student',
            'is_active' => true,
            'plan'      => $selectedPlan,
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

        // Jika plan berbayar, arahkan ke onboarding payment
        if (in_array($selectedPlan, ['pro', 'team'], true)) {
            $plan = SubscriptionPlan::where('is_active', true)
                ->where(fn($q) => $q->where('slug', $selectedPlan)->orWhereRaw('LOWER(name) = ?', [$selectedPlan]))
                ->first();

            if ($plan) {
                return redirect()->route('auth.onboarding-payment', ['plan' => $plan->id])
                    ->with('success', 'Akun berhasil dibuat! Lanjutkan pembayaran untuk aktivasi premium.');
            }

            // Jika plan di DB belum ada, arahkan ke dashboard dengan warning
            return redirect()->route('dashboard')
                ->with('warning', 'Akun dibuat dengan paket gratis. Paket premium belum tersedia, hubungi admin.');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Selamat datang di SFHUB, ' . $user->name . '!');
    }

    public function showOnboardingPayment(Request $request)
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        if ($plans->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('warning', 'Belum ada paket premium yang tersedia.');
        }

        $selectedPlan = $plans->firstWhere('id', (int) $request->query('plan')) ?? $plans->first();

        return view('auth.onboarding-payment', compact('plans', 'selectedPlan'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

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
        if (!in_array((string) $user->plan, ['pro', 'team'], true)) {
            return false;
        }
        return !UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->exists();
    }

    private function resolvePlanBySlug(string $slug): ?SubscriptionPlan
    {
        return SubscriptionPlan::where('is_active', true)
            ->where(fn($q) => $q->where('slug', $slug)->orWhereRaw('LOWER(name) = ?', [strtolower($slug)]))
            ->first();
    }
}
