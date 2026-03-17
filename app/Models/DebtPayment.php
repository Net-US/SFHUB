<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'debt_id',
        'payment_date',
        'amount',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }

    // Methods
    public function getFormattedAmount()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}
