<?php

namespace App\Http\Controllers;

use App\Models\FinanceAccount;
use App\Models\Transaction;
use App\Models\SavingsGoal;
use App\Models\Budget;
use App\Models\InvestmentInstrument;
use App\Models\PendingNeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // OWNERSHIP HELPERS
    // Cara ini TIDAK memerlukan Policy sama sekali.
    // Logikanya: tambahkan where('user_id', Auth::id()) di setiap
    // query → jika record bukan milik user yang login, Eloquent
    // akan lempar ModelNotFoundException → otomatis jadi 404.
    // User lain tidak tahu apakah record itu ada atau bukan.
    // ─────────────────────────────────────────────────────────────
    private function ownAccount(int $id): FinanceAccount
    {
        return FinanceAccount::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    private function ownTransaction(int $id): Transaction
    {
        return Transaction::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    private function ownSavingsGoal(int $id): SavingsGoal
    {
        return SavingsGoal::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    private function ownBudget(int $id): Budget
    {
        return Budget::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    private function ownPendingNeed(int $id): PendingNeed
    {
        return PendingNeed::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    // ─────────────────────────────────────────────────────────────
    // DASHBOARD INDEX
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $userId = Auth::id();

        $accounts = FinanceAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('type')
            ->get();

        $totalLiquid     = $accounts->whereNotIn('type', ['investment', 'receivable'])->sum('balance');
        $totalInvestment = $accounts->where('type', 'investment')->sum('balance');
        $totalReceivable = $accounts->where('type', 'receivable')->sum('balance');
        $totalAll        = $accounts->sum('balance');

        $blockedAmount   = PendingNeed::where('user_id', $userId)->where('status', 'pending')->sum('amount');
        $availableLiquid = max(0, $totalLiquid - $blockedAmount);

        $monthlyIncome  = Transaction::where('user_id', $userId)->income()->thisMonth()->sum('amount');
        $monthlyExpense = Transaction::where('user_id', $userId)->expense()->thisMonth()->sum('amount');

        $recentTransactions = Transaction::where('user_id', $userId)
            ->with(['account', 'toAccount'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $savingsGoals = SavingsGoal::where('user_id', $userId)
            ->where('status', 'active')
            ->with('account')
            ->orderByDesc('created_at')
            ->get();

        $budgets = Budget::where('user_id', $userId)
            ->active()->monthly()
            ->orderByDesc('amount')
            ->get()
            ->each(fn($b) => $b->syncSpent())
            ->map(fn($b) => $b->refresh());

        $pendingNeeds = PendingNeed::where('user_id', $userId)
            ->where('status', 'pending')
            ->with('account')
            ->orderByDesc('created_at')
            ->get();

        $totalPending = $pendingNeeds->sum('amount');

        $expenseByCategory = Transaction::where('user_id', $userId)
            ->expense()->thisMonth()
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $indodaxAccount = $accounts->first(function ($account) {
            return $account->type === 'investment' && strtolower($account->name) === 'indodax';
        });

        $indodaxInstruments = collect();
        if ($indodaxAccount) {
            $indodaxInstruments = InvestmentInstrument::where('user_id', $userId)
                ->where('type', 'crypto')
                ->where('finance_account_id', $indodaxAccount->id)
                ->orderByDesc('total_quantity')
                ->get();
        }

        $indodaxTotalValue = $indodaxInstruments->sum(function ($instrument) {
            return (float) $instrument->total_quantity * (float) $instrument->current_price;
        });

        return view('dashboard.finance', compact(
            'accounts',
            'totalLiquid',
            'totalInvestment',
            'totalReceivable',
            'totalAll',
            'availableLiquid',
            'blockedAmount',
            'monthlyIncome',
            'monthlyExpense',
            'recentTransactions',
            'savingsGoals',
            'budgets',
            'pendingNeeds',
            'totalPending',
            'expenseByCategory',
            'indodaxAccount',
            'indodaxInstruments',
            'indodaxTotalValue',
        ));
    }

    // ─────────────────────────────────────────────────────────────
    // FINANCE ACCOUNTS
    // ─────────────────────────────────────────────────────────────
    public function storeAccount(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'type'           => 'required|in:cash,bank,e-wallet,investment,receivable',
            'account_number' => 'nullable|string|max:50',
            'balance'        => 'required|numeric|min:0',
            'currency'       => 'nullable|string|max:10',
            'color'          => 'nullable|string|max:20',
            'notes'          => 'nullable|string|max:500',
        ]);

        $data['user_id']  = Auth::id();
        $data['currency'] = $data['currency'] ?? 'IDR';
        $data['color']    = $data['color']    ?? '#6b7280';

        $account = FinanceAccount::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil ditambahkan.',
            'account' => $this->formatAccount($account),
        ]);
    }

    public function updateAccount(Request $request, int $id)
    {
        $account = $this->ownAccount($id); // ← cek kepemilikan

        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'color'          => 'nullable|string|max:20',
            'notes'          => 'nullable|string|max:500',
            'is_active'      => 'boolean',
        ]);

        $account->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil diupdate.',
            'account' => $this->formatAccount($account->fresh()),
        ]);
    }

    public function updateAccountBalance(Request $request, int $id)
    {
        $account = $this->ownAccount($id);

        $data = $request->validate(['balance' => 'required|numeric|min:0']);
        $account->update(['balance' => $data['balance']]);

        return response()->json([
            'success'     => true,
            'message'     => 'Saldo berhasil diupdate.',
            'new_balance' => (float) $account->fresh()->balance,
        ]);
    }

    public function destroyAccount(int $id)
    {
        $account = $this->ownAccount($id);

        if ($account->transactions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak dapat dihapus karena sudah memiliki transaksi.',
            ], 422);
        }

        $account->delete();

        return response()->json(['success' => true, 'message' => 'Akun berhasil dihapus.']);
    }

    // ─────────────────────────────────────────────────────────────
    // TRANSACTIONS
    // ─────────────────────────────────────────────────────────────
    public function storeTransaction(Request $request)
    {
        $data = $request->validate([
            'type'               => 'required|in:income,expense,transfer',
            'finance_account_id' => 'required|integer',
            'to_account_id'      => 'required_if:type,transfer|nullable|integer',
            'amount'             => 'required|numeric|min:1',
            'fee'                => 'nullable|numeric|min:0',
            'category'           => 'nullable|string|max:100',
            'description'        => 'nullable|string|max:255',
            'transaction_date'   => 'required|date',
            'notes'              => 'nullable|string|max:500',
        ]);

        // Cek kepemilikan akun sumber
        $this->ownAccount((int) $data['finance_account_id']);

        if ($data['type'] === 'transfer') {
            if ((int) $data['finance_account_id'] === (int) ($data['to_account_id'] ?? 0)) {
                return response()->json(['success' => false, 'message' => 'Akun asal dan tujuan tidak boleh sama.'], 422);
            }
            // Cek kepemilikan akun tujuan
            $this->ownAccount((int) $data['to_account_id']);
        }

        DB::transaction(function () use ($data) {
            $data['user_id'] = Auth::id();
            $data['fee']     = $data['fee'] ?? 0;

            $transaction = Transaction::create($data);
            $transaction->processBalance();

            if ($data['type'] === 'expense' && !empty($data['category'])) {
                Budget::where('user_id', Auth::id())
                    ->active()->where('category', $data['category'])
                    ->get()->each(fn($b) => $b->syncSpent());
            }
        });

        return response()->json(['success' => true, 'message' => 'Transaksi berhasil disimpan.']);
    }

    public function destroyTransaction(int $id)
    {
        $transaction = $this->ownTransaction($id);

        DB::transaction(function () use ($transaction) {
            $transaction->reverseBalance();
            $transaction->delete();

            if ($transaction->type === 'expense' && $transaction->category) {
                Budget::where('user_id', Auth::id())
                    ->active()->where('category', $transaction->category)
                    ->get()->each(fn($b) => $b->syncSpent());
            }
        });

        return response()->json(['success' => true, 'message' => 'Transaksi berhasil dihapus.']);
    }

    public function getTransactions(Request $request)
    {
        $query = Transaction::where('user_id', Auth::id()) // ← filter milik user ini
            ->with(['account', 'toAccount'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        if ($request->filled('type'))     $query->where('type', $request->type);
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('account'))  $query->where('finance_account_id', $request->account);
        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('transaction_date', $year)->whereMonth('transaction_date', $month);
        }

        $transactions = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $transactions->through(fn($t) => $this->formatTransaction($t)),
            'meta'    => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // TRANSFER
    // ─────────────────────────────────────────────────────────────
    public function storeTransfer(Request $request)
    {
        $data = $request->validate([
            'from_account_id'  => 'required|integer',
            'to_account_id'    => 'required|integer',
            'amount'           => 'required|numeric|min:1',
            'fee'              => 'nullable|numeric|min:0',
            'description'      => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'notes'            => 'nullable|string|max:500',
        ]);

        if ((int) $data['from_account_id'] === (int) $data['to_account_id']) {
            return response()->json(['success' => false, 'message' => 'Akun asal dan tujuan tidak boleh sama.'], 422);
        }

        $this->ownAccount((int) $data['from_account_id']);
        $this->ownAccount((int) $data['to_account_id']);

        DB::transaction(function () use ($data) {
            $transaction = Transaction::create([
                'user_id'            => Auth::id(),
                'finance_account_id' => $data['from_account_id'],
                'to_account_id'      => $data['to_account_id'],
                'type'               => 'transfer',
                'amount'             => $data['amount'],
                'fee'                => $data['fee'] ?? 0,
                'category'           => 'Transfer',
                'description'        => $data['description'] ?? 'Transfer antar akun',
                'transaction_date'   => $data['transaction_date'],
                'notes'              => $data['notes'] ?? null,
            ]);
            $transaction->processBalance();
        });

        return response()->json(['success' => true, 'message' => 'Transfer berhasil dicatat.']);
    }

    // ─────────────────────────────────────────────────────────────
    // SAVINGS GOALS
    // ─────────────────────────────────────────────────────────────
    public function storeSavingsGoal(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'finance_account_id' => 'nullable|integer',
            'target_amount'      => 'required|numeric|min:1',
            'daily_saving'       => 'nullable|numeric|min:0',
            'target_date'        => 'nullable|date|after:today',
            'notes'              => 'nullable|string|max:500',
        ]);

        if (!empty($data['finance_account_id'])) {
            $this->ownAccount((int) $data['finance_account_id']);
        }

        $data['user_id']    = Auth::id();
        $data['start_date'] = today();

        $goal = SavingsGoal::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Target tabungan berhasil ditambahkan.',
            'goal'    => $this->formatGoal($goal->load('account')),
        ]);
    }

    public function updateSavingsGoal(Request $request, int $id)
    {
        $goal = $this->ownSavingsGoal($id);

        $data = $request->validate([
            'name'               => 'sometimes|required|string|max:100',
            'finance_account_id' => 'nullable|integer',
            'target_amount'      => 'sometimes|required|numeric|min:1',
            'current_amount'     => 'sometimes|numeric|min:0',
            'daily_saving'       => 'nullable|numeric|min:0',
            'target_date'        => 'nullable|date',
            'notes'              => 'nullable|string|max:500',
            'status'             => 'sometimes|in:active,completed,cancelled',
        ]);

        $goal->update($data);

        if ($goal->fresh()->isCompleted()) {
            $goal->update(['status' => 'completed']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Target tabungan berhasil diupdate.',
            'goal'    => $this->formatGoal($goal->fresh()->load('account')),
        ]);
    }

    public function destroySavingsGoal(int $id)
    {
        $this->ownSavingsGoal($id)->delete();
        return response()->json(['success' => true, 'message' => 'Target tabungan berhasil dihapus.']);
    }

    // ─────────────────────────────────────────────────────────────
    // BUDGETS
    // ─────────────────────────────────────────────────────────────
    public function storeBudget(Request $request)
    {
        $data = $request->validate([
            'category'        => 'required|string|max:100',
            'amount'          => 'required|numeric|min:1',
            'period'          => 'required|in:monthly,weekly',
            'alert_threshold' => 'nullable|integer|min:1|max:100',
        ]);

        $exists = Budget::where('user_id', Auth::id())
            ->where('category', $data['category'])
            ->where('period', $data['period'])
            ->where('is_active', true)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Budget untuk kategori "' . $data['category'] . '" sudah ada.',
            ], 422);
        }

        $data['user_id']         = Auth::id();
        $data['alert_threshold'] = $data['alert_threshold'] ?? 80;

        $budget = Budget::create($data);
        $budget->syncSpent();

        return response()->json([
            'success' => true,
            'message' => 'Budget berhasil ditambahkan.',
            'budget'  => $this->formatBudget($budget->fresh()),
        ]);
    }

    public function updateBudget(Request $request, int $id)
    {
        $budget = $this->ownBudget($id);

        $data = $request->validate([
            'amount'          => 'sometimes|required|numeric|min:1',
            'alert_threshold' => 'nullable|integer|min:1|max:100',
            'is_active'       => 'boolean',
        ]);

        $budget->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Budget berhasil diupdate.',
            'budget'  => $this->formatBudget($budget->fresh()),
        ]);
    }

    public function destroyBudget(int $id)
    {
        $this->ownBudget($id)->delete();
        return response()->json(['success' => true, 'message' => 'Budget berhasil dihapus.']);
    }

    // ─────────────────────────────────────────────────────────────
    // PENDING NEEDS
    // ─────────────────────────────────────────────────────────────
    public function storePendingNeed(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'finance_account_id' => 'required|integer',
            'amount'             => 'required|numeric|min:1',
            'category'           => 'nullable|string|max:100',
            'notes'              => 'nullable|string|max:500',
        ]);

        $this->ownAccount((int) $data['finance_account_id']);

        $data['user_id'] = Auth::id();
        $need = PendingNeed::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Kebutuhan ditambahkan. Saldo tersedia otomatis berkurang.',
            'need'    => $this->formatPendingNeed($need->load('account')),
        ]);
    }

    public function purchasePendingNeed(Request $request, int $id)
    {
        $need = $this->ownPendingNeed($id);

        if ($need->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Kebutuhan ini sudah bukan pending.'], 422);
        }

        $request->validate([
            'transaction_date' => 'nullable|date',
            'notes'            => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($need, $request) {
            $transaction = Transaction::create([
                'user_id'            => Auth::id(),
                'finance_account_id' => $need->finance_account_id,
                'type'               => 'expense',
                'amount'             => $need->amount,
                'category'           => $need->category ?? 'Kebutuhan',
                'description'        => $need->name,
                'transaction_date'   => $request->transaction_date ?? today(),
                'notes'              => $request->notes ?? $need->notes,
                'fee'                => 0,
            ]);
            $transaction->processBalance();

            $need->update(['status' => 'purchased', 'transaction_id' => $transaction->id]);

            Budget::where('user_id', Auth::id())
                ->active()->where('category', $need->category ?? 'Kebutuhan')
                ->get()->each(fn($b) => $b->syncSpent());
        });

        return response()->json(['success' => true, 'message' => 'Pembelian dicatat sebagai transaksi pengeluaran.']);
    }

    public function cancelPendingNeed(int $id)
    {
        $need = $this->ownPendingNeed($id);

        if ($need->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Kebutuhan ini sudah bukan pending.'], 422);
        }

        $need->update(['status' => 'cancelled']);

        return response()->json(['success' => true, 'message' => 'Kebutuhan dibatalkan. Saldo tersedia kembali normal.']);
    }

    public function destroyPendingNeed(int $id)
    {
        $need = $this->ownPendingNeed($id);
        if ($need->status === 'pending') {
            $need->update(['status' => 'cancelled']);
        }
        $need->delete();

        return response()->json(['success' => true, 'message' => 'Kebutuhan berhasil dihapus.']);
    }

    // ─────────────────────────────────────────────────────────────
    // SUMMARY (AJAX)
    // ─────────────────────────────────────────────────────────────
    public function getSummary()
    {
        $userId   = Auth::id();
        $accounts = FinanceAccount::where('user_id', $userId)->where('is_active', true)->get();

        $liquid     = $accounts->whereNotIn('type', ['investment', 'receivable'])->sum('balance');
        $investment = $accounts->where('type', 'investment')->sum('balance');
        $receivable = $accounts->where('type', 'receivable')->sum('balance');
        $blocked    = PendingNeed::where('user_id', $userId)->where('status', 'pending')->sum('amount');

        return response()->json([
            'summary' => [
                'total_liquid'     => (float) $liquid,
                'total_investment' => (float) $investment,
                'total_receivable' => (float) $receivable,
                'total_all'        => (float) ($liquid + $investment + $receivable),
                'blocked_amount'   => (float) $blocked,
                'available_liquid' => (float) max(0, $liquid - $blocked),
                'monthly_income'   => (float) Transaction::where('user_id', $userId)->income()->thisMonth()->sum('amount'),
                'monthly_expense'  => (float) Transaction::where('user_id', $userId)->expense()->thisMonth()->sum('amount'),
            ],
            'accounts' => $accounts->map(fn($a) => $this->formatAccount($a)),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE FORMATTERS
    // ─────────────────────────────────────────────────────────────
    private function formatAccount(FinanceAccount $a): array
    {
        return [
            'id'                => $a->id,
            'name'              => $a->name,
            'type'              => $a->type,
            'type_label'        => $a->getTypeLabel(),
            'type_icon'         => $a->getTypeIcon(),
            'account_number'    => $a->account_number,
            'balance'           => (float) $a->balance,
            'balance_fmt'       => $a->getFormattedBalance(),
            'available_balance' => $a->getAvailableBalance(),
            'currency'          => $a->currency,
            'color'             => $a->color,
            'notes'             => $a->notes,
            'is_active'         => $a->is_active,
            'is_liquid'         => $a->isLiquid(),
        ];
    }

    private function formatTransaction(Transaction $t): array
    {
        return [
            'id'               => $t->id,
            'type'             => $t->type,
            'amount'           => (float) $t->amount,
            'fee'              => (float) $t->fee,
            'category'         => $t->category,
            'description'      => $t->description,
            'transaction_date' => $t->transaction_date?->format('Y-m-d'),
            'date_fmt'         => $t->transaction_date?->isoFormat('D MMM YYYY'),
            'notes'            => $t->notes,
            'amount_color'     => $t->getAmountColor(),
            'type_icon'        => $t->getTypeIcon(),
            'account_name'     => $t->account?->name,
            'account_color'    => $t->account?->color,
            'to_account_name'  => $t->toAccount?->name,
        ];
    }

    private function formatGoal(SavingsGoal $g): array
    {
        return [
            'id'              => $g->id,
            'name'            => $g->name,
            'target_amount'   => (float) $g->target_amount,
            'current_amount'  => (float) $g->current_amount,
            'daily_saving'    => (float) $g->daily_saving,
            'progress'        => $g->getProgressPercentage(),
            'days_remaining'  => $g->getDaysRemaining(),
            'daily_needed'    => $g->getDailyNeeded(),
            'target_date'     => $g->target_date?->format('Y-m-d'),
            'target_date_fmt' => $g->target_date?->isoFormat('D MMM YY'),
            'status'          => $g->status,
            'account_name'    => $g->account?->name,
            'account_color'   => $g->account?->color,
            'is_completed'    => $g->isCompleted(),
        ];
    }

    private function formatBudget(Budget $b): array
    {
        return [
            'id'              => $b->id,
            'category'        => $b->category,
            'amount'          => (float) $b->amount,
            'spent_amount'    => (float) $b->spent_amount,
            'remaining'       => $b->getRemainingAmount(),
            'usage_pct'       => round($b->getUsagePercentage(), 1),
            'alert_threshold' => $b->alert_threshold,
            'is_over_budget'  => $b->isOverBudget(),
            'is_near_limit'   => $b->isNearLimit(),
            'status_badge'    => $b->getStatusBadge(),
            'status_icon'     => $b->getStatusIcon(),
            'period'          => $b->period,
        ];
    }

    private function formatPendingNeed(PendingNeed $n): array
    {
        return [
            'id'            => $n->id,
            'name'          => $n->name,
            'amount'        => (float) $n->amount,
            'category'      => $n->category,
            'notes'         => $n->notes,
            'status'        => $n->status,
            'status_badge'  => $n->getStatusBadge(),
            'account_id'    => $n->finance_account_id,
            'account_name'  => $n->account?->name,
            'account_color' => $n->account?->color,
        ];
    }
}
