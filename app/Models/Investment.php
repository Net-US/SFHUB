<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'initial_amount',
        'current_value',
        'purchase_date',
        'description',
        'notes',
        'broker',
        'account_number',
        'status',
    ];

    protected $casts = [
        'initial_amount' => 'decimal:2',
        'current_value' => 'decimal:2',
        'purchase_date' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InvestmentTransaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function getProfitLoss()
    {
        return $this->current_value - $this->initial_amount;
    }

    public function getProfitLossPercentage()
    {
        if ($this->initial_amount == 0) {
            return 0;
        }

        return ($this->getProfitLoss() / $this->initial_amount) * 100;
    }

    public function getTotalInvested()
    {
        return $this->transactions()
            ->where('type', 'buy')
            ->sum('amount');
    }

    public function getTotalWithdrawn()
    {
        return $this->transactions()
            ->where('type', 'sell')
            ->sum('amount');
    }

    public function getTotalDividends()
    {
        return $this->transactions()
            ->where('type', 'dividend')
            ->sum('amount');
    }

    public function getTotalFees()
    {
        return $this->transactions()
            ->where('type', 'fee')
            ->sum('amount');
    }

    public function getNetProfitLoss()
    {
        $totalInvested = $this->getTotalInvested();
        $totalWithdrawn = $this->getTotalWithdrawn();
        $totalDividends = $this->getTotalDividends();
        $totalFees = $this->getTotalFees();

        return ($totalWithdrawn + $totalDividends) - ($totalInvested + $totalFees);
    }

    public function getAgeInDays()
    {
        return Carbon::parse($this->purchase_date)->diffInDays(Carbon::now());
    }

    public function getAnnualizedReturn()
    {
        if ($this->initial_amount == 0 || $this->getAgeInDays() == 0) {
            return 0;
        }

        $years = $this->getAgeInDays() / 365;
        $totalReturn = $this->getNetProfitLoss();
        $annualizedReturn = ($totalReturn / $this->initial_amount) * 100;

        return pow(1 + ($annualizedReturn / 100), 1 / $years) - 1;
    }

    public function updateCurrentValue()
    {
        $totalInvested = $this->getTotalInvested();
        $totalWithdrawn = $this->getTotalWithdrawn();
        $totalDividends = $this->getTotalDividends();

        $newValue = $totalInvested - $totalWithdrawn + $totalDividends;
        
        $this->update(['current_value' => $newValue]);
    }

    public function addTransaction($type, $amount, $date = null, $quantity = null, $pricePerUnit = null, $notes = '')
    {
        return $this->transactions()->create([
            'type' => $type,
            'amount' => $amount,
            'transaction_date' => $date ?? today(),
            'quantity' => $quantity,
            'price_per_unit' => $pricePerUnit,
            'notes' => $notes,
        ]);
    }

    public function getFormattedInitialAmount()
    {
        return 'Rp ' . number_format($this->initial_amount, 0, ',', '.');
    }

    public function getFormattedCurrentValue()
    {
        return 'Rp ' . number_format($this->current_value, 0, ',', '.');
    }

    public function getFormattedProfitLoss()
    {
        $profitLoss = $this->getProfitLoss();
        $prefix = $profitLoss >= 0 ? '+' : '';
        return $prefix . 'Rp ' . number_format($profitLoss, 0, ',', '.');
    }

    public function getProfitLossColor()
    {
        $profitLoss = $this->getProfitLoss();
        if ($profitLoss > 0) return 'text-green-600';
        if ($profitLoss < 0) return 'text-red-600';
        return 'text-gray-600';
    }

    public function getInvestmentTypeIcon()
    {
        return match ($this->type) {
            'Stock' => '📈',
            'Bond' => '📊',
            'Mutual Fund' => '💼',
            'ETF' => '🏛️',
            'Crypto' => '₿',
            'Real Estate' => '🏠',
            'Gold' => '🥇',
            'Savings' => '🏦',
            default => '💰',
        };
    }

    public function markAsSold()
    {
        $this->update(['status' => 'sold']);
    }

    public function markAsActive()
    {
        $this->update(['status' => 'active']);
    }
}
