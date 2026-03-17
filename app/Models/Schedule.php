<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day',
        'start_time',
        'end_time',
        'activity',
        'type',
        'location',
        'instructor',
        'course_code',
        'is_recurring',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_recurring' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function academicCourse()
    {
        return $this->belongsTo(AcademicCourse::class, 'course_code', 'course_code')
            ->where('user_id', $this->user_id);
    }

    // Scopes
    public function scopeToday($query)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $today = $days[date('w')];

        return $query->where('day', $today)
            ->orWhere('is_recurring', false);
    }

    public function scopeByDay($query, $day)
    {
        return $query->where('day', $day);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeUpcoming($query, $hours = 24)
    {
        return $query->where(function ($q) {
            $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $currentDay = $days[date('w')];
            $currentTime = date('H:i:s');

            $q->where('day', $currentDay)
                ->where('start_time', '>=', $currentTime)
                ->orWhere(function ($q2) use ($currentDay) {
                    $q2->where('day', '!=', $currentDay)
                        ->orWhere('is_recurring', false);
                });
        });
    }

    // Methods
    public function getTypeColor()
    {
        return match ($this->type) {
            'academic' => 'bg-blue-100 text-blue-800 border-blue-300',
            'creative' => 'bg-orange-100 text-orange-800 border-orange-300',
            'pkl' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'exam' => 'bg-red-100 text-red-800 border-red-300',
            'personal' => 'bg-purple-100 text-purple-800 border-purple-300',
            'routine' => 'bg-stone-100 text-stone-800 border-stone-300',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }

    public function getIcon()
    {
        return match ($this->type) {
            'academic' => 'fa-graduation-cap',
            'creative' => 'fa-palette',
            'pkl' => 'fa-briefcase',
            'exam' => 'fa-file-pen',
            'personal' => 'fa-user',
            'routine' => 'fa-repeat',
            default => 'fa-calendar',
        };
    }

    public function isNow()
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $currentDay = $days[date('w')];
        $currentTime = date('H:i');

        return $this->day === $currentDay &&
            $currentTime >= $this->start_time->format('H:i') &&
            $currentTime <= $this->end_time->format('H:i');
    }

    public function isUpcomingToday()
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $currentDay = $days[date('w')];
        $currentTime = date('H:i');

        return $this->day === $currentDay &&
            $currentTime < $this->start_time->format('H:i');
    }
}
