<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'code',
        'sks',
        'semester',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'lecturer',
        'is_active',
        'progress',
        'drive_link',
        'notes',
    ];

    protected $casts = [
        'sks'        => 'integer',
        'semester'   => 'integer',
        'is_active'  => 'boolean',
        'progress'   => 'integer',
        'start_time' => 'datetime:H:i',
        'end_time'   => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'linked_subject_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeToday($query)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $today = $days[date('w')];
        return $query->where('day_of_week', $today);
    }

    public function getTimeRangeAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return '-';
        }

        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    public function getDurationMinutes()
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        return $this->start_time->diffInMinutes($this->end_time);
    }

    public function getTotalHours()
    {
        return $this->sks * 50 / 60; // 1 SKS = 50 menit per minggu
    }

    public function isNow()
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $currentDay = $days[date('w')];
        $currentTime = now()->format('H:i');

        return $this->day_of_week === $currentDay &&
            $currentTime >= $this->start_time->format('H:i') &&
            $currentTime <= $this->end_time->format('H:i');
    }

    public function getStatusColor()
    {
        return match (true) {
            $this->isNow() => 'bg-green-100 text-green-800 border-green-300',
            !$this->is_active => 'bg-gray-100 text-gray-800 border-gray-300',
            default => 'bg-blue-100 text-blue-800 border-blue-300',
        };
    }
}
