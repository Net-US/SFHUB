{{-- resources/views/dashboard/finance.blade.php --}}
@extends('layouts.app-dashboard')

@section('title', 'Finance Manager | StudentHub')
@section('page-title', 'Finance Manager')

@push('styles')
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp .45s ease-out;
        }

        .profit {
            color: #10b981;
        }

        .loss {
            color: #ef4444;
        }

        .modal-open {
            overflow: hidden;
        }
    </style>
@endpush

@section('content')
    <div class="animate-fade-in-up space-y-8">

        {{-- ════════════════════════════════════════════════════════════
     HEADER
════════════════════════════════════════════════════════════ --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Finance Manager</h2>
                <p class="text-stone-500 dark:text-stone-400 text-sm">Kelola keuangan mahasiswa &amp; freelancer secara
                    terintegrasi</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button onclick="openModal('modal-account')"
                    class="flex items-center gap-2 px-4 py-2 bg-stone-800 dark:bg-stone-700 hover:bg-stone-900 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-plus"></i> Tambah Akun
                </button>
                <button onclick="openModal('modal-transaction')"
                    class="flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-arrows-rotate"></i> Transaksi
                </button>
                <button onclick="openModal('modal-transfer')"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-right-left"></i> Transfer
                </button>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════
     SUMMARY CARDS (top)
════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total semua aset --}}
            <div
                class="col-span-2 lg:col-span-1 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg">
                <p class="text-emerald-100 text-xs mb-1">Total Semua Aset</p>
                <h3 class="text-2xl font-bold">Rp {{ number_format($totalAll, 0, ',', '.') }}</h3>
                <p class="text-emerald-200 text-[11px] mt-1">Liquid + Investasi + Piutang</p>
            </div>
            {{-- Uang tersedia (liquid – pending) --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-5 text-white shadow-lg">
                <p class="text-blue-100 text-xs mb-1">Uang Tersedia</p>
                <h3 class="text-2xl font-bold">Rp {{ number_format($availableLiquid, 0, ',', '.') }}</h3>
                <p class="text-blue-200 text-[11px] mt-1">Liquid – kebutuhan pending</p>
            </div>
            {{-- Pemasukan bulan ini --}}
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <p class="text-stone-500 dark:text-stone-400 text-xs mb-1">Pemasukan {{ now()->isoFormat('MMM') }}</p>
                <h3 class="text-2xl font-bold text-emerald-600">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</h3>
                <p class="text-stone-400 text-[11px] mt-1">Bulan ini</p>
            </div>
            {{-- Pengeluaran bulan ini --}}
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <p class="text-stone-500 dark:text-stone-400 text-xs mb-1">Pengeluaran {{ now()->isoFormat('MMM') }}</p>
                <h3 class="text-2xl font-bold text-rose-600">Rp {{ number_format($monthlyExpense, 0, ',', '.') }}</h3>
                <p class="text-stone-400 text-[11px] mt-1">Bulan ini</p>
            </div>
        </div>


        {{-- ════════════════════════════════════════════════════════════
     ANALYTICS ROW — Cashflow + Kategori + Net Worth
════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Net Worth Card --}}
            @php
                $netWorth = $totalAll - ($debts ?? collect())->where('debt_type', 'borrower')->sum('remaining_amount');
                $savingsRate =
                    $monthlyIncome > 0 ? round((($monthlyIncome - $monthlyExpense) / $monthlyIncome) * 100, 1) : 0;
                $cashflowBalance = $monthlyIncome - $monthlyExpense;
            @endphp
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <p class="text-xs text-stone-500 dark:text-stone-400 mb-3 font-medium uppercase tracking-wider">Net Worth
                </p>
                <h3 class="text-2xl font-bold {{ $netWorth >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    Rp {{ number_format(abs($netWorth), 0, ',', '.') }}
                </h3>
                <div class="mt-3 space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-stone-400">Total Aset</span>
                        <span class="font-medium text-stone-700 dark:text-stone-300">Rp
                            {{ number_format($totalAll, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-stone-400">Cashflow Bulan Ini</span>
                        <span class="font-medium {{ $cashflowBalance >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $cashflowBalance >= 0 ? '+' : '' }}Rp {{ number_format($cashflowBalance, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-stone-400">Savings Rate</span>
                        <span
                            class="font-bold {{ $savingsRate >= 20 ? 'text-emerald-600' : ($savingsRate >= 10 ? 'text-amber-600' : 'text-rose-600') }}">
                            {{ $savingsRate }}%
                        </span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex justify-between text-[10px] text-stone-400 mb-1">
                        <span>Savings Rate</span>
                        <span>Target: 20%</span>
                    </div>
                    <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all {{ $savingsRate >= 20 ? 'bg-emerald-500' : ($savingsRate >= 10 ? 'bg-amber-500' : 'bg-rose-500') }}"
                            style="width:{{ min(100, max(0, $savingsRate)) }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Cashflow Chart --}}
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <p class="text-xs text-stone-500 dark:text-stone-400 mb-1 font-medium uppercase tracking-wider">Cashflow
                    Bulan Ini</p>
                <div class="flex items-baseline gap-2 mb-3">
                    <span class="text-lg font-bold {{ $cashflowBalance >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $cashflowBalance >= 0 ? '+' : '' }}Rp {{ number_format($cashflowBalance, 0, ',', '.') }}
                    </span>
                </div>
                <div style="position:relative;width:100%;height:120px;">
                    <canvas id="cashflowChart"></canvas>
                </div>
                <div class="flex justify-between mt-2 text-xs">
                    <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div><span class="text-stone-400">Masuk: Rp
                            {{ number_format($monthlyIncome, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full bg-rose-500"></div><span class="text-stone-400">Keluar: Rp
                            {{ number_format($monthlyExpense, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Top Kategori Pengeluaran --}}
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <p class="text-xs text-stone-500 dark:text-stone-400 mb-3 font-medium uppercase tracking-wider">Top
                    Pengeluaran</p>
                @if ($expenseByCategory->isEmpty())
                    <p class="text-center py-4 text-stone-400 text-xs">Belum ada data pengeluaran bulan ini.</p>
                @else
                    @php $maxCat = $expenseByCategory->max('total'); @endphp
                    <div class="space-y-2">
                        @foreach ($expenseByCategory->take(5) as $cat)
                            <div>
                                <div class="flex justify-between text-xs mb-0.5">
                                    <span
                                        class="text-stone-600 dark:text-stone-400 truncate">{{ $cat->category ?? 'Lainnya' }}</span>
                                    <span class="font-medium text-stone-700 dark:text-stone-300 ml-2 flex-shrink-0">Rp
                                        {{ number_format($cat->total, 0, ',', '.') }}</span>
                                </div>
                                <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-1.5">
                                    <div class="bg-rose-400 h-1.5 rounded-full transition-all"
                                        style="width:{{ $maxCat > 0 ? round(($cat->total / $maxCat) * 100, 0) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════
     MAIN 3-COL GRID
════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- ── LEFT + MIDDLE (col-span-2) ── --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- AKUN / ASET ──────────────────────────────────────── --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-stone-800 dark:text-white">Akun &amp; Aset Keuangan</h3>
                        <span class="text-xs text-stone-400">Klik akun untuk edit saldo</span>
                    </div>

                    @if ($accounts->isEmpty())
                        <div class="text-center py-8 text-stone-400">
                            <i class="fa-solid fa-wallet text-3xl mb-2 block"></i>
                            Belum ada akun. Tambah akun pertama Anda!
                        </div>
                    @else
                        {{-- Group by type --}}
                        @php
                            $grouped = $accounts->groupBy('type');
                            $typeOrder = ['cash', 'bank', 'e-wallet', 'investment', 'receivable'];
                        @endphp
                        <div class="space-y-4">
                            @foreach ($typeOrder as $type)
                                @if ($grouped->has($type))
                                    <div>
                                        <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2">
                                            {{ ['cash' => 'Cash / Tunai', 'bank' => 'Rekening Bank', 'e-wallet' => 'E-Wallet', 'investment' => 'Investasi', 'receivable' => 'Piutang'][$type] ?? $type }}
                                        </p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            @foreach ($grouped[$type] as $acc)
                                                <div class="flex items-center justify-between p-3 border border-stone-200 dark:border-stone-700 rounded-xl hover:shadow-sm transition-shadow group cursor-pointer"
                                                    onclick="openEditBalanceModal({{ $acc->id }}, '{{ addslashes($acc->name) }}', {{ $acc->balance }})">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm flex-shrink-0"
                                                            style="background-color:{{ $acc->color }}">
                                                            <i class="fa-solid {{ $acc->getTypeIcon() }}"></i>
                                                        </div>
                                                        <div>
                                                            <p class="font-semibold text-stone-800 dark:text-white text-sm">
                                                                {{ $acc->name }}</p>
                                                            @if ($acc->account_number)
                                                                <p class="text-xs text-stone-400">
                                                                    {{ $acc->account_number }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="font-bold text-stone-800 dark:text-white">
                                                            {{ $acc->getFormattedBalance() }}</p>
                                                        @if ($acc->isLiquid())
                                                            <p class="text-[11px] text-stone-400">Tersedia: Rp
                                                                {{ number_format($acc->getAvailableBalance(), 0, ',', '.') }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- TRANSAKSI TERBARU ────────────────────────────────── --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-stone-800 dark:text-white">Transaksi Terbaru</h3>
                        <button onclick="openModal('modal-transaction')"
                            class="text-xs bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 px-3 py-1 rounded-lg hover:bg-amber-100 transition-colors">
                            <i class="fa-solid fa-plus mr-1"></i>Tambah
                        </button>
                    </div>

                    @if ($recentTransactions->isEmpty())
                        <p class="text-center py-6 text-stone-400 text-sm">Belum ada transaksi.</p>
                    @else
                        <div class="space-y-2" id="transaction-list">
                            @foreach ($recentTransactions as $t)
                                <div
                                    class="flex items-center justify-between p-3 rounded-xl hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors group">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-full flex items-center justify-center
                        {{ $t->type === 'income' ? 'bg-emerald-100 dark:bg-emerald-900/30' : ($t->type === 'transfer' ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-rose-100 dark:bg-rose-900/30') }}">
                                            <i class="fa-solid {{ $t->getTypeIcon() }} text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-stone-800 dark:text-white text-sm">
                                                {{ $t->description ?? $t->category }}</p>
                                            <p class="text-xs text-stone-400">
                                                {{ $t->account?->name ?? '-' }}
                                                @if ($t->type === 'transfer')
                                                    → {{ $t->toAccount?->name ?? '-' }}
                                                @endif
                                                · {{ $t->transaction_date->isoFormat('D MMM') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="font-bold text-sm
                        {{ $t->type === 'income' ? 'text-emerald-600' : ($t->type === 'transfer' ? 'text-blue-600' : 'text-rose-600') }}">
                                            {{ $t->type === 'income' ? '+' : ($t->type === 'transfer' ? '⇄' : '−') }}Rp
                                            {{ number_format($t->amount, 0, ',', '.') }}
                                        </span>
                                        <button onclick="deleteTransaction({{ $t->id }})"
                                            class="opacity-0 group-hover:opacity-100 text-stone-300 hover:text-rose-500 transition-all ml-1">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- PENDING NEEDS (kebutuhan belum tentu kapan dibeli) ── --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="font-bold text-stone-800 dark:text-white">Kebutuhan Tertunda</h3>
                            <p class="text-xs text-stone-400 mt-0.5">Mengurangi "uang tersedia" tapi belum jadi pengeluaran
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($pendingNeeds->count())
                                <span class="text-xs text-amber-700 dark:text-amber-400 font-medium">
                                    −Rp {{ number_format($totalPending, 0, ',', '.') }} diblokir
                                </span>
                            @endif
                            <button onclick="openModal('modal-pending-need')"
                                class="text-xs bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 px-3 py-1 rounded-lg hover:bg-amber-100 transition-colors">
                                <i class="fa-solid fa-plus mr-1"></i>Tambah
                            </button>
                        </div>
                    </div>

                    @if ($pendingNeeds->isEmpty())
                        <p class="text-center py-6 text-stone-400 text-sm">Tidak ada kebutuhan tertunda.</p>
                    @else
                        <div class="space-y-2">
                            @foreach ($pendingNeeds as $need)
                                <div
                                    class="flex items-center justify-between p-3 border border-amber-100 dark:border-amber-900/30 bg-amber-50/50 dark:bg-amber-900/10 rounded-xl group">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600">
                                            <i class="fa-solid fa-clock text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-stone-800 dark:text-white text-sm">
                                                {{ $need->name }}</p>
                                            <p class="text-xs text-stone-400">{{ $need->account?->name }} ·
                                                {{ $need->category ?? 'Umum' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-amber-700 dark:text-amber-400 text-sm">
                                            Rp {{ number_format($need->amount, 0, ',', '.') }}
                                        </span>
                                        {{-- Tombol: Sudah dibeli --}}
                                        <button onclick="purchaseNeed({{ $need->id }})" title="Tandai sudah dibeli"
                                            class="opacity-0 group-hover:opacity-100 w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 hover:bg-emerald-200 flex items-center justify-center transition-all">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </button>
                                        {{-- Tombol: Batalkan --}}
                                        <button onclick="cancelNeed({{ $need->id }})"
                                            title="Batalkan (saldo kembali)"
                                            class="opacity-0 group-hover:opacity-100 w-7 h-7 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-500 hover:bg-rose-100 hover:text-rose-500 flex items-center justify-center transition-all">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div><!-- end left+middle -->

            {{-- ── RIGHT COLUMN ── --}}
            <div class="space-y-6">

                {{-- DISTRIBUSI ASET (chart) ─────────────────────────── --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-4">Distribusi Aset</h3>
                    <div style="position:relative;width:100%;height:220px;">
                        <canvas id="assetChart"></canvas>
                    </div>
                    {{-- legend --}}
                    <div class="mt-3 space-y-1">
                        @foreach ($accounts->groupBy('type') as $type => $accs)
                            <div class="flex justify-between text-xs">
                                <span class="text-stone-500 dark:text-stone-400">
                                    {{ ['cash' => 'Cash', 'bank' => 'Bank', 'e-wallet' => 'E-Wallet', 'investment' => 'Investasi', 'receivable' => 'Piutang'][$type] ?? $type }}
                                </span>
                                <span class="font-medium text-stone-700 dark:text-stone-300">
                                    Rp {{ number_format($accs->sum('balance'), 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- BUDGET (target pengeluaran) ─────────────────────── --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-stone-800 dark:text-white">Budget Bulan Ini</h3>
                        <button onclick="openModal('modal-budget')"
                            class="text-xs bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 px-3 py-1 rounded-lg hover:bg-stone-200 transition-colors">
                            <i class="fa-solid fa-plus mr-1"></i>Set Budget
                        </button>
                    </div>

                    @if ($budgets->isEmpty())
                        <p class="text-center py-4 text-stone-400 text-sm">Belum ada budget. Buat target pengeluaran!</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($budgets as $b)
                                <div class="group">
                                    <div class="flex justify-between items-center mb-1">
                                        <div class="flex items-center gap-2">
                                            <i
                                                class="fa-solid {{ $b->getStatusIcon() }} text-xs
                            {{ $b->isOverBudget() ? 'text-red-500' : ($b->isNearLimit() ? 'text-amber-500' : 'text-emerald-500') }}"></i>
                                            <span
                                                class="text-sm font-medium text-stone-700 dark:text-stone-300">{{ $b->category }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-xs font-bold
                            {{ $b->isOverBudget() ? 'text-red-600' : ($b->isNearLimit() ? 'text-amber-600' : 'text-emerald-600') }}">
                                                {{ round($b->getUsagePercentage(), 0) }}%
                                            </span>
                                            <button onclick="deleteBudget({{ $b->id }})"
                                                class="opacity-0 group-hover:opacity-100 text-stone-300 hover:text-rose-500 transition-all">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-2 mb-1">
                                        <div class="h-2 rounded-full transition-all
                        {{ $b->isOverBudget() ? 'bg-red-500' : ($b->isNearLimit() ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                            style="width:{{ min(100, $b->getUsagePercentage()) }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-[11px] text-stone-400">
                                        <span>Terpakai: Rp {{ number_format($b->spent_amount, 0, ',', '.') }}</span>
                                        <span>Limit: Rp {{ number_format($b->amount, 0, ',', '.') }}</span>
                                    </div>
                                    @if ($b->isOverBudget())
                                        <p class="text-[11px] text-red-500 mt-0.5">
                                            ⚠ Melebihi budget Rp
                                            {{ number_format($b->spent_amount - $b->amount, 0, ',', '.') }}!
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- TARGET TABUNGAN ─────────────────────────────────── --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-stone-800 dark:text-white">Target Tabungan</h3>
                        <button onclick="openModal('modal-savings')"
                            class="text-xs bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 px-3 py-1 rounded-lg hover:bg-emerald-100 transition-colors">
                            <i class="fa-solid fa-plus mr-1"></i>Tambah
                        </button>
                    </div>

                    @if ($savingsGoals->isEmpty())
                        <p class="text-center py-4 text-stone-400 text-sm">Belum ada target tabungan.</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($savingsGoals as $g)
                                <div class="p-3 border border-stone-200 dark:border-stone-700 rounded-xl group">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="font-semibold text-stone-800 dark:text-white text-sm">
                                                {{ $g->name }}</h4>
                                            <p class="text-[11px] text-stone-400">{{ $g->account?->name }} · Selesai
                                                {{ $g->target_date?->isoFormat('D MMM YY') ?? '–' }}</p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span
                                                class="text-sm font-bold text-stone-700 dark:text-stone-300">{{ round($g->getProgressPercentage(), 0) }}%</span>
                                            <button onclick="deleteSavingsGoal({{ $g->id }})"
                                                class="opacity-0 group-hover:opacity-100 text-stone-300 hover:text-rose-500 transition-all ml-1">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-2 mb-2">
                                        <div class="bg-emerald-500 h-2 rounded-full transition-all"
                                            style="width:{{ $g->getProgressPercentage() }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-[11px] text-stone-400">
                                        <span>Rp {{ number_format($g->current_amount, 0, ',', '.') }}</span>
                                        <span>Target: Rp {{ number_format($g->target_amount, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-[11px] text-stone-400 mt-0.5">
                                        <span><i class="fa-solid fa-calendar-day mr-1"></i>{{ $g->getDaysRemaining() }}
                                            hari lagi</span>
                                        <span><i class="fa-solid fa-coins mr-1"></i>Rp
                                            {{ number_format($g->getDailyNeeded(), 0, ',', '.') }}/hari</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- PENGELUARAN PER KATEGORI ─────────────────────────── --}}
                @if ($expenseByCategory->count())
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                        <h3 class="font-bold text-stone-800 dark:text-white mb-4">Pengeluaran per Kategori</h3>
                        <div
                            style="position:relative;width:100%;height:{{ min(200, $expenseByCategory->count() * 32) }}px;">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                @endif

            </div><!-- end right column -->

        </div><!-- end main grid -->

    </div><!-- end .animate-fade-in-up -->


    {{-- ══════════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════════ --}}

    {{-- ── MODAL: TAMBAH AKUN ──────────────────────────────────── --}}
    <div id="modal-account"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white">Tambah Akun / Aset</h3>
                <button onclick="closeModal('modal-account')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="form-account" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nama Akun
                            *</label>
                        <input type="text" name="name" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="Misal: BCA Tabungan, GoPay, Dompet">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tipe *</label>
                        <select name="type" required onchange="toggleAccountNumber(this.value)"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="cash">Cash / Tunai</option>
                            <option value="bank">Rekening Bank</option>
                            <option value="e-wallet">E-Wallet</option>
                            <option value="investment">Investasi</option>
                            <option value="receivable">Piutang</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Saldo Awal (Rp)
                            *</label>
                        <input type="number" name="balance" required min="0"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0">
                    </div>
                    <div id="field-account-number" class="col-span-2 hidden">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nomor
                            Rekening</label>
                        <input type="text" name="account_number"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="Nomor rekening (opsional)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Warna</label>
                        <input type="color" name="color" value="#6b7280"
                            class="w-full h-10 border border-stone-300 dark:border-stone-700 rounded-xl px-2 cursor-pointer">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Catatan</label>
                        <input type="text" name="notes"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="Catatan opsional">
                    </div>
                </div>
            </form>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-account')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800">Batal</button>
                <button onclick="submitAccount()"
                    class="flex-1 py-2.5 bg-stone-800 hover:bg-stone-900 text-white rounded-xl font-medium">Simpan
                    Akun</button>
            </div>
        </div>
    </div>

    {{-- ── MODAL: EDIT SALDO AKUN ───────────────────────────────── --}}
    <div id="modal-edit-balance"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-sm shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white" id="edit-balance-title">Update Saldo</h3>
                <button onclick="closeModal('modal-edit-balance')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" id="edit-balance-account-id">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Saldo Baru
                        (Rp)</label>
                    <input type="number" id="edit-balance-value" min="0"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white text-lg font-medium"
                        placeholder="0">
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-edit-balance')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitUpdateBalance()"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium">Update
                    Saldo</button>
            </div>
        </div>
    </div>

    {{-- ── MODAL: TAMBAH TRANSAKSI ──────────────────────────────── --}}
    <div id="modal-transaction"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white">Catat Transaksi</h3>
                <button onclick="closeModal('modal-transaction')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="form-transaction" class="p-6 space-y-4">
                @csrf
                {{-- Toggle income / expense --}}
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">Tipe Transaksi</label>
                    <div class="flex rounded-xl overflow-hidden border border-stone-200 dark:border-stone-700">
                        <button type="button" id="btn-income" onclick="setTxType('income')"
                            class="flex-1 py-2 text-sm font-medium bg-emerald-500 text-white transition-colors">
                            <i class="fa-solid fa-arrow-down mr-1"></i>Pemasukan
                        </button>
                        <button type="button" id="btn-expense" onclick="setTxType('expense')"
                            class="flex-1 py-2 text-sm font-medium bg-white dark:bg-stone-800 text-stone-500 transition-colors hover:bg-rose-50">
                            <i class="fa-solid fa-arrow-up mr-1"></i>Pengeluaran
                        </button>
                    </div>
                    <input type="hidden" name="type" id="input-tx-type" value="income">
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">
                        Akun <span id="tx-account-label">Tujuan (uang masuk ke)</span> *
                    </label>
                    <select name="finance_account_id" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <option value="">-- Pilih Akun --</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">[{{ strtoupper($acc->type) }}] {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Jumlah (Rp)
                            *</label>
                        <input type="number" name="amount" required min="1"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tanggal *</label>
                        <input type="date" name="transaction_date" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Kategori</label>
                    <select name="category"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <option value="">-- Pilih / ketik --</option>
                        {{-- Pemasukan --}}
                        <optgroup label="Pemasukan">
                            <option>Gaji PKL</option>
                            <option>Freelance</option>
                            <option>Shutterstock</option>
                            <option>Beasiswa</option>
                            <option>Bisnis</option>
                        </optgroup>
                        {{-- Pengeluaran --}}
                        <optgroup label="Pengeluaran">
                            <option>Makan &amp; Minum</option>
                            <option>Transportasi</option>
                            <option>Kebutuhan Pokok</option>
                            <option>Hiburan</option>
                            <option>Pendidikan</option>
                            <option>Kesehatan</option>
                            <option>Pakaian</option>
                            <option>Tagihan</option>
                            <option>Lainnya</option>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Deskripsi</label>
                    <input type="text" name="description"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Keterangan singkat">
                </div>
            </form>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-transaction')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitTransaction()"
                    class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-medium">Simpan</button>
            </div>
        </div>
    </div>

    {{-- ── MODAL: TRANSFER ──────────────────────────────────────── --}}
    <div id="modal-transfer"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white">Transfer Dana</h3>
                <button onclick="closeModal('modal-transfer')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="form-transfer" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Dari Akun *</label>
                    <select name="from_account_id" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <option value="">-- Pilih Akun Asal --</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">[{{ strtoupper($acc->type) }}] {{ $acc->name }} — Rp
                                {{ number_format($acc->balance, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Ke Akun *</label>
                    <select name="to_account_id" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <option value="">-- Pilih Akun Tujuan --</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">[{{ strtoupper($acc->type) }}] {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Jumlah (Rp)
                            *</label>
                        <input type="number" name="amount" required min="1"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Admin / Biaya
                            (Rp)</label>
                        <input type="number" name="fee" min="0" value="0"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tanggal *</label>
                        <input type="date" name="transaction_date" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Keterangan</label>
                        <input type="text" name="description"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="Opsional">
                    </div>
                </div>
            </form>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-transfer')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitTransfer()"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium">Transfer</button>
            </div>
        </div>
    </div>

    {{-- ── MODAL: TAMBAH BUDGET ─────────────────────────────────── --}}
    <div id="modal-budget"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-sm shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white">Set Budget</h3>
                <button onclick="closeModal('modal-budget')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="form-budget" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Kategori *</label>
                    <select name="category" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <option value="">-- Pilih Kategori --</option>
                        <option>Makan &amp; Minum</option>
                        <option>Transportasi</option>
                        <option>Kebutuhan Pokok</option>
                        <option>Hiburan</option>
                        <option>Pendidikan</option>
                        <option>Kesehatan</option>
                        <option>Pakaian</option>
                        <option>Tagihan</option>
                        <option>Lainnya</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Limit (Rp)
                            *</label>
                        <input type="number" name="amount" required min="1"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Alert di
                            (%)</label>
                        <input type="number" name="alert_threshold" value="80" min="10" max="100"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Periode</label>
                    <select name="period"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <option value="monthly">Bulanan</option>
                        <option value="weekly">Mingguan</option>
                    </select>
                </div>
            </form>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-budget')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitBudget()"
                    class="flex-1 py-2.5 bg-stone-800 hover:bg-stone-900 text-white rounded-xl font-medium">Simpan
                    Budget</button>
            </div>
        </div>
    </div>

    {{-- ── MODAL: TAMBAH TARGET TABUNGAN ───────────────────────── --}}
    <div id="modal-savings"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white">Target Tabungan Baru</h3>
                <button onclick="closeModal('modal-savings')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="form-savings" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nama Target *</label>
                    <input type="text" name="name" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Misal: Laptop Baru, Dana Darurat">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Target (Rp)
                            *</label>
                        <input type="number" name="target_amount" required min="1"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nabung/Hari
                            (Rp)</label>
                        <input type="number" name="daily_saving" min="0"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Target
                            Tanggal</label>
                        <input type="date" name="target_date"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Akun
                            Tabungan</label>
                        <select name="finance_account_id"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="">-- Pilih --</option>
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-savings')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitSavings()"
                    class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-medium">Simpan
                    Target</button>
            </div>
        </div>
    </div>

    {{-- ── MODAL: TAMBAH KEBUTUHAN TERTUNDA ───────────────────────── --}}
    <div id="modal-pending-need"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <div>
                    <h3 class="text-lg font-bold text-stone-900 dark:text-white">Tambah Kebutuhan</h3>
                    <p class="text-xs text-stone-400 mt-0.5">Uang akan "diblokir" dari saldo tersedia</p>
                </div>
                <button onclick="closeModal('modal-pending-need')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="form-pending-need" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nama Kebutuhan
                        *</label>
                    <input type="text" name="name" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Misal: Pulsa, Laundry, Sabun">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Estimasi (Rp)
                            *</label>
                        <input type="number" name="amount" required min="1"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Kategori</label>
                        <select name="category"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="">-- Pilih --</option>
                            <option>Kebutuhan Pokok</option>
                            <option>Tagihan</option>
                            <option>Transportasi</option>
                            <option>Kesehatan</option>
                            <option>Lainnya</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Ambil dari Akun
                        *</label>
                    <select name="finance_account_id" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <option value="">-- Pilih Akun --</option>
                        @foreach ($accounts->where('is_active', true) as $acc)
                            @if ($acc->isLiquid())
                                <option value="{{ $acc->id }}">{{ $acc->name }} — Rp
                                    {{ number_format($acc->getAvailableBalance(), 0, ',', '.') }} tersedia</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Catatan</label>
                    <input type="text" name="notes"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Opsional">
                </div>
            </form>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-pending-need')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitPendingNeed()"
                    class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-medium">Simpan
                    Kebutuhan</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ── Helpers ──────────────────────────────────────────────────
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
            document.body.classList.add('modal-open');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
            document.body.classList.remove('modal-open');
        }

        function toast(msg, ok = true) {
            const el = document.createElement('div');
            el.className = `fixed bottom-5 right-5 z-[9999] px-4 py-3 rounded-xl shadow-xl text-white text-sm font-medium flex items-center gap-2
        ${ok ? 'bg-emerald-500' : 'bg-rose-500'}`;
            el.innerHTML = `<i class="fa-solid ${ok ? 'fa-check-circle' : 'fa-circle-xmark'}"></i> ${msg}`;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3500);
        }

        function formToJson(formId) {
            const fd = new FormData(document.getElementById(formId));
            const obj = {};
            fd.forEach((v, k) => {
                if (v !== '') obj[k] = v;
            });
            return obj;
        }

        // ── Core API helper (FIXED) ──────────────────────────────────────────────
        async function api(method, url, data = null) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const upper = method.toUpperCase();
            const opts = {
                method: upper,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
            };
            if (!['GET', 'HEAD'].includes(upper)) {
                opts.body = JSON.stringify(data ?? {});
            }
            const res = await fetch(url, opts);
            if (res.status === 419) {
                toast('Sesi habis, halaman akan di-refresh...', false);
                setTimeout(() => location.reload(), 1500);
                return {
                    success: false,
                    message: 'CSRF token expired'
                };
            }
            if (res.status === 405) {
                toast('Endpoint error (405). Jalankan: php artisan route:clear', false);
                console.error('405:', upper, url);
                return {
                    success: false,
                    message: 'Method Not Allowed'
                };
            }
            if (!res.ok && res.status !== 422) {
                toast(`Error ${res.status}`, false);
                return {
                    success: false,
                    message: `HTTP ${res.status}`
                };
            }
            return res.json();
        }

        // ── Toggle akun-number field ──────────────────────────────────
        function toggleAccountNumber(type) {
            const f = document.getElementById('field-account-number');
            f.classList.toggle('hidden', type !== 'bank');
        }

        // ── Transaction type toggle ───────────────────────────────────
        let txType = 'income';

        function setTxType(type) {
            txType = type;
            document.getElementById('input-tx-type').value = type;
            const inc = document.getElementById('btn-income');
            const exp = document.getElementById('btn-expense');
            const lbl = document.getElementById('tx-account-label');

            if (type === 'income') {
                inc.className = 'flex-1 py-2 text-sm font-medium bg-emerald-500 text-white transition-colors';
                exp.className =
                    'flex-1 py-2 text-sm font-medium bg-white dark:bg-stone-800 text-stone-500 transition-colors hover:bg-rose-50';
                lbl.textContent = 'Tujuan (uang masuk ke)';
            } else {
                exp.className = 'flex-1 py-2 text-sm font-medium bg-rose-500 text-white transition-colors';
                inc.className =
                    'flex-1 py-2 text-sm font-medium bg-white dark:bg-stone-800 text-stone-500 transition-colors hover:bg-emerald-50';
                lbl.textContent = 'Sumber (uang keluar dari)';
            }
        }

        // ── Open edit-balance modal ───────────────────────────────────
        function openEditBalanceModal(id, name, balance) {
            document.getElementById('edit-balance-title').textContent = 'Update Saldo: ' + name;
            document.getElementById('edit-balance-account-id').value = id;
            document.getElementById('edit-balance-value').value = balance;
            openModal('modal-edit-balance');
        }

        // ── SUBMIT: Akun ──────────────────────────────────────────────
        async function submitAccount() {
            const data = formToJson('form-account');
            const res = await api('POST', '{{ route('finance.accounts.store') }}', data);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800);
            } else toast(res.message, false);
        }

        // ── SUBMIT: Update Saldo ──────────────────────────────────────
        async function submitUpdateBalance() {
            const id = document.getElementById('edit-balance-account-id').value;
            const val = document.getElementById('edit-balance-value').value;
            const res = await api('PATCH', `/finance/accounts/${id}/balance`, {
                balance: val
            });
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800);
            } else toast(res.message, false);
        }

        // ── SUBMIT: Transaksi ─────────────────────────────────────────
        async function submitTransaction() {
            const data = formToJson('form-transaction');
            if (!data.finance_account_id) {
                toast('Pilih akun terlebih dahulu', false);
                return;
            }
            const res = await api('POST', '{{ route('finance.transactions.store') }}', data);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800);
            } else toast(res.message, false);
        }

        // ── DELETE: Transaksi ─────────────────────────────────────────
        async function deleteTransaction(id) {
            if (!confirm('Hapus transaksi ini? Saldo akun akan dikembalikan.')) return;
            const res = await api('DELETE', `/finance/transactions/${id}`);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800);
            } else toast(res.message, false);
        }

        // ── SUBMIT: Transfer ──────────────────────────────────────────
        async function submitTransfer() {
            const data = formToJson('form-transfer');
            if (data.from_account_id === data.to_account_id) {
                toast('Akun asal dan tujuan harus berbeda', false);
                return;
            }
            const res = await api('POST', '{{ route('finance.transfer.store') }}', data);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800);
            } else toast(res.message, false);
        }

        // ── SUBMIT: Budget ────────────────────────────────────────────
        async function submitBudget() {
            const data = formToJson('form-budget');
            const res = await api('POST', '{{ route('finance.budgets.store') }}', data);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800);
            } else toast(res.message, false);
        }

        // ── DELETE: Budget ────────────────────────────────────────────
        async function deleteBudget(id) {
            if (!confirm('Hapus budget ini?')) return;
            const res = await api('DELETE', `/finance/budgets/${id}`);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800);
            } else toast(res.message, false);
        }

        // ── SUBMIT: Savings Goal ──────────────────────────────────────
        async function submitSavings() {
            const data = formToJson('form-savings');
            const res = await api('POST', '{{ route('finance.savings.store') }}', data);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800);
            } else toast(res.message, false);
        }

        // ── DELETE: Savings Goal ──────────────────────────────────────
        async function deleteSavingsGoal(id) {
            if (!confirm('Hapus target tabungan ini?')) return;
            const res = await api('DELETE', `/finance/savings-goals/${id}`);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800);
            } else toast(res.message, false);
        }

        // ── SUBMIT: Pending Need ──────────────────────────────────────
        async function submitPendingNeed() {
            const data = formToJson('form-pending-need');
            const res = await api('POST', '{{ route('finance.needs.store') }}', data);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800);
            } else toast(res.message, false);
        }

        // ── ACTION: Purchase Need ─────────────────────────────────────
        async function purchaseNeed(id) {
            if (!confirm('Tandai kebutuhan ini sudah dibeli? Akan otomatis jadi transaksi pengeluaran.')) return;
            const res = await api('POST', `/finance/pending-needs/${id}/purchase`, {
                transaction_date: new Date().toISOString().split('T')[0]
            });
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800);
            } else toast(res.message, false);
        }

        // ── ACTION: Cancel Need ───────────────────────────────────────
        async function cancelNeed(id) {
            if (!confirm('Batalkan kebutuhan ini? Saldo tersedia akan kembali normal.')) return;
            const res = await api('POST', `/finance/pending-needs/${id}/cancel`);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800);
            } else toast(res.message, false);
        }

        // ── Charts Init ───────────────────────────────────────────────────
        (function initAllCharts() {
            // 1. Asset Distribution Doughnut
            const ctx1 = document.getElementById('assetChart');
            if (ctx1) {
                const labels = [],
                    values = [],
                    bgColors = [];
                const colorMap = {
                    cash: '#f59e0b',
                    bank: '#3b82f6',
                    'e-wallet': '#8b5cf6',
                    investment: '#10b981',
                    receivable: '#f97316'
                };
                const nameMap = {
                    cash: 'Cash',
                    bank: 'Bank',
                    'e-wallet': 'E-Wallet',
                    investment: 'Investasi',
                    receivable: 'Piutang'
                };
                @foreach ($accounts->groupBy('type') as $type => $accs)
                    labels.push(nameMap['{{ $type }}'] || '{{ $type }}');
                    values.push({{ $accs->sum('balance') }});
                    bgColors.push(colorMap['{{ $type }}'] || '#6b7280');
                @endforeach
                if (values.length && !values.every(v => v === 0)) {
                    new Chart(ctx1.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels,
                            datasets: [{
                                data: values,
                                backgroundColor: bgColors,
                                borderWidth: 2,
                                borderColor: document.documentElement.classList.contains('dark') ?
                                    '#1c1917' : '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: c => `${c.label}: Rp ${c.raw.toLocaleString('id-ID')}`
                                    }
                                }
                            }
                        }
                    });
                }
            }

            // 2. Cashflow Bar Chart (income vs expense)
            const ctx2 = document.getElementById('cashflowChart');
            if (ctx2) {
                new Chart(ctx2.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Pemasukan', 'Pengeluaran'],
                        datasets: [{
                            data: [{{ $monthlyIncome }}, {{ $monthlyExpense }}],
                            backgroundColor: ['#10b981', '#f43f5e'],
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: c => `Rp ${c.raw.toLocaleString('id-ID')}`
                                }
                            }
                        },
                        scales: {
                            y: {
                                display: false
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 3. Expense by Category Horizontal Bar
            const ctx3 = document.getElementById('categoryChart');
            if (ctx3) {
                const catLabels = [],
                    catData = [];
                @foreach ($expenseByCategory as $cat)
                    catLabels.push('{{ $cat->category ?? 'Lainnya' }}');
                    catData.push({{ $cat->total }});
                @endforeach
                if (catLabels.length) {
                    new Chart(ctx3.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: catLabels,
                            datasets: [{
                                data: catData,
                                backgroundColor: '#f43f5e',
                                borderRadius: 4
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: c => `Rp ${c.raw.toLocaleString('id-ID')}`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    display: false
                                },
                                y: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 10
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        })();

        // ── Set today on date inputs ──────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            const today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('input[type="date"]').forEach(el => {
                if (!el.value) el.value = today;
            });
        });
    </script>
@endpush
