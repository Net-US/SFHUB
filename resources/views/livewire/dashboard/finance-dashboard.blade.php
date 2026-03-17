<div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
    <h3 class="font-bold text-stone-800 dark:text-white mb-4 flex items-center">
        <i class="fa-solid fa-wallet text-amber-500 mr-2"></i>
        Keuangan
    </h3>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="text-center p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
            <div class="text-lg font-bold text-stone-800 dark:text-white">Rp{{ number_format($totalBalance, 0, ',', '.') }}</div>
            <div class="text-xs text-stone-500 dark:text-stone-400">Total Saldo</div>
        </div>
        <div class="text-center p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">+Rp{{ number_format($monthlyIncome, 0, ',', '.') }}</div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400">Pemasukan</div>
        </div>
        <div class="text-center p-4 bg-rose-50 dark:bg-rose-900/20 rounded-xl">
            <div class="text-lg font-bold text-rose-600 dark:text-rose-400">-Rp{{ number_format($monthlyExpense, 0, ',', '.') }}</div>
            <div class="text-xs text-rose-600 dark:text-rose-400">Pengeluaran</div>
        </div>
    </div>

    @if(!empty($budgets))
        <div class="mb-4">
            <div class="text-sm font-semibold text-stone-700 dark:text-stone-300 mb-2">Budget</div>
            @foreach($budgets as $budget)
                <div class="mb-2 p-3 bg-stone-50 dark:bg-stone-800 rounded-lg">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-stone-600 dark:text-stone-400">{{ $budget['category'] }}</span>
                        <span class="text-stone-600 dark:text-stone-400 {{ $budget['status'] === 'over' ? 'text-rose-600' : ($budget['status'] === 'warning' ? 'text-amber-600' : '') }}">
                            Rp{{ number_format($budget['spent'], 0, ',', '.') }} / Rp{{ number_format($budget['amount'], 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="h-2 bg-stone-200 dark:bg-stone-700 rounded-full overflow-hidden">
                        <div class="h-full {{ $budget['status'] === 'over' ? 'bg-rose-500' : ($budget['status'] === 'warning' ? 'bg-amber-500' : 'bg-emerald-500') }} rounded-full"
                             style="width: {{ min(100, $budget['percentage']) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($recentTransactions))
        <div>
            <div class="text-sm font-semibold text-stone-700 dark:text-stone-300 mb-2">Transaksi Terbaru</div>
            <div class="space-y-2">
                @foreach(array_slice($recentTransactions, 0, 5) as $transaction)
                    <div class="flex justify-between items-center p-2 bg-stone-50 dark:bg-stone-800 rounded text-sm">
                        <div>
                            <div class="text-stone-800 dark:text-white">{{ $transaction['description'] ?? $transaction['category'] }}</div>
                            <div class="text-xs text-stone-500">{{ \Carbon\Carbon::parse($transaction['transaction_date'])->format('d M') }}</div>
                        </div>
                        <span class="{{ $transaction['type'] === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $transaction['type'] === 'income' ? '+' : '-' }}Rp{{ number_format($transaction['amount'], 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
