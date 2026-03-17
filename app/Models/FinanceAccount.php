<?php
// ============================================================
// app/Models/FinanceAccount.php  (UPDATED)
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'account_number',
        'balance',
        'currency',
        'color',
        'icon',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'balance'   => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'finance_account_id');
    }

    public function incomingTransactions()
    {
        return $this->hasMany(Transaction::class, 'finance_account_id')->where('type', 'income');
    }

    public function outgoingTransactions()
    {
        return $this->hasMany(Transaction::class, 'finance_account_id')->where('type', 'expense');
    }

    public function transfersOut()
    {
        return $this->hasMany(Transaction::class, 'finance_account_id')->where('type', 'transfer');
    }

    public function transfersIn()
    {
        return $this->hasMany(Transaction::class, 'to_account_id')->where('type', 'transfer');
    }

    public function savingsGoals()
    {
        return $this->hasMany(SavingsGoal::class, 'finance_account_id');
    }

    public function pendingNeeds()
    {
        return $this->hasMany(PendingNeed::class, 'finance_account_id');
    }

    // ── Helpers ───────────────────────────────────────────────
    public function getFormattedBalance(): string
    {
        $symbol = match ($this->currency) {
            'IDR'  => 'Rp',
            'USD'  => '$',
            'EUR'  => '€',
            default => $this->currency,
        };
        return $symbol . ' ' . number_format($this->balance, 0, ',', '.');
    }

    /**
     * Uang "tersedia" = saldo dikurangi pending needs yang belum dibeli
     * (khusus untuk tipe yang liquid: cash, bank, e-wallet)
     */
    public function getAvailableBalance(): float
    {
        $blocked = $this->pendingNeeds()
            ->where('status', 'pending')
            ->sum('amount');
        return max(0, (float)$this->balance - (float)$blocked);
    }

    public function isLiquid(): bool
    {
        return in_array($this->type, ['cash', 'bank', 'e-wallet']);
    }

    public function updateBalance(float $amount, string $type): void
    {
        if ($type === 'income') {
            $this->increment('balance', $amount);
        } elseif ($type === 'expense') {
            $this->decrement('balance', $amount);
        }
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'cash'        => 'Cash / Tunai',
            'bank'        => 'Rekening Bank',
            'e-wallet'    => 'E-Wallet',
            'investment'  => 'Investasi',
            'receivable'  => 'Piutang',
            default       => ucfirst($this->type),
        };
    }

    public function getTypeIcon(): string
    {
        return match ($this->type) {
            'cash'       => 'fa-money-bill-wave',
            'bank'       => 'fa-building-columns',
            'e-wallet'   => 'fa-mobile-screen-button',
            'investment' => 'fa-chart-line',
            'receivable' => 'fa-hand-holding-dollar',
            default      => 'fa-wallet',
        };
    }
}
