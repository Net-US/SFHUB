<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'end_date',
        'type',
        'color',
        'is_all_day',
        'is_recurring',
        'recurring_rule',
        'reminders',
        'location',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'end_date' => 'date',
        'is_all_day' => 'boolean',
        'is_recurring' => 'boolean',
        'reminders' => 'array',
    ];

    // Scopes
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('start_time', '>=', now())
            ->where('start_time', '<=', now()->addDays($days))
            ->orderBy('start_time');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Methods
    public function getDuration()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return $start->diff($end);
    }

    public function isPast()
    {
        return Carbon::parse($this->end_time)->isPast();
    }

    public function isUpcoming()
    {
        return Carbon::parse($this->start_time)->isFuture();
    }

    public function getTimeRemaining()
    {
        $now = Carbon::now();
        $start = Carbon::parse($this->start_time);

        return $now->diffForHumans($start, [
            'parts' => 2,
            'short' => true,
        ]);
    }
}
