<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingHero extends Model
{
    use HasFactory;

    protected $table = 'landing_heroes';

    protected $fillable = [
        'title',
        'subtitle',
        'cta_text',
        'cta_link',
        'hero_image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
