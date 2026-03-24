<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\SystemSetting;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function __construct()
    {
        \Midtrans\Config::$serverKey = SystemSetting::get('midtrans_server_key');
        \Midtrans\Config::$clientKey = SystemSetting::get('midtrans_client_key');
        \Midtrans\Config::$isProduction = !SystemSetting::get('midtrans_sandbox', true);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    public function createTransaction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $user = Auth::user();

        $existingActive = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->first();

        if ($existingActive) {
            return response()->json([
                'message' => 'Kamu sudah memiliki subscription aktif.',
            ], 422);
        }

        $amount = $validated['billing_cycle'] === 'monthly'
            ? $plan->price_monthly
            : $plan->price_yearly;

        $orderId = 'SUB-' . $user->id . '-' . time();

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'pending',
            'billing_cycle' => $validated['billing_cycle'],
            'amount_paid' => $amount,
            'starts_at' => now(),
            'ends_at' => $validated['billing_cycle'] === 'monthly' ? now()->addMonth() : now()->addYear(),
            'transaction_id' => $orderId,
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ],
            'item_details' => [
                [
                    'id' => 'PLAN-' . $plan->id,
                    'price' => (int) $amount,
                    'quantity' => 1,
                    'name' => $plan->name . ' (' . ucfirst($validated['billing_cycle']) . ')',
                ],
            ],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            return response()->json([
                'snap_token' => $snapToken,
                'subscription_id' => $subscription->id,
                'order_id' => $orderId,
                'client_key' => \Midtrans\Config::$clientKey,
            ]);
        } catch (\Exception $e) {
            $subscription->update(['status' => 'failed']);
            Log::error('Midtrans error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Gagal membuat transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        $serverKey = SystemSetting::get('midtrans_server_key');
        $signatureKey = hash(
            'sha512',
            ($payload['order_id'] ?? '') .
                ($payload['status_code'] ?? '') .
                ($payload['gross_amount'] ?? '') .
                $serverKey
        );

        if (!isset($payload['signature_key']) || $signatureKey !== $payload['signature_key']) {
            Log::warning('Invalid Midtrans signature for order: ' . ($payload['order_id'] ?? 'unknown'));
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = $payload['order_id'];
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? 'accept';

        $subscription = UserSubscription::where('transaction_id', $orderId)->first();

        if (!$subscription) {
            Log::warning('Subscription not found for order: ' . $orderId);
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($transactionStatus === 'capture' && $fraudStatus === 'accept') {
            $subscription->update(['status' => 'active']);
        } elseif ($transactionStatus === 'settlement') {
            $subscription->update(['status' => 'active']);
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'], true)) {
            $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        } elseif ($transactionStatus === 'pending') {
            $subscription->update(['status' => 'pending']);
        }

        Log::info("Midtrans webhook: order={$orderId} status={$transactionStatus}");

        return response()->json(['message' => 'OK']);
    }

    public function finish(Request $request)
    {
        $status = $request->get('transaction_status');

        if (in_array($status, ['capture', 'settlement'], true)) {
            return redirect()->route('dashboard')->with('success', 'Subscription berhasil diaktifkan!');
        }

        return redirect()->back()->with('error', 'Pembayaran tidak berhasil. Silakan coba lagi.');
    }
}
