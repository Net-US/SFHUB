<?php

namespace App\Http\Controllers;

use App\Models\FinanceAccount;
use App\Models\Asset;
use App\Models\Debt;
use App\Models\IndodaxConnection;
use App\Models\InvestmentInstrument;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    // ── HALAMAN PROFIL & SETTING ──────────────────────────────────────────
    public function edit()
    {
        /** @var User $user */
        $user = Auth::user();

        $userId = $user->id;

        $notifications = Notification::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $unreadCount = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        // Statistik ringkas — query langsung agar IDE tidak complain
        $stats = [
            'finance_accounts' => FinanceAccount::where('user_id', $userId)->where('is_active', true)->count(),
            'assets'           => Asset::where('user_id', $userId)->count(),
            'debts_active'     => Debt::where('user_id', $userId)->where('status', 'active')->count(),
            'investments'      => InvestmentInstrument::where('user_id', $userId)->count(),
            'total_net_worth'  => FinanceAccount::where('user_id', $userId)->where('is_active', true)->sum('balance'),
        ];

        $indodaxConnection = IndodaxConnection::where('user_id', $userId)
            ->where('provider', 'indodax')
            ->first();

        $indodaxAccount = FinanceAccount::where('user_id', $userId)
            ->where('type', 'investment')
            ->where('name', 'Indodax')
            ->first();

        $indodaxInstruments = collect();
        if ($indodaxAccount) {
            $indodaxInstruments = InvestmentInstrument::where('user_id', $userId)
                ->where('type', 'crypto')
                ->where('finance_account_id', $indodaxAccount->id)
                ->orderByDesc('total_quantity')
                ->get();
        }

        return view('profile.edit', compact(
            'user',
            'notifications',
            'unreadCount',
            'stats',
            'indodaxConnection',
            'indodaxAccount',
            'indodaxInstruments'
        ));
    }

    // ── UPDATE PROFIL DASAR ───────────────────────────────────────────────
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'avatar'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'occupation' => 'nullable|string|max:100',
            'phone'      => 'nullable|string|max:20',
            'location'   => 'nullable|string|max:100',
            'bio'        => 'nullable|string|max:500',
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                $oldPath = get_image_base_path() . '/' . $user->avatar;
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            // Ensure avatars directory exists
            $folderCheck = ensure_image_directory('avatars');
            if (!$folderCheck['success']) {
                Log::error('Avatar upload failed', ['error' => $folderCheck['error']]);
                return response()->json([
                    'success' => false,
                    'message' => $folderCheck['error'],
                ], 500);
            }

            $avatarPath = $folderCheck['path'];

            // Move uploaded file
            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            try {
                $file->move($avatarPath, $filename);
                $data['avatar'] = 'images/avatars/' . $filename;
                Log::info('Avatar saved', ['path' => $data['avatar']]);
            } catch (\Exception $e) {
                Log::error('Failed to save avatar', ['error' => $e->getMessage()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan avatar: ' . $e->getMessage(),
                ], 500);
            }
        }

        // Unset avatar key jika tidak ada file upload (agar tidak overwrite dengan null)
        if (!$request->hasFile('avatar')) {
            unset($data['avatar']);
        }

        $user->update($data);

        if ($request->expectsJson()) {
            /** @var User $fresh */
            $fresh = $user->fresh() ?? $user;
            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diupdate.',
                'user'    => [
                    'name'       => $fresh->name,
                    'email'      => $fresh->email,
                    'occupation' => $fresh->occupation ?? null,
                    'location'   => $fresh->location ?? null,
                ],
            ]);
        }

        return back()->with('success', 'Profil berhasil diupdate.');
    }

    // ── UPDATE PASSWORD ───────────────────────────────────────────────────
    public function updatePassword(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai.',
            ], 422);
        }

        User::where('id', $user->id)->update(['password' => Hash::make($request->password)]);  // @phpstan-ignore-line

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Password berhasil diubah.']);
        }

        return back()->with('success', 'Password berhasil diubah.');
    }

    // ── UPDATE PREFERENCES / SETTINGS ────────────────────────────────────
    public function updatePreferences(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'theme'                  => 'nullable|in:light,dark,system',
            'language'               => 'nullable|in:id,en',
            'currency'               => 'nullable|string|max:5',
            'timezone'               => 'nullable|string|max:50',
            'notif_budget_alert'     => 'boolean',
            'notif_debt_reminder'    => 'boolean',
            'notif_investment_alert' => 'boolean',
            'notif_weekly_report'    => 'boolean',
            'dashboard_widgets'      => 'nullable|array',
            'sidebar_collapsed'      => 'boolean',
        ]);

        // Merge dengan preferences lama agar tidak menghapus field lain
        $current = $user->preferences ?? [];
        $merged  = array_merge($current, $data);

        $user->update(['preferences' => $merged]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil disimpan.',
        ]);
    }

    // ── DELETE ACCOUNT ────────────────────────────────────────────────────
    public function destroy(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'password' => 'required',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password tidak sesuai.',
            ], 422);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return response()->json([
            'success'  => true,
            'message'  => 'Akun berhasil dihapus.',
            'redirect' => route('home'),
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════
    // NOTIFICATION ENDPOINTS
    // ═════════════════════════════════════════════════════════════════════

    // ── GET SEMUA NOTIFIKASI (AJAX) ───────────────────────────────────────
    public function getNotifications(Request $request)
    {
        $query = Notification::where('user_id', Auth::id())
            ->orderByDesc('created_at');

        if ($request->filter === 'unread') {
            $query->where('is_read', false);
        }

        $notifications = $query->limit(50)->get();
        $unreadCount   = Notification::where('user_id', Auth::id())->where('is_read', false)->count();

        return response()->json([
            'success'       => true,
            'notifications' => $notifications->map(fn($n) => $this->formatNotif($n)),
            'unread_count'  => $unreadCount,
        ]);
    }

    // ── TANDAI SATU NOTIFIKASI DIBACA ─────────────────────────────────────
    public function markRead(int $id)
    {
        $notif = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notif->markAsRead();

        return response()->json([
            'success'     => true,
            'unread_count' => Notification::where('user_id', Auth::id())->where('is_read', false)->count(),
        ]);
    }

    // ── TANDAI SEMUA DIBACA ───────────────────────────────────────────────
    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true, 'unread_count' => 0]);
    }

    // ── HAPUS SATU NOTIFIKASI ─────────────────────────────────────────────
    public function destroyNotification(int $id)
    {
        Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail()
            ->delete();

        return response()->json(['success' => true]);
    }

    // ── HAPUS SEMUA NOTIFIKASI ────────────────────────────────────────────
    public function clearAllNotifications()
    {
        Notification::where('user_id', Auth::id())->delete();
        return response()->json(['success' => true, 'message' => 'Semua notifikasi dihapus.']);
    }

    // ── FORMAT NOTIFIKASI ─────────────────────────────────────────────────
    private function formatNotif(Notification $n): array
    {
        $iconMap = [
            'system'    => ['icon' => 'fa-gear',             'color' => 'text-blue-500',   'bg' => 'bg-blue-100 dark:bg-blue-900/30'],
            'deadline'  => ['icon' => 'fa-clock',            'color' => 'text-rose-500',   'bg' => 'bg-rose-100 dark:bg-rose-900/30'],
            'reminder'  => ['icon' => 'fa-bell',             'color' => 'text-amber-500',  'bg' => 'bg-amber-100 dark:bg-amber-900/30'],
            'financial' => ['icon' => 'fa-wallet',           'color' => 'text-emerald-500', 'bg' => 'bg-emerald-100 dark:bg-emerald-900/30'],
            'academic'  => ['icon' => 'fa-graduation-cap',   'color' => 'text-purple-500', 'bg' => 'bg-purple-100 dark:bg-purple-900/30'],
            'investment' => ['icon' => 'fa-chart-line',       'color' => 'text-emerald-500', 'bg' => 'bg-emerald-100 dark:bg-emerald-900/30'],
            'budget'    => ['icon' => 'fa-triangle-exclamation', 'color' => 'text-rose-500', 'bg' => 'bg-rose-100 dark:bg-rose-900/30'],
        ];

        $style = $iconMap[$n->type] ?? ['icon' => 'fa-circle-info', 'color' => 'text-stone-500', 'bg' => 'bg-stone-100 dark:bg-stone-800'];

        return [
            'id'         => $n->id,
            'title'      => $n->title,
            'message'    => $n->message,
            'type'       => $n->type,
            'is_read'    => $n->is_read,
            'time_ago'   => $n->created_at->diffForHumans(),
            'created_at' => $n->created_at->isoFormat('D MMM YYYY, HH:mm'),
            'action_url' => $n->getActionUrl(),
            'icon'       => $style['icon'],
            'icon_color' => $style['color'],
            'icon_bg'    => $style['bg'],
        ];
    }
}
