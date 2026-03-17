<?php
// app/Models/InvestmentInstrument.php  (UPDATED)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentInstrument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'finance_account_id',   // ← BARU: link ke akun Indodax / Ajaib / dll
        'name',
        'symbol',
        'type',
        'notes',
        'current_price',
        'total_invested',
        'total_quantity',
        'average_price',
    ];

    protected $casts = [
        'current_price'  => 'decimal:8',
        'total_invested' => 'decimal:2',
        'total_quantity' => 'decimal:8',
        'average_price'  => 'decimal:8',
    ];

    // ── Relationships ─────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Platform / akun tempat instrumen ini disimpan.
     * Contoh: Indodax (untuk BTC, ETH), Ajaib (untuk BBCA, IHSG)
     */
    public function financeAccount()
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function purchases()
    {
        return $this->hasMany(InvestmentPurchase::class, 'instrument_id');
    }

    // ── Methods ───────────────────────────────────────────────────────
    public function getCurrentValue()
    {
        return $this->total_quantity * $this->current_price;
    }

    public function getProfitLoss()
    {
        return $this->getCurrentValue() - $this->total_invested;
    }

    public function getProfitLossPercentage()
    {
        if ($this->total_invested == 0) return 0;
        return ($this->getProfitLoss() / $this->total_invested) * 100;
    }

    public function getPerformanceColor()
    {
        $pct = $this->getProfitLossPercentage();
        if ($pct > 0)  return 'text-emerald-600';
        if ($pct < 0)  return 'text-rose-600';
        return 'text-gray-600';
    }

    public function updateAveragePrice()
    {
        $totalAmount   = $this->purchases()->sum('amount');
        $totalQuantity = $this->purchases()->sum('quantity');

        if ($totalQuantity > 0) {
            $this->update([
                'average_price'  => $totalAmount / $totalQuantity,
                'total_invested' => $totalAmount,
                'total_quantity' => $totalQuantity,
            ]);
        } else {
            // Semua pembelian dihapus → reset ke 0
            $this->update([
                'average_price'  => 0,
                'total_invested' => 0,
                'total_quantity' => 0,
            ]);
        }
    }

    public function addPurchase($data)
    {
        $purchase = $this->purchases()->create($data);
        $this->updateAveragePrice();
        return $purchase;
    }
}
