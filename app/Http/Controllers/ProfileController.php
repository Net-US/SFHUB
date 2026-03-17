<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show the form for editing user profile
     */
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);

        return view('profile.edit', compact('user', 'profile'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'university' => ['nullable', 'string', 'max:255'],
            'major' => ['nullable', 'string', 'max:255'],
            'semester' => ['nullable', 'string', 'max:50'],
            'skills' => ['nullable', 'string'],
            'bio' => ['nullable', 'string', 'max:500'],
            'portfolio_url' => ['nullable', 'url'],
        ]);

        // Update user basic info
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);
        }

        // Update or create profile
        $profileData = [
            'university' => $request->university,
            'major' => $request->major,
            'semester' => $request->semester,
            'skills' => $request->skills,
            'bio' => $request->bio,
            'portfolio_url' => $request->portfolio_url,
            'social_links' => json_encode([
                'twitter' => $request->twitter,
                'instagram' => $request->instagram,
                'linkedin' => $request->linkedin,
                'github' => $request->github,
                'behance' => $request->behance,
                'dribbble' => $request->dribbble,
            ]),
        ];

        if ($user->profile) {
            $user->profile->update($profileData);
        } else {
            $profileData['user_id'] = $user->id;
            Profile::create($profileData);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'theme' => ['required', 'in:light,dark,system'],
            'notifications' => ['required', 'boolean'],
            'language' => ['required', 'in:id,en'],
            'timezone' => ['required', 'timezone'],
        ]);

        $preferences = $user->preferences ?? [];

        $user->update([
            'preferences' => json_encode(array_merge($preferences, [
                'theme' => $request->theme,
                'notifications' => $request->notifications,
                'language' => $request->language,
                'timezone' => $request->timezone,
            ])),
        ]);

        return back()->with('success', 'Preferensi berhasil diperbarui.');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
