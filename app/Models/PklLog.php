<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'log_date',
        'task',
        'category',
        'hours',
        'status',
        'notes',
        'attendance_status',
        'check_in',
        'check_out',
        'working_hours',
        'daily_tasks',
        'achievements',
        'challenges',
        'learnings',
        'supervisor_name',
        'performance_rating',
        'is_approved',
    ];

    protected $casts = [
        'log_date'           => 'date',
        'check_in'           => 'datetime',
        'check_out'          => 'datetime',
        'working_hours'      => 'decimal:2',
        'hours'              => 'decimal:1',
        'performance_rating' => 'integer',
        'is_approved'        => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('log_date', [$startDate, $endDate]);
    }

    public function scopeByAttendanceStatus($query, $status)
    {
        return $query->where('attendance_status', $status);
    }

    public function scopeByMonth($query, $month, $year)
    {
        return $query->whereMonth('log_date', $month)->whereYear('log_date', $year);
    }

    // Methods
    public function getDuration()
    {
        if (!$this->check_in || !$this->check_out) {
            return '00:00';
        }

        $start = $this->check_in;
        $end = $this->check_out;

        // Handle overnight case
        if ($end < $start) {
            $end->addDay();
        }

        $seconds = $end->diffInSeconds($start);
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function getHoursWorked()
    {
        if (!$this->check_in || !$this->check_out) {
            return 0;
        }

        $start = $this->check_in;
        $end = $this->check_out;

        // Handle overnight case
        if ($end < $start) {
            $end->addDay();
        }

        return $end->diffInHours($start);
    }

    public function getFormattedDate()
    {
        return $this->log_date->format('d F Y');
    }

    public function getTimeRange()
    {
        if (!$this->check_in) {
            return '-';
        }

        $start = $this->check_in->format('H:i');
        $end = $this->check_out ? $this->check_out->format('H:i') : '-';

        return "{$start} - {$end}";
    }

    public function getAttendanceStatusLabel()
    {
        return match ($this->attendance_status) {
            'present' => 'Hadir',
            'absent' => 'Tidak Hadir',
            'late' => 'Terlambat',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            default => 'Unknown',
        };
    }

    public function getAttendanceStatusColor()
    {
        return match ($this->attendance_status) {
            'present' => 'green',
            'absent' => 'red',
            'late' => 'yellow',
            'sick' => 'blue',
            'permission' => 'purple',
            default => 'gray',
        };
    }

    public function getAttendanceStatusIcon()
    {
        return match ($this->attendance_status) {
            'present' => '✅',
            'absent' => '❌',
            'late' => '⏰',
            'sick' => '🏥',
            'permission' => '📄',
            default => '❓',
        };
    }

    public function getPerformanceStars()
    {
        if (!$this->performance_rating) {
            return '☆☆☆☆☆';
        }

        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->performance_rating) {
                $stars .= '⭐';
            } else {
                $stars .= '☆';
            }
        }
        return $stars;
    }

    public function getPerformanceColor()
    {
        if (!$this->performance_rating) {
            return 'gray';
        }

        return match ($this->performance_rating) {
            5 => 'green',
            4 => 'blue',
            3 => 'yellow',
            2 => 'orange',
            1 => 'red',
            default => 'gray',
        };
    }

    public function isPresent()
    {
        return in_array($this->attendance_status, ['present', 'late']);
    }

    public function isAbsent()
    {
        return $this->attendance_status === 'absent';
    }

    public function isLate()
    {
        return $this->attendance_status === 'late';
    }

    public function approve()
    {
        $this->is_approved = true;
        $this->save();
    }

    public function reject()
    {
        $this->is_approved = false;
        $this->save();
    }
}
