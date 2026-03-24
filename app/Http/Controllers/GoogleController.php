<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/**
 * Google OAuth Controller
 *
 * REQUIREMENTS:
 *   composer require laravel/socialite
 *
 * config/services.php:
 *   'google' => [
 *       'client_id'     => env('GOOGLE_CLIENT_ID'),
 *       'client_secret' => env('GOOGLE_CLIENT_SECRET'),
 *       'redirect'      => env('GOOGLE_REDIRECT_URI', url('/auth/google/callback')),
 *   ],
 *
 * .env:
 *   GOOGLE_CLIENT_ID=
 *   GOOGLE_CLIENT_SECRET=
 *   GOOGLE_REDIRECT_URI=https://domainmu.com/auth/google/callback
 *
 * routes/web.php (dalam middleware guest):
 *   Route::get('/auth/google',          [GoogleController::class, 'redirect'])->name('auth.google');
 *   Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
 */
class GoogleController extends Controller
{
    /**
     * Redirect ke Google OAuth screen.
     */
    public function redirect(): RedirectResponse
    {
        // Guard: jika Google OAuth belum dikonfigurasi, redirect balik dengan pesan
        if (empty(config('services.google.client_id'))) {
            return redirect()->route('login')
                ->with('error', 'Login Google belum dikonfigurasi. Hubungi admin.');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback dari Google.
     * — Jika user sudah ada (by email) → login langsung
     * — Jika user baru → buat akun, buat profile, redirect ke dashboard
     */
    public function callback(): RedirectResponse
    {
        if (empty(config('services.google.client_id'))) {
            return redirect()->route('login')
                ->with('error', 'Login Google belum dikonfigurasi.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Login Google gagal. Silakan coba lagi.');
        }

        // Cari user yang sudah ada berdasarkan google_id atau email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Update google_id jika belum ada (user daftar via email dulu)
            if (empty($user->google_id)) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            // Cek akun aktif
            if (isset($user->is_active) && !$user->is_active) {
                return redirect()->route('login')
                    ->with('error', 'Akun kamu telah dinonaktifkan.');
            }

            Auth::login($user, remember: true);

        } else {
            // Buat akun baru dari Google
            $baseUsername = strtolower(str_replace(' ', '', $googleUser->getName() ?: 'user'));
            $username     = $baseUsername;
            $i = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $i++;
            }

            $user = User::create([
                'name'             => $googleUser->getName(),
                'username'         => $username,
                'email'            => $googleUser->getEmail(),
                'password'         => bcrypt(\Illuminate\Support\Str::random(24)), // random, user can reset
                'google_id'        => $googleUser->getId(),
                'avatar'           => $googleUser->getAvatar(),
                'role'             => 'student',
                'is_active'        => true,
                'plan'             => 'free',
                'email_verified_at'=> now(), // Google email sudah terverifikasi
                'preferences'      => json_encode([
                    'theme'         => 'light',
                    'notifications' => true,
                    'language'      => 'id',
                    'timezone'      => 'Asia/Jakarta',
                ]),
            ]);

            // Buat profile
            Profile::create(['user_id' => $user->id]);

            // Buat default workspaces
            $this->createDefaultWorkspaces($user);

            Auth::login($user, remember: true);
        }

        // Redirect berdasarkan role
        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.index'));
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Berhasil masuk dengan Google. Selamat datang, ' . $user->name . '!');
    }

    private function createDefaultWorkspaces(User $user): void
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
}
