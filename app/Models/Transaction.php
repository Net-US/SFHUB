<?php
// ============================================================
// app/Models/Transaction.php  (UPDATED)
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'finance_account_id',
        'to_account_id',
        'type',
        'amount',
        'fee',
        'category',
        'description',
        'transaction_date',
        'payment_method',
        'notes',
        'tags',
        'receipt_url',
        'is_recurring',
        'recurring_pattern',
        'related_transaction_id',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'fee'              => 'decimal:2',
        'transaction_date' => 'date',
        'tags'             => 'array',
        'is_recurring'     => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'to_account_id');
    }

    public function relatedTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'related_transaction_id');
    }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeIncome($q)
    {
        return $q->where('type', 'income');
    }
    public function scopeExpense($q)
    {
        return $q->where('type', 'expense');
    }
    public function scopeTransfer($q)
    {
        return $q->where('type', 'transfer');
    }
    public function scopeByCategory($q, $cat)
    {
        return $q->where('category', $cat);
    }
    public function scopeByDateRange($q, $s, $e)
    {
        return $q->whereBetween('transaction_date', [$s, $e]);
    }
    public function scopeThisMonth($q)
    {
        return $q->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);
    }

    // ── Methods ───────────────────────────────────────────────
    public function getAmountColor(): string
    {
        return match ($this->type) {
            'income'   => 'text-emerald-600',
            'expense'  => 'text-rose-600',
            'transfer' => 'text-blue-600',
            default    => 'text-gray-600',
        };
    }

    public function getTypeIcon(): string
    {
        return match ($this->type) {
            'income'   => 'fa-arrow-down text-emerald-600',
            'expense'  => 'fa-arrow-up text-rose-600',
            'transfer' => 'fa-right-left text-blue-600',
            default    => 'fa-circle text-gray-600',
        };
    }

    /**
     * Proses update saldo akun sumber & tujuan.
     * Dipanggil setelah record dibuat.
     */
    public function processBalance(): void
    {
        $account = $this->account;

        if ($this->type === 'income' && $account) {
            $account->increment('balance', $this->amount);
        } elseif ($this->type === 'expense' && $account) {
            $account->decrement('balance', $this->amount);
        } elseif ($this->type === 'transfer') {
            // dari: kurangi amount + fee
            if ($account) {
                $account->decrement('balance', $this->amount + $this->fee);
            }
            // ke: tambah amount saja
            $to = $this->toAccount;
            if ($to) {
                $to->increment('balance', $this->amount);
            }
        }
    }

    /**
     * Balik efek saldo (untuk delete / rollback)
     */
    public function reverseBalance(): void
    {
        $account = $this->account;

        if ($this->type === 'income' && $account) {
            $account->decrement('balance', $this->amount);
        } elseif ($this->type === 'expense' && $account) {
            $account->increment('balance', $this->amount);
        } elseif ($this->type === 'transfer') {
            if ($account) {
                $account->increment('balance', $this->amount + $this->fee);
            }
            $to = $this->toAccount;
            if ($to) {
                $to->decrement('balance', $this->amount);
            }
        }
    }
}
