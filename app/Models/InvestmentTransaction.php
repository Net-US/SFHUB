<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class InvestmentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_id',
        'type',
        'amount',
        'transaction_date',
        'quantity',
        'price_per_unit',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'quantity' => 'decimal:8',
        'price_per_unit' => 'decimal:8',
    ];

    // Relationships
    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    // Scopes
    public function scopeBuys($query)
    {
        return $query->where('type', 'buy');
    }

    public function scopeSells($query)
    {
        return $query->where('type', 'sell');
    }

    public function scopeDividends($query)
    {
        return $query->where('type', 'dividend');
    }

    public function scopeFees($query)
    {
        return $query->where('type', 'fee');
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('transaction_date', $date);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // Methods
    public function getFormattedAmount()
    {
        $prefix = match ($this->type) {
            'buy' => '-',
            'sell' => '+',
            'dividend' => '+',
            'fee' => '-',
            default => '',
        };

        return $prefix . ' Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getAmountColor()
    {
        return match ($this->type) {
            'buy' => 'text-red-600',
            'sell' => 'text-green-600',
            'dividend' => 'text-blue-600',
            'fee' => 'text-red-600',
            default => 'text-gray-600',
        };
    }

    public function getTypeLabel()
    {
        return match ($this->type) {
            'buy' => 'Pembelian',
            'sell' => 'Penjualan',
            'dividend' => 'Dividen',
            'fee' => 'Biaya',
            default => ucfirst($this->type),
        };
    }

    public function getTypeIcon()
    {
        return match ($this->type) {
            'buy' => '📥',
            'sell' => '📤',
            'dividend' => '💰',
            'fee' => '💸',
            default => '📄',
        };
    }

    public function getTotalValue()
    {
        if ($this->quantity && $this->price_per_unit) {
            return $this->quantity * $this->price_per_unit;
        }
        
        return $this->amount;
    }

    public function getFormattedTotalValue()
    {
        return 'Rp ' . number_format($this->getTotalValue(), 0, ',', '.');
    }

    public function getFormattedDate()
    {
        return Carbon::parse($this->transaction_date)->format('d M Y');
    }

    public function getFormattedQuantity()
    {
        if ($this->quantity) {
            return number_format($this->quantity, 8, '.', ',');
        }
        
        return '-';
    }

    public function getFormattedPricePerUnit()
    {
        if ($this->price_per_unit) {
            return 'Rp ' . number_format($this->price_per_unit, 2, ',', '.');
        }
        
        return '-';
    }

    public function isBuy()
    {
        return $this->type === 'buy';
    }

    public function isSell()
    {
        return $this->type === 'sell';
    }

    public function isDividend()
    {
        return $this->type === 'dividend';
    }

    public function isFee()
    {
        return $this->type === 'fee';
    }

    public function getNetAmount()
    {
        return match ($this->type) {
            'buy' => -$this->amount,
            'sell' => $this->amount,
            'dividend' => $this->amount,
            'fee' => -$this->amount,
            default => 0,
        };
    }

    public function getFormattedNetAmount()
    {
        $netAmount = $this->getNetAmount();
        $prefix = $netAmount >= 0 ? '+' : '';
        
        return $prefix . ' Rp ' . number_format(abs($netAmount), 0, ',', '.');
    }

    public function getNetAmountColor()
    {
        $netAmount = $this->getNetAmount();
        if ($netAmount > 0) return 'text-green-600';
        if ($netAmount < 0) return 'text-red-600';
        return 'text-gray-600';
    }
}
