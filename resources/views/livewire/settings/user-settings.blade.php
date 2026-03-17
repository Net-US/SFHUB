<div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
    <h3 class="font-bold text-stone-800 dark:text-white mb-6">Pengaturan</h3>

    <div class="space-y-6">
        {{-- Profile Section --}}
        <div class="border-b border-stone-200 dark:border-stone-700 pb-6">
            <h4 class="font-semibold text-stone-700 dark:text-stone-300 mb-4">Profil</h4>
            <form wire:submit.prevent="saveProfile" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nama</label>
                    <input type="text" wire:model="name" class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Email</label>
                    <input type="email" wire:model="email" class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 dark:text-white">
                </div>
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm">Simpan Profil</button>
            </form>
        </div>

        {{-- Sleep & Prayer Section --}}
        <div class="border-b border-stone-200 dark:border-stone-700 pb-6">
            <h4 class="font-semibold text-stone-700 dark:text-stone-300 mb-4">Waktu Tidur & Sholat</h4>
            <form wire:submit.prevent="saveSleepPrayer" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tidur</label>
                        <input type="time" wire:model="sleep_start" class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Bangun</label>
                        <input type="time" wire:model="sleep_end" class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 dark:text-white">
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="auto_prayer_times" class="rounded border-stone-300 dark:border-stone-600 mr-2">
                        <span class="text-sm text-stone-700 dark:text-stone-300">Auto jadwal sholat</span>
                    </label>
                    @if($auto_prayer_times)
                        <input type="text" wire:model="prayer_city" placeholder="Kota" class="rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 dark:text-white text-sm">
                    @endif
                </div>
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm">Simpan</button>
            </form>
        </div>

        {{-- Content Platforms --}}
        <div class="border-b border-stone-200 dark:border-stone-700 pb-6">
            <h4 class="font-semibold text-stone-700 dark:text-stone-300 mb-4">Platform Konten</h4>
            <form wire:submit.prevent="saveContentPlatforms" class="space-y-3">
                @foreach($platforms as $index => $platform)
                    <div class="flex items-center gap-4 p-3 bg-stone-50 dark:bg-stone-800 rounded-lg">
                        <label class="flex items-center flex-1">
                            <input type="checkbox" wire:model="platforms.{{ $index }}.active" class="rounded border-stone-300 dark:border-stone-600 mr-3">
                            <span class="font-medium text-stone-700 dark:text-stone-300 capitalize">{{ $platform['name'] }}</span>
                        </label>
                        @if($platform['active'])
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-stone-500">Target:</span>
                                <input type="number" wire:model="platforms.{{ $index }}.target" min="1" class="w-16 text-center rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 dark:text-white text-sm">
                                <span class="text-xs text-stone-500">/minggu</span>
                            </div>
                        @endif
                    </div>
                @endforeach
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm">Simpan Platform</button>
            </form>
        </div>

        {{-- Notifications --}}
        <div>
            <h4 class="font-semibold text-stone-700 dark:text-stone-300 mb-4">Notifikasi</h4>
            <form wire:submit.prevent="saveNotifications" class="space-y-3">
                <label class="flex items-center">
                    <input type="checkbox" wire:model="email_notifications" class="rounded border-stone-300 dark:border-stone-600 mr-3">
                    <span class="text-stone-700 dark:text-stone-300">Email notifications</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" wire:model="daily_summary" class="rounded border-stone-300 dark:border-stone-600 mr-3">
                    <span class="text-stone-700 dark:text-stone-300">Daily summary</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" wire:model="deadline_alerts" class="rounded border-stone-300 dark:border-stone-600 mr-3">
                    <span class="text-stone-700 dark:text-stone-300">Deadline alerts</span>
                </label>
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm">Simpan Notifikasi</button>
            </form>
        </div>
    </div>
</div>
