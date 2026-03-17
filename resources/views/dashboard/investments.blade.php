{{-- resources/views/dashboard/investments.blade.php --}}
@extends('layouts.app-dashboard')
@section('title', 'Investment Portfolio | StudentHub')
@section('page-title', 'Investment Portfolio')
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
            animation: fadeInUp .45s ease-out
        }

        .modal-open {
            overflow: hidden
        }
    </style>
@endpush

@section('content')
    <div class="animate-fade-in-up space-y-6">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Investment Portfolio</h2>
                <p class="text-stone-500 dark:text-stone-400 text-sm">Monitor semua instrumen investasi per platform</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button onclick="openModal('modal-add-instrument')"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-plus"></i> Tambah Instrumen
                </button>
                <button onclick="openModal('modal-add-purchase')"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-cart-plus"></i> Catat Pembelian
                </button>
            </div>
        </div>

        {{-- INFO: tidak ada akun investasi --}}
        @if ($investmentAccounts->isEmpty())
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-4">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-amber-500 mt-0.5 text-lg"></i>
                    <div>
                        <p class="font-semibold text-amber-800 dark:text-amber-300 text-sm">Belum ada akun investasi</p>
                        <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">
                            Tambahkan akun bertipe <strong>Investasi</strong> di halaman
                            <a href="{{ route('dashboard.finance') }}" class="underline">Finance</a>
                            atau <a href="{{ route('dashboard.assets') }}" class="underline">Assets</a>
                            untuk menghubungkan platform (Indodax, Ajaib, Bibit, dll) ke instrumen investasi Anda.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- SUMMARY CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                    <p class="text-stone-500 dark:text-stone-400 text-xs">Total Modal</p>
                </div>
                <h3 class="text-2xl font-bold text-stone-800 dark:text-white">Rp
                    {{ number_format($totalInvested, 0, ',', '.') }}</h3>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <p class="text-stone-500 dark:text-stone-400 text-xs">Nilai Saat Ini</p>
                </div>
                <h3 class="text-2xl font-bold text-stone-800 dark:text-white">Rp
                    {{ number_format($totalCurrentValue, 0, ',', '.') }}</h3>
            </div>
            <div
                class="bg-white dark:bg-stone-900 rounded-2xl p-5 border {{ $totalPL >= 0 ? 'border-emerald-200 dark:border-emerald-800' : 'border-rose-200 dark:border-rose-800' }} shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 rounded-full {{ $totalPL >= 0 ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-rose-100 dark:bg-rose-900/30' }} flex items-center justify-center {{ $totalPL >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        <i class="fa-solid {{ $totalPL >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                    </div>
                    <p class="text-stone-500 dark:text-stone-400 text-xs">Untung / Rugi</p>
                </div>
                <h3 class="text-2xl font-bold {{ $totalPL >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $totalPL >= 0 ? '+' : '' }}Rp {{ number_format(abs($totalPL), 0, ',', '.') }}
                </h3>
                <p class="text-[11px] mt-1 {{ $totalPL >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                    {{ $totalPLPct >= 0 ? '+' : '' }}{{ $totalPLPct }}%</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600">
                        <i class="fa-solid fa-piggy-bank"></i>
                    </div>
                    <p class="text-stone-500 dark:text-stone-400 text-xs">Instrumen</p>
                </div>
                <h3 class="text-2xl font-bold text-stone-800 dark:text-white">{{ $instruments->count() }}</h3>
                <p class="text-[11px] text-stone-400 mt-1">di {{ $investmentAccounts->count() }} platform</p>
            </div>
        </div>

        {{-- ══ PER PLATFORM / AKUN ════════════════════════════════════════════ --}}
        @if ($byAccount->count())
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h3 class="font-bold text-stone-800 dark:text-white">Distribusi per Platform</h3>
                        <p class="text-xs text-stone-400 mt-0.5">Alokasi instrumen dari setiap akun investasi</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($byAccount as $acc)
                        @php
                            $accPL = $acc['profit_loss'];
                            $allocPct =
                                $acc['account_balance'] > 0
                                    ? round(($acc['current_value'] / $acc['account_balance']) * 100, 0)
                                    : 0;
                        @endphp
                        <div class="border border-stone-200 dark:border-stone-700 rounded-xl p-4">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                                    style="background-color:{{ $acc['account_color'] }}">
                                    {{ strtoupper(substr($acc['account_name'], 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-stone-800 dark:text-white text-sm">{{ $acc['account_name'] }}
                                    </p>
                                    <p class="text-[11px] text-stone-400">Saldo: Rp
                                        {{ number_format($acc['account_balance'], 0, ',', '.') }}</p>
                                </div>
                            </div>

                            {{-- Instrumen di dalam akun ini --}}
                            <div class="space-y-2 mb-3">
                                @foreach ($acc['instruments'] as $ins)
                                    <div class="flex justify-between items-center text-xs">
                                        <span
                                            class="text-stone-600 dark:text-stone-400 font-medium">{{ $ins['symbol'] }}</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-stone-700 dark:text-stone-300">Rp
                                                {{ number_format($ins['current_value'], 0, ',', '.') }}</span>
                                            <span
                                                class="{{ $ins['profit_loss'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-medium">
                                                {{ $ins['profit_loss_pct'] >= 0 ? '+' : '' }}{{ $ins['profit_loss_pct'] }}%
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Progress bar: alokasi vs saldo akun --}}
                            @if ($acc['account_balance'] > 0)
                                <div class="mt-2">
                                    <div class="flex justify-between text-[10px] text-stone-400 mb-1">
                                        <span>Dialokasikan: Rp
                                            {{ number_format($acc['total_invested'], 0, ',', '.') }}</span>
                                        <span>{{ $allocPct }}% dari saldo</span>
                                    </div>
                                    <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ $allocPct > 100 ? 'bg-rose-500' : 'bg-blue-500' }}"
                                            style="width:{{ min(100, $allocPct) }}%"></div>
                                    </div>
                                </div>
                            @endif

                            <div
                                class="mt-3 pt-3 border-t border-stone-100 dark:border-stone-700 flex justify-between items-center">
                                <div>
                                    <p class="text-[10px] text-stone-400">Nilai Portfolio</p>
                                    <p class="text-sm font-bold text-stone-800 dark:text-white">Rp
                                        {{ number_format($acc['current_value'], 0, ',', '.') }}</p>
                                </div>
                                <span class="text-sm font-bold {{ $accPL >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $accPL >= 0 ? '+' : '' }}Rp {{ number_format(abs($accPL), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- INSTRUMEN BELUM DIKAITKAN KE PLATFORM --}}
        @if ($unlinkedInstruments->count())
            <div class="bg-amber-50/50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-2xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-link-slash text-amber-500 text-sm"></i>
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">{{ $unlinkedInstruments->count() }}
                        instrumen belum dikaitkan ke platform</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($unlinkedInstruments as $ins)
                        <span
                            class="px-2 py-1 bg-white dark:bg-stone-800 border border-amber-200 dark:border-amber-700 rounded-lg text-xs text-stone-600 dark:text-stone-400 cursor-pointer hover:bg-amber-50 transition-colors"
                            onclick="openLinkAccountModal({{ $ins->id }}, '{{ addslashes($ins->name) }}')">
                            {{ $ins->symbol }} · Klik untuk tautkan
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- MAIN GRID --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">

                {{-- DAFTAR INSTRUMEN --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-stone-800 dark:text-white">Semua Instrumen</h3>
                        <div class="flex gap-2">
                            <button onclick="filterType('all')" id="filter-all"
                                class="px-3 py-1 text-xs rounded-full bg-stone-800 dark:bg-stone-700 text-white">Semua</button>
                            <button onclick="filterType('crypto')" id="filter-crypto"
                                class="px-3 py-1 text-xs rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:bg-stone-200">Crypto</button>
                            <button onclick="filterType('stocks')" id="filter-stocks"
                                class="px-3 py-1 text-xs rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:bg-stone-200">Saham</button>
                        </div>
                    </div>

                    @if ($instruments->isEmpty())
                        <p class="text-center py-8 text-stone-400 text-sm">Belum ada instrumen investasi.</p>
                    @else
                        <div class="space-y-3" id="instrument-list">
                            @foreach ($instruments as $ins)
                                @php
                                    $currentValue = $ins->getCurrentValue();
                                    $pl = $ins->getProfitLoss();
                                    $plPct = round($ins->getProfitLossPercentage(), 2);
                                @endphp
                                <div class="instrument-card p-4 border border-stone-200 dark:border-stone-700 rounded-xl hover:shadow-md transition-shadow cursor-pointer group"
                                    data-type="{{ $ins->type }}" onclick="openDetailModal({{ $ins->id }})">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full {{ match ($ins->type) {'crypto' => 'bg-orange-100 dark:bg-orange-900/30','stocks' => 'bg-blue-100 dark:bg-blue-900/30','gold' => 'bg-amber-100 dark:bg-amber-900/30',default => 'bg-stone-100 dark:bg-stone-800'} }} flex items-center justify-center">
                                                <i
                                                    class="fa-solid {{ match ($ins->type) {'crypto' => 'fa-bitcoin-sign text-orange-600','stocks' => 'fa-chart-column text-blue-600','gold' => 'fa-coins text-amber-600','mutual-fund' => 'fa-briefcase text-purple-600',default => 'fa-chart-bar text-stone-600'} }}"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-stone-800 dark:text-white">{{ $ins->name }}
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <p class="text-xs text-stone-500 dark:text-stone-400">
                                                        {{ $ins->symbol }} · {{ ucfirst($ins->type) }}</p>
                                                    {{-- Badge platform --}}
                                                    @if ($ins->financeAccount)
                                                        <span
                                                            class="text-[10px] px-1.5 py-0.5 rounded-full text-white font-medium"
                                                            style="background-color:{{ $ins->financeAccount->color }}">
                                                            {{ $ins->financeAccount->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-sm font-bold {{ $pl >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                {{ $plPct >= 0 ? '+' : '' }}{{ $plPct }}%
                                            </span>
                                            <div class="opacity-0 group-hover:opacity-100 flex gap-1 transition-all"
                                                onclick="event.stopPropagation()">
                                                <button
                                                    onclick="openUpdatePriceModal({{ $ins->id }}, '{{ addslashes($ins->name) }}', {{ $ins->current_price }})"
                                                    class="w-7 h-7 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 flex items-center justify-center hover:bg-amber-200"
                                                    title="Update Harga">
                                                    <i class="fa-solid fa-rotate text-xs"></i>
                                                </button>
                                                <button
                                                    onclick="openLinkAccountModal({{ $ins->id }}, '{{ addslashes($ins->name) }}')"
                                                    class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center hover:bg-blue-200"
                                                    title="Tautkan ke Platform">
                                                    <i class="fa-solid fa-link text-xs"></i>
                                                </button>
                                                <button onclick="deleteInstrument({{ $ins->id }})"
                                                    class="w-7 h-7 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-500 flex items-center justify-center hover:bg-rose-200"
                                                    title="Hapus">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-3 text-sm">
                                        <div>
                                            <p class="text-xs text-stone-400">Modal</p>
                                            <p class="font-bold text-stone-800 dark:text-white">Rp
                                                {{ number_format($ins->total_invested, 0, ',', '.') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-stone-400">Nilai Sekarang</p>
                                            <p class="font-bold text-stone-800 dark:text-white">Rp
                                                {{ number_format($currentValue, 0, ',', '.') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-stone-400">Untung/Rugi</p>
                                            <p class="font-bold {{ $pl >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $pl >= 0 ? '+' : '' }}Rp {{ number_format(abs($pl), 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex justify-between text-xs text-stone-400">
                                        <span><i class="fa-solid fa-coins mr-1"></i>{{ $ins->total_quantity }}
                                            {{ $ins->symbol }}</span>
                                        <span>Avg: Rp {{ number_format($ins->average_price, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- CHART PERFORMA --}}
                @if ($instruments->count())
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                        <h3 class="font-bold text-stone-800 dark:text-white mb-4">Performa Return per Instrumen</h3>
                        <div style="position:relative;width:100%;height:220px;"><canvas id="perfChart"></canvas></div>
                    </div>
                @endif

                {{-- PEMBELIAN TERBARU --}}
                @if ($recentPurchases->count())
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                        <h3 class="font-bold text-stone-800 dark:text-white mb-4">Pembelian Terbaru</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-stone-200 dark:border-stone-700">
                                        <th class="text-left py-2 text-stone-500 font-medium">Tanggal</th>
                                        <th class="text-left py-2 text-stone-500 font-medium">Instrumen</th>
                                        <th class="text-right py-2 text-stone-500 font-medium">Jumlah</th>
                                        <th class="text-right py-2 text-stone-500 font-medium">Nilai Skrg</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentPurchases as $p)
                                        @php
                                            $cv = $p->quantity * $p->instrument->current_price;
                                            $pl2 = $cv - $p->amount;
                                        @endphp
                                        <tr
                                            class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800">
                                            <td class="py-2.5 text-stone-500 dark:text-stone-400">
                                                {{ $p->purchase_date->isoFormat('D MMM YY') }}</td>
                                            <td class="py-2.5">
                                                <p class="font-medium text-stone-800 dark:text-white">
                                                    {{ $p->instrument->name }}</p>
                                                <p class="text-xs text-stone-400">{{ $p->quantity }}
                                                    {{ $p->instrument->symbol }}</p>
                                            </td>
                                            <td class="py-2.5 text-right font-medium text-stone-700 dark:text-stone-300">Rp
                                                {{ number_format($p->amount, 0, ',', '.') }}</td>
                                            <td class="py-2.5 text-right">
                                                <p class="font-medium text-stone-800 dark:text-white">Rp
                                                    {{ number_format($cv, 0, ',', '.') }}</p>
                                                <p class="text-xs {{ $pl2 >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                    {{ $pl2 >= 0 ? '+' : '' }}Rp
                                                    {{ number_format(abs($pl2), 0, ',', '.') }}</p>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">

                {{-- BREAKDOWN PER TIPE --}}
                @if ($byType->count())
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                        <h3 class="font-bold text-stone-800 dark:text-white mb-4">Breakdown per Tipe</h3>
                        <div class="space-y-3">
                            @foreach ($byType as $type => $data)
                                @php $pl3 = $data['profit_loss']; @endphp
                                <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl">
                                    <div class="flex justify-between items-center mb-1">
                                        <span
                                            class="text-sm font-medium text-stone-700 dark:text-stone-300">{{ ucfirst($type) }}</span>
                                        <span
                                            class="text-sm font-bold {{ $pl3 >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $pl3 >= 0 ? '+' : '' }}Rp
                                            {{ number_format(abs($pl3), 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-xs text-stone-400">
                                        <span>Modal: Rp {{ number_format($data['total_invested'], 0, ',', '.') }}</span>
                                        <span>{{ $data['count'] }} instrumen</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- BEST / WORST --}}
                @if ($bestPerformer)
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                        <h3 class="font-bold text-stone-800 dark:text-white mb-4">Statistik Cepat</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-stone-500 dark:text-stone-400 mb-2">🏆 Best Performer</p>
                                <div class="flex items-center gap-3 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                                    <div
                                        class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600">
                                        <i class="fa-solid fa-trophy text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-stone-800 dark:text-white text-sm">
                                            {{ $bestPerformer->name }}</p>
                                        <p class="text-xs text-emerald-600 dark:text-emerald-400">
                                            {{ $bestPerformer->getProfitLossPercentage() >= 0 ? '+' : '' }}{{ round($bestPerformer->getProfitLossPercentage(), 2) }}%
                                            · {{ $bestPerformer->getProfitLoss() >= 0 ? '+' : '' }}Rp
                                            {{ number_format(abs($bestPerformer->getProfitLoss()), 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @if ($instruments->count() > 1 && $worstPerformer && $worstPerformer->id !== $bestPerformer->id)
                                <div>
                                    <p class="text-xs text-stone-500 dark:text-stone-400 mb-2">📉 Worst Performer</p>
                                    <div class="flex items-center gap-3 p-3 bg-rose-50 dark:bg-rose-900/20 rounded-xl">
                                        <div
                                            class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center text-rose-500">
                                            <i class="fa-solid fa-arrow-trend-down text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-stone-800 dark:text-white text-sm">
                                                {{ $worstPerformer->name }}</p>
                                            <p
                                                class="text-xs {{ $worstPerformer->getProfitLoss() >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $worstPerformer->getProfitLossPercentage() >= 0 ? '+' : '' }}{{ round($worstPerformer->getProfitLossPercentage(), 2) }}%
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- AKUN INVESTASI --}}
                @if ($investmentAccounts->count())
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-stone-800 dark:text-white">Platform Investasi</h3>
                            <a href="{{ route('dashboard.finance') }}" class="text-xs text-blue-600 hover:underline">+
                                Tambah</a>
                        </div>
                        <div class="space-y-2">
                            @foreach ($investmentAccounts as $acc)
                                <div
                                    class="flex items-center justify-between p-3 border border-stone-200 dark:border-stone-700 rounded-xl">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
                                            style="background-color:{{ $acc->color }}">
                                            {{ strtoupper(substr($acc->name, 0, 2)) }}
                                        </div>
                                        <p class="font-medium text-stone-800 dark:text-white text-sm">{{ $acc->name }}
                                        </p>
                                    </div>
                                    <p class="font-bold text-stone-700 dark:text-stone-300 text-sm">
                                        {{ $acc->getFormattedBalance() }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- MODAL: TAMBAH INSTRUMEN --}}
    <div id="modal-add-instrument"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white">Tambah Instrumen Investasi</h3>
                <button onclick="closeModal('modal-add-instrument')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="form-add-instrument" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"><label
                            class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nama *</label><input
                            type="text" name="name" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="Misal: Bitcoin, Saham BBCA"></div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Simbol
                            *</label><input type="text" name="symbol" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="BTC, BBCA"></div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tipe *</label>
                        <select name="type" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="crypto">Cryptocurrency</option>
                            <option value="stocks">Saham</option>
                            <option value="mutual-fund">Reksadana</option>
                            <option value="gold">Emas</option>
                            <option value="bonds">Obligasi</option>
                            <option value="etf">ETF</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-span-2"><label
                            class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Harga Saat Ini (Rp)
                            *</label><input type="number" name="current_price" required min="0" step="any"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0"></div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">
                            Platform / Akun Investasi
                            <span class="text-stone-400 font-normal">(Indodax, Ajaib, dll)</span>
                        </label>
                        <select name="finance_account_id"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="">-- Tanpa Platform (bisa diisi nanti) --</option>
                            @foreach ($investmentAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} (Rp
                                    {{ number_format($acc->balance, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                        @if ($investmentAccounts->isEmpty())
                            <p class="text-xs text-amber-600 mt-1"><i class="fa-solid fa-circle-info mr-1"></i>Belum ada
                                akun investasi. <a href="{{ route('dashboard.finance') }}" class="underline">Tambah di
                                    Finance</a>.</p>
                        @endif
                    </div>
                    <div class="col-span-2"><label
                            class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Catatan</label><input
                            type="text" name="notes"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="Opsional"></div>
                </div>
            </form>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-instrument')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitInstrument()"
                    class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-medium">Tambah
                    Instrumen</button>
            </div>
        </div>
    </div>

    {{-- MODAL: CATAT PEMBELIAN --}}
    <div id="modal-add-purchase"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white">Catat Pembelian</h3>
                <button onclick="closeModal('modal-add-purchase')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="form-add-purchase" class="p-6 space-y-4">
                @csrf
                <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Instrumen *</label>
                    <select id="select-instrument" onchange="setPurchaseInstrument(this.value)" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <option value="">-- Pilih Instrumen --</option>
                        @foreach ($instruments as $ins)
                            <option value="{{ $ins->id }}">{{ $ins->name }}
                                ({{ $ins->symbol }})
                                {{ $ins->financeAccount ? ' — ' . $ins->financeAccount->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Jumlah (Rp)
                            *</label><input type="number" name="amount" required min="1"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0"></div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Kuantitas
                            *</label><input type="number" name="quantity" required min="0" step="any"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Harga/Unit (Rp)
                            *</label><input type="number" name="price_per_unit" required min="0" step="any"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0"></div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Fee
                            (Rp)</label><input type="number" name="fees" min="0" value="0"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0"></div>
                </div>
                <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tanggal Beli
                        *</label><input type="date" name="purchase_date" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                </div>
            </form>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-purchase')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitPurchase()"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium">Simpan</button>
            </div>
        </div>
    </div>

    {{-- MODAL: UPDATE HARGA --}}
    <div id="modal-update-price"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-sm shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white" id="price-modal-title">Update Harga</h3>
                <button onclick="closeModal('modal-update-price')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" id="price-instrument-id">
                <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Harga Saat Ini (Rp)
                        *</label><input type="number" id="new-price-val" min="0" step="any"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white text-lg font-medium"
                        placeholder="0"></div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-update-price')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitUpdatePrice()"
                    class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-medium">Update
                    Harga</button>
            </div>
        </div>
    </div>

    {{-- MODAL: TAUTKAN KE PLATFORM --}}
    <div id="modal-link-account"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-sm shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white" id="link-modal-title">Tautkan ke Platform
                </h3>
                <button onclick="closeModal('modal-link-account')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" id="link-instrument-id">
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Platform / Akun
                        Investasi</label>
                    <select id="link-account-select"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <option value="">-- Tanpa Platform --</option>
                        @foreach ($investmentAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                        @endforeach
                    </select>
                    @if ($investmentAccounts->isEmpty())
                        <p class="text-xs text-amber-600 mt-2"><i class="fa-solid fa-circle-info mr-1"></i>Belum ada akun
                            investasi. <a href="{{ route('dashboard.finance') }}" class="underline">Tambah di
                                Finance</a>.</p>
                    @endif
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-link-account')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitLinkAccount()"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium">Simpan
                    Tautan</button>
            </div>
        </div>
    </div>

    {{-- MODAL: DETAIL INSTRUMEN --}}
    <div id="modal-detail"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div
                class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800 sticky top-0 bg-white dark:bg-stone-900 z-10">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white" id="detail-title">Detail Instrumen</h3>
                <button onclick="closeModal('modal-detail')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div id="detail-body" class="p-6">
                <p class="text-center text-stone-400 py-8">Memuat...</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
            document.body.classList.add('modal-open')
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
            document.body.classList.remove('modal-open')
        }

        function toast(msg, ok = true) {
            const el = document.createElement('div');
            el.className =
                `fixed bottom-5 right-5 z-[9999] px-4 py-3 rounded-xl shadow-xl text-white text-sm font-medium flex items-center gap-2 ${ok?'bg-emerald-500':'bg-rose-500'}`;
            el.innerHTML = `<i class="fa-solid ${ok?'fa-check-circle':'fa-circle-xmark'}"></i> ${msg}`;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3500)
        }
        // ── Core API helper (FINAL FIX) ──────────────────────────────────────────
        // Kirim PUT/PATCH/DELETE langsung (bukan diubah jadi POST).
        // fetch() mendukung semua HTTP method secara native.
        // Yang penting: X-CSRF-TOKEN di header (bukan di body).
        // Error 405 biasanya karena route belum terdaftar, BUKAN karena method-nya.
        async function api(method, url, data = null) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const upper = method.toUpperCase();

            const opts = {
                method: upper, // kirim method sebenarnya langsung
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
            };

            // Semua method kecuali GET/HEAD boleh punya body
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
                // Route tidak ada — jangan pakai spoofing, tapi debug routenya
                toast('Endpoint tidak ditemukan. Pastikan web.php sudah di-update dan php artisan route:clear sudah dijalankan.',
                    false);
                console.error('405 on:', upper, url);
                return {
                    success: false,
                    message: 'Method Not Allowed - route belum terdaftar'
                };
            }
            if (!res.ok && res.status !== 422) {
                const text = await res.text().catch(() => '');
                console.error(`HTTP ${res.status}:`, text);
                toast(`Error ${res.status}`, false);
                return {
                    success: false,
                    message: `HTTP ${res.status}`
                };
            }
            return res.json();
        }


        function formToJson(id) {
            const fd = new FormData(document.getElementById(id));
            const obj = {};
            fd.forEach((v, k) => {
                if (v !== '') obj[k] = v
            });
            return obj
        }

        function filterType(type) {
            ['all', 'crypto', 'stocks', 'mutual-fund'].forEach(t => {
                const b = document.getElementById('filter-' + t);
                if (b) b.className =
                    'px-3 py-1 text-xs rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:bg-stone-200'
            });
            const ab = document.getElementById('filter-' + type);
            if (ab) ab.className = 'px-3 py-1 text-xs rounded-full bg-stone-800 dark:bg-stone-700 text-white';
            document.querySelectorAll('.instrument-card').forEach(c => {
                c.style.display = (type === 'all' || c.dataset.type === type) ? 'block' : 'none'
            });
        }

        async function submitInstrument() {
            const data = formToJson('form-add-instrument');
            const res = await api('POST', '{{ route('investments.instruments.store') }}', data);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800)
            } else toast(res.message, false)
        }

        let purchaseInstrumentId = null;

        function setPurchaseInstrument(id) {
            purchaseInstrumentId = id
        }
        async function submitPurchase() {
            if (!purchaseInstrumentId) {
                toast('Pilih instrumen dulu', false);
                return
            }
            const data = formToJson('form-add-purchase');
            const res = await api('POST', `/investments/${purchaseInstrumentId}/purchases`, data);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800)
            } else toast(res.message, false)
        }

        async function deleteInstrument(id) {
            if (!confirm('Hapus instrumen beserta semua pembelian?')) return;
            const res = await api('DELETE', `/investments/${id}`);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800)
            } else toast(res.message, false)
        }

        function openUpdatePriceModal(id, name, price) {
            document.getElementById('price-instrument-id').value = id;
            document.getElementById('price-modal-title').textContent = 'Update Harga: ' + name;
            document.getElementById('new-price-val').value = price;
            openModal('modal-update-price')
        }
        async function submitUpdatePrice() {
            const id = document.getElementById('price-instrument-id').value;
            const res = await api('PATCH', `/investments/${id}/price`, {
                current_price: document.getElementById('new-price-val').value
            });
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800)
            } else toast(res.message, false)
        }

        // ── Tautkan instrumen ke platform ─────────────────────────────────
        function openLinkAccountModal(id, name) {
            document.getElementById('link-instrument-id').value = id;
            document.getElementById('link-modal-title').textContent = 'Tautkan: ' + name;
            openModal('modal-link-account')
        }
        async function submitLinkAccount() {
            const id = document.getElementById('link-instrument-id').value;
            const accountId = document.getElementById('link-account-select').value;
            const res = await api('PUT', `/investments/${id}`, {
                finance_account_id: accountId || null
            });
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800)
            } else toast(res.message, false)
        }

        // ── Detail instrumen ───────────────────────────────────────────────
        async function openDetailModal(id) {
            openModal('modal-detail');
            document.getElementById('detail-body').innerHTML =
                '<p class="text-center text-stone-400 py-8">Memuat...</p>';
            const res = await api('GET', `/investments/${id}`);
            if (!res.success) {
                document.getElementById('detail-body').innerHTML =
                    '<p class="text-center text-rose-500 py-8">Gagal memuat.</p>';
                return
            }
            const i = res.instrument;
            document.getElementById('detail-title').textContent = i.name + ' (' + i.symbol + ')';
            const plColor = i.profit_loss >= 0 ? 'text-emerald-600' : 'text-rose-600';
            document.getElementById('detail-body').innerHTML = `
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl"><p class="text-xs text-stone-400 mb-1">Total Modal</p><p class="text-lg font-bold text-stone-800 dark:text-white">Rp ${Number(i.total_invested).toLocaleString('id-ID')}</p></div>
            <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl"><p class="text-xs text-stone-400 mb-1">Nilai Sekarang</p><p class="text-lg font-bold text-stone-800 dark:text-white">Rp ${Number(i.current_value).toLocaleString('id-ID')}</p></div>
            <div class="p-3 ${i.profit_loss>=0?'bg-emerald-50 dark:bg-emerald-900/20':'bg-rose-50 dark:bg-rose-900/20'} rounded-xl"><p class="text-xs text-stone-400 mb-1">Profit/Loss</p><p class="text-lg font-bold ${plColor}">${i.profit_loss>=0?'+':''}Rp ${Math.abs(Number(i.profit_loss)).toLocaleString('id-ID')}</p><p class="text-xs ${plColor}">${i.profit_loss_pct>=0?'+':''}${i.profit_loss_pct}%</p></div>
            <div class="p-3 bg-stone-50 dark:bg-stone-800 rounded-xl"><p class="text-xs text-stone-400 mb-1">Platform</p><p class="text-sm font-bold text-stone-800 dark:text-white">${i.account_name||'<span class="text-stone-400 font-normal">Tidak dikaitkan</span>'}</p><p class="text-xs text-stone-400">Harga: Rp ${Number(i.current_price).toLocaleString('id-ID')}</p></div>
        </div>
        <h4 class="font-bold text-stone-800 dark:text-white mb-3">Riwayat Pembelian (${i.purchases_count})</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-sm"><thead><tr class="border-b border-stone-200 dark:border-stone-700"><th class="text-left py-2 text-stone-500 font-medium">Tanggal</th><th class="text-right py-2 text-stone-500 font-medium">Jumlah</th><th class="text-right py-2 text-stone-500 font-medium">Qty</th><th class="text-right py-2 text-stone-500 font-medium">Skrg</th><th></th></tr></thead>
            <tbody>${i.purchases.map(p=>`<tr class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800 group"><td class="py-2.5 text-stone-500">${p.purchase_date_fmt}</td><td class="py-2.5 text-right font-medium text-stone-800 dark:text-white">Rp ${Number(p.amount).toLocaleString('id-ID')}</td><td class="py-2.5 text-right text-stone-500">${p.quantity}</td><td class="py-2.5 text-right"><p class="font-medium text-stone-800 dark:text-white">Rp ${Number(p.current_value).toLocaleString('id-ID')}</p><p class="text-xs ${p.profit_loss>=0?'text-emerald-600':'text-rose-600'}">${p.profit_loss>=0?'+':''}${p.profit_loss_pct}%</p></td><td class="py-2.5 text-right"><button onclick="deletePurchase(${p.id})" class="opacity-0 group-hover:opacity-100 text-rose-400 hover:text-rose-600"><i class="fa-solid fa-trash-can text-xs"></i></button></td></tr>`).join('')}
            </tbody></table>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <button onclick="closeModal('modal-detail');openUpdatePriceModal(${i.id},'${i.name.replace(/'/g,"\\'")}',${i.current_price})" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-medium">Update Harga</button>
            <button onclick="closeModal('modal-detail');document.getElementById('select-instrument').value='${i.id}';setPurchaseInstrument('${i.id}');openModal('modal-add-purchase')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium">Tambah Pembelian</button>
            <button onclick="closeModal('modal-detail');openLinkAccountModal(${i.id},'${i.name.replace(/'/g,"\\'")}');" class="px-4 py-2 bg-stone-600 hover:bg-stone-700 text-white rounded-xl text-sm font-medium">Tautkan Platform</button>
        </div>
    `;
        }

        async function deletePurchase(id) {
            if (!confirm('Hapus pembelian ini?')) return;
            const res = await api('DELETE', `/investments/purchases/${id}`);
            if (res.success) {
                toast(res.message);
                closeModal('modal-detail');
                setTimeout(() => location.reload(), 800)
            } else toast(res.message, false)
        }

        // Chart
        (function() {
            const ctx = document.getElementById('perfChart');
            if (!ctx) return;
            const labels = [],
                vals = [],
                colors = [];
            @foreach ($instruments as $ins)
                labels.push('{{ $ins->symbol }}');
                vals.push({{ round($ins->getProfitLossPercentage(), 2) }});
                colors.push({{ $ins->getProfitLoss() >= 0 ? 'true' : 'false' }} ? '#10b981' : '#ef4444');
            @endforeach
            if (!vals.length) return;
            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Return (%)',
                        data: vals,
                        backgroundColor: colors,
                        borderRadius: 6
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
                                label: c => `${c.raw>=0?'+':''}${c.raw}%`
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: v => v + '%'
                            }
                        }
                    }
                }
            });
        })();

        document.addEventListener('DOMContentLoaded', () => {
            const today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('input[type="date"]').forEach(el => {
                if (!el.value) el.value = today
            });
        });
    </script>
@endpush
