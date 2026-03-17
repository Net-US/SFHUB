<div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
    <h3 class="font-bold text-stone-800 dark:text-white mb-4 flex items-center">
        <i class="fa-solid fa-hand-holding-usd text-rose-500 mr-2"></i>
        Hutang & Piutang
    </h3>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="text-center p-4 bg-rose-50 dark:bg-rose-900/20 rounded-xl">
            <div class="text-lg font-bold text-rose-600 dark:text-rose-400">Rp{{ number_format($totalPayable, 0, ',', '.') }}</div>
            <div class="text-xs text-rose-600 dark:text-rose-400">Hutang (Payable)</div>
        </div>
        <div class="text-center p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp{{ number_format($totalReceivable, 0, ',', '.') }}</div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400">Piutang (Receivable)</div>
        </div>
    </div>

    @if(!empty($upcoming))
        <div class="mb-4">
            <div class="text-sm font-semibold text-stone-700 dark:text-stone-300 mb-2">Jatuh Tempo 7 Hari Ke Depan</div>
            @foreach($upcoming as $debt)
                <div class="flex justify-between items-center p-3 {{ $debt['is_overdue'] ? 'bg-rose-50 dark:bg-rose-900/20 border border-rose-200' : 'bg-stone-50 dark:bg-stone-800' }} rounded-lg mb-2">
                    <div>
                        <div class="text-sm font-medium text-stone-800 dark:text-white">{{ $debt['debtor'] }}</div>
                        <div class="text-xs {{ $debt['is_overdue'] ? 'text-rose-600' : 'text-stone-500' }}">
                            {{ $debt['is_overdue'] ? 'Overdue ' : '' }}{{ $debt['due_date'] }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-stone-800 dark:text-white">Rp{{ number_format($debt['remaining'], 0, ',', '.') }}</div>
                        <div class="text-xs text-stone-500">{{ round($debt['progress']) }}% lunas</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
