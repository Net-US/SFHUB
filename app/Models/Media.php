<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'original_name',
        'filepath',
        'mime_type',
        'size',
        'extension',
        'type',
        'alt_text',
        'description',
        'folder',
        'is_public',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByFolder($query, string $folder)
    {
        return $query->where('folder', $folder);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function getFullPath(): string
    {
        return storage_path('app/public/' . $this->filepath);
    }

    public function getUrl(): string
    {
        return asset('storage/' . $this->filepath);
    }

    public function getSizeForHumans(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
