<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Budget;
use Carbon\Carbon;

class FinanceDashboard extends Component
{
    public float $totalBalance = 0;
    public float $monthlyIncome = 0;
    public float $monthlyExpense = 0;
    public array $expenseByCategory = [];
    public array $budgets = [];
    public array $recentTransactions = [];

    public function mount()
    {
        $this->loadFinanceData();
    }

    public function loadFinanceData()
    {
        $userId = auth()->id();

        // Total balance dari semua accounts
        $this->totalBalance = \App\Models\FinanceAccount::where('user_id', $userId)->sum('balance');

        // Monthly stats
        $this->monthlyIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        $this->monthlyExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        // Expense by category
        $this->expenseByCategory = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        // Budgets with usage
        $this->budgets = Budget::where('user_id', $userId)
            ->active()
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'category' => $b->category,
                'amount' => $b->amount,
                'spent' => $b->spent_amount,
                'percentage' => $b->getUsagePercentage(),
                'status' => $b->isOverBudget() ? 'over' : ($b->isNearLimit() ? 'warning' : 'ok'),
            ])
            ->toArray();

        // Recent transactions
        $this->recentTransactions = Transaction::where('user_id', $userId)
            ->with('account')
            ->latest()
            ->take(10)
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard.finance-dashboard');
    }
}
