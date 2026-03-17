<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Methods
    public static function getValue($userId, $key, $default = null)
    {
        $setting = self::where('user_id', $userId)
                      ->where('key', $key)
                      ->first();

        return $setting ? $setting->value : $default;
    }

    public static function setValue($userId, $key, $value)
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value]
        );
    }

    public static function getSettings($userId)
    {
        return self::where('user_id', $userId)
                  ->pluck('value', 'key')
                  ->toArray();
    }

    public static function getThemeSettings($userId)
    {
        $theme = self::getValue($userId, 'theme', 'light');
        $colorScheme = self::getValue($userId, 'color_scheme', 'orange');

        return [
            'theme' => $theme,
            'color_scheme' => $colorScheme,
            'dark_mode' => $theme === 'dark',
        ];
    }

    public static function getFinanceSettings($userId)
    {
        return [
            'currency' => self::getValue($userId, 'currency', 'IDR'),
            'monthly_income_target' => (float) self::getValue($userId, 'monthly_income_target', 0),
            'monthly_expense_limit' => (float) self::getValue($userId, 'monthly_expense_limit', 0),
            'savings_target' => (float) self::getValue($userId, 'savings_target', 0),
            'investment_target' => (float) self::getValue($userId, 'investment_target', 0),
        ];
    }

    public static function getProductivitySettings($userId)
    {
        return [
            'focus_mode' => (bool) self::getValue($userId, 'focus_mode', false),
            'pomodoro_enabled' => (bool) self::getValue($userId, 'pomodoro_enabled', true),
            'pomodoro_work' => (int) self::getValue($userId, 'pomodoro_work', 25),
            'pomodoro_break' => (int) self::getValue($userId, 'pomodoro_break', 5),
            'daily_task_limit' => (int) self::getValue($userId, 'daily_task_limit', 10),
            'reminder_notifications' => (bool) self::getValue($userId, 'reminder_notifications', true),
        ];
    }
}
