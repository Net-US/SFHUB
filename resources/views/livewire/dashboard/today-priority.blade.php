<div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-stone-800 dark:text-white flex items-center">
            <i class="fa-solid fa-bolt text-orange-500 mr-2"></i>
            What to do NOW
        </h3>
        <span class="text-sm text-stone-500 dark:text-stone-400 font-mono">{{ $currentTime }}</span>
    </div>

    @if($hasConflict)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-3 mb-4">
            <div class="flex items-center text-red-700 dark:text-red-400 text-sm">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                <span>Ada konflik jadwal yang perlu diselesaikan!</span>
            </div>
        </div>
    @endif

    @if(!empty($contentAlerts))
        @foreach($contentAlerts as $alert)
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3 mb-4">
                <div class="flex items-center text-amber-700 dark:text-amber-400 text-sm">
                    <i class="fa-solid fa-bell mr-2"></i>
                    <span>{{ $alert['message'] }}</span>
                </div>
            </div>
        @endforeach
    @endif

    @if($whatToDoNow['current_activity'])
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 mb-4">
            <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase mb-1">Sedang Berlangsung</div>
            <div class="font-semibold text-stone-800 dark:text-white">{{ $whatToDoNow['current_activity']['title'] }}</div>
            <div class="text-sm text-stone-500 dark:text-stone-400">{{ $whatToDoNow['current_activity']['time'] }}</div>
        </div>
    @endif

    @if($whatToDoNow['current_gap'])
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-4">
            <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase mb-1">Waktu Kosong (Gap)</div>
            <div class="font-semibold text-stone-800 dark:text-white">
                {{ $whatToDoNow['current_gap']['start'] }} - {{ $whatToDoDoNow['current_gap']['end'] }}
                ({{ $whatToDoNow['current_gap']['duration_minutes'] }} menit)
            </div>
        </div>

        @if(!empty($whatToDoNow['recommended_tasks']))
            <div class="mb-4">
                <div class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase mb-2">Rekomendasi Task</div>
                @foreach($whatToDoNow['recommended_tasks'] as $task)
                    <div class="bg-stone-50 dark:bg-stone-800 rounded-lg p-3 mb-2 flex items-center justify-between">
                        <div>
                            <div class="font-medium text-stone-800 dark:text-white text-sm">{{ $task['title'] }}</div>
                            <div class="text-xs text-stone-500 dark:text-stone-400">{{ $task['reason'] ?? '' }}</div>
                        </div>
                        <button wire:click="markTaskComplete({{ $task['id'] }})" class="text-emerald-600 hover:text-emerald-700 text-sm">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    @if($whatToDoNow['next_activity'])
        <div class="bg-stone-50 dark:bg-stone-800 rounded-xl p-4">
            <div class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase mb-1">Selanjutnya</div>
            <div class="font-semibold text-stone-800 dark:text-white">{{ $whatToDoNow['next_activity']['title'] }}</div>
            <div class="text-sm text-stone-500 dark:text-stone-400">
                {{ $whatToDoNow['next_activity']['time'] }}
                ({{ $whatToDoNow['next_activity']['minutes_until'] }} menit lagi)
            </div>
        </div>
    @endif

    @if($whatToDoNow['content_summary'])
        <div class="mt-4 pt-4 border-t border-stone-200 dark:border-stone-700">
            <div class="text-sm text-stone-600 dark:text-stone-300">
                {{ $whatToDoNow['content_summary'] }}
            </div>
        </div>
    @endif
</div>
