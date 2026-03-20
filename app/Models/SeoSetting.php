<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'page',
        'title',
        'description',
        'keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'priority',
        'change_freq',
    ];

    protected $casts = [
        'priority' => 'float',
    ];

    public static function getForPage(string $page): ?self
    {
        return static::where('page', $page)->first();
    }
}
