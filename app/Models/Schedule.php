<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        // ── Konten ─────────────────────────────
        'activity',       // nama kegiatan
        'title',          // alias activity (backward compat)
        'type',           // academic | creative | pkl | personal | routine | health | finance
        'location',
        'instructor',
        'course_code',
        'notes',
        'color',          // hex warna pilihan user
        // ── Waktu ──────────────────────────────
        'start_time',     // HH:MM  (TIME kolom)
        'end_time',       // HH:MM
        'start_date',     // DATE: mulai berlaku
        'end_date',       // DATE: berakhir
        // ── Frekuensi ──────────────────────────
        'is_recurring',
        'frequency',      // daily | weekly | monthly
        'day',            // satu hari Indonesia (backward compat): "Senin"
        'days_of_week',   // comma-separated Indonesia: "Senin,Rabu,Jumat"
        'day_of_month',   // integer 1-31 (untuk monthly)
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
        'start_date'   => 'date',
        'end_date'     => 'date',
        'day_of_month' => 'integer',
    ];

    // ─── Relationships ─────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ────────────────────────────────────────────────────────
    public function scopeByDay($query, $day)
    {
        return $query->where('day', $day)
            ->orWhere(function ($q) use ($day) {
                $q->whereNotNull('days_of_week')
                    ->where('days_of_week', 'LIKE', "%{$day}%");
            });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeActiveNow($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('start_date')->orWhere('start_date', '<=', today());
        })->where(function ($q) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', today());
        });
    }

    // ─── Helpers ───────────────────────────────────────────────────────

    /**
     * Kembalikan array integer hari (0=Min, 1=Sen, ...) sesuai frequency
     */
    public function getDaysArray(): array
    {
        $dayMap = ['Minggu' => 0, 'Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6];

        if ($this->frequency === 'monthly') return [];

        if (!empty($this->days_of_week)) {
            return collect(explode(',', $this->days_of_week))
                ->map(fn($d) => $dayMap[trim($d)] ?? 0)
                ->toArray();
        }
        if (!empty($this->day)) {
            return [$dayMap[trim($this->day)] ?? 1];
        }
        return [];
    }

    /**
     * Apakah kegiatan ini aktif pada tanggal tertentu?
     */
    public function isActiveOn(\Carbon\Carbon $date): bool
    {
        if ($this->start_date && $date->lt($this->start_date)) return false;
        if ($this->end_date   && $date->gt($this->end_date))   return false;

        return match ($this->frequency ?? 'weekly') {
            'daily'   => true,
            'weekly'  => in_array($date->dayOfWeek, $this->getDaysArray()),
            'monthly' => $date->day === ($this->day_of_month ?? 1),
            default   => false,
        };
    }

    public function getTypeColor(): string
    {
        return match ($this->type) {
            'academic' => 'bg-blue-100 text-blue-800 border-blue-300',
            'creative' => 'bg-orange-100 text-orange-800 border-orange-300',
            'pkl'      => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'health'   => 'bg-red-100 text-red-800 border-red-300',
            'finance'  => 'bg-amber-100 text-amber-800 border-amber-300',
            'personal' => 'bg-purple-100 text-purple-800 border-purple-300',
            'routine'  => 'bg-stone-100 text-stone-800 border-stone-300',
            default    => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }

    public function getIcon(): string
    {
        return match ($this->type) {
            'academic' => 'fa-graduation-cap',
            'creative' => 'fa-palette',
            'pkl'      => 'fa-briefcase',
            'health'   => 'fa-heart-pulse',
            'finance'  => 'fa-coins',
            'personal' => 'fa-user',
            'routine'  => 'fa-repeat',
            default    => 'fa-calendar',
        };
    }

    /** Apakah kegiatan ini sedang berlangsung sekarang? */
    public function isNow(): bool
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $today = $days[now()->dayOfWeek];
        $nowTime = now()->format('H:i');

        $dayMatch = $this->day === $today
            || (!empty($this->days_of_week) && str_contains($this->days_of_week, $today));

        if (!$dayMatch) return false;

        $start = $this->start_time ? substr($this->start_time, 0, 5) : '00:00';
        $end   = $this->end_time   ? substr($this->end_time, 0, 5)   : '23:59';

        return $nowTime >= $start && $nowTime <= $end;
    }
}
