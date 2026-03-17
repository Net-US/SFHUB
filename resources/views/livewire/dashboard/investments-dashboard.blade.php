<div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
    <h3 class="font-bold text-stone-800 dark:text-white mb-4 flex items-center">
        <i class="fa-solid fa-chart-line text-emerald-500 mr-2"></i>
        Investasi
    </h3>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="text-center p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
            <div class="text-lg font-bold text-stone-800 dark:text-white">Rp{{ number_format($totalInvested, 0, ',', '.') }}</div>
            <div class="text-xs text-stone-500 dark:text-stone-400">Total Investasi</div>
        </div>
        <div class="text-center p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
            <div class="text-lg font-bold text-stone-800 dark:text-white">Rp{{ number_format($totalCurrentValue, 0, ',', '.') }}</div>
            <div class="text-xs text-stone-500 dark:text-stone-400">Nilai Saat Ini</div>
        </div>
        <div class="text-center p-4 {{ $totalProfitLoss >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-rose-50 dark:bg-rose-900/20' }} rounded-xl">
            <div class="text-lg font-bold {{ $totalProfitLoss >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                {{ $totalProfitLoss >= 0 ? '+' : '' }}Rp{{ number_format($totalProfitLoss, 0, ',', '.') }}
            </div>
            <div class="text-xs {{ $totalProfitLoss >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Profit/Loss</div>
        </div>
    </div>

    @if(!empty($investments))
        <div class="mb-4">
            <div class="text-sm font-semibold text-stone-700 dark:text-stone-300 mb-2">Portfolio</div>
            @foreach($investments as $investment)
                <div class="flex justify-between items-center p-3 bg-stone-50 dark:bg-stone-800 rounded-lg mb-2">
                    <div>
                        <div class="text-sm font-medium text-stone-800 dark:text-white">{{ $investment['name'] }}</div>
                        <div class="text-xs text-stone-500">{{ strtoupper($investment['symbol']) }} • {{ ucfirst($investment['type']) }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold {{ $investment['profit_loss'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $investment['profit_loss_percentage'] >= 0 ? '+' : '' }}{{ round($investment['profit_loss_percentage'], 1) }}%
                        </div>
                        <div class="text-xs text-stone-500">Rp{{ number_format($investment['current_value'], 0, ',', '.') }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
