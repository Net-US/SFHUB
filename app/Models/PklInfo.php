<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PklInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company',
        'department',
        'supervisor',
        'supervisor_phone',
        'address',
        'start_date',
        'end_date',
        'hours_required',
        'allowance',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'hours_required' => 'integer',
        'allowance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDaysLeft(): int
    {
        if (!$this->end_date) return 0;
        return max(0, (int) now()->diffInDays($this->end_date, false));
    }

    public function getProgressPercentage(int $hoursDone): float
    {
        if ($this->hours_required <= 0) return 0;
        return min(100, round(($hoursDone / $this->hours_required) * 100));
    }

    public function getCalendarProgressPercentage(): float
    {
        if (!$this->start_date || !$this->end_date) return 0;
        $total = $this->start_date->diffInDays($this->end_date);
        if ($total <= 0) return 0;
        $done = $this->start_date->diffInDays(now());
        return min(100, round(($done / $total) * 100));
    }
}
