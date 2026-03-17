<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Debt;

class DebtsDashboard extends Component
{
    public float $totalPayable = 0;
    public float $totalReceivable = 0;
    public array $debts = [];
    public array $upcoming = [];

    public function mount()
    {
        $this->loadDebtsData();
    }

    public function loadDebtsData()
    {
        $userId = auth()->id();

        $debts = Debt::where('user_id', $userId)->get();

        $this->totalPayable = $debts->where('type', 'payable')->sum('remaining_amount');
        $this->totalReceivable = $debts->where('type', 'receivable')->sum('remaining_amount');

        $this->debts = $debts->map(fn($d) => [
            'id' => $d->id,
            'debtor' => $d->debtor,
            'type' => $d->type,
            'amount' => $d->amount,
            'remaining' => $d->getRemainingAmount(),
            'progress' => $d->getProgressPercentage(),
            'due_date' => $d->due_date->format('d M Y'),
            'days_remaining' => $d->getDaysRemaining(),
            'is_overdue' => $d->isOverdue(),
        ])->toArray();

        $this->upcoming = $debts
            ->where('status', '!=', 'paid')
            ->where('due_date', '<=', now()->addDays(7))
            ->sortBy('due_date')
            ->values()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard.debts-dashboard');
    }
}
