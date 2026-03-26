<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransController extends Controller
{
    // ── Inisialisasi Midtrans config ─────────────────────────────────────
    private function bootMidtrans(): bool
    {
        $serverKey = SystemSetting::get('midtrans_server_key');
        $clientKey = SystemSetting::get('midtrans_client_key');

        if (empty($serverKey) || empty($clientKey)) {
            return false;
        }

        \Midtrans\Config::$serverKey    = $serverKey;
        \Midtrans\Config::$clientKey    = $clientKey;
        \Midtrans\Config::$isProduction = !(bool) SystemSetting::get('midtrans_sandbox', true);
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;

        return true;
    }

    // ────────────────────────────────────────────────────────────────────
    // CREATE TRANSACTION (POST /subscribe)
    // ────────────────────────────────────────────────────────────────────
    public function createTransaction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id'       => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        // ── Cek konfigurasi Midtrans ─────────────────────────────────
        if (!$this->bootMidtrans()) {
            return response()->json([
                'success' => false,
                'message' => 'Sistem pembayaran belum dikonfigurasi. Silakan hubungi admin.',
                'code'    => 'MIDTRANS_NOT_CONFIGURED',
            ], 503);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        // ── Cek sudah punya active subscription ─────────────────────
        $existingActive = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->first();

        if ($existingActive) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah memiliki subscription aktif hingga ' .
                    $existingActive->ends_at->isoFormat('D MMMM YYYY') . '.',
                'code'    => 'ALREADY_SUBSCRIBED',
            ], 422);
        }

        $amount = $validated['billing_cycle'] === 'monthly'
            ? (float) $plan->price_monthly
            : (float) $plan->price_yearly;

        // ── Free plan (harga 0) ──────────────────────────────────────
        if ($amount <= 0) {
            // Hapus pending subscription lama jika ada
            UserSubscription::where('user_id', $user->id)
                ->where('status', 'pending')
                ->delete();

            UserSubscription::create([
                'user_id'              => $user->id,
                'subscription_plan_id' => $plan->id,
                'status'               => 'active',
                'billing_cycle'        => $validated['billing_cycle'],
                'amount_paid'          => 0,
                'starts_at'            => now(),
                'ends_at'              => $validated['billing_cycle'] === 'monthly'
                    ? now()->addMonth()
                    : now()->addYear(),
                'transaction_id'       => 'FREE-' . $user->id . '-' . time(),
            ]);

            $this->syncUserPlan($user, $plan);

            return response()->json([
                'success'   => true,
                'message'   => 'Paket gratis berhasil diaktifkan!',
                'free_plan' => true,
                'redirect'  => route('dashboard'),
            ]);
        }

        // ── Buat atau dapatkan pending subscription ──────────────────
        // Gunakan pending yang sudah ada jika ada, atau buat baru
        $orderId = 'SUB-' . $user->id . '-' . time();

        // Hapus pending lama untuk plan yang sama
        UserSubscription::where('user_id', $user->id)
            ->where('status', 'pending')
            ->delete();

        $subscription = UserSubscription::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'status'               => 'pending',
            'billing_cycle'        => $validated['billing_cycle'],
            'amount_paid'          => $amount,
            'starts_at'            => now(),
            'ends_at'              => $validated['billing_cycle'] === 'monthly'
                ? now()->addMonth()
                : now()->addYear(),
            'transaction_id'       => $orderId,
        ]);

        // ── Buat Midtrans Snap Token ─────────────────────────────────
        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone ?? '',
            ],
            'item_details' => [[
                'id'       => 'PLAN-' . $plan->id,
                'price'    => (int) $amount,
                'quantity' => 1,
                'name'     => $plan->name . ' (' . ucfirst($validated['billing_cycle']) . ')',
            ]],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            return response()->json([
                'success'         => true,
                'snap_token'      => $snapToken,
                'subscription_id' => $subscription->id,
                'order_id'        => $orderId,
                'client_key'      => \Midtrans\Config::$clientKey,
            ]);

        } catch (\Exception $e) {
            // Hapus pending yang baru dibuat agar tidak garbage
            $subscription->delete();

            Log::error('Midtrans createTransaction error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'amount'  => $amount,
            ]);

            // Berikan pesan error yang informatif
            $errMsg = $this->humanizeMidtransError($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $errMsg,
                'code'    => 'MIDTRANS_ERROR',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // ────────────────────────────────────────────────────────────────────
    // WEBHOOK (POST /midtrans/webhook)
    // ────────────────────────────────────────────────────────────────────
    public function webhook(Request $request): JsonResponse
    {
        $payload   = $request->all();
        $serverKey = SystemSetting::get('midtrans_server_key', '');

        // Verifikasi signature
        $signatureKey = hash('sha512',
            ($payload['order_id']      ?? '') .
            ($payload['status_code']   ?? '') .
            ($payload['gross_amount']  ?? '') .
            $serverKey
        );

        if (!isset($payload['signature_key']) || $signatureKey !== $payload['signature_key']) {
            Log::warning('Invalid Midtrans signature for order: ' . ($payload['order_id'] ?? 'unknown'));
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId           = $payload['order_id'];
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus       = $payload['fraud_status'] ?? 'accept';

        $subscription = UserSubscription::where('transaction_id', $orderId)
            ->with(['user', 'plan'])
            ->first();

        if (!$subscription) {
            Log::warning('Subscription not found for order: ' . $orderId);
            return response()->json(['message' => 'Order not found'], 404);
        }

        if (($transactionStatus === 'capture' && $fraudStatus === 'accept') || $transactionStatus === 'settlement') {
            $subscription->update(['status' => 'active']);
            $this->syncUserPlan($subscription->user, $subscription->plan);
            Log::info("Subscription activated: order={$orderId} user={$subscription->user_id}");
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'], true)) {
            $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            $this->downgradeUserIfNoActive($subscription->user_id);
        } elseif ($transactionStatus === 'pending') {
            $subscription->update(['status' => 'pending']);
        }

        return response()->json(['message' => 'OK']);
    }

    // ────────────────────────────────────────────────────────────────────
    // FINISH REDIRECT (GET /midtrans/finish)
    // ────────────────────────────────────────────────────────────────────
    public function finish(Request $request)
    {
        $status = $request->get('transaction_status');

        if (in_array($status, ['capture', 'settlement'], true)) {
            return redirect()->route('dashboard')
                ->with('success', '🎉 Subscription berhasil diaktifkan! Selamat menikmati fitur premium.');
        }

        if ($status === 'pending') {
            return redirect()->route('dashboard')
                ->with('info', 'Pembayaran sedang diproses. Subscription akan aktif setelah konfirmasi.');
        }

        return redirect()->route('auth.onboarding-payment')
            ->with('error', 'Pembayaran tidak berhasil atau dibatalkan. Silakan coba lagi.');
    }

    // ────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ────────────────────────────────────────────────────────────────────
    private function syncUserPlan(User $user, SubscriptionPlan $plan): void
    {
        $slug = strtolower((string) ($plan->slug ?? ''));
        if (!in_array($slug, ['pro', 'team', 'premium'], true)) {
            $slug = 'pro'; // fallback
        }
        $user->update(['plan' => $slug]);
        Log::info("User plan synced: user_id={$user->id} plan={$slug}");
    }

    private function downgradeUserIfNoActive(int $userId): void
    {
        $hasActive = UserSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->exists();

        if (!$hasActive) {
            User::where('id', $userId)->update(['plan' => 'free']);
            Log::info("User downgraded to free: user_id={$userId}");
        }
    }

    /**
     * Ubah pesan error Midtrans menjadi lebih user-friendly
     */
    private function humanizeMidtransError(string $message): string
    {
        $msg = strtolower($message);

        if (str_contains($msg, '401') || str_contains($msg, 'unauthorized') || str_contains($msg, 'access denied')) {
            return 'Konfigurasi Midtrans tidak valid (401 Unauthorized). Pastikan Server Key dan Client Key sudah benar di Admin Settings.';
        }
        if (str_contains($msg, '400') || str_contains($msg, 'bad request')) {
            return 'Data transaksi tidak valid. Silakan coba lagi atau hubungi admin.';
        }
        if (str_contains($msg, 'curl') || str_contains($msg, 'connection') || str_contains($msg, 'timeout')) {
            return 'Koneksi ke Midtrans gagal. Periksa koneksi internet server.';
        }
        if (str_contains($msg, '500') || str_contains($msg, 'internal server')) {
            return 'Midtrans mengalami gangguan sementara. Coba lagi dalam beberapa menit.';
        }

        return 'Gagal membuat transaksi: ' . Str::limit($message, 100);
    }
}
