<?php

// ============================================================
// app/Models/Budget.php  (UPDATED — sudah ada, tambah helper)
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'amount',
        'period',
        'spent_amount',
        'alert_threshold',
        'is_active',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'spent_amount'    => 'decimal:2',
        'alert_threshold' => 'integer',
        'is_active'       => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
    public function scopeMonthly($q)
    {
        return $q->where('period', 'monthly');
    }
    public function scopeOverBudget($q)
    {
        return $q->whereColumn('spent_amount', '>', 'amount');
    }

    public function getRemainingAmount(): float
    {
        return max(0, (float)$this->amount - (float)$this->spent_amount);
    }
    public function getUsagePercentage(): float
    {
        if ($this->amount == 0) return 0;
        return min(100, ($this->spent_amount / $this->amount) * 100);
    }
    public function isOverBudget(): bool
    {
        return $this->spent_amount > $this->amount;
    }
    public function isNearLimit(): bool
    {
        return $this->getUsagePercentage() >= $this->alert_threshold && !$this->isOverBudget();
    }

    /** Sinkronkan spent_amount dari transaksi bulan ini */
    public function syncSpent(): void
    {
        $spent = Transaction::where('user_id', $this->user_id)
            ->where('type', 'expense')
            ->where('category', $this->category)
            ->when(
                $this->period === 'monthly',
                fn($q) =>
                $q->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
            )
            ->when(
                $this->period === 'weekly',
                fn($q) =>
                $q->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()])
            )
            ->sum('amount');

        $this->update(['spent_amount' => $spent]);
    }

    public function getStatusBadge(): string
    {
        if ($this->isOverBudget())  return 'bg-red-100 text-red-800 border border-red-300';
        if ($this->isNearLimit())   return 'bg-amber-100 text-amber-800 border border-amber-300';
        return 'bg-emerald-100 text-emerald-800 border border-emerald-300';
    }

    public function getStatusIcon(): string
    {
        if ($this->isOverBudget()) return 'fa-triangle-exclamation';
        if ($this->isNearLimit())  return 'fa-circle-exclamation';
        return 'fa-check-circle';
    }
}
