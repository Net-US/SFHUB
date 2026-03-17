<?php // ============================================================
// app/Models/PendingNeed.php  (NEW)
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingNeed extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'finance_account_id',
        'name',
        'amount',
        'category',
        'notes',
        'status',
        'transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function account()
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }
    public function scopePurchased($q)
    {
        return $q->where('status', 'purchased');
    }
    public function scopeCancelled($q)
    {
        return $q->where('status', 'cancelled');
    }

    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'pending'   => 'bg-amber-100 text-amber-800 border border-amber-300',
            'purchased' => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
            'cancelled' => 'bg-stone-100 text-stone-500 border border-stone-300',
            default     => 'bg-stone-100 text-stone-500',
        };
    }
}
