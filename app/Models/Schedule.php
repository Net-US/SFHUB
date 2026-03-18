<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    // ════════════════════════════════════════════════════════════════════════
    // KATEGORI TERSTANDAR - Standarisasi untuk seluruh aplikasi
    // ════════════════════════════════════════════════════════════════════════
    const CATEGORY_PKL = 'pkl';
    const CATEGORY_ACADEMIC = 'academic';
    const CATEGORY_CREATIVE = 'creative';
    const CATEGORY_FREELANCE = 'freelance';
    const CATEGORY_HEALTH = 'health';
    const CATEGORY_FINANCE = 'finance';
    const CATEGORY_PERSONAL = 'personal';
    const CATEGORY_ROUTINE = 'routine';
    const CATEGORY_SKRIPSI = 'skripsi';

    // Label untuk display
    const CATEGORY_LABELS = [
        self::CATEGORY_PKL => 'PKL',
        self::CATEGORY_ACADEMIC => 'Akademik',
        self::CATEGORY_SKRIPSI => 'Skripsi',
        self::CATEGORY_CREATIVE => 'Kreatif',
        self::CATEGORY_FREELANCE => 'Freelance',
        self::CATEGORY_HEALTH => 'Kesehatan',
        self::CATEGORY_FINANCE => 'Keuangan',
        self::CATEGORY_PERSONAL => 'Personal',
        self::CATEGORY_ROUTINE => 'Rutin',
    ];

    // Icon untuk kategori
    const CATEGORY_ICONS = [
        self::CATEGORY_PKL => 'fa-briefcase',
        self::CATEGORY_ACADEMIC => 'fa-graduation-cap',
        self::CATEGORY_SKRIPSI => 'fa-pen-to-square',
        self::CATEGORY_CREATIVE => 'fa-palette',
        self::CATEGORY_FREELANCE => 'fa-laptop-code',
        self::CATEGORY_HEALTH => 'fa-heart-pulse',
        self::CATEGORY_FINANCE => 'fa-coins',
        self::CATEGORY_PERSONAL => 'fa-user',
        self::CATEGORY_ROUTINE => 'fa-repeat',
    ];

    // Warna untuk kategori
    const CATEGORY_COLORS = [
        self::CATEGORY_PKL => '#10b981',      // emerald
        self::CATEGORY_ACADEMIC => '#3b82f6', // blue
        self::CATEGORY_SKRIPSI => '#f97316',  // orange
        self::CATEGORY_CREATIVE => '#8b5cf6', // violet
        self::CATEGORY_FREELANCE => '#a855f7', // purple
        self::CATEGORY_HEALTH => '#ef4444',   // red
        self::CATEGORY_FINANCE => '#f59e0b',  // amber
        self::CATEGORY_PERSONAL => '#6b7280', // gray
        self::CATEGORY_ROUTINE => '#14b8a6',  // teal
    ];

    protected $fillable = [
        'user_id',
        // ── Konten ─────────────────────────────
        'activity',       // nama kegiatan
        'title',          // alias activity (backward compat)
        'type',           // KATEGORI TERSTANDAR (lihat const di atas)
        'location',
        'instructor',
        'course_code',
        'notes',
        'color',          // hex warna pilihan user (opsional, override default)
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

    public function scopeActiveToday($query)
    {
        $todayDow = $this->getIndonesianDayName(now()->dayOfWeek);

        return $query->where(function ($q) {
            $q->whereNull('start_date')->orWhere('start_date', '<=', today());
        })->where(function ($q) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', today());
        })->where(function ($q) use ($todayDow) {
            $q->where('frequency', 'daily')
                ->orWhere('day', $todayDow)
                ->orWhere('days_of_week', 'LIKE', "%{$todayDow}%");
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

    /**
     * Normalisasi kategori - selalu kembalikan kategori standar
     */
    public function getNormalizedCategory(): string
    {
        $type = strtolower(trim($this->type ?? 'routine'));

        return match ($type) {
            'pkl', 'pkk', 'kerja', 'work', 'internship' => self::CATEGORY_PKL,
            'academic', 'kuliah', 'academy', 'college', 'university', 'course' => self::CATEGORY_ACADEMIC,
            'skripsi', 'thesis', 'tugas_akhir', 'final_project', 'ta' => self::CATEGORY_SKRIPSI,
            'creative', 'kreatif', 'content', 'konten', 'video', 'design' => self::CATEGORY_CREATIVE,
            'freelance', 'freelancer', 'project', 'proyek', 'client' => self::CATEGORY_FREELANCE,
            'health', 'kesehatan', 'workout', 'olahraga', 'fitness' => self::CATEGORY_HEALTH,
            'finance', 'keuangan', 'money', 'uang', 'budget' => self::CATEGORY_FINANCE,
            'personal', 'organisasi', 'org', 'self', 'family', 'keluarga' => self::CATEGORY_PERSONAL,
            'routine', 'rutin', 'rutinitas', 'habit', 'kebiasaan', 'daily' => self::CATEGORY_ROUTINE,
            default => self::CATEGORY_ROUTINE,
        };
    }

    /**
     * Get type color dengan fallback ke warna default kategori
     */
    public function getTypeColor(): string
    {
        $cat = $this->getNormalizedCategory();

        return match ($cat) {
            self::CATEGORY_ACADEMIC => 'bg-blue-100 text-blue-800 border-blue-300',
            self::CATEGORY_SKRIPSI => 'bg-orange-100 text-orange-800 border-orange-300',
            self::CATEGORY_CREATIVE => 'bg-violet-100 text-violet-800 border-violet-300',
            self::CATEGORY_FREELANCE => 'bg-purple-100 text-purple-800 border-purple-300',
            self::CATEGORY_PKL => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::CATEGORY_HEALTH => 'bg-red-100 text-red-800 border-red-300',
            self::CATEGORY_FINANCE => 'bg-amber-100 text-amber-800 border-amber-300',
            self::CATEGORY_PERSONAL => 'bg-gray-100 text-gray-800 border-gray-300',
            self::CATEGORY_ROUTINE => 'bg-teal-100 text-teal-800 border-teal-300',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }

    /**
     * Get icon untuk kategori
     */
    public function getIcon(): string
    {
        $cat = $this->getNormalizedCategory();
        return self::CATEGORY_ICONS[$cat] ?? 'fa-calendar';
    }

    /**
     * Get display label untuk kategori
     */
    public function getCategoryLabel(): string
    {
        $cat = $this->getNormalizedCategory();
        return self::CATEGORY_LABELS[$cat] ?? 'Lainnya';
    }

    /**
     * Get color hex untuk kategori
     */
    public function getCategoryColor(): string
    {
        $cat = $this->getNormalizedCategory();
        return $this->color ?? self::CATEGORY_COLORS[$cat] ?? '#6b7280';
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

    /**
     * Helper untuk mengkonversi dayOfWeek ke nama hari Indonesia
     */
    public static function getIndonesianDayName(int $dayOfWeek): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $days[$dayOfWeek] ?? 'Senin';
    }
}
