<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalSeo extends Model
{
    use HasFactory;

    protected $table = 'global_seo';

    protected $fillable = [
        'default_title',
        'default_description',
        'default_keywords',
        'author',
        'robots',
        'google_analytics_id',
        'facebook_pixel_id',
        'analytics_active',
    ];

    protected $casts = [
        'analytics_active' => 'boolean',
    ];

    public static function getSettings(): ?self
    {
        return static::first();
    }
}
