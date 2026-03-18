<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $loginField => $request->login,
            'password'  => $request->password,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            if (isset($user->is_active) && !$user->is_active) {
                Auth::logout();
                return back()->withErrors(['login' => 'Akun Anda telah dinonaktifkan.'])->onlyInput('login');
            }
            $request->session()->regenerate();

            // Redirect berdasarkan role
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.index'));
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'login' => 'Email/username atau password salah.',
        ])->onlyInput('login');
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:student,freelancer,both,entrepreneur'],
            'plan' => ['required', 'in:free,pro,team'],
            'avatar' => ['nullable', 'image', 'max:2048'], // 2MB max
            'terms' => ['required', 'accepted'],
        ]);

        // Handle avatar upload
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        // Auto-generate username from name
        $baseUsername = strtolower(str_replace(' ', '', $request->name));
        $username     = $baseUsername;
        $counter      = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter++;
        }

        $user = User::create([
            'name'      => $request->name,
            'username'  => $username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'avatar'    => $avatarPath,
            'role'      => $request->role,
            'is_active' => true,
            'plan'      => $request->plan,
            'preferences' => json_encode([
                'theme'         => 'light',
                'notifications' => true,
                'language'      => 'id',
                'timezone'      => 'Asia/Jakarta',
            ]),
        ]);

        // Create user profile
        Profile::create([
            'user_id' => $user->id,
        ]);

        // Create default workspace
        $this->createDefaultWorkspace($user);

        event(new Registered($user));

        Auth::login($user);

        // Redirect to donation page if user chooses pro/team plan
        if (in_array($request->plan, ['pro', 'team'])) {
            return redirect()->route('donation.show')
                ->with('success', 'Akun Anda berhasil dibuat! Silakan lanjutkan dengan donasi untuk mengaktifkan paket premium.');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Selamat datang di StudentHub! Akun Anda berhasil dibuat.');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Create default workspace for new user
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
}
