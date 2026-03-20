<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitemapSetting extends Model
{
    use HasFactory;

    protected $table = 'sitemap_settings';

    protected $fillable = [
        'last_generated',
        'url_count',
        'auto_generate',
        'sitemap_path',
    ];

    protected $casts = [
        'last_generated' => 'datetime',
        'url_count' => 'integer',
        'auto_generate' => 'boolean',
    ];

    public static function getSettings(): ?self
    {
        return static::first();
    }
}
