<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'schedule_id',
        'date',
        'reason',
        'replaced_by_event_id',
        'is_cancelled',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_cancelled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'replaced_by_event_id');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForSchedule($query, $scheduleId)
    {
        return $query->where('schedule_id', $scheduleId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_cancelled', false);
    }

    public function scopeCancelled($query)
    {
        return $query->where('is_cancelled', true);
    }

    public function cancel()
    {
        $this->update(['is_cancelled' => true]);
        return $this;
    }

    public function restore()
    {
        $this->update(['is_cancelled' => false]);
        return $this;
    }

    public function getTypeLabel()
    {
        if ($this->replaced_by_event_id) {
            return 'Diganti dengan Event';
        }

        if ($this->is_cancelled) {
            return 'Dibatalkan';
        }

        return 'Override';
    }

    public function getStatusColor()
    {
        if ($this->is_cancelled) {
            return 'bg-red-100 text-red-800';
        }

        if ($this->replaced_by_event_id) {
            return 'bg-amber-100 text-amber-800';
        }

        return 'bg-blue-100 text-blue-800';
    }
}
