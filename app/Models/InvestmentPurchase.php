<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'instrument_id',
        'purchase_date',
        'amount',
        'quantity',
        'price_per_unit',
        'fees',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'amount' => 'decimal:2',
        'quantity' => 'decimal:8',
        'price_per_unit' => 'decimal:8',
        'fees' => 'decimal:2',
    ];

    // Relationships
    public function instrument()
    {
        return $this->belongsTo(InvestmentInstrument::class, 'instrument_id');
    }

    // Methods
    public function getCurrentValue()
    {
        return $this->quantity * $this->instrument->current_price;
    }

    public function getProfitLoss()
    {
        $currentValue = $this->getCurrentValue();
        return $currentValue - $this->amount;
    }

    public function getProfitLossPercentage()
    {
        $profitLoss = $this->getProfitLoss();
        return ($profitLoss / $this->amount) * 100;
    }
}
