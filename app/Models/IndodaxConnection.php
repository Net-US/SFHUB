<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class IndodaxConnection extends Model
{
    use HasFactory;

    protected $table = 'api_connections';

    protected $fillable = [
        'user_id',
        'provider',
        'api_key',
        'api_secret',
        'is_active',
        'last_synced_at',
        'sync_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
        'sync_status' => 'array',
    ];

    protected $hidden = [
        'api_secret', // Jangan expose di JSON response
    ];

    // ── Relationships ──
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Accessors & Mutators ──

    /**
     * Encrypt API secret saat disimpan ke database
     */
    public function setApiSecretAttribute($value): void
    {
        $this->attributes['api_secret'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt API secret saat diambil dari database
     */
    public function getApiSecretAttribute($value): string
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return '';
        }
    }

    // ── Methods ──

    /**
     * Update status sync terakhir
     */
    public function updateSyncStatus(bool $success, ?string $message = null): void
    {
        $this->update([
            'last_synced_at' => now(),
            'sync_status' => [
                'success' => $success,
                'message' => $message,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Cek apakah koneksi valid (ada API key & secret)
     */
    public function isValid(): bool
    {
        return !empty($this->api_key) && !empty($this->api_secret);
    }
}
