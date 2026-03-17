<div>
    <!-- Header Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Saldo</dt>
                        <dd class="text-lg font-semibold text-gray-900">Rp {{ number_format($totalBalance, 0, ',', '.') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Pemasukan Bulan Ini</dt>
                        <dd class="text-lg font-semibold text-green-600">+Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Pengeluaran Bulan Ini</dt>
                        <dd class="text-lg font-semibold text-red-600">-Rp {{ number_format($monthlyExpense, 0, ',', '.') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button 
                    wire:click="switchTab('accounts')"
                    class="{{ $activeTab === 'accounts' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} py-4 px-6 text-sm font-medium border-b-2 focus:outline-none"
                >
                    Akun Keuangan
                </button>
                <button 
                    wire:click="switchTab('transactions')"
                    class="{{ $activeTab === 'transactions' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} py-4 px-6 text-sm font-medium border-b-2 focus:outline-none"
                >
                    Transaksi
                </button>
            </nav>
        </div>

        <div class="p-6">
            <!-- Accounts Tab -->
            @if($activeTab === 'accounts')
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Akun Keuangan</h3>
                    <button wire:click="openAccountModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                        Tambah Akun
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($accounts as $account)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg" style="background-color: {{ $account->color }}20;">
                                        💰
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $account->name }}</h4>
                                        <span class="text-xs text-gray-500">{{ ucfirst($account->type) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-lg font-semibold text-gray-900">
                                Rp {{ number_format($account->balance, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($accounts->count() === 0)
                    <div class="text-center py-8">
                        <div class="text-gray-400 text-4xl mb-2">💰</div>
                        <p class="text-gray-600">Belum ada akun keuangan</p>
                        <button wire:click="openAccountModal" class="mt-2 text-blue-600 hover:text-blue-800 text-sm">
                            Tambah akun pertama
                        </button>
                    </div>
                @endif
            @endif

            <!-- Transactions Tab -->
            @if($activeTab === 'transactions')
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Transaksi Terbaru</h3>
                    <button wire:click="openTransactionModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                        Tambah Transaksi
                    </button>
                </div>

                <div class="space-y-3">
                    @foreach($recentTransactions as $transaction)
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg {{ $transaction->type === 'income' ? 'bg-green-100' : ($transaction->type === 'expense' ? 'bg-red-100' : 'bg-blue-100') }}">
                                    {{ $transaction->type === 'income' ? '📈' : ($transaction->type === 'expense' ? '📉' : '🔄') }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $transaction->description }}</p>
                                    <p class="text-sm text-gray-500">{{ $transaction->category }} • {{ $transaction->account->name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold {{ $transaction->type === 'income' ? 'text-green-600' : ($transaction->type === 'expense' ? 'text-red-600' : 'text-blue-600') }}">
                                    @if($transaction->type === 'income') + @endif
                                    @if($transaction->type === 'expense') - @endif
                                    Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($recentTransactions->count() === 0)
                    <div class="text-center py-8">
                        <div class="text-gray-400 text-4xl mb-2">💰</div>
                        <p class="text-gray-600">Belum ada transaksi</p>
                        <button wire:click="openTransactionModal" class="mt-2 text-blue-600 hover:text-blue-800 text-sm">
                            Tambah transaksi pertama
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Account Modal -->
    @if($showAccountModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah Akun Baru</h3>
                    <button wire:click="closeAccountModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <livewire:finance.finance-account-manager />

                <div class="flex gap-3 mt-6">
                    <button wire:click="closeAccountModal" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Transaction Modal -->
    @if($showTransactionModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah Transaksi Baru</h3>
                    <button wire:click="closeTransactionModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <livewire:finance.transaction-manager />

                <div class="flex gap-3 mt-6">
                    <button wire:click="closeTransactionModal" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
