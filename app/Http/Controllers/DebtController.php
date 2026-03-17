<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DebtController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // OWNERSHIP HELPERS
    // ─────────────────────────────────────────────────────────────
    private function ownDebt(int $id): Debt
    {
        return Debt::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    private function ownPayment(int $id): DebtPayment
    {
        // Join ke debt supaya bisa cek user_id
        return DebtPayment::whereHas('debt', fn($q) => $q->where('user_id', Auth::id()))
            ->where('id', $id)
            ->firstOrFail();
    }

    // ─────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $userId = Auth::id();

        $debts = Debt::where('user_id', $userId)
            ->with('payments')
            ->orderByRaw("FIELD(status,'active','pending','paid')")
            ->orderBy('due_date')
            ->get();

        // ── Ringkasan ──────────────────────────────────────────
        // Hutang yang harus dibayar (debt_type = borrower)
        $myDebts       = $debts->where('debt_type', 'borrower');
        $totalDebt     = $myDebts->sum('total_amount');
        $totalPaid     = $myDebts->sum(fn($d) => $d->total_amount - $d->remaining_amount);
        $totalRemaining= $myDebts->sum('remaining_amount');

        // Piutang yang harus ditagih (debt_type = lender)
        $myReceivables      = $debts->where('debt_type', 'lender');
        $totalReceivable    = $myReceivables->sum('total_amount');
        $totalReceived      = $myReceivables->sum(fn($d) => $d->total_amount - $d->remaining_amount);
        $totalStillOwed     = $myReceivables->sum('remaining_amount');

        // Overdue
        $overdueDebts       = $debts->filter(fn($d) => $d->isOverdue() && $d->debt_type === 'borrower');
        $overdueReceivables = $debts->filter(fn($d) => $d->isOverdue() && $d->debt_type === 'lender');

        // Jatuh tempo terdekat (7 hari ke depan)
        $upcomingDue = $debts
            ->where('debt_type', 'borrower')
            ->whereNotIn('status', ['paid'])
            ->filter(fn($d) => $d->due_date && $d->due_date->diffInDays(today(), false) <= 7 && $d->due_date >= today())
            ->sortBy('due_date')
            ->take(3);

        // Semua riwayat pembayaran (terbaru)
        $recentPayments = DebtPayment::whereHas('debt', fn($q) => $q->where('user_id', $userId))
            ->with('debt')
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('dashboard.debts', compact(
            'debts',
            'myDebts',
            'myReceivables',
            'totalDebt',
            'totalPaid',
            'totalRemaining',
            'totalReceivable',
            'totalReceived',
            'totalStillOwed',
            'overdueDebts',
            'overdueReceivables',
            'upcomingDue',
            'recentPayments',
        ));
    }

    // ─────────────────────────────────────────────────────────────
    // STORE — catat hutang/piutang baru
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'creditor_name'  => 'required|string|max:100',
            'debt_type'      => 'required|in:borrower,lender',
            'total_amount'   => 'required|numeric|min:1',
            'start_date'     => 'required|date',
            'due_date'       => 'nullable|date|after_or_equal:start_date',
            'interest_rate'  => 'nullable|numeric|min:0|max:100',
            'description'    => 'nullable|string|max:255',
            'notes'          => 'nullable|string|max:500',
        ]);

        $data['user_id']         = Auth::id();
        $data['remaining_amount']= $data['total_amount'];
        $data['interest_rate']   = $data['interest_rate'] ?? 0;
        $data['status']          = 'active';

        $debt = Debt::create($data);

        return response()->json([
            'success' => true,
            'message' => ($data['debt_type'] === 'borrower' ? 'Hutang' : 'Piutang') . ' berhasil ditambahkan.',
            'debt'    => $this->formatDebt($debt->load('payments')),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // UPDATE — edit data hutang/piutang
    // ─────────────────────────────────────────────────────────────
    public function update(Request $request, int $id)
    {
        $debt = $this->ownDebt($id);

        $data = $request->validate([
            'creditor_name' => 'sometimes|required|string|max:100',
            'due_date'      => 'nullable|date',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'description'   => 'nullable|string|max:255',
            'notes'         => 'nullable|string|max:500',
            'status'        => 'sometimes|in:active,pending,paid',
        ]);

        $debt->update($data);

        // Jika status diubah jadi paid, pastikan remaining = 0
        if (($data['status'] ?? null) === 'paid') {
            $debt->update(['remaining_amount' => 0]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate.',
            'debt'    => $this->formatDebt($debt->fresh()->load('payments')),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // DESTROY — hapus hutang beserta riwayat pembayaran
    // ─────────────────────────────────────────────────────────────
    public function destroy(int $id)
    {
        $debt = $this->ownDebt($id);

        DB::transaction(function () use ($debt) {
            $debt->payments()->delete();
            $debt->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Hutang/Piutang berhasil dihapus.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // ADD PAYMENT — bayar sebagian/lunas hutang
    // ─────────────────────────────────────────────────────────────
    public function addPayment(Request $request, int $debtId)
    {
        $debt = $this->ownDebt($debtId);

        if ($debt->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Hutang ini sudah lunas.',
            ], 422);
        }

        $data = $request->validate([
            'amount'         => 'required|numeric|min:1',
            'payment_date'   => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes'          => 'nullable|string|max:500',
        ]);

        // Bayar tidak boleh melebihi sisa hutang
        $payAmount = min((float) $data['amount'], (float) $debt->remaining_amount);

        DB::transaction(function () use ($debt, $data, $payAmount) {
            DebtPayment::create([
                'debt_id'        => $debt->id,
                'payment_date'   => $data['payment_date'],
                'amount'         => $payAmount,
                'payment_method' => $data['payment_method'] ?? null,
                'notes'          => $data['notes'] ?? null,
            ]);

            $newRemaining = (float) $debt->remaining_amount - $payAmount;
            $newStatus    = $newRemaining <= 0 ? 'paid' : $debt->status;

            $debt->update([
                'remaining_amount' => max(0, $newRemaining),
                'status'           => $newStatus,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dicatat.' . ($debt->fresh()->status === 'paid' ? ' Hutang sudah LUNAS!' : ''),
            'debt'    => $this->formatDebt($debt->fresh()->load('payments')),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // DELETE PAYMENT — hapus satu record pembayaran
    // ─────────────────────────────────────────────────────────────
    public function destroyPayment(int $paymentId)
    {
        $payment = $this->ownPayment($paymentId);
        $debt    = $payment->debt;

        DB::transaction(function () use ($debt, $payment) {
            // Kembalikan sisa hutang
            $debt->increment('remaining_amount', $payment->amount);

            // Jika statusnya paid, kembalikan ke active
            if ($debt->status === 'paid') {
                $debt->update(['status' => 'active']);
            }

            $payment->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dihapus.',
            'debt'    => $this->formatDebt($debt->fresh()->load('payments')),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // MARK AS PAID — tandai lunas sekaligus
    // ─────────────────────────────────────────────────────────────
    public function markAsPaid(Request $request, int $id)
    {
        $debt = $this->ownDebt($id);

        $data = $request->validate([
            'payment_date'   => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes'          => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($debt, $data) {
            if ($debt->remaining_amount > 0) {
                DebtPayment::create([
                    'debt_id'        => $debt->id,
                    'payment_date'   => $data['payment_date'],
                    'amount'         => $debt->remaining_amount,
                    'payment_method' => $data['payment_method'] ?? null,
                    'notes'          => $data['notes'] ?? 'Pelunasan penuh',
                ]);
            }

            $debt->update([
                'remaining_amount' => 0,
                'status'           => 'paid',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Hutang berhasil ditandai lunas!',
            'debt'    => $this->formatDebt($debt->fresh()->load('payments')),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET PAYMENTS — ambil riwayat pembayaran satu hutang (AJAX)
    // ─────────────────────────────────────────────────────────────
    public function getPayments(int $debtId)
    {
        $debt     = $this->ownDebt($debtId);
        $payments = $debt->payments()->orderByDesc('payment_date')->get();

        return response()->json([
            'success'  => true,
            'payments' => $payments->map(fn($p) => $this->formatPayment($p)),
            'debt'     => $this->formatDebt($debt->load('payments')),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE FORMATTERS
    // ─────────────────────────────────────────────────────────────
    private function formatDebt(Debt $d): array
    {
        $paid         = (float) $d->total_amount - (float) $d->remaining_amount;
        $progressPct  = $d->total_amount > 0 ? round(($paid / $d->total_amount) * 100, 1) : 0;
        $daysRemaining= $d->due_date ? (int) Carbon::now()->diffInDays($d->due_date, false) : null;

        return [
            'id'                  => $d->id,
            'creditor_name'       => $d->creditor_name,
            'debt_type'           => $d->debt_type,
            'debt_type_label'     => $d->debt_type === 'borrower' ? 'Hutang' : 'Piutang',
            'total_amount'        => (float) $d->total_amount,
            'total_amount_fmt'    => 'Rp ' . number_format($d->total_amount, 0, ',', '.'),
            'remaining_amount'    => (float) $d->remaining_amount,
            'remaining_amount_fmt'=> 'Rp ' . number_format($d->remaining_amount, 0, ',', '.'),
            'paid_amount'         => $paid,
            'paid_amount_fmt'     => 'Rp ' . number_format($paid, 0, ',', '.'),
            'progress_pct'        => $progressPct,
            'interest_rate'       => (float) $d->interest_rate,
            'accrued_interest'    => round($d->calculateInterest(), 0),
            'total_with_interest' => round($d->getTotalWithInterest(), 0),
            'start_date'          => $d->start_date?->format('Y-m-d'),
            'start_date_fmt'      => $d->start_date?->isoFormat('D MMM YYYY'),
            'due_date'            => $d->due_date?->format('Y-m-d'),
            'due_date_fmt'        => $d->due_date?->isoFormat('D MMM YYYY'),
            'days_remaining'      => $daysRemaining,
            'is_overdue'          => $d->isOverdue(),
            'description'         => $d->description,
            'notes'               => $d->notes,
            'status'              => $d->status,
            'payments_count'      => $d->payments->count(),
            'payments'            => $d->payments->map(fn($p) => $this->formatPayment($p))->toArray(),
        ];
    }

    private function formatPayment(DebtPayment $p): array
    {
        return [
            'id'             => $p->id,
            'debt_id'        => $p->debt_id,
            'amount'         => (float) $p->amount,
            'amount_fmt'     => 'Rp ' . number_format($p->amount, 0, ',', '.'),
            'payment_date'   => $p->payment_date?->format('Y-m-d'),
            'payment_date_fmt'=> $p->payment_date?->isoFormat('D MMM YYYY'),
            'payment_method' => $p->payment_method,
            'notes'          => $p->notes,
        ];
    }
}
