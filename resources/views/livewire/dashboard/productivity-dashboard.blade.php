<div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
    <h3 class="font-bold text-stone-800 dark:text-white mb-4 flex items-center">
        <i class="fa-solid fa-chart-line text-indigo-500 mr-2"></i>
        Produktivitas
    </h3>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="text-center p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
            <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $stats['today']['completed'] }}/{{ $stats['today']['total'] }}</div>
            <div class="text-xs text-stone-500 dark:text-stone-400">Hari Ini</div>
        </div>
        <div class="text-center p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
            <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $stats['week']['completed'] }}/{{ $stats['week']['total'] }}</div>
            <div class="text-xs text-stone-500 dark:text-stone-400">Minggu Ini</div>
        </div>
        <div class="text-center p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
            <div class="text-2xl font-bold text-stone-800 dark:text-white">{{ $stats['month']['completed'] }}/{{ $stats['month']['total'] }}</div>
            <div class="text-xs text-stone-500 dark:text-stone-400">Bulan Ini</div>
        </div>
    </div>

    @if($pklStreak > 0)
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-emerald-600 dark:text-emerald-400 font-semibold">Streak PKL</div>
                    <div class="text-2xl font-bold text-emerald-800 dark:text-emerald-300">{{ $pklStreak }} hari</div>
                </div>
                <i class="fa-solid fa-fire text-3xl text-emerald-500"></i>
            </div>
        </div>
    @endif

    @if(!empty($contentProgress))
        <div class="mb-4">
            <div class="text-sm font-semibold text-stone-700 dark:text-stone-300 mb-2">Progress Konten</div>
            @foreach($contentProgress as $platform => $data)
                <div class="mb-2">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-stone-600 dark:text-stone-400">{{ ucfirst($platform) }}</span>
                        <span class="text-stone-600 dark:text-stone-400">{{ $data['completed'] }}/{{ $data['target'] }}</span>
                    </div>
                    <div class="h-2 bg-stone-200 dark:bg-stone-700 rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $data['progress_percentage'] >= 100 ? 'emerald' : ($data['progress_percentage'] >= 50 ? 'blue' : 'amber') }}-500 rounded-full"
                             style="width: {{ min(100, $data['progress_percentage']) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
