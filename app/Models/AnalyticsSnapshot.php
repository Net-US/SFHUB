<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsSnapshot extends Model
{
    use HasFactory;

    protected $table = 'analytics_snapshots';

    protected $fillable = [
        'snapshot_date',
        'period',
        'active_users',
        'new_users',
        'total_users',
        'tasks_completed',
        'revenue',
        'metadata',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'active_users' => 'integer',
        'new_users' => 'integer',
        'total_users' => 'integer',
        'tasks_completed' => 'integer',
        'revenue' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function scopeDaily($query)
    {
        return $query->where('period', 'daily');
    }

    public function scopeWeekly($query)
    {
        return $query->where('period', 'weekly');
    }

    public function scopeMonthly($query)
    {
        return $query->where('period', 'monthly');
    }
}
