<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DonationController extends Controller
{
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $plan = $user->plan;

        $amounts = [
            'pro' => 49000,
            'team' => 99000,
        ];

        return view('donation.show', [
            'plan' => $plan,
            'amount' => $amounts[$plan] ?? 0,
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'donation_amount' => 'required|numeric|min:1000',
            'payment_method' => 'required|in:bank_transfer,ovo,gopay,dana',
        ]);

        // Simpan informasi donasi (dalam aplikasi nyata, ini akan terintegrasi dengan payment gateway)
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'plan_activated_at' => now(),
            'donation_amount' => $request->donation_amount,
            'donation_method' => $request->payment_method,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Terima kasih atas donasi Anda! Paket premium telah diaktifkan.');
    }
}
