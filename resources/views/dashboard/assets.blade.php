{{-- resources/views/dashboard/assets.blade.php --}}
@extends('layouts.app-dashboard')
@section('title', 'Asset Management | StudentHub')
@section('page-title', 'Asset Management')
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
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white">Asset Management</h2>
                <p class="text-stone-500 dark:text-stone-400 text-sm">Kelola semua aset fisik dan akun keuangan</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button onclick="openModal('modal-add-account')"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-wallet"></i> Tambah Akun
                </button>
                <button onclick="resetAssetForm(); openModal('modal-add-asset')"
                    class="flex items-center gap-2 px-4 py-2 bg-stone-800 dark:bg-stone-700 hover:bg-stone-900 text-white rounded-xl text-sm font-medium transition-colors">
                    <i class="fa-solid fa-plus"></i> Aset Fisik
                </button>
            </div>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                class="col-span-2 lg:col-span-1 bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-5 text-white shadow-lg">
                <p class="text-amber-100 text-xs mb-1">Cash &amp; Wallet</p>
                <h3 class="text-2xl font-bold">Rp {{ number_format($totalCash + $totalWallet, 0, ',', '.') }}</h3>
                <p class="text-amber-200 text-[11px] mt-1">Cash + E-Wallet</p>
            </div>
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-5 text-white shadow-lg">
                <p class="text-blue-100 text-xs mb-1">Rekening Bank</p>
                <h3 class="text-2xl font-bold">Rp {{ number_format($totalBank, 0, ',', '.') }}</h3>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <p class="text-stone-500 dark:text-stone-400 text-xs mb-1">Aset Fisik (Nilai)</p>
                <h3 class="text-2xl font-bold {{ $totalAppreciation >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Rp
                    {{ number_format($totalCurrentValue, 0, ',', '.') }}</h3>
                <p class="text-[11px] mt-1 {{ $totalAppreciation >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                    {{ $totalAppreciation >= 0 ? '+' : '' }}Rp {{ number_format($totalAppreciation, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200 dark:border-stone-800 shadow-sm">
                <p class="text-stone-500 dark:text-stone-400 text-xs mb-1">Total Semua Akun</p>
                <h3 class="text-2xl font-bold text-stone-800 dark:text-white">Rp
                    {{ number_format($totalAllAccounts, 0, ',', '.') }}</h3>
                <p class="text-[11px] text-stone-400 mt-1">{{ $accounts->count() }} akun aktif</p>
            </div>
        </div>

        {{-- ALERT GARANSI --}}
        @if ($warrantyAlerts->count() || $insuranceAlerts->count())
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-4">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
                    <div>
                        <p class="font-semibold text-amber-800 dark:text-amber-300 text-sm">Perhatian!</p>
                        @foreach ($warrantyAlerts as $a)
                            <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">Garansi
                                <strong>{{ $a->name }}</strong> akan habis
                                {{ $a->warranty_expiry->isoFormat('D MMM YYYY') }}
                            </p>
                        @endforeach
                        @foreach ($insuranceAlerts as $a)
                            <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">Asuransi
                                <strong>{{ $a->name }}</strong> akan habis
                                {{ $a->insurance_expiry->isoFormat('D MMM YYYY') }}
                            </p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- MAIN GRID --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">

                {{-- AKUN KEUANGAN --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-stone-800 dark:text-white">Akun Keuangan</h3>
                        <span class="text-xs text-stone-400">Klik akun untuk update saldo</span>
                    </div>
                    @if ($accounts->isEmpty())
                        <p class="text-center py-6 text-stone-400 text-sm">Belum ada akun.</p>
                    @else
                        @php
                            $typeOrder = ['cash', 'bank', 'e-wallet', 'investment', 'receivable'];
                            $typeLabels = [
                                'cash' => 'Cash / Tunai',
                                'bank' => 'Rekening Bank',
                                'e-wallet' => 'E-Wallet',
                                'investment' => 'Investasi',
                                'receivable' => 'Piutang',
                            ];
                            $chartColors = [
                                'cash' => '#f59e0b',
                                'bank' => '#3b82f6',
                                'e-wallet' => '#8b5cf6',
                                'investment' => '#10b981',
                                'receivable' => '#f97316',
                            ];
                            $grouped = $accounts->groupBy('type');
                        @endphp
                        <div class="space-y-4">
                            @foreach ($typeOrder as $type)
                                @if ($grouped->has($type))
                                    <div>
                                        <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2">
                                            {{ $typeLabels[$type] }}</p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            @foreach ($grouped[$type] as $acc)
                                                <div class="flex items-center justify-between p-3 border border-stone-200 dark:border-stone-700 rounded-xl hover:shadow-sm transition-shadow cursor-pointer group"
                                                    onclick="openEditAccount({{ json_encode(['id' => $acc->id, 'name' => $acc->name, 'type' => $acc->type, 'balance' => (float) $acc->balance, 'account_number' => $acc->account_number, 'color' => $acc->color, 'notes' => $acc->notes, 'is_active' => $acc->is_active]) }})">
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
                                                        <p class="font-bold text-stone-800 dark:text-white text-sm">
                                                            {{ $acc->getFormattedBalance() }}</p>
                                                        <button
                                                            onclick="event.stopPropagation(); deleteAccount({{ $acc->id }})"
                                                            class="opacity-0 group-hover:opacity-100 text-xs text-stone-300 hover:text-rose-500 transition-all mt-0.5"><i
                                                                class="fa-solid fa-trash-can"></i></button>
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

                {{-- ASET FISIK --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-stone-800 dark:text-white">Aset Fisik</h3>
                        <button onclick="resetAssetForm(); openModal('modal-add-asset')"
                            class="text-xs bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 px-3 py-1 rounded-lg hover:bg-stone-200 transition-colors"><i
                                class="fa-solid fa-plus mr-1"></i>Tambah</button>
                    </div>
                    @if ($assets->isEmpty())
                        <p class="text-center py-6 text-stone-400 text-sm">Belum ada aset fisik tercatat.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($assets as $asset)
                                <div
                                    class="flex items-center justify-between p-3 border border-stone-200 dark:border-stone-700 rounded-xl hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors group">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 flex-shrink-0">
                                            <i
                                                class="fa-solid {{ match ($asset->category) {'electronics' => 'fa-laptop','vehicle' => 'fa-motorcycle','education' => 'fa-book','furniture' => 'fa-couch','property' => 'fa-house','jewelry' => 'fa-gem',default => 'fa-box'} }} text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-stone-800 dark:text-white text-sm">
                                                {{ $asset->name }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span
                                                    class="text-xs px-1.5 py-0.5 rounded bg-stone-100 dark:bg-stone-700 text-stone-500">{{ ucfirst($asset->category) }}</span>
                                                <span
                                                    class="text-[10px] font-medium px-1.5 py-0.5 rounded {{ match ($asset->condition) {'Excellent' => 'bg-emerald-100 text-emerald-700','Good' => 'bg-blue-100 text-blue-700','Fair' => 'bg-amber-100 text-amber-700','Poor' => 'bg-red-100 text-red-700',default => 'bg-stone-100 text-stone-500'} }}">{{ $asset->condition }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="text-right">
                                            <p class="font-bold text-stone-800 dark:text-white text-sm">
                                                {{ $asset->getFormattedCurrentValue() }}</p>
                                            <p class="text-[11px] {{ $asset->getStatusColor() }}">
                                                {{ $asset->getAppreciation() >= 0 ? '+' : '' }}Rp
                                                {{ number_format($asset->getAppreciation(), 0, ',', '.') }}</p>
                                        </div>
                                        <div class="opacity-0 group-hover:opacity-100 flex gap-1 transition-all">
                                            <button onclick="openEditAsset({{ $asset->id }})"
                                                class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center hover:bg-blue-200 transition-colors"><i
                                                    class="fa-solid fa-pen text-xs"></i></button>
                                            <button onclick="deleteAsset({{ $asset->id }})"
                                                class="w-7 h-7 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-500 flex items-center justify-center hover:bg-rose-200 transition-colors"><i
                                                    class="fa-solid fa-trash-can text-xs"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                {{-- CHART --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-4">Distribusi Akun</h3>
                    <div style="position:relative;width:100%;height:200px;"><canvas id="accountChart"></canvas></div>
                    @if ($accounts->count())
                        <div class="mt-3 space-y-1.5">
                            @foreach ($accounts->groupBy('type') as $type => $accs)
                                <div class="flex justify-between items-center text-xs">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2.5 h-2.5 rounded-full"
                                            style="background-color:{{ $chartColors[$type] ?? '#6b7280' }}"></div>
                                        <span
                                            class="text-stone-500 dark:text-stone-400">{{ $typeLabels[$type] ?? ucfirst($type) }}</span>
                                    </div>
                                    <span class="font-medium text-stone-700 dark:text-stone-300">Rp
                                        {{ number_format($accs->sum('balance'), 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- PER KATEGORI --}}
                @if ($assetsByCategory->count())
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                        <h3 class="font-bold text-stone-800 dark:text-white mb-4">Aset Fisik per Kategori</h3>
                        <div class="space-y-3">
                            @foreach ($assetsByCategory as $cat => $catAssets)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span
                                            class="font-medium text-stone-700 dark:text-stone-300">{{ ucfirst($cat) }}</span>
                                        <span class="text-stone-800 dark:text-white font-bold">Rp
                                            {{ number_format($catAssets->sum('current_value'), 0, ',', '.') }}</span>
                                    </div>
                                    <p class="text-[11px] text-stone-400">{{ $catAssets->count() }} item · Beli: Rp
                                        {{ number_format($catAssets->sum('purchase_price'), 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- QUICK NAV --}}
                <div
                    class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm">
                    <h3 class="font-bold text-stone-800 dark:text-white mb-4">Navigasi</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('dashboard.finance') }}"
                            class="p-3 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 rounded-xl transition-colors text-center block"><i
                                class="fa-solid fa-wallet text-amber-600 block mb-1 text-lg"></i>
                            <p class="text-xs font-medium text-amber-800 dark:text-amber-300">Finance</p>
                        </a>
                        <a href="{{ route('dashboard.investments') }}"
                            class="p-3 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 rounded-xl transition-colors text-center block"><i
                                class="fa-solid fa-chart-line text-emerald-600 block mb-1 text-lg"></i>
                            <p class="text-xs font-medium text-emerald-800 dark:text-emerald-300">Investasi</p>
                        </a>
                        <a href="{{ route('dashboard.debts') }}"
                            class="p-3 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 rounded-xl transition-colors text-center block"><i
                                class="fa-solid fa-hand-holding-usd text-rose-600 block mb-1 text-lg"></i>
                            <p class="text-xs font-medium text-rose-800 dark:text-rose-300">Hutang</p>
                        </a>
                        <button onclick="resetAssetForm(); openModal('modal-add-asset')"
                            class="p-3 bg-stone-50 dark:bg-stone-800 hover:bg-stone-100 rounded-xl transition-colors text-center"><i
                                class="fa-solid fa-plus text-stone-600 block mb-1 text-lg"></i>
                            <p class="text-xs font-medium text-stone-700 dark:text-stone-300">Aset Baru</p>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: TAMBAH AKUN --}}
    <div id="modal-add-account"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white">Tambah Akun Keuangan</h3>
                <button onclick="closeModal('modal-add-account')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="form-add-account" class="p-6 space-y-4">
                @csrf
                <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nama Akun
                        *</label><input type="text" name="name" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Misal: BCA, GoPay, Dompet"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tipe
                            *</label><select name="type" required onchange="toggleAccountNum(this.value)"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="e-wallet">E-Wallet</option>
                            <option value="investment">Investasi</option>
                            <option value="receivable">Piutang</option>
                        </select></div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Saldo (Rp)
                            *</label><input type="number" name="balance" required min="0"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0"></div>
                </div>
                <div id="acct-num-field" class="hidden"><label
                        class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">No.
                        Rekening</label><input type="text" name="account_number"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Opsional"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label
                            class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Warna</label><input
                            type="color" name="color" value="#3b82f6"
                            class="w-full h-10 border border-stone-300 dark:border-stone-700 rounded-xl px-2 cursor-pointer">
                    </div>
                    <div><label
                            class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Catatan</label><input
                            type="text" name="notes"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="Opsional"></div>
                </div>
            </form>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-add-account')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitAccount()"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium">Simpan</button>
            </div>
        </div>
    </div>

    {{-- MODAL: EDIT AKUN (lengkap) --}}
    <div id="modal-edit-account"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800">
                <div>
                    <h3 class="text-lg font-bold text-stone-900 dark:text-white" id="edit-account-title">Edit Akun</h3>
                    <p class="text-xs text-stone-400 mt-0.5" id="edit-account-type-label"></p>
                </div>
                <button onclick="closeModal('modal-edit-account')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="form-edit-account" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="edit-account-id">
                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nama Akun *</label>
                    <input type="text" id="edit-account-name" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Nama akun">
                </div>
                {{-- Saldo --}}
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Saldo (Rp) *</label>
                    <input type="number" id="edit-account-balance" min="0" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white text-lg font-medium"
                        placeholder="0">
                </div>
                {{-- No. Rekening (hanya untuk bank) --}}
                <div id="edit-account-number-wrap" class="hidden">
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nomor Rekening</label>
                    <input type="text" id="edit-account-number"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Nomor rekening (opsional)">
                </div>
                {{-- Warna & Status --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Warna</label>
                        <div class="flex items-center gap-2">
                            <input type="color" id="edit-account-color"
                                class="w-12 h-10 border border-stone-300 dark:border-stone-700 rounded-xl px-1 cursor-pointer flex-shrink-0">
                            <span id="edit-account-color-preview"
                                class="flex-1 h-10 rounded-xl border border-stone-200 dark:border-stone-700 flex items-center px-3 text-xs text-stone-400">Preview</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Status</label>
                        <select id="edit-account-active"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                {{-- Catatan --}}
                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Catatan</label>
                    <textarea id="edit-account-notes" rows="2"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                        placeholder="Catatan opsional"></textarea>
                </div>
            </form>
            <div class="flex gap-3 px-6 pb-6">
                <button onclick="closeModal('modal-edit-account')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800">Batal</button>
                <button onclick="submitEditAccount()"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>Simpan Perubahan
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL: TAMBAH / EDIT ASET FISIK --}}
    <div id="modal-add-asset"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 flex">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-lg shadow-2xl max-h-[90vh] overflow-y-auto">
            <div
                class="flex justify-between items-center p-6 border-b border-stone-200 dark:border-stone-800 sticky top-0 bg-white dark:bg-stone-900 z-10">
                <h3 class="text-lg font-bold text-stone-900 dark:text-white" id="asset-modal-title">Tambah Aset Fisik</h3>
                <button onclick="closeModal('modal-add-asset')"
                    class="text-stone-400 hover:text-stone-700 dark:hover:text-white"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="form-add-asset" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="edit-asset-id">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"><label
                            class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nama Aset
                            *</label><input type="text" name="name" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="Misal: MacBook Pro, Honda Beat"></div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Kategori
                            *</label><select name="category" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="electronics">Elektronik</option>
                            <option value="vehicle">Kendaraan</option>
                            <option value="education">Pendidikan</option>
                            <option value="furniture">Furnitur</option>
                            <option value="property">Properti</option>
                            <option value="jewelry">Perhiasan</option>
                            <option value="other">Lainnya</option>
                        </select></div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Kondisi
                            *</label><select name="condition" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="Excellent">Excellent</option>
                            <option value="Good" selected>Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Poor">Poor</option>
                        </select></div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Harga Beli (Rp)
                            *</label><input type="number" name="purchase_price" required min="0"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0"></div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Nilai Saat Ini
                            (Rp) *</label><input type="number" name="current_value" required min="0"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="0"></div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tanggal Beli
                            *</label><input type="date" name="purchase_date" required
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                    <div><label
                            class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Lokasi</label><input
                            type="text" name="location"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="Misal: Kamar"></div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">No.
                            Seri</label><input type="text" name="serial_number"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="Opsional"></div>
                    <div><label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Garansi
                            Habis</label><input type="date" name="warranty_expiry"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                    <div><label
                            class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Diasuransikan?</label><select
                            name="is_insured" onchange="toggleInsurance(this.value)"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                            <option value="0">Tidak</option>
                            <option value="1">Ya</option>
                        </select></div>
                    <div id="insurance-date-field" class="col-span-2 hidden"><label
                            class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Asuransi
                            Habis</label><input type="date" name="insurance_expiry"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white">
                    </div>
                    <div class="col-span-2"><label
                            class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Catatan</label>
                        <textarea name="notes" rows="2"
                            class="w-full border border-stone-300 dark:border-stone-700 rounded-xl px-4 py-2 focus:ring-2 focus:ring-stone-500 focus:outline-none dark:bg-stone-800 dark:text-white"
                            placeholder="Opsional"></textarea>
                    </div>
                </div>
            </form>
            <div
                class="flex gap-3 px-6 pb-6 sticky bottom-0 bg-white dark:bg-stone-900 pt-2 border-t border-stone-100 dark:border-stone-800">
                <button onclick="closeModal('modal-add-asset')"
                    class="flex-1 py-2.5 border border-stone-300 dark:border-stone-700 rounded-xl text-stone-600 dark:text-stone-300">Batal</button>
                <button onclick="submitAsset()" id="submit-asset-btn"
                    class="flex-1 py-2.5 bg-stone-800 hover:bg-stone-900 text-white rounded-xl font-medium">Simpan
                    Aset</button>
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

        async function api(method, url, data = null) {
            // Ambil token terbaru setiap kali fungsi ini dipanggil
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const upper = method.toUpperCase();

            const opts = {
                method: upper,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token, // Kirim token di header
                    'Accept': 'application/json',
                },
            };

            if (!['GET', 'HEAD'].includes(upper)) {
                opts.body = JSON.stringify(data ?? {});
            }

            try {
                const res = await fetch(url, opts);

                if (res.status === 419) {
                    // Jika token kadaluarsa, paksa refresh halaman
                    alert('Sesi keamanan habis (419). Halaman akan dimuat ulang.');
                    location.reload();
                    return {
                        success: false
                    };
                }

                if (!res.ok) {
                    const errorData = await res.json();
                    return {
                        success: false,
                        message: errorData.message || 'Server error'
                    };
                }

                return await res.json();
            } catch (err) {
                console.error("Fetch error:", err);
                return {
                    success: false,
                    message: "Koneksi terputus"
                };
            }
        }

        function formToJson(id) {
            const fd = new FormData(document.getElementById(id));
            const obj = {};
            fd.forEach((v, k) => {
                if (v !== '') obj[k] = v
            });
            return obj
        }

        // ── Toggle no. rekening di form TAMBAH akun ───────────────────────────
        function toggleAccountNum(type) {
            document.getElementById('acct-num-field').classList.toggle('hidden', type !== 'bank')
        }

        // ── Toggle field asuransi di form aset ───────────────────────────────
        function toggleInsurance(val) {
            document.getElementById('insurance-date-field').classList.toggle('hidden', val !== '1')
        }

        // ── TAMBAH AKUN BARU ──────────────────────────────────────────────────
        async function submitAccount() {
            const data = formToJson('form-add-account');
            const res = await api('POST', '{{ route('assets.accounts.store') }}', data);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800)
            } else toast(res.message || 'Gagal menyimpan akun', false)
        }

        // ── EDIT AKUN (buka modal lengkap) ────────────────────────────────────
        // Menerima object akun dari Blade via onclick
        function openEditAccount(acc) {
            // Isi semua field
            document.getElementById('edit-account-id').value = acc.id;
            document.getElementById('edit-account-name').value = acc.name ?? '';
            document.getElementById('edit-account-balance').value = acc.balance ?? 0;
            document.getElementById('edit-account-number').value = acc.account_number ?? '';
            document.getElementById('edit-account-color').value = acc.color ?? '#6b7280';
            document.getElementById('edit-account-notes').value = acc.notes ?? '';
            document.getElementById('edit-account-active').value = acc.is_active ? '1' : '0';

            // Preview warna
            updateColorPreview(acc.color ?? '#6b7280', acc.name);

            // Label tipe akun
            const typeMap = {
                cash: 'Cash / Tunai',
                bank: 'Rekening Bank',
                'e-wallet': 'E-Wallet',
                investment: 'Investasi',
                receivable: 'Piutang'
            };
            document.getElementById('edit-account-type-label').textContent = typeMap[acc.type] ?? acc.type;
            document.getElementById('edit-account-title').textContent = 'Edit: ' + (acc.name ?? 'Akun');

            // Tampilkan no. rekening hanya untuk bank
            document.getElementById('edit-account-number-wrap').classList.toggle('hidden', acc.type !== 'bank');

            openModal('modal-edit-account');
        }

        // Live preview warna
        function updateColorPreview(color, name) {
            const preview = document.getElementById('edit-account-color-preview');
            if (!preview) return;
            preview.style.backgroundColor = color;
            preview.style.color = '#fff';
            preview.textContent = name ?? 'Preview';
        }

        // Sinkron color picker → preview
        document.addEventListener('DOMContentLoaded', () => {
            const colorInput = document.getElementById('edit-account-color');
            const nameInput = document.getElementById('edit-account-name');
            if (colorInput) {
                colorInput.addEventListener('input', () => {
                    updateColorPreview(colorInput.value, nameInput?.value);
                });
            }
            if (nameInput) {
                nameInput.addEventListener('input', () => {
                    if (colorInput) updateColorPreview(colorInput.value, nameInput.value);
                });
            }
        });

        // ── SUBMIT EDIT AKUN ──────────────────────────────────────────────────
        async function submitEditAccount() {
            const id = document.getElementById('edit-account-id').value;
            if (!id) {
                toast('ID akun tidak ditemukan', false);
                return;
            }

            const name = document.getElementById('edit-account-name').value;
            const balance = parseFloat(document.getElementById('edit-account-balance').value) || 0;
            const color = document.getElementById('edit-account-color').value;
            const notes = document.getElementById('edit-account-notes').value || null;
            const active = document.getElementById('edit-account-active').value === '1';
            const accNum = document.getElementById('edit-account-number').value || null;

            if (!name?.trim()) {
                toast('Nama akun tidak boleh kosong', false);
                return;
            }

            // Kirim update nama/warna/catatan dan update saldo secara paralel
            const [resInfo, resBalance] = await Promise.all([
                api('PUT', `/assets/accounts/${id}`, {
                    name,
                    account_number: accNum,
                    color,
                    notes,
                    is_active: active,
                }),
                api('PATCH', `/assets/accounts/${id}/balance`, {
                    balance
                }),
            ]);

            if (resInfo.success && resBalance.success) {
                toast('Akun berhasil diupdate!');
                setTimeout(() => location.reload(), 800);
            } else {
                const errMsg = (!resInfo.success ? resInfo.message : null) ||
                    (!resBalance.success ? resBalance.message : null) ||
                    'Gagal mengupdate akun';
                toast(errMsg, false);
            }
        }

        // ── HAPUS AKUN ────────────────────────────────────────────────────────
        async function deleteAccount(id) {
            if (!confirm('Hapus akun ini? Akun yang sudah memiliki transaksi tidak dapat dihapus.')) return;
            const res = await api('DELETE', `/assets/accounts/${id}`);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800)
            } else toast(res.message || 'Akun tidak dapat dihapus', false)
        }

        let editingAssetId = null;

        function resetAssetForm() {
            editingAssetId = null;
            document.getElementById('edit-asset-id').value = '';
            document.getElementById('asset-modal-title').textContent = 'Tambah Aset Fisik';
            document.getElementById('submit-asset-btn').textContent = 'Simpan Aset';
            document.getElementById('form-add-asset').reset();
            document.getElementById('insurance-date-field').classList.add('hidden');
        }
        async function openEditAsset(id) {
            const res = await api('GET', `/assets/${id}`);
            if (!res.success) {
                toast('Gagal memuat data', false);
                return
            }
            const a = res.asset;
            editingAssetId = id;
            document.getElementById('edit-asset-id').value = id;
            document.getElementById('asset-modal-title').textContent = 'Edit: ' + a.name;
            document.getElementById('submit-asset-btn').textContent = 'Update Aset';
            const form = document.getElementById('form-add-asset');
            const set = (n, v) => {
                const el = form.querySelector(`[name="${n}"]`);
                if (el && v !== null && v !== undefined) el.value = v ?? ''
            };
            set('name', a.name);
            set('category', a.category);
            set('condition', a.condition);
            set('purchase_price', a.purchase_price);
            set('current_value', a.current_value);
            set('purchase_date', a.purchase_date);
            set('location', a.location);
            set('serial_number', a.serial_number);
            set('warranty_expiry', a.warranty_expiry);
            set('is_insured', a.is_insured ? '1' : '0');
            set('insurance_expiry', a.insurance_expiry);
            set('notes', a.notes);
            toggleInsurance(a.is_insured ? '1' : '0');
            openModal('modal-add-asset')
        }
        async function submitAsset() {
            const data = formToJson('form-add-asset');
            const res = editingAssetId ? await api('PUT', `/assets/${editingAssetId}`, data) : await api('POST',
                '{{ route('assets.store') }}', data);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800)
            } else toast(res.message, false)
        }
        async function deleteAsset(id) {
            if (!confirm('Hapus aset ini?')) return;
            const res = await api('DELETE', `/assets/${id}`);
            if (res.success) {
                toast(res.message);
                setTimeout(() => location.reload(), 800)
            } else toast(res.message, false)
        }

        (function initChart() {
            const ctx = document.getElementById('accountChart');
            if (!ctx) return;
            const labels = [],
                values = [],
                colors = [];
            const cmap = {
                'cash': '#f59e0b',
                'bank': '#3b82f6',
                'e-wallet': '#8b5cf6',
                'investment': '#10b981',
                'receivable': '#f97316'
            };
            const tlbls = {
                'cash': 'Cash',
                'bank': 'Bank',
                'e-wallet': 'E-Wallet',
                'investment': 'Investasi',
                'receivable': 'Piutang'
            };
            @foreach ($accounts->groupBy('type') as $type => $accs)
                labels.push('{{ $typeLabels[$type] ?? ucfirst($type) }}');
                values.push({{ $accs->sum('balance') }});
                colors.push(cmap['{{ $type }}'] || '#6b7280');
            @endforeach
            if (!values.length || values.every(v => v === 0)) {
                if (ctx.parentElement) ctx.parentElement.innerHTML =
                    '<p class="text-center text-stone-400 text-sm py-8">Belum ada saldo</p>';
                return
            }
            new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
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
            })
        })();
    </script>
@endpush
