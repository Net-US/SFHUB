<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_code',
        'course_name',
        'credits',
        'lecturer',
        'semester',
        'progress',
        'assignments',
        'grades',
        'notes',
    ];

    protected $casts = [
        'assignments' => 'array',
        'grades' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeCurrentSemester($query)
    {
        // Assuming current semester is stored in user profile
        return $query->where('semester', function ($query) {
            $query->select('semester')
                ->from('profiles')
                ->whereColumn('user_id', 'academic_courses.user_id')
                ->limit(1);
        });
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    // Methods
    public function getAverageGrade()
    {
        if (empty($this->grades)) {
            return null;
        }

        $grades = collect($this->grades);
        $totalWeight = $grades->sum('weight');
        $weightedSum = $grades->sum(fn($grade) => $grade['score'] * ($grade['weight'] ?? 1));

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }

    public function getGradeLetter()
    {
        $average = $this->getAverageGrade();

        if ($average === null) {
            return '-';
        }

        return match (true) {
            $average >= 85 => 'A',
            $average >= 80 => 'A-',
            $average >= 75 => 'B+',
            $average >= 70 => 'B',
            $average >= 65 => 'B-',
            $average >= 60 => 'C+',
            $average >= 55 => 'C',
            $average >= 50 => 'C-',
            $average >= 45 => 'D',
            default => 'E',
        };
    }

    public function getNextAssignment()
    {
        if (empty($this->assignments)) {
            return null;
        }

        $assignments = collect($this->assignments);
        return $assignments->where('completed', false)
            ->sortBy('due_date')
            ->first();
    }

    public function getCompletedAssignmentsCount()
    {
        if (empty($this->assignments)) {
            return 0;
        }

        return collect($this->assignments)->where('completed', true)->count();
    }

    public function getTotalAssignmentsCount()
    {
        if (empty($this->assignments)) {
            return 0;
        }

        return count($this->assignments);
    }
}
