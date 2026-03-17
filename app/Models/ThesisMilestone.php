<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThesisMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'target_date',
        'done',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'done'       => 'boolean',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
