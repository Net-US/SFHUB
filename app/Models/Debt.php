<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Debt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'creditor_name',
        'debt_type',
        'total_amount',
        'remaining_amount',
        'start_date',
        'due_date',
        'interest_rate',
        'description',
        'notes',
        'status',
        'payment_schedule',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'due_date' => 'date',
        'start_date' => 'date',
        'payment_schedule' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(DebtPayment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', today())->whereNotIn('status', ['paid']);
    }

    public function scopePayable($query)
    {
        return $query->where('debt_type', 'borrower');
    }

    public function scopeReceivable($query)
    {
        return $query->where('debt_type', 'lender');
    }

    // Methods
    public function getRemainingAmount()
    {
        return $this->remaining_amount;
    }

    public function getProgressPercentage()
    {
        if ($this->total_amount == 0) {
            return 100;
        }

        $paid = $this->total_amount - $this->remaining_amount;
        return ($paid / $this->total_amount) * 100;
    }

    public function getDaysRemaining()
    {
        if (!$this->due_date) return null;
        return Carbon::parse($this->due_date)->diffInDays(Carbon::now(), false);
    }

    public function isOverdue()
    {
        return $this->due_date && $this->due_date < today() && $this->remaining_amount > 0;
    }

    public function calculateInterest()
    {
        if ($this->interest_rate == 0 || !$this->start_date) {
            return 0;
        }

        $days = Carbon::parse($this->start_date)->diffInDays(Carbon::now());
        $yearlyInterest = $this->total_amount * ($this->interest_rate / 100);
        $dailyInterest = $yearlyInterest / 365;

        return $dailyInterest * $days;
    }

    public function getTotalWithInterest()
    {
        return $this->total_amount + $this->calculateInterest();
    }

    public function addPayment($amount, $date = null, $notes = '')
    {
        $payment = $this->payments()->create([
            'payment_date' => $date ?? today(),
            'amount' => $amount,
            'notes' => $notes,
        ]);

        // Update remaining amount
        $newRemaining = $this->remaining_amount - $amount;
        $this->update(['remaining_amount' => max(0, $newRemaining)]);

        return $payment;
    }
}
