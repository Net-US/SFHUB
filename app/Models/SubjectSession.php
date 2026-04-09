<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectSession extends Model
{
    protected $fillable = [
        'subject_id',
        'session_number',
        'date',
        'original_date',
        'type',
        'status',
        'title',
        'notes',
        'material_link'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
