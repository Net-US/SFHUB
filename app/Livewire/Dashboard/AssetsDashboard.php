<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Asset;

class AssetsDashboard extends Component
{
    public float $totalValue = 0;
    public float $totalPurchaseValue = 0;
    public array $assets = [];
    public array $byCategory = [];

    public function mount()
    {
        $this->loadAssetsData();
    }

    public function loadAssetsData()
    {
        $userId = auth()->id();

        $assets = Asset::where('user_id', $userId)->get();

        $this->totalValue = $assets->sum('current_value');
        $this->totalPurchaseValue = $assets->sum('purchase_value');
        $this->assets = $assets->toArray();

        $this->byCategory = $assets->groupBy('type')
            ->map(fn($items) => [
                'count' => $items->count(),
                'value' => $items->sum('current_value'),
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard.assets-dashboard');
    }
}
