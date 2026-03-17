<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
        'metadata',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Methods
    public function markAsRead()
    {
        $this->is_read = true;
        $this->save();
    }

    public function markAsUnread()
    {
        $this->is_read = false;
        $this->save();
    }

    public function getIcon()
    {
        return match ($this->type) {
            'system' => 'cog',
            'deadline' => 'clock',
            'reminder' => 'bell',
            'financial' => 'currency-dollar',
            'academic' => 'academic-cap',
            default => 'information-circle',
        };
    }

    public function getColor()
    {
        return match ($this->type) {
            'system' => 'bg-blue-100 text-blue-800',
            'deadline' => 'bg-red-100 text-red-800',
            'reminder' => 'bg-yellow-100 text-yellow-800',
            'financial' => 'bg-emerald-100 text-emerald-800',
            'academic' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getTimeAgo()
    {
        return $this->created_at->diffForHumans();
    }

    public function getActionUrl()
    {
        if (!$this->metadata || !isset($this->metadata['action_url'])) {
            return null;
        }

        return $this->metadata['action_url'];
    }
}
