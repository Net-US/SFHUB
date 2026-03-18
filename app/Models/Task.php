<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'workspace_id',
        'parent_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'deadline',
        'notes',
        'linked_subject_id',
        'project_type',
        'project_mode',
        'due_date',
        'estimated_time',
        'actual_time',
        'progress',
        'workflow_stage',
        'total_subtasks',
        'completed_subtasks',
        'tags',
        'links',
        'attachments',
        'is_recurring',
        'recurring_pattern',
        'recurring_until',
        'client',
        'budget',
        'deliverable_format',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'deadline' => 'date',
        'due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_recurring' => 'boolean',
        'progress' => 'integer',
        'total_subtasks' => 'integer',
        'completed_subtasks' => 'integer',
        'budget' => 'decimal:2',
        'tags' => 'array',
        'links' => 'array',
        'attachments' => 'array',
        'recurring_until' => 'date',
        'estimated_time' => 'datetime:H:i:s',
        'actual_time' => 'datetime:H:i:s',
    ];

    protected $appends = [
        'is_overdue',
        'is_today',
        'priority_label',
        'project_type_label',
        'workflow_stage_label',
        'estimated_hours',
        'actual_hours',
        'days_remaining',
        'time_spent',
    ];

    /**
     * Relationship with User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Workspace
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Relationship with Parent Task
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    /**
     * Relationship with Child Tasks
     */
    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    /**
     * Relationship with Subtasks
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(SubTask::class)->orderBy('order');
    }

    /**
     * Relationship with Productivity Logs
     */
    public function productivityLogs(): HasMany
    {
        return $this->hasMany(ProductivityLog::class);
    }

    /**
     * Relationship with Subject
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Check if task is overdue
     */
    protected function getIsOverdueAttribute(): bool
    {
        return $this->due_date &&
            $this->due_date->isPast() &&
            $this->status !== 'done' &&
            $this->status !== 'archived';
    }

    
    /**
     * Check if task is due today
     */
    protected function getIsTodayAttribute(): bool
    {
        return $this->due_date && $this->due_date->isToday();
    }

    /**
     * Get priority label
     */
    protected function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'urgent-important' => 'Prioritas 1 (Penting & Mendesak)',
            'important-not-urgent' => 'Prioritas 2 (Penting Tidak Mendesak)',
            'urgent-not-important' => 'Prioritas 3 (Mendesak Tidak Penting)',
            'not-urgent-not-important' => 'Prioritas 4 (Tidak Mendesak Tidak Penting)',
            default => 'Tanpa Prioritas',
        };
    }

    /**
     * Get project type label
     */
    protected function getProjectTypeLabelAttribute(): string
    {
        $labels = [
            // Creative
            'video_editing' => 'Video Editing',
            'graphic_design' => 'Graphic Design',
            'motion_graphics' => 'Motion Graphics',
            'audio_production' => 'Audio Production',
            'script_writing' => 'Script Writing',
            'ui_ux_design' => 'UI/UX Design',
            'animation' => 'Animation',
            'photography' => 'Photography',
            'illustration' => 'Illustration',
            'branding' => 'Branding',
            'social_media' => 'Social Media Content',

            // Academic
            'academic_assignment' => 'Tugas Akademik',
            'research' => 'Penelitian',
            'thesis' => 'Skripsi/Tesis',
            'pkl' => 'PKL/Praktikum',

            // Health & Personal
            'workout' => 'Workout',
            'meal_prep' => 'Meal Preparation',
            'cleaning' => 'Cleaning',
            'meeting' => 'Meeting',
            'event' => 'Event',
            'other' => 'Lainnya',
        ];

        return $labels[$this->project_type] ?? ucfirst(str_replace('_', ' ', $this->project_type));
    }

    /**
     * Get workflow stage label
     */
    protected function getWorkflowStageLabelAttribute(): string
    {
        $labels = [
            'planning' => 'Perencanaan',
            'script' => 'Naskah/Script',
            'concept' => 'Konsep',
            'design' => 'Desain',
            'recording' => 'Rekaman',
            'editing' => 'Editing',
            'review' => 'Review',
            'revision' => 'Revisi',
            'finalize' => 'Finalisasi',
            'publish' => 'Publish',
            'distribution' => 'Distribution',
            'analysis' => 'Analisis',
            'draft' => 'Draft',
            'research' => 'Penelitian',
            'writing' => 'Penulisan',
            'none' => 'Belum Dimulai',
        ];

        return $labels[$this->workflow_stage] ?? 'Unknown';
    }

    /**
     * Get estimated hours
     */
    protected function getEstimatedHoursAttribute(): float
    {
        if (!$this->estimated_time) return 0;

        $time = explode(':', $this->estimated_time);
        return (int)$time[0] + ((int)$time[1] / 60);
    }

    /**
     * Get actual hours
     */
    protected function getActualHoursAttribute(): ?float
    {
        if (!$this->actual_time) return null;

        $time = explode(':', $this->actual_time);
        return (int)$time[0] + ((int)$time[1] / 60);
    }

    /**
     * Get days remaining
     */
    protected function getDaysRemainingAttribute(): ?int
    {
        if (!$this->due_date) return null;

        return Carbon::now()->diffInDays($this->due_date, false);
    }

    /**
     * Get time spent percentage
     */
    protected function getTimeSpentAttribute(): ?float
    {
        if (!$this->estimated_time || !$this->actual_time) return null;

        $estimated = $this->estimated_hours;
        $actual = $this->actual_hours;

        if ($estimated == 0) return null;

        return ($actual / $estimated) * 100;
    }

    /**
     * Scope for creative projects
     */
    public function scopeCreativeProjects($query)
    {
        return $query->where('category', 'Creative');
    }

    /**
     * Scope for academic projects
     */
    public function scopeAcademicProjects($query)
    {
        return $query->where('category', 'Academic');
    }

    /**
     * Scope for pending tasks
     */
    public function scopePending($query)
    {
        return $query->where('status', 'todo');
    }

    /**
     * Scope for in progress tasks
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'doing');
    }

    /**
     * Scope for completed tasks
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'done');
    }

    /**
     * Scope for overdue tasks
     */
    public function scopeOverdue($query)
    {
        return $query->whereDate('due_date', '<', now())
            ->whereNotIn('status', ['done', 'archived']);
    }

    /**
     * Scope for today's tasks
     */
    public function scopeToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    /**
     * Scope by project type
     */
    public function scopeByProjectType($query, $projectType)
    {
        return $query->where('project_type', $projectType);
    }

    /**
     * Scope by workflow stage
     */
    public function scopeByWorkflowStage($query, $stage)
    {
        return $query->where('workflow_stage', $stage);
    }

    /**
     * Scope for tasks with subtasks
     */
    public function scopeWithSubtasks($query)
    {
        return $query->where('total_subtasks', '>', 0);
    }

    /**
     * Update progress from subtasks
     */
    public function updateProgressFromSubtasks(): void
    {
        if ($this->subtasks()->count() > 0) {
            $total = $this->subtasks()->count();
            $completed = $this->subtasks()->where('status', 'completed')->count();

            $this->completed_subtasks = $completed;
            $this->progress = $total > 0 ? round(($completed / $total) * 100) : 0;

            // Auto update status based on progress
            if ($this->progress >= 100 && $this->status !== 'done') {
                $this->markAsComplete();
            } elseif ($this->progress > 0 && $this->status === 'todo') {
                $this->status = 'doing';
                $this->started_at = $this->started_at ?? now();
            }

            $this->save();
        }
    }

    /**
     * Mark task as complete
     */
    public function markAsComplete(): void
    {
        $this->update([
            'status' => 'done',
            'progress' => 100,
            'completed_at' => now(),
            'actual_time' => $this->calculateActualTime(),
        ]);

        // Mark all subtasks as completed
        $this->subtasks()->where('status', '!=', 'completed')->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
        ]);

        // Log productivity
        $this->logProductivity();
    }

    /**
     * Start working on task
     */
    public function startWorking(): void
    {
        $this->update([
            'status' => 'doing',
            'started_at' => now(),
            'progress' => max(25, $this->progress), // Minimum 25% when starting
        ]);
    }

    /**
     * Add a link to the task
     */
    public function addLink(string $type, string $url, ?string $label = null): void
    {
        $links = $this->links ?? [];
        $links[] = [
            'type' => $type,
            'url' => $url,
            'label' => $label ?? ucfirst($type),
            'added_at' => now()->toDateTimeString(),
        ];

        $this->links = $links;
        $this->save();
    }

    /**
     * Add a tag to the task
     */
    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->tags = $tags;
            $this->save();
        }
    }

    /**
     * Get creative workflow stages based on project type
     */
    public function getWorkflowStages(): array
    {
        $stages = [
            'video_editing' => [
                ['key' => 'planning', 'label' => 'Perencanaan'],
                ['key' => 'script', 'label' => 'Naskah/Script'],
                ['key' => 'recording', 'label' => 'Rekaman'],
                ['key' => 'editing', 'label' => 'Editing'],
                ['key' => 'review', 'label' => 'Review'],
                ['key' => 'publish', 'label' => 'Publish'],
            ],
            'graphic_design' => [
                ['key' => 'planning', 'label' => 'Brief & Research'],
                ['key' => 'concept', 'label' => 'Konsep & Moodboard'],
                ['key' => 'design', 'label' => 'Desain'],
                ['key' => 'review', 'label' => 'Review Client'],
                ['key' => 'revision', 'label' => 'Revisi'],
                ['key' => 'finalize', 'label' => 'Finalisasi'],
            ],
            'animation' => [
                ['key' => 'script', 'label' => 'Script & Storyboard'],
                ['key' => 'design', 'label' => 'Character & Asset Design'],
                ['key' => 'animation', 'label' => 'Animasi'],
                ['key' => 'editing', 'label' => 'Sound & Editing'],
                ['key' => 'review', 'label' => 'Review'],
                ['key' => 'publish', 'label' => 'Render & Delivery'],
            ],
            'research' => [
                ['key' => 'planning', 'label' => 'Perencanaan'],
                ['key' => 'research', 'label' => 'Literature Review'],
                ['key' => 'analysis', 'label' => 'Analisis Data'],
                ['key' => 'writing', 'label' => 'Penulisan'],
                ['key' => 'review', 'label' => 'Review'],
                ['key' => 'finalize', 'label' => 'Finalisasi'],
            ],
        ];

        return $stages[$this->project_type] ?? [
            ['key' => 'planning', 'label' => 'Perencanaan'],
            ['key' => 'doing', 'label' => 'Pengerjaan'],
            ['key' => 'review', 'label' => 'Review'],
            ['key' => 'finalize', 'label' => 'Finalisasi'],
        ];
    }

    /**
     * Create default subtasks based on workflow
     */
    public function createDefaultSubtasks(): array
    {
        $stages = $this->getWorkflowStages();
        $createdSubtasks = [];

        foreach ($stages as $index => $stage) {
            $subtask = $this->subtasks()->create([
                'user_id' => $this->user_id,
                'title' => $stage['label'] . ' - ' . $this->title,
                'description' => "Stage: " . $stage['label'],
                'type' => 'stage',
                'stage_key' => $stage['key'],
                'stage_label' => $stage['label'],
                'order' => $index,
                'estimated_minutes' => 60,
                'status' => 'pending',
            ]);

            $createdSubtasks[] = $subtask;
        }

        $this->update([
            'total_subtasks' => count($stages),
            'workflow_stage' => $stages[0]['key'],
        ]);

        return $createdSubtasks;
    }

    /**
     * Calculate actual time from subtasks
     */
    private function calculateActualTime(): string
    {
        $totalMinutes = $this->subtasks()->sum('actual_minutes') ?? 0;

        if ($totalMinutes > 0) {
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            return sprintf('%02d:%02d:00', $hours, $minutes);
        }

        return $this->actual_time ?? '00:00:00';
    }

    /**
     * Log productivity when task is completed
     */
    private function logProductivity(): void
    {
        $totalMinutes = $this->subtasks()->sum('actual_minutes') ??
            ($this->estimated_hours * 60) ?? 60;

        ProductivityLog::create([
            'user_id' => $this->user_id,
            'log_date' => now()->toDateString(),
            'task_id' => $this->id,
            'activity_type' => 'task_completed',
            'duration_minutes' => $totalMinutes,
            'focus_score' => 90,
            'energy_level' => 80,
            'description' => 'Menyelesaikan task: ' . $this->title,
            'details' => json_encode([
                'category' => $this->category,
                'project_type' => $this->project_type,
                'priority' => $this->priority,
                'progress' => $this->progress,
            ]),
            'task_snapshot' => json_encode([
                'title' => $this->title,
                'category' => $this->category,
                'project_type' => $this->project_type,
                'progress' => $this->progress,
                'completed_at' => now()->toDateTimeString(),
            ]),
        ]);
    }

    /**
     * Get completion rate of subtasks
     */
    public function getSubtaskCompletionRate(): float
    {
        if ($this->total_subtasks == 0) return 0;

        return ($this->completed_subtasks / $this->total_subtasks) * 100;
    }

    /**
     * Get next workflow stage
     */
    public function getNextWorkflowStage(): ?string
    {
        $stages = $this->getWorkflowStages();
        $currentIndex = array_search($this->workflow_stage, array_column($stages, 'key'));

        if ($currentIndex !== false && isset($stages[$currentIndex + 1])) {
            return $stages[$currentIndex + 1]['key'];
        }

        return null;
    }

    /**
     * Advance to next workflow stage
     */
    public function advanceWorkflowStage(): bool
    {
        $nextStage = $this->getNextWorkflowStage();

        if ($nextStage) {
            $this->update(['workflow_stage' => $nextStage]);
            return true;
        }

        return false;
    }
}
