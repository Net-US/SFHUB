<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day',
        'start_time',
        'end_time',
        'type',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStartTimeFormatted(): string
    {
        return $this->start_time ? $this->start_time->format('H:i') : '';
    }

    public function getEndTimeFormatted(): string
    {
        return $this->end_time ? $this->end_time->format('H:i') : '';
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'full' => 'Full Day',
            'half' => 'Half Day',
            'off'  => 'Libur',
            default => ucfirst($this->type),
        };
    }
}
