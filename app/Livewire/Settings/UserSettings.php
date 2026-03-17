<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Setting;
use App\Models\ContentSchedule;
use App\Models\User;

class UserSettings extends Component
{
    public $user;
    public $settings = [];
    public $contentPlatforms = [];
    public $prayerSettings = [];

    // Profile
    public $name = '';
    public $email = '';
    public $avatar = '';

    // Sleep & Prayer
    public $sleep_start = '22:00';
    public $sleep_end = '06:00';
    public $prayer_city = 'Jakarta';
    public $auto_prayer_times = true;

    // Content Platforms
    public $platforms = [
        ['name' => 'instagram', 'active' => false, 'target' => 3],
        ['name' => 'youtube', 'active' => false, 'target' => 1],
        ['name' => 'tiktok', 'active' => false, 'target' => 5],
        ['name' => 'twitter', 'active' => false, 'target' => 7],
        ['name' => 'linkedin', 'active' => false, 'target' => 2],
    ];

    // Notifications
    public $email_notifications = true;
    public $push_notifications = true;
    public $daily_summary = true;
    public $deadline_alerts = true;

    public function mount()
    {
        $this->user = auth()->user();
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->avatar = $this->user->avatar;

        // Load existing settings
        $dbSettings = Setting::where('user_id', $this->user->id)->first();

        if ($dbSettings) {
            $prefs = $dbSettings->preferences ?? [];
            $this->sleep_start = $prefs['sleep_start'] ?? '22:00';
            $this->sleep_end = $prefs['sleep_end'] ?? '06:00';
            $this->prayer_city = $prefs['prayer_city'] ?? 'Jakarta';
            $this->auto_prayer_times = $prefs['auto_prayer_times'] ?? true;
            $this->email_notifications = $prefs['email_notifications'] ?? true;
            $this->push_notifications = $prefs['push_notifications'] ?? true;
            $this->daily_summary = $prefs['daily_summary'] ?? true;
            $this->deadline_alerts = $prefs['deadline_alerts'] ?? true;
        }

        // Load content platforms
        $contentSchedules = ContentSchedule::where('user_id', $this->user->id)
            ->active()
            ->get();

        foreach ($this->platforms as &$platform) {
            $schedule = $contentSchedules->firstWhere('platform', $platform['name']);
            if ($schedule) {
                $platform['active'] = true;
                $platform['target'] = $schedule->target_per_period;
            }
        }
    }

    public function saveProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
        ]);

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->dispatch('profile-saved');
    }

    public function saveSleepPrayer()
    {
        $settings = Setting::firstOrNew(['user_id' => $this->user->id]);
        $prefs = $settings->preferences ?? [];

        $prefs['sleep_start'] = $this->sleep_start;
        $prefs['sleep_end'] = $this->sleep_end;
        $prefs['prayer_city'] = $this->prayer_city;
        $prefs['auto_prayer_times'] = $this->auto_prayer_times;

        $settings->preferences = $prefs;
        $settings->save();

        $this->dispatch('sleep-prayer-saved');
    }

    public function saveContentPlatforms()
    {
        foreach ($this->platforms as $platform) {
            if ($platform['active']) {
                ContentSchedule::updateOrCreate(
                    [
                        'user_id' => $this->user->id,
                        'platform' => $platform['name'],
                    ],
                    [
                        'target_per_period' => $platform['target'],
                        'frequency' => 'weekly',
                        'status' => 'active',
                        'due_date' => now()->endOfWeek(),
                    ]
                );
            } else {
                ContentSchedule::where('user_id', $this->user->id)
                    ->where('platform', $platform['name'])
                    ->delete();
            }
        }

        $this->dispatch('platforms-saved');
    }

    public function saveNotifications()
    {
        $settings = Setting::firstOrNew(['user_id' => $this->user->id]);
        $prefs = $settings->preferences ?? [];

        $prefs['email_notifications'] = $this->email_notifications;
        $prefs['push_notifications'] = $this->push_notifications;
        $prefs['daily_summary'] = $this->daily_summary;
        $prefs['deadline_alerts'] = $this->deadline_alerts;

        $settings->preferences = $prefs;
        $settings->save();

        $this->dispatch('notifications-saved');
    }

    public function render()
    {
        return view('livewire.settings.user-settings');
    }
}
