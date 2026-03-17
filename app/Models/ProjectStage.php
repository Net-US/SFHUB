<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'stage',
        'progress',
        'deadline',
        'client',
        'budget',
        'status',
        'attachments',
        'notes',
    ];

    protected $casts = [
        'deadline' => 'date',
        'budget' => 'decimal:2',
        'attachments' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByStage($query, $stage)
    {
        return $query->where('stage', $stage);
    }

    // Methods
    public function getStageColor()
    {
        return match ($this->stage) {
            'concept' => 'bg-gray-100 text-gray-800',
            'script' => 'bg-blue-100 text-blue-800',
            'storyboard' => 'bg-purple-100 text-purple-800',
            'production' => 'bg-yellow-100 text-yellow-800',
            'editing' => 'bg-orange-100 text-orange-800',
            'audio' => 'bg-pink-100 text-pink-800',
            'review' => 'bg-indigo-100 text-indigo-800',
            'delivery' => 'bg-emerald-100 text-emerald-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusIcon()
    {
        return match ($this->status) {
            'active' => 'play-circle',
            'paused' => 'pause-circle',
            'completed' => 'check-circle',
            'cancelled' => 'x-circle',
            default => 'question-circle',
        };
    }

    public function isOverdue()
    {
        return $this->deadline && $this->deadline < today() && $this->status !== 'completed';
    }

    public function getDaysUntilDeadline()
    {
        if (!$this->deadline) {
            return null;
        }

        return now()->diffInDays($this->deadline, false); // negative if overdue
    }

    public function updateProgress($progress)
    {
        $this->progress = max(0, min(100, $progress));

        if ($this->progress >= 100) {
            $this->status = 'completed';
        }

        $this->save();
    }
}
