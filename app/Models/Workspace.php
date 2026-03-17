<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'type',
        'settings',
        'is_default',
        'is_private',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_private' => 'boolean',
        'settings' => 'array',
    ];

    protected $appends = [
        'type_label',
        'icon_class',
        'task_count',
        'active_task_count',
        'completed_task_count',
        'overdue_task_count',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($workspace) {
            if (empty($workspace->slug)) {
                $workspace->slug = Str::slug($workspace->name) . '-' . Str::random(6);
            }
        });

        static::saving(function ($workspace) {
            if ($workspace->is_default) {
                // Remove default flag from other workspaces
                $workspace->user->workspaces()
                    ->where('id', '!=', $workspace->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Relationship with User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Tasks
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get type label
     */
    protected function getTypeLabelAttribute(): string
    {
        $labels = [
            'personal' => 'Personal',
            'academic' => 'Akademik',
            'creative' => 'Kreatif',
            'work' => 'Work',
            'health' => 'Kesehatan & Fitness',
        ];

        return $labels[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get icon class based on icon name
     */
    protected function getIconClassAttribute(): string
    {
        $icons = [
            'user' => 'fa-user',
            'graduation-cap' => 'fa-graduation-cap',
            'palette' => 'fa-palette',
            'briefcase' => 'fa-briefcase',
            'heart' => 'fa-heart',
            'home' => 'fa-home',
            'building' => 'fa-building',
            'book' => 'fa-book',
            'code' => 'fa-code',
            'music' => 'fa-music',
            'camera' => 'fa-camera',
            'chart-line' => 'fa-chart-line',
            'shopping-cart' => 'fa-shopping-cart',
            'utensils' => 'fa-utensils',
            'dumbbell' => 'fa-dumbbell',
            'bed' => 'fa-bed',
        ];

        return $icons[$this->icon] ?? 'fa-folder';
    }

    /**
     * Get total task count
     */
    protected function getTaskCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    /**
     * Get active task count
     */
    protected function getActiveTaskCountAttribute(): int
    {
        return $this->tasks()->whereIn('status', ['todo', 'doing'])->count();
    }

    /**
     * Get completed task count
     */
    protected function getCompletedTaskCountAttribute(): int
    {
        return $this->tasks()->where('status', 'done')->count();
    }

    /**
     * Get overdue task count
     */
    protected function getOverdueTaskCountAttribute(): int
    {
        return $this->tasks()
            ->whereDate('due_date', '<', now())
            ->whereNotIn('status', ['done', 'archived'])
            ->count();
    }

    /**
     * Scope for personal workspaces
     */
    public function scopePersonal($query)
    {
        return $query->where('type', 'personal');
    }

    /**
     * Scope for academic workspaces
     */
    public function scopeAcademic($query)
    {
        return $query->where('type', 'academic');
    }

    /**
     * Scope for creative workspaces
     */
    public function scopeCreative($query)
    {
        return $query->where('type', 'creative');
    }

    /**
     * Scope for default workspace
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for private workspaces
     */
    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    /**
     * Scope for public workspaces
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Get workspace statistics
     */
    public function getStatistics(): array
    {
        $tasks = $this->tasks()->get();

        $totalTime = 0;
        $totalEstimated = 0;

        foreach ($tasks as $task) {
            if ($task->actual_hours) {
                $totalTime += $task->actual_hours;
            }
            if ($task->estimated_hours) {
                $totalEstimated += $task->estimated_hours;
            }
        }

        $completionRate = $this->task_count > 0
            ? round(($this->completed_task_count / $this->task_count) * 100)
            : 0;

        $timeEfficiency = $totalEstimated > 0
            ? round(($totalTime / $totalEstimated) * 100)
            : 0;

        return [
            'total_tasks' => $this->task_count,
            'active_tasks' => $this->active_task_count,
            'completed_tasks' => $this->completed_task_count,
            'overdue_tasks' => $this->overdue_task_count,
            'completion_rate' => $completionRate,
            'total_time_spent' => $totalTime,
            'total_time_estimated' => $totalEstimated,
            'time_efficiency' => $timeEfficiency,
        ];
    }

    /**
     * Get tasks grouped by category
     */
    public function getTasksByCategory(): array
    {
        return $this->tasks()
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category')
            ->toArray();
    }

    /**
     * Get tasks grouped by status
     */
    public function getTasksByStatus(): array
    {
        return $this->tasks()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get recent tasks
     */
    public function getRecentTasks(int $limit = 5)
    {
        return $this->tasks()
            ->with('subtasks')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get upcoming deadlines
     */
    public function getUpcomingDeadlines(int $days = 7)
    {
        return $this->tasks()
            ->whereDate('due_date', '>=', now())
            ->whereDate('due_date', '<=', now()->addDays($days))
            ->whereNotIn('status', ['done', 'archived'])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Set as default workspace
     */
    public function setAsDefault(): void
    {
        $this->update(['is_default' => true]);
    }

    /**
     * Update workspace settings
     */
    public function updateSettings(array $settings): void
    {
        $currentSettings = $this->settings ?? [];
        $mergedSettings = array_merge($currentSettings, $settings);

        $this->update(['settings' => $mergedSettings]);
    }

    /**
     * Get a specific setting
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Get workspace color with fallback
     */
    public function getColor(): string
    {
        if ($this->color) return $this->color;

        $defaultColors = [
            'personal' => '#3b82f6',
            'academic' => '#10b981',
            'creative' => '#f97316',
            'work' => '#8b5cf6',
            'health' => '#ef4444',
        ];

        return $defaultColors[$this->type] ?? '#6b7280';
    }

    /**
     * Get workspace icon with fallback
     */
    public function getIcon(): string
    {
        if ($this->icon) return $this->icon;

        $defaultIcons = [
            'personal' => 'user',
            'academic' => 'graduation-cap',
            'creative' => 'palette',
            'work' => 'briefcase',
            'health' => 'heart',
        ];

        return $defaultIcons[$this->type] ?? 'folder';
    }

    /**
     * Check if workspace can be deleted
     */
    public function canBeDeleted(): bool
    {
        return !$this->is_default && $this->tasks()->count() === 0;
    }

    /**
     * Duplicate workspace with all tasks
     */
    public function duplicate(string $newName): Workspace
    {
        $newWorkspace = $this->replicate();
        $newWorkspace->name = $newName;
        $newWorkspace->slug = null;
        $newWorkspace->is_default = false;
        $newWorkspace->save();

        // Duplicate tasks
        foreach ($this->tasks as $task) {
            $newTask = $task->replicate();
            $newTask->workspace_id = $newWorkspace->id;
            $newTask->save();

            // Duplicate subtasks
            foreach ($task->subtasks as $subtask) {
                $newSubtask = $subtask->replicate();
                $newSubtask->task_id = $newTask->id;
                $newSubtask->save();
            }
        }

        return $newWorkspace;
    }
}
