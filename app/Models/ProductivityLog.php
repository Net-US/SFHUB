<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'log_date',
        'tasks_completed',
        'tasks_planned',
        'focus_score',
        'total_work_hours',
        'category_breakdown',
        'notes',
    ];

    protected $casts = [
        'log_date' => 'date',
        'total_work_hours' => 'datetime',
        'category_breakdown' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('log_date', [$startDate, $endDate]);
    }

    public function scopeLastWeek($query)
    {
        return $query->whereBetween('log_date', [now()->subWeek(), now()]);
    }

    public function scopeLastMonth($query)
    {
        return $query->whereBetween('log_date', [now()->subMonth(), now()]);
    }

    // Methods
    public function getCompletionRate()
    {
        if ($this->tasks_planned == 0) {
            return 0;
        }

        return ($this->tasks_completed / $this->tasks_planned) * 100;
    }

    public function getProductivityScore()
    {
        $completionRate = $this->getCompletionRate();
        return ($completionRate * 0.6) + ($this->focus_score * 0.4);
    }

    public function getCategoryData()
    {
        return collect($this->category_breakdown ?? [])->map(function ($item) {
            return [
                'name' => $item['name'] ?? 'Unknown',
                'completed' => $item['completed'] ?? 0,
                'total' => $item['total'] ?? 0,
            ];
        });
    }

    public function getHoursWorked()
    {
        if (!$this->total_work_hours) {
            return 0;
        }

        $time = explode(':', $this->total_work_hours->format('H:i:s'));
        return (int)$time[0] + ((int)$time[1] / 60);
    }
}
