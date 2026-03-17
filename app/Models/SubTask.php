<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class SubTask extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'task_id',
        'user_id',
        'title',
        'description',
        'type',
        'order',
        'depends_on',
        'status',
        'progress',
        'estimated_minutes',
        'actual_minutes',
        'scheduled_at',
        'started_at',
        'completed_at',
        'due_date',
        'stage_key',
        'stage_label',
        'notes',
        'attachments',
        'checklist',
        'deliverable',
        'specifications',
        'quality_standard',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'due_date' => 'date',
        'progress' => 'integer',
        'estimated_minutes' => 'integer',
        'actual_minutes' => 'integer',
        'order' => 'integer',
        'notes' => 'array',
        'attachments' => 'array',
        'checklist' => 'array',
        'specifications' => 'array',
    ];

    protected $appends = [
        'is_overdue',
        'is_today',
        'status_label',
        'type_label',
        'estimated_hours',
        'actual_hours',
        'time_remaining',
        'completion_time',
        'is_started',
        'is_completed',
        'stage_icon',
    ];

    /**
     * Relationship with Task
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Relationship with User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Dependent Subtask
     */
    public function dependency(): BelongsTo
    {
        return $this->belongsTo(SubTask::class, 'depends_on');
    }

    /**
     * Relationship with Dependent By (reverse)
     */
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(SubTask::class, 'sub_task_dependencies', 'sub_task_id', 'depends_on');
    }

    /**
     * Relationship with Productivity Logs
     */
    public function productivityLogs(): BelongsToMany
    {
        return $this->belongsToMany(ProductivityLog::class);
    }

    /**
     * Check if subtask is overdue
     */
    protected function getIsOverdueAttribute(): bool
    {
        return $this->due_date &&
            $this->due_date->isPast() &&
            $this->status !== 'completed';
    }

    /**
     * Check if subtask is due today
     */
    protected function getIsTodayAttribute(): bool
    {
        return $this->due_date && $this->due_date->isToday();
    }

    /**
     * Check if subtask is started
     */
    protected function getIsStartedAttribute(): bool
    {
        return $this->started_at !== null;
    }

    /**
     * Check if subtask is completed
     */
    protected function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get status label
     */
    protected function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'blocked' => 'Tertahan',
            default => 'Unknown',
        };
    }

    /**
     * Get type label
     */
    protected function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'stage' => 'Tahapan',
            'checklist' => 'Checklist',
            'milestone' => 'Milestone',
            'review' => 'Review',
            default => 'Lainnya',
        };
    }

    /**
     * Get estimated hours
     */
    protected function getEstimatedHoursAttribute(): float
    {
        return $this->estimated_minutes ? round($this->estimated_minutes / 60, 2) : 0;
    }

    /**
     * Get actual hours
     */
    protected function getActualHoursAttribute(): ?float
    {
        return $this->actual_minutes ? round($this->actual_minutes / 60, 2) : null;
    }

    /**
     * Get time remaining
     */
    protected function getTimeRemainingAttribute(): ?int
    {
        if (!$this->due_date) return null;

        return Carbon::now()->diffInDays($this->due_date, false);
    }

    /**
     * Get completion time in minutes
     */
    protected function getCompletionTimeAttribute(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }

        return null;
    }

    /**
     * Get stage icon based on stage key
     */
    protected function getStageIconAttribute(): string
    {
        $icons = [
            'planning' => 'fa-clipboard-list',
            'script' => 'fa-file-lines',
            'concept' => 'fa-lightbulb',
            'design' => 'fa-palette',
            'recording' => 'fa-microphone',
            'editing' => 'fa-scissors',
            'review' => 'fa-eye',
            'revision' => 'fa-repeat',
            'finalize' => 'fa-check-double',
            'publish' => 'fa-upload',
            'distribution' => 'fa-share-nodes',
            'analysis' => 'fa-chart-bar',
            'research' => 'fa-magnifying-glass',
            'writing' => 'fa-pen',
            'draft' => 'fa-file-draft',
        ];

        return $icons[$this->stage_key] ?? 'fa-circle';
    }

    /**
     * Scope for pending subtasks
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for in progress subtasks
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for completed subtasks
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for blocked subtasks
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    /**
     * Scope by stage key
     */
    public function scopeByStage($query, $stageKey)
    {
        return $query->where('stage_key', $stageKey);
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Start working on subtask
     */
    public function start(): void
    {
        if (!$this->started_at) {
            $this->update([
                'status' => 'in_progress',
                'started_at' => now(),
                'progress' => max(25, $this->progress),
            ]);

            // Update parent task
            $this->task->update([
                'status' => 'doing',
                'started_at' => $this->task->started_at ?? now(),
            ]);
        }
    }

    /**
     * Mark subtask as complete
     */
    public function markAsComplete(): void
    {
        $this->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
            'actual_minutes' => $this->calculateActualMinutes(),
        ]);

        // Update parent task progress
        $this->task->updateProgressFromSubtasks();

        // Log productivity
        $this->logProductivity();

        // Check if we can advance parent workflow stage
        $this->checkWorkflowAdvancement();
    }

    /**
     * Update progress
     */
    public function updateProgress(int $progress): void
    {
        $this->progress = max(0, min(100, $progress));

        if ($progress >= 100) {
            $this->markAsComplete();
        } elseif ($progress > 0 && $this->status === 'pending') {
            $this->status = 'in_progress';
            $this->started_at = $this->started_at ?? now();
        }

        $this->save();
    }

    /**
     * Check if this subtask can advance parent workflow
     */
    private function checkWorkflowAdvancement(): void
    {
        $task = $this->task;

        // Check if all subtasks in current stage are completed
        $currentStageSubtasks = $task->subtasks()
            ->where('stage_key', $task->workflow_stage)
            ->get();

        $allCompleted = $currentStageSubtasks->every(function ($subtask) {
            return $subtask->status === 'completed';
        });

        if ($allCompleted && $currentStageSubtasks->count() > 0) {
            $task->advanceWorkflowStage();
        }
    }

    /**
     * Calculate actual minutes from start time
     */
    private function calculateActualMinutes(): int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        } elseif ($this->started_at) {
            return $this->started_at->diffInMinutes(now());
        }

        return $this->estimated_minutes ?? 0;
    }

    /**
     * Log productivity when subtask is completed
     */
    private function logProductivity(): void
    {
        $actualMinutes = $this->calculateActualMinutes();

        ProductivityLog::create([
            'user_id' => $this->user_id,
            'log_date' => now()->toDateString(),
            'task_id' => $this->task_id,
            'sub_task_id' => $this->id,
            'activity_type' => 'subtask_completed',
            'duration_minutes' => $actualMinutes,
            'focus_score' => 85,
            'energy_level' => 75,
            'description' => 'Menyelesaikan subtask: ' . $this->title,
            'details' => json_encode([
                'task_title' => $this->task->title,
                'stage' => $this->stage_label,
                'progress' => $this->progress,
            ]),
            'task_snapshot' => json_encode([
                'task_title' => $this->task->title,
                'subtask_title' => $this->title,
                'stage' => $this->stage_label,
                'completed_at' => now()->toDateTimeString(),
            ]),
        ]);
    }

    /**
     * Add checklist item
     */
    public function addChecklistItem(string $text, bool $checked = false): void
    {
        $checklist = $this->checklist ?? [];
        $checklist[] = [
            'id' => uniqid(),
            'text' => $text,
            'checked' => $checked,
            'created_at' => now()->toDateTimeString(),
        ];

        $this->checklist = $checklist;
        $this->save();
    }

    /**
     * Toggle checklist item
     */
    public function toggleChecklistItem(string $itemId): bool
    {
        $checklist = $this->checklist ?? [];

        foreach ($checklist as &$item) {
            if ($item['id'] === $itemId) {
                $item['checked'] = !$item['checked'];
                $item['completed_at'] = $item['checked'] ? now()->toDateTimeString() : null;

                $this->checklist = $checklist;
                $this->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Get checklist completion rate
     */
    public function getChecklistCompletionRate(): float
    {
        $checklist = $this->checklist ?? [];

        if (empty($checklist)) return 0;

        $completed = count(array_filter($checklist, function ($item) {
            return $item['checked'] ?? false;
        }));

        return ($completed / count($checklist)) * 100;
    }

    /**
     * Add note
     */
    public function addNote(string $content, string $type = 'info'): void
    {
        $notes = $this->notes ?? [];
        $notes[] = [
            'id' => uniqid(),
            'content' => $content,
            'type' => $type,
            'created_at' => now()->toDateTimeString(),
            'created_by' => $this->user_id,
        ];

        $this->notes = $notes;
        $this->save();
    }

    /**
     * Check if this subtask depends on another
     */
    public function dependsOn(SubTask $subtask): bool
    {
        return $this->depends_on === $subtask->id;
    }

    /**
     * Check if this subtask can be started (dependencies satisfied)
     */
    public function canBeStarted(): bool
    {
        if (!$this->depends_on) return true;

        $dependency = SubTask::find($this->depends_on);

        return $dependency && $dependency->status === 'completed';
    }

    /**
     * Get estimated completion time
     */
    public function getEstimatedCompletionTime(): ?Carbon
    {
        if (!$this->scheduled_at || !$this->estimated_minutes) return null;

        return $this->scheduled_at->copy()->addMinutes($this->estimated_minutes);
    }

    /**
     * Get time spent percentage
     */
    public function getTimeSpentPercentage(): ?float
    {
        if (!$this->estimated_minutes || !$this->actual_minutes) return null;

        return ($this->actual_minutes / $this->estimated_minutes) * 100;
    }
}
