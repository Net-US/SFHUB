<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day',
        'type',           // full | half | off | split
        'start_time',     // Sesi 1 mulai
        'end_time',       // Sesi 1 selesai
        'start_time_2',   // Sesi 2 mulai (split shift)
        'end_time_2',     // Sesi 2 selesai (split shift)
        'notes',
    ];

    protected $casts = [
        // Simpan sebagai string H:i — JANGAN cast ke datetime agar
        // tidak di-prefix tanggal hari ini oleh Carbon
        'start_time'   => 'string',
        'end_time'     => 'string',
        'start_time_2' => 'string',
        'end_time_2'   => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /** Format HH:MM, fallback ke '' */
    public function fmt(?string $time): string
    {
        if (!$time) return '';
        // Jika sudah H:i (5 karakter) ambil langsung, kalau datetime ambil 11-15
        return strlen($time) <= 5 ? $time : substr($time, 11, 5);
    }

    public function getStartTimeFormatted(): string
    {
        return $this->fmt($this->start_time);
    }
    public function getEndTimeFormatted(): string
    {
        return $this->fmt($this->end_time);
    }
    public function getStartTime2Formatted(): string
    {
        return $this->fmt($this->start_time_2);
    }
    public function getEndTime2Formatted(): string
    {
        return $this->fmt($this->end_time_2);
    }

    public function hasSplitShift(): bool
    {
        return !empty($this->start_time_2) && !empty($this->end_time_2);
    }

    /** Total jam dalam satu hari (sesi 1 + sesi 2 jika ada) */
    public function getTotalHours(): float
    {
        $calc = function (?string $start, ?string $end): float {
            // Proteksi jika string kosong atau null
            if (!$start || !$end || strlen($start) < 5 || strlen($end) < 5) return 0.0;

            try {
                [$sh, $sm] = explode(':', substr($start, 0, 5));
                [$eh, $em] = explode(':', substr($end, 0, 5));
                $mins = (int)$eh * 60 + (int)$em - ((int)$sh * 60 + (int)$sm);
                return max(0.0, round($mins / 60, 2));
            } catch (\Exception $e) {
                return 0.0;
            }
        };

        return $calc($this->start_time, $this->end_time)
            + $calc($this->start_time_2, $this->end_time_2);
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'full'  => 'Full Day',
            'half'  => 'Half Day',
            'off'   => 'Libur',
            'split' => 'Split Shift',
            default => ucfirst($this->type ?? ''),
        };
    }

    public function getTypeBadgeClass(): string
    {
        return match ($this->type) {
            'full'  => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
            'half'  => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
            'split' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
            'off'   => 'bg-stone-100 dark:bg-stone-700 text-stone-500',
            default => 'bg-stone-100 dark:bg-stone-700 text-stone-500',
        };
    }
}
