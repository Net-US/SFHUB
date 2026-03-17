<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\InvestmentInstrument;

class InvestmentsDashboard extends Component
{
    public float $totalInvested = 0;
    public float $totalCurrentValue = 0;
    public float $totalProfitLoss = 0;
    public array $investments = [];
    public array $byType = [];

    public function mount()
    {
        $this->loadInvestmentsData();
    }

    public function loadInvestmentsData()
    {
        $userId = auth()->id();

        $instruments = InvestmentInstrument::where('user_id', $userId)->get();

        $this->totalInvested = $instruments->sum('total_invested');
        $this->totalCurrentValue = $instruments->sum(fn($i) => $i->getCurrentValue());
        $this->totalProfitLoss = $this->totalCurrentValue - $this->totalInvested;

        $this->investments = $instruments->map(fn($i) => [
            'id' => $i->id,
            'name' => $i->name,
            'symbol' => $i->symbol,
            'type' => $i->type,
            'invested' => $i->total_invested,
            'current_value' => $i->getCurrentValue(),
            'profit_loss' => $i->getProfitLoss(),
            'profit_loss_percentage' => $i->getProfitLossPercentage(),
            'color' => $i->getPerformanceColor(),
        ])->toArray();

        $this->byType = $instruments->groupBy('type')
            ->map(fn($items) => [
                'invested' => $items->sum('total_invested'),
                'current_value' => $items->sum(fn($i) => $i->getCurrentValue()),
                'count' => $items->count(),
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard.investments-dashboard');
    }
}
