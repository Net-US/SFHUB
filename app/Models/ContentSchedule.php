<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'content_type',
        'frequency',
        'target_per_period',
        'due_date',
        'status',
        'notes',
        'completed_count',
    ];

    protected $casts = [
        'due_date' => 'date',
        'target_per_period' => 'integer',
        'completed_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeDueThisMonth($query)
    {
        return $query->whereBetween('due_date', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', today())
            ->whereColumn('completed_count', '<', 'target_per_period');
    }

    public function scopeNotOnTrack($query)
    {
        return $query->where(function ($q) {
            $q->whereColumn('completed_count', '<', 'target_per_period')
                ->where('due_date', '>=', today());
        });
    }

    public function getProgressPercentage()
    {
        if ($this->target_per_period == 0) {
            return 100;
        }

        return min(100, ($this->completed_count / $this->target_per_period) * 100);
    }

    public function getRemainingCount()
    {
        return max(0, $this->target_per_period - $this->completed_count);
    }

    public function isComplete()
    {
        return $this->completed_count >= $this->target_per_period;
    }

    public function isOverdue()
    {
        return $this->due_date < today() && !$this->isComplete();
    }

    public function incrementCompleted($count = 1)
    {
        $this->increment('completed_count', $count);

        if ($this->isComplete()) {
            $this->update(['status' => 'completed']);
        }

        return $this;
    }

    public function getPlatformColor()
    {
        return match ($this->platform) {
            'instagram' => 'bg-pink-100 text-pink-800 border-pink-300',
            'youtube' => 'bg-red-100 text-red-800 border-red-300',
            'tiktok' => 'bg-black/10 text-black border-gray-800',
            'twitter' => 'bg-blue-100 text-blue-800 border-blue-300',
            'linkedin' => 'bg-blue-800/10 text-blue-900 border-blue-900',
            'shutterstock' => 'bg-amber-100 text-amber-800 border-amber-300',
            'behance' => 'bg-blue-600/10 text-blue-700 border-blue-600',
            'dribbble' => 'bg-pink-500/10 text-pink-600 border-pink-500',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }

    public function getPlatformIcon()
    {
        return match ($this->platform) {
            'instagram' => 'fa-instagram',
            'youtube' => 'fa-youtube',
            'tiktok' => 'fa-tiktok',
            'twitter' => 'fa-twitter',
            'linkedin' => 'fa-linkedin',
            'shutterstock' => 'fa-camera',
            'behance' => 'fa-behance',
            'dribbble' => 'fa-dribbble',
            default => 'fa-share-nodes',
        };
    }

    public function getDaysRemaining()
    {
        if ($this->isComplete()) {
            return 0;
        }

        return now()->diffInDays($this->due_date, false);
    }
}
