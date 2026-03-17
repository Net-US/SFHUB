<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'date',
        'start_time',
        'end_time',
        'type',
        'location',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->whereDate('date', '>=', today())
            ->whereDate('date', '<=', today()->addDays($days))
            ->orderBy('date')
            ->orderBy('start_time');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function getDurationMinutes()
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return $start->diffInMinutes($end);
    }

    public function isConflictingWith($startTime, $endTime, $date = null)
    {
        $checkDate = $date ?? $this->date;

        if ($this->date->format('Y-m-d') !== $checkDate) {
            return false;
        }

        $eventStart = Carbon::parse($this->start_time);
        $eventEnd = Carbon::parse($this->end_time);
        $checkStart = Carbon::parse($startTime);
        $checkEnd = Carbon::parse($endTime);

        return $eventStart < $checkEnd && $eventEnd > $checkStart;
    }

    public function getTypeColor()
    {
        return match ($this->type) {
            'seminar' => 'bg-purple-100 text-purple-800 border-purple-300',
            'deadline' => 'bg-red-100 text-red-800 border-red-300',
            'acara' => 'bg-blue-100 text-blue-800 border-blue-300',
            'lainnya' => 'bg-gray-100 text-gray-800 border-gray-300',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }

    public function getTypeIcon()
    {
        return match ($this->type) {
            'seminar' => 'fa-chalkboard-user',
            'deadline' => 'fa-clock',
            'acara' => 'fa-calendar-day',
            'lainnya' => 'fa-circle',
            default => 'fa-circle',
        };
    }

    public function getTimeRangeAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return 'All Day';
        }

        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }
}
