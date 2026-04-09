{{-- resources/views/dashboard/debts.blade.php --}}
@extends('layouts.app-dashboard')
@section('title', 'Debt Tracker | StudentHub')
@section('page-title', 'Debt Tracker')
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
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Debt Tracker</h2>
                <p class="text-stone-500 dark:text-stone-400 text-sm">Kelola hutang dan piutang Anda</p>
            </div>
            <div class="flex gap-2">
                <button onclick="openDebtModal('borrower')"
                    class="flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-plus"></i> Tambah Hutang
                </button>
                <button onclick="openDebtModal('lender')"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-plus"></i> Tambah Piutang
                </button>
            </div>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-rose-500 to-rose-600 rounded-2xl p-5 text-white shadow-lg">
                <p class="text-rose-100 text-xs mb-1">Total Hutang</p>
                <h3 class="text-2xl font-bold">Rp {{ number_format($totalDebt, 0, ',', '.') }}</h3>
                <p class="text-rose-200 text-[11px] mt-1">Sisa: Rp {{ number_format($totalRemaining, 0, ',', '.') }}</p>
            </div>
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg">
                <p class="text-emerald-100 text-xs mb-1">Total Piutang</p>
                <h3 class="text-2xl font-bold">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</h3>
                <p class="text-emerald-200 text-[11px] mt-1">Belum terima: Rp
                    {{ number_format($totalStillOwed, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <p class="text-stone-500 dark:text-stone-400 text-xs mb-1">Total Dibayar</p>
                <h3 class="text-2xl font-bold text-emerald-600">Rp {{ number_format($totalPaid, 0, ',', '.') }}</h3>
                <p class="text-[11px] text-stone-400 mt-1">Dari total hutang</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <p class="text-stone-500 dark:text-stone-400 text-xs mb-1">Overdue</p>
                <h3
                    class="text-2xl font-bold {{ $overdueDebts->count() > 0 ? 'text-rose-600' : 'text-stone-800 dark:text-white' }}">
                    {{ $overdueDebts->count() + $overdueReceivables->count() }}
                </h3>
                <p class="text-[11px] text-stone-400 mt-1">hutang/piutang terlambat</p>
            </div>
        </div>

        {{-- OVERDUE ALERT --}}
        @if ($overdueDebts->count() > 0)
            <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl p-4">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 mt-0.5 text-lg"></i>
                    <div>
                        <p class="font-semibold text-rose-800 dark:text-rose-300 text-sm mb-1">Hutang Overdue!</p>
                        @foreach ($overdueDebts as $d)
                            <p class="text-xs text-rose-700 dark:text-rose-400">
                                <strong>{{ $d->creditor_name }}</strong> — Rp
                                {{ number_format($d->remaining_amount, 0, ',', '.') }}
                                (jatuh tempo {{ $d->due_date->isoFormat('D MMM YYYY') }})
                            </p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- MAIN GRID --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- LEFT (2-col) --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- TABS: Hutang / Piutang --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
                    <div class="flex border-b border-stone-200 dark:border-stone-800">
                        <button onclick="switchTab('tab-debts')" id="btn-tab-debts"
                            class="flex-1 py-3 text-sm font-semibold text-rose-600 border-b-2 border-rose-500 transition-colors">
                            <i class="fa-solid fa-hand-holding-usd mr-2"></i>Hutang ({{ $myDebts->count() }})
                        </button>
                        <button onclick="switchTab('tab-receivables')" id="btn-tab-receivables"
                            class="flex-1 py-3 text-sm font-medium text-stone-500 dark:text-stone-400 hover:text-stone-800 dark:hover:text-white transition-colors">
                            <i class="fa-solid fa-money-bill-transfer mr-2"></i>Piutang ({{ $myReceivables->count() }})
                        </button>
                    </div>

                    {{-- Tab: Hutang --}}
                    <div id="tab-debts" class="p-6">
                        @if ($myDebts->isEmpty())
                            <p class="text-center py-8 text-stone-400 text-sm">Tidak ada hutang. Bagus!</p>
                        @else
                            <div class="space-y-4">
                                @foreach ($myDebts as $debt)
                                    @php
                                        $paid = $debt->total_amount - $debt->remaining_amount;
                                        $pct =
                                            $debt->total_amount > 0 ? round(($paid / $debt->total_amount) * 100, 0) : 0;
                                        $daysLeft = $debt->due_date
                                            ? (int) now()->diffInDays($debt->due_date, false)
                                            : null;
                                    @endphp
                                    <div
                                        class="p-4 border {{ $debt->isOverdue() ? 'border-rose-200 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-900/10' : 'border-stone-200 dark:border-stone-700' }} rounded-xl group">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <h4 class="font-bold text-stone-800 dark:text-white">
                                                    {{ $debt->creditor_name }}</h4>
                                                <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">
                                                    {{ $debt->description }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                @if ($debt->status === 'paid')
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Lunas</span>
                                                @elseif($debt->isOverdue())
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">Overdue</span>
                                                @else
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Aktif</span>
                                                @endif
                                                <div class="opacity-0 group-hover:opacity-100 flex gap-1 transition-all">
                                                    @if ($debt->status !== 'paid')
                                                        <button
                                                            onclick="openPayModal({{ $debt->id }}, '{{ addslashes($debt->creditor_name) }}', {{ $debt->remaining_amount }})"
                                                            class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center hover:bg-emerald-200 transition-colors"
                                                            title="Bayar">
                                                            <i class="fa-solid fa-money-bill-transfer text-xs"></i>
                                                        </button>
                                                    @endif
                                                    <button
                                                        onclick="deleteDebt({{ $debt->id }}, '{{ addslashes($debt->name) }}')"
                                                        class="w-7 h-7 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-500 flex items-center justify-center hover:bg-rose-200 transition-colors"
                                                        title="Hapus">
                                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <p class="text-lg font-bold text-stone-800 dark:text-white">Rp
                                                {{ number_format($debt->total_amount, 0, ',', '.') }}</p>
                                            @if ($debt->interest_rate > 0)
                                                <p class="text-xs text-amber-600 dark:text-amber-400">Bunga
                                                    {{ $debt->interest_rate }}%/tahun · Rp
                                                    {{ number_format($debt->calculateInterest(), 0, ',', '.') }}</p>
                                            @endif
                                        </div>

                                        <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-2 mb-2">
                                            <div class="h-2 rounded-full transition-all {{ $pct >= 100 ? 'bg-emerald-500' : 'bg-blue-500' }}"
                                                style="width:{{ $pct }}%"></div>
                                        </div>
                                        <div class="flex justify-between text-xs text-stone-500 dark:text-stone-400 mb-2">
                                            <span>Terbayar: Rp {{ number_format($paid, 0, ',', '.') }}
                                                ({{ $pct }}%)
                                            </span>
                                            <span>Sisa: Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}</span>
                                        </div>

                                        <div class="flex justify-between text-xs text-stone-500 dark:text-stone-400">
                                            <span><i class="fa-solid fa-calendar mr-1"></i>Mulai:
                                                {{ $debt->start_date->isoFormat('D MMM YYYY') }}</span>
                                            @if ($debt->due_date)
                                                <span
                                                    class="{{ $debt->isOverdue() ? 'text-rose-600 dark:text-rose-400 font-medium' : ($daysLeft !== null && $daysLeft <= 7 ? 'text-amber-600 dark:text-amber-400 font-medium' : '') }}">
                                                    <i class="fa-solid fa-clock mr-1"></i>
                                                    {{ $debt->due_date->isoFormat('D MMM YYYY') }}
                                                    @if ($daysLeft !== null)
                                                        ({{ $debt->isOverdue() ? abs($daysLeft) . ' hari terlambat' : $daysLeft . ' hari lagi' }})
                                                    @endif
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Riwayat pembayaran (collapsed) --}}
                                        @if ($debt->payments->count() > 0)
                                            <div class="mt-3 pt-3 border-t border-stone-100 dark:border-stone-700">
                                                <p class="text-xs font-medium text-stone-500 dark:text-stone-400 mb-2">
                                                    Riwayat {{ $debt->payments->count() }} pembayaran:</p>
                                                <div class="space-y-1 max-h-24 overflow-y-auto">
                                                    @foreach ($debt->payments->sortByDesc('payment_date') as $pay)
                                                        <div
                                                            class="flex justify-between text-xs text-stone-600 dark:text-stone-400 group/pay">
                                                            <span>{{ $pay->payment_date->isoFormat('D MMM YYYY') }}
                                                                {{ $pay->payment_method ? '· ' . $pay->payment_method : '' }}</span>
                                                            <div class="flex items-center gap-2">
                                                                <span class="font-medium text-emerald-600">+Rp
                                                                    {{ number_format($pay->amount, 0, ',', '.') }}</span>
                                                                <button onclick="deletePayment({{ $pay->id }})"
                                                                    class="opacity-0 group-hover/pay:opacity-100 text-rose-400 hover:text-rose-600 transition-all"><i
                                                                        class="fa-solid fa-xmark text-[10px]"></i></button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Tab: Piutang --}}
                    <div id="tab-receivables" class="p-6 hidden">
                        @if ($myReceivables->isEmpty())
                            <p class="text-center py-8 text-stone-400 text-sm">Tidak ada piutang.</p>
                        @else
                            <div class="space-y-4">
                                @foreach ($myReceivables as $debt)
                                    @php
                                        $received = $debt->total_amount - $debt->remaining_amount;
                                        $pct =
                                            $debt->total_amount > 0
                                                ? round(($received / $debt->total_amount) * 100, 0)
                                                : 0;
                                        $daysLeft = $debt->due_date
                                            ? (int) now()->diffInDays($debt->due_date, false)
                                            : null;
                                    @endphp
                                    <div
                                        class="p-4 border {{ $debt->isOverdue() ? 'border-amber-200 dark:border-amber-800 bg-amber-50/30 dark:bg-amber-900/10' : 'border-stone-200 dark:border-stone-700' }} rounded-xl group">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <h4 class="font-bold text-stone-800 dark:text-white">
                                                    {{ $debt->creditor_name }}</h4>
                                                <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">
                                                    {{ $debt->description }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                @if ($debt->status === 'paid')
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Lunas</span>
                                                @else
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Aktif</span>
                                                @endif
                                                <div class="opacity-0 group-hover:opacity-100 flex gap-1 transition-all">
                                                    @if ($debt->status !== 'paid')
                                                        <button
                                                            onclick="openReceiveModal({{ $debt->id }}, '{{ addslashes($debt->creditor_name) }}', {{ $debt->remaining_amount }})"
                                                            class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center hover:bg-emerald-200 transition-colors"
                                                            title="Catat Penerimaan">
                                                            <i class="fa-solid fa-money-bill-transfer text-xs"></i>
                                                        </button>
                                                    @endif
                                                    <button
                                                        onclick="deleteDebt({{ $debt->id }}, '{{ addslashes($debt->name) }}')"
                                                        class="w-7 h-7 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-500 flex items-center justify-center hover:bg-rose-200 transition-colors"
                                                        title="Hapus">
                                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-lg font-bold text-stone-800 dark:text-white mb-2">Rp
                                            {{ number_format($debt->total_amount, 0, ',', '.') }}</p>
                                        <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-2 mb-2">
                                            <div class="h-2 rounded-full bg-emerald-500 transition-all"
                                                style="width:{{ $pct }}%"></div>
                                        </div>
                                        <div class="flex justify-between text-xs text-stone-500 dark:text-stone-400">
                                            <span>Diterima: Rp {{ number_format($received, 0, ',', '.') }}
                                                ({{ $pct }}%)
                                            </span>
                                            <span>Belum: Rp
                                                {{ number_format($debt->remaining_amount, 0, ',', '.') }}</span>
                                        </div>
                                        @if ($debt->due_date)
                                            <p
                                                class="text-xs mt-1 {{ $debt->isOverdue() ? 'text-amber-600 dark:text-amber-400 font-medium' : 'text-stone-400' }}">
                                                <i
                                                    class="fa-solid fa-clock mr-1"></i>{{ $debt->due_date->isoFormat('D MMM YYYY') }}
                                                @if ($daysLeft !== null)
                                                    ({{ $debt->isOverdue() ? abs($daysLeft) . ' hari terlambat' : $daysLeft . ' hari lagi' }})
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- RIWAYAT PEMBAYARAN --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-4">Riwayat Pembayaran Terbaru</h3>
                    @if ($recentPayments->isEmpty())
                        <p class="text-center py-4 text-stone-400 text-sm">Belum ada pembayaran.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-stone-200 dark:border-stone-700">
                                        <th class="text-left py-2 text-stone-500 dark:text-stone-400 font-medium">Tanggal
                                        </th>
                                        <th class="text-left py-2 text-stone-500 dark:text-stone-400 font-medium">Hutang /
                                            Piutang</th>
                                        <th class="text-left py-2 text-stone-500 dark:text-stone-400 font-medium">Metode
                                        </th>
                                        <th class="text-right py-2 text-stone-500 dark:text-stone-400 font-medium">Jumlah
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentPayments as $pay)
                                        <tr
                                            class="border-b border-stone-100 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800 group">
                                            <td class="py-2.5 text-stone-600 dark:text-stone-400">
                                                {{ $pay->payment_date->isoFormat('D MMM YYYY') }}</td>
                                            <td class="py-2.5">
                                                <p class="font-medium text-stone-800 dark:text-white">
                                                    {{ $pay->debt->creditor_name }}</p>
                                                @if ($pay->notes)
                                                    <p class="text-xs text-stone-400">{{ $pay->notes }}</p>
                                                @endif
                                            </td>
                                            <td class="py-2.5 text-stone-500 dark:text-stone-400">
                                                {{ $pay->payment_method ?? '-' }}</td>
                                            <td class="py-2.5 text-right font-bold text-emerald-600">Rp
                                                {{ number_format($pay->amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>

            {{-- RIGHT --}}
            <div class="space-y-6">

                {{-- JATUH TEMPO SEGERA --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-4">Jatuh Tempo Dekat</h3>
                    @if ($upcomingDue->isEmpty())
                        <p class="text-center py-4 text-stone-400 text-sm">Tidak ada yang jatuh tempo dalam 7 hari.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($upcomingDue as $d)
                                @php $dl = (int) now()->diffInDays($d->due_date, false); @endphp
                                <div
                                    class="p-3 border {{ $dl <= 3 ? 'border-rose-200 dark:border-rose-800 bg-rose-50/50 dark:bg-rose-900/10' : 'border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-900/10' }} rounded-xl">
                                    <div class="flex justify-between items-start mb-1">
                                        <h4 class="font-semibold text-stone-800 dark:text-white text-sm">
                                            {{ $d->creditor_name }}</h4>
                                        <span
                                            class="text-xs font-bold {{ $dl <= 3 ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }}">{{ $dl }}
                                            hari</span>
                                    </div>
                                    <p class="text-sm font-bold text-stone-700 dark:text-stone-300">Rp
                                        {{ number_format($d->remaining_amount, 0, ',', '.') }}</p>
                                    <p class="text-xs text-stone-400 mt-1">{{ $d->due_date->isoFormat('D MMM YYYY') }}</p>
                                    <button
                                        onclick="openPayModal({{ $d->id }}, '{{ addslashes($d->creditor_name) }}', {{ $d->remaining_amount }})"
                                        class="mt-2 w-full py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors font-medium">
                                        <i class="fa-solid fa-money-bill-transfer mr-1"></i>Bayar Sekarang
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- STATISTIK --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-4">Statistik</h3>
                    <div class="space-y-4">
                        @if ($totalDebt > 0)
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm text-stone-600 dark:text-stone-400">Progress Pelunasan
                                        Hutang</span>
                                    <span
                                        class="text-sm font-bold text-stone-800 dark:text-white">{{ $totalDebt > 0 ? round(($totalPaid / $totalDebt) * 100, 0) : 0 }}%</span>
                                </div>
                                <div class="w-full bg-stone-100 dark:bg-stone-700 rounded-full h-2">
                                    <div class="bg-emerald-500 h-2 rounded-full transition-all"
                                        style="width:{{ $totalDebt > 0 ? min(100, ($totalPaid / $totalDebt) * 100) : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-stone-50 dark:bg-stone-800 p-3 rounded-xl text-center">
                                <p class="text-xs text-stone-500 dark:text-stone-400">Hutang Aktif</p>
                                <p class="font-bold text-xl text-stone-800 dark:text-white">
                                    {{ $myDebts->where('status', '!=', 'paid')->count() }}</p>
                            </div>
                            <div class="bg-stone-50 dark:bg-stone-800 p-3 rounded-xl text-center">
                                <p class="text-xs text-stone-500 dark:text-stone-400">Piutang Aktif</p>
                                <p class="font-bold text-xl text-stone-800 dark:text-white">
                                    {{ $myReceivables->where('status', '!=', 'paid')->count() }}</p>
                            </div>
                            <div class="bg-emerald-50 dark:bg-emerald-900/20 p-3 rounded-xl text-center">
                                <p class="text-xs text-emerald-600 dark:text-emerald-400">Hutang Lunas</p>
                                <p class="font-bold text-xl text-emerald-700 dark:text-emerald-300">
                                    {{ $myDebts->where('status', 'paid')->count() }}</p>
                            </div>
                            <div class="bg-rose-50 dark:bg-rose-900/20 p-3 rounded-xl text-center">
                                <p class="text-xs text-rose-600 dark:text-rose-400">Overdue</p>
                                <p class="font-bold text-xl text-rose-700 dark:text-rose-300">{{ $overdueDebts->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL: TAMBAH HUTANG / PIUTANG --}}
    <div id="modal-add-debt"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white" id="debt-modal-title">Tambah Hutang</h3>
                <button onclick="closeModal('modal-add-debt')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="form-add-debt" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="debt_type" id="input-debt-type" value="borrower">
                <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1"
                        id="creditor-label">Nama Pemberi Pinjaman *</label><input type="text" name="creditor_name"
                        required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-rose-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Nama orang/lembaga"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Jumlah (Rp)
                            *</label><input type="number" name="total_amount" required min="1"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-rose-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0"></div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Bunga
                            (%/tahun)</label><input type="number" name="interest_rate" value="0" min="0"
                            max="100" step="0.1"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-rose-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tanggal Mulai
                            *</label><input type="date" name="start_date" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-rose-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Jatuh
                            Tempo</label><input type="date" name="due_date"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-rose-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                </div>
                <div><label
                        class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Keterangan</label><input
                        type="text" name="description"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-rose-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Misal: Pinjaman mendesak, Hutang motor"></div>
            </form>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-debt')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitDebt()" id="submit-debt-btn"
                    class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-medium">Simpan
                    Hutang</button>
            </div>
        </div>
    </div>

    {{-- MODAL: BAYAR HUTANG --}}
    <div id="modal-pay-debt"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-sm shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white" id="pay-modal-title">Bayar Hutang</h3>
                <button onclick="closeModal('modal-pay-debt')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" id="pay-debt-id">
                <div class="bg-stone-50 dark:bg-stone-800 rounded-xl p-3 text-center">
                    <p class="text-xs text-stone-500 dark:text-stone-400 mb-1">Sisa Hutang</p>
                    <p class="text-xl font-bold text-rose-600" id="pay-remaining-display">Rp 0</p>
                </div>
                <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Jumlah Bayar (Rp)
                        *</label><input type="number" id="pay-amount" min="1"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="0"></div>
                <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tanggal Bayar
                        *</label><input type="date" id="pay-date"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                </div>
                <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Metode
                        Bayar</label><select id="pay-method"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                        <option value="">-- Pilih (opsional) --</option>
                        <option>Transfer Bank</option>
                        <option>Cash</option>
                        <option>GoPay</option>
                        <option>OVO</option>
                        <option>Dana</option>
                    </select></div>
                <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Catatan</label><input
                        type="text" id="pay-notes"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Opsional"></div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-pay-debt')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitPayment()"
                    class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-medium">Catat
                    Pembayaran</button>
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


        // Tab
        function switchTab(tab) {
            ['tab-debts', 'tab-receivables'].forEach(t => document.getElementById(t).classList.add('hidden'));
            ['btn-tab-debts', 'btn-tab-receivables'].forEach(b => {
                document.getElementById(b).className =
                    'flex-1 py-3 text-sm font-medium text-stone-500 dark:text-stone-400 hover:text-stone-800 dark:hover:text-white transition-colors'
            });
            document.getElementById(tab).classList.remove('hidden');
            const btn = tab === 'tab-debts' ? 'btn-tab-debts' : 'btn-tab-receivables';
            const color = tab === 'tab-debts' ? 'text-rose-600 border-b-2 border-rose-500' :
                'text-emerald-600 border-b-2 border-emerald-500';
            document.getElementById(btn).className = `flex-1 py-3 text-sm font-semibold ${color} transition-colors`;
        }

        // Open debt modal
        function openDebtModal(type) {
            document.getElementById('input-debt-type').value = type;
            document.getElementById('debt-modal-title').textContent = type === 'borrower' ? 'Tambah Hutang' :
                'Tambah Piutang';
            document.getElementById('creditor-label').textContent = type === 'borrower' ? 'Nama Pemberi Pinjaman *' :
                'Nama Peminjam *';
            document.getElementById('submit-debt-btn').className =
                `flex-1 py-2.5 ${type==='borrower'?'bg-rose-600 hover:bg-rose-700':'bg-emerald-600 hover:bg-emerald-700'} text-white rounded-xl font-medium`;
            document.getElementById('submit-debt-btn').textContent = type === 'borrower' ? 'Simpan Hutang' :
                'Simpan Piutang';
            openModal('modal-add-debt')
        }

        async function submitDebt() {
            const form = document.getElementById('form-add-debt');
            const fd = new FormData(form);
            const data = {};
            fd.forEach((v, k) => {
                if (v !== '') data[k] = v
            });
            const res = await api('POST', '{{ route('debts.store') }}', data);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800)
            } else toast(res.message, false)
        }

        async function deleteDebt(id, name) {
            showDeleteConfirm({
                title: 'Hapus Hutang/Piutang?',
                message: `Hapus "${name || 'Data ini'}" beserta semua riwayat pembayarannya?`,
                warning: 'Semua pembayaran terkait akan ikut terhapus.',
                onConfirm: async () => {
                    const res = await api('DELETE', `/debts/${id}`);
                    if (res.success) {
                        toast(res.message);
                        setTimeout(() => location.reload(), 800)
                    } else toast(res.message, false)
                }
            });
        }

        // Pay modal
        let payDebtId = null;

        function openPayModal(id, name, remaining) {
            payDebtId = id;
            document.getElementById('pay-debt-id').value = id;
            document.getElementById('pay-modal-title').textContent = 'Bayar: ' + name;
            document.getElementById('pay-remaining-display').textContent = 'Rp ' + remaining.toLocaleString('id-ID');
            document.getElementById('pay-amount').value = remaining;
            document.getElementById('pay-date').value = new Date().toISOString().split('T')[0];
            document.getElementById('pay-notes').value = '';
            openModal('modal-pay-debt')
        }

        function openReceiveModal(id, name, remaining) {
            openPayModal(id, 'Terima dari: ' + name, remaining)
        }

        async function submitPayment() {
            const id = document.getElementById('pay-debt-id').value;
            const data = {
                amount: document.getElementById('pay-amount').value,
                payment_date: document.getElementById('pay-date').value,
                payment_method: document.getElementById('pay-method').value || undefined,
                notes: document.getElementById('pay-notes').value || undefined,
            };
            if (!data.amount || !data.payment_date) {
                toast('Isi jumlah dan tanggal bayar', false);
                return
            }
            const res = await api('POST', `/debts/${id}/payments`, data);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800)
            } else toast(res.message, false)
        }

        async function deletePayment(id) {
            showDeleteConfirm({
                title: 'Hapus Pembayaran?',
                message: 'Hapus riwayat pembayaran ini?',
                warning: 'Sisa hutang/piutang akan dikembalikan seperti sebelumnya.',
                onConfirm: async () => {
                    const res = await api('DELETE', `/debts/payments/${id}`);
                    if (res.success) {
                        toast(res.message);
                        setTimeout(() => location.reload(), 800)
                    } else toast(res.message, false)
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('input[type="date"]').forEach(el => {
                if (!el.value) el.value = today
            });
        });
    </script>
@endpush
