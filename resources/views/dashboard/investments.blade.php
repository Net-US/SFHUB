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
                                                <button
                                                    onclick="openEditPosisiModal({{ $ins->id }}, '{{ addslashes($ins->name) }}', {{ $ins->total_invested }}, {{ $ins->total_quantity }}, {{ $ins->current_price }})"
                                                    class="w-7 h-7 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 flex items-center justify-center hover:bg-amber-200"
                                                    title="Edit Posisi (koreksi modal/qty)">
                                                    <i class="fa-solid fa-sliders text-xs"></i>
                                                </button>
                                                <button
                                                    onclick="deleteInstrument({{ $ins->id }}, '{{ addslashes($ins->name) }}')"
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
            <form id="form-add-instrument" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    {{-- Info dasar --}}
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nama *</label>
                        <input type="text" name="name" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="Misal: Bitcoin, Saham BBCA">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Simbol *</label>
                        <input type="text" name="symbol" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="BTC, BBCA">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tipe *</label>
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
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Harga Saat Ini
                            (Rp) *</label>
                        <input type="number" name="current_price" id="inst-current-price" required min="0"
                            step="any" oninput="calcInitialModal()"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Platform / Akun
                            Investasi <span class="text-stone-400 font-normal">(Indodax, Ajaib, dll)</span></label>
                        <select name="finance_account_id"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="">-- Tanpa Platform --</option>
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
                </div>

                {{-- Setup Awal — kondisi portfolio saat ini --}}
                <div class="border-t border-stone-200 dark:border-stone-700 pt-4">
                    <div class="flex items-center gap-2 mb-3">
                        <button type="button" onclick="toggleSetupAwal()" id="setup-awal-toggle"
                            class="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
                            <i class="fa-solid fa-plus-circle"></i>
                            <span id="setup-awal-label">Isi kondisi portfolio saat ini (opsional)</span>
                        </button>
                    </div>

                    <div id="setup-awal-fields" class="hidden space-y-4">
                        {{-- Penjelasan kontekstual --}}
                        <div
                            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-3">
                            <p class="text-xs text-blue-700 dark:text-blue-300 font-medium mb-1">
                                <i class="fa-solid fa-lightbulb mr-1"></i>Untuk apa ini?
                            </p>
                            <p class="text-xs text-blue-600 dark:text-blue-400">
                                Jika kamu sudah punya BTC/saham tapi lupa riwayat pembeliannya, isi saja <strong>nilai
                                    sekarang</strong> dan <strong>jumlah yang dimiliki</strong>.
                                Sistem akan otomatis menghitung modal dari return % jika diisi.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">
                                    Nilai Saat Ini (Rp)
                                    <span class="text-stone-400 font-normal text-[11px]">total uang yang ada</span>
                                </label>
                                <input type="number" name="initial_value" id="inst-initial-value" min="0"
                                    step="any" oninput="calcInitialModal()"
                                    class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                                    placeholder="Contoh: 1650000 untuk BTC">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">
                                    Jumlah yang Dimiliki
                                    <span class="text-stone-400 font-normal text-[11px]">unit/koin</span>
                                </label>
                                <input type="number" name="initial_quantity" id="inst-initial-qty" min="0"
                                    step="any" oninput="calcInitialModal()"
                                    class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                                    placeholder="Contoh: 0.00173 BTC">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">
                                    Return / Untung-Rugi (%)
                                    <span class="text-stone-400 font-normal text-[11px]">jika tahu, digunakan menghitung
                                        modal</span>
                                </label>
                                <input type="number" name="initial_return_pct" id="inst-return-pct" step="any"
                                    oninput="calcInitialModal()"
                                    class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                                    placeholder="Contoh: 15.5 atau -8.2 (opsional)">
                            </div>
                        </div>

                        {{-- Kalkulasi otomatis (preview) --}}
                        <div id="setup-preview" class="hidden bg-stone-50 dark:bg-stone-800 rounded-xl p-3 space-y-1.5">
                            <p class="text-xs font-semibold text-stone-600 dark:text-stone-300 mb-2">
                                <i class="fa-solid fa-calculator mr-1"></i>Estimasi otomatis:
                            </p>
                            <div class="flex justify-between text-xs">
                                <span class="text-stone-500 dark:text-stone-400">Modal (perkiraan)</span>
                                <span id="preview-modal" class="font-medium text-stone-700 dark:text-stone-300">—</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-stone-500 dark:text-stone-400">Harga rata-rata beli</span>
                                <span id="preview-avg" class="font-medium text-stone-700 dark:text-stone-300">—</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-stone-500 dark:text-stone-400">Profit/Loss estimasi</span>
                                <span id="preview-pl" class="font-medium">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Catatan</label>
                    <input type="text" name="notes"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Opsional">
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

    {{-- MODAL: EDIT POSISI (koreksi manual total_invested & total_quantity) --}}
    <div id="modal-edit-posisi"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <div>
                    <h3 class="text-lg font-bold text-stone-900 dark:text-white" id="posisi-modal-title">Edit Posisi</h3>
                    <p class="text-xs text-stone-400 mt-0.5">Koreksi manual jika data tidak akurat</p>
                </div>
                <button onclick="closeModal('modal-edit-posisi')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" id="posisi-instrument-id">
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3">
                    <p class="text-xs text-amber-700 dark:text-amber-400">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                        Seperti "edit saldo" di bank — gunakan jika modal/quantity tidak sesuai kenyataan.
                        Pembelian sebelumnya tidak dihapus, tapi summary akan mengikuti nilai yang diisi di sini.
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">
                        Total Modal yang Diinvestasikan (Rp) *
                    </label>
                    <input type="number" id="posisi-modal" min="0" step="any" oninput="calcPosisiAvg()"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white text-lg font-medium"
                        placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">
                        Total Unit/Koin yang Dimiliki *
                    </label>
                    <input type="number" id="posisi-qty" min="0" step="any" oninput="calcPosisiAvg()"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white text-lg font-medium"
                        placeholder="0">
                </div>
                <div class="bg-stone-50 dark:bg-stone-800 rounded-xl p-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-stone-500 dark:text-stone-400">Harga rata-rata beli (dihitung otomatis)</span>
                        <span id="posisi-avg-display" class="font-bold text-stone-800 dark:text-white">—</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Update harga saat ini?
                        <span class="text-stone-400 font-normal">(opsional)</span></label>
                    <input type="number" id="posisi-price" min="0" step="any"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Kosongkan jika tidak ingin ubah">
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-edit-posisi')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitEditPosisi()"
                    class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-medium">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>Simpan Posisi
                </button>
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
        // ── Core API helper ───────────────────────────────────────────────────
        // Laravel hanya menerima GET & POST secara native dari fetch/XHR.
        // PUT/PATCH/DELETE dikirim sebagai POST + _method spoofing.
        async function api(method, url, data = null) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const upper = method.toUpperCase();
            const spoof = ['PUT', 'PATCH', 'DELETE'].includes(upper);
            const payload = Object.assign({}, data ?? {});
            if (spoof) payload['_method'] = upper;
            const opts = {
                method: spoof ? 'POST' : upper,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            };
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
                toast('Method tidak diizinkan (405)', false);
                return {
                    success: false,
                    message: 'Method Not Allowed'
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

        async function deleteInstrument(id, name) {
            showDeleteConfirm({
                title: 'Hapus Instrumen?',
                message: `Hapus "${name || 'Instrumen ini'}" beserta seluruh riwayat pembelian?`,
                warning: 'Semua data pembelian terkait akan ikut terhapus.',
                onConfirm: async () => {
                    const res = await api('DELETE', `/investments/${id}`);
                    if (res.success) {
                        toast(res.message);
                        setTimeout(() => location.reload(), 800)
                    } else toast(res.message, false)
                }
            });
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
            <button onclick="closeModal('modal-detail');openEditPosisiModal(${i.id},'${i.name.replace(/'/g,"\\'")}',${i.total_invested},${i.total_quantity},${i.current_price})" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-sm font-medium"><i class='fa-solid fa-sliders mr-1'></i>Edit Posisi</button>
        </div>
    `;
        }

        async function deletePurchase(id) {
            showDeleteConfirm({
                title: 'Hapus Pembelian?',
                message: 'Hapus data pembelian ini?',
                warning: 'Data pembelian akan dihapus dan portofolio akan diperbarui.',
                onConfirm: async () => {
                    const res = await api('DELETE', `/investments/purchases/${id}`);
                    if (res.success) {
                        toast(res.message);
                        closeModal('modal-detail');
                        setTimeout(() => location.reload(), 800)
                    } else toast(res.message, false)
                }
            });
        }

        // ── Toggle Setup Awal fields ──────────────────────────────────────
        function toggleSetupAwal() {
            const fields = document.getElementById('setup-awal-fields');
            const label = document.getElementById('setup-awal-label');
            const icon = document.getElementById('setup-awal-toggle').querySelector('i');
            const hidden = fields.classList.toggle('hidden');
            label.textContent = hidden ? 'Isi kondisi portfolio saat ini (opsional)' : 'Sembunyikan setup awal';
            icon.className = hidden ? 'fa-solid fa-plus-circle' : 'fa-solid fa-minus-circle';
        }

        function calcInitialModal() {
            const price = parseFloat(document.getElementById('inst-current-price')?.value) || 0;
            const value = parseFloat(document.getElementById('inst-initial-value')?.value) || 0;
            const qty = parseFloat(document.getElementById('inst-initial-qty')?.value) || 0;
            const retPct = parseFloat(document.getElementById('inst-return-pct')?.value);
            const preview = document.getElementById('setup-preview');

            if (!preview) return;

            // Hitung nilai
            const hasData = value > 0 || qty > 0;
            if (!hasData) {
                preview.classList.add('hidden');
                return;
            }
            preview.classList.remove('hidden');

            let modal = 0;
            if (!isNaN(retPct) && retPct !== 0 && value > 0) {
                modal = value / (1 + retPct / 100);
            } else if (value > 0) {
                modal = value;
            }

            const computedQty = qty > 0 ? qty : (price > 0 && value > 0 ? value / price : 0);
            const avgPrice = computedQty > 0 ? modal / computedQty : 0;
            const pl = value - modal;

            const fmt = (n) => 'Rp ' + Math.round(n).toLocaleString('id-ID');
            document.getElementById('preview-modal').textContent = modal > 0 ? fmt(modal) : '—';
            document.getElementById('preview-avg').textContent = avgPrice > 0 ? fmt(avgPrice) : '—';
            const plEl = document.getElementById('preview-pl');
            if (modal > 0) {
                plEl.textContent = (pl >= 0 ? '+' : '') + fmt(pl) + (modal > 0 ? ` (${((pl/modal)*100).toFixed(1)}%)` : '');
                plEl.className = 'font-medium ' + (pl >= 0 ? 'text-emerald-600' : 'text-rose-600');
            } else {
                plEl.textContent = '—';
                plEl.className = 'font-medium text-stone-400';
            }
        }

        // ── Edit Posisi ───────────────────────────────────────────────────
        function openEditPosisiModal(id, name, totalInvested, totalQty, currentPrice) {
            document.getElementById('posisi-instrument-id').value = id;
            document.getElementById('posisi-modal-title').textContent = 'Edit Posisi: ' + name;
            document.getElementById('posisi-modal').value = totalInvested || '';
            document.getElementById('posisi-qty').value = totalQty || '';
            document.getElementById('posisi-price').value = '';
            calcPosisiAvg();
            openModal('modal-edit-posisi');
        }

        function calcPosisiAvg() {
            const modal = parseFloat(document.getElementById('posisi-modal')?.value) || 0;
            const qty = parseFloat(document.getElementById('posisi-qty')?.value) || 0;
            const avg = qty > 0 ? modal / qty : 0;
            const el = document.getElementById('posisi-avg-display');
            if (el) el.textContent = avg > 0 ? 'Rp ' + Math.round(avg).toLocaleString('id-ID') : '—';
        }

        async function submitEditPosisi() {
            const id = document.getElementById('posisi-instrument-id').value;
            const modal = document.getElementById('posisi-modal').value;
            const qty = document.getElementById('posisi-qty').value;
            const price = document.getElementById('posisi-price').value;

            if (!modal || !qty) {
                toast('Isi total modal dan jumlah unit', false);
                return;
            }

            const payload = {
                total_invested: parseFloat(modal),
                total_quantity: parseFloat(qty),
            };
            if (price) payload.current_price = parseFloat(price);

            const res = await api('PATCH', `/investments/${id}/position`, payload);
            if (res.success) {
                toast(res.message);
                closeModal('modal-edit-posisi');
                setTimeout(() => location.reload(), 800);
            } else toast(res.message, false);
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
