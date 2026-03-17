<?php // ============================================================
// app/Models/SavingsGoal.php  (NEW)
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SavingsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'finance_account_id',
        'name',
        'target_amount',
        'current_amount',
        'daily_saving',
        'start_date',
        'target_date',
        'notes',
        'status',
    ];

    protected $casts = [
        'target_amount'  => 'decimal:2',
        'current_amount' => 'decimal:2',
        'daily_saving'   => 'decimal:2',
        'start_date'     => 'date',
        'target_date'    => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function getProgressPercentage(): float
    {
        if ($this->target_amount == 0) return 100;
        return min(100, round(($this->current_amount / $this->target_amount) * 100, 1));
    }

    public function getDaysRemaining(): int
    {
        if (!$this->target_date) return 0;
        return max(0, (int) Carbon::now()->diffInDays($this->target_date, false));
    }

    public function getDailyNeeded(): float
    {
        $days = $this->getDaysRemaining();
        if ($days <= 0) return 0;
        return round(($this->target_amount - $this->current_amount) / $days, 0);
    }

    public function isCompleted(): bool
    {
        return $this->current_amount >= $this->target_amount;
    }
}
