<div>
    <!-- Header & Summary -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Manajemen Investasi</h2>
                <p class="text-gray-600">Kelola portofolio investasi Anda</p>
            </div>
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Investasi
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Investasi</p>
                        <p class="text-xl font-bold text-blue-600">Rp {{ number_format($totalInvested, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-2xl">💼</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Nilai Saat Ini</p>
                        <p class="text-xl font-bold text-green-600">Rp {{ number_format($totalCurrentValue, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-2xl">📈</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Profit/Loss</p>
                        <p class="text-xl font-bold {{ $totalProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $totalProfit >= 0 ? '+' : '' }}Rp {{ number_format($totalProfit, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="text-2xl">{{ $totalProfit >= 0 ? '📊' : '📉' }}</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Dividen</p>
                        <p class="text-xl font-bold text-purple-600">Rp {{ number_format($totalDividends, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-2xl">💰</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <input 
                    wire:model.live="search" 
                    type="text" 
                    placeholder="Cari investasi..." 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            <div>
                <select wire:model.live="filterType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Tipe</option>
                    @foreach($this->getCommonInvestmentTypes() as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="filterStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="sold">Terjual</option>
                </select>
            </div>
            <div>
                <button wire:click="resetFilters" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Investments List -->
    <div class="space-y-4">
        @foreach($investments as $investment)
            <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="text-2xl">{{ $this->getInvestmentTypeIcon($investment->type) }}</div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $investment->name }}</h3>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $investment->type }}
                                </span>
                                @if($investment->broker)
                                    <span class="text-sm text-gray-500 ml-2">{{ $investment->broker }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="openTransactionModal({{ $investment->id }})" class="text-green-600 hover:text-green-800" title="Tambah Transaksi">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>
                            <button wire:click="edit({{ $investment->id }})" class="text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button wire:click="deleteConfirm({{ $investment->id }})" class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <!-- Value and Performance -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-gray-600">Investasi Awal:</span>
                                    <span class="font-semibold text-gray-900">Rp {{ number_format($investment->initial_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Nilai Saat Ini:</span>
                                    <span class="font-semibold text-green-600">Rp {{ number_format($investment->current_value, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-gray-600">Profit/Loss:</span>
                                    <span class="font-semibold text-{{ $this->getProfitLossColor($investment) }}-600">
                                        {{ $this->getProfitLoss($investment) >= 0 ? '+' : '' }}Rp {{ number_format($this->getProfitLoss($investment), 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Persentase:</span>
                                    <span class="text-sm font-medium text-{{ $this->getProfitLossColor($investment) }}-600">
                                        {{ $this->getProfitLossPercentage($investment) >= 0 ? '+' : '' }}{{ number_format($this->getProfitLossPercentage($investment), 2) }}%
                                    </span>
                                </div>
                            </div>
                            <div>
                                <!-- Performance Indicator -->
                                <div class="text-center">
                                    <div class="text-3xl mb-1">
                                        {{ $this->getProfitLoss($investment) >= 0 ? '📈' : '📉' }}
                                    </div>
                                    <div class="text-sm font-medium text-{{ $this->getProfitLossColor($investment) }}-600">
                                        {{ $this->getProfitLossPercentage($investment) >= 0 ? 'Profit' : 'Loss' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($investment->description)
                            <div class="text-sm text-gray-600">
                                <span class="font-medium">Deskripsi:</span> {{ Str::limit($investment->description, 100) }}
                            </div>
                        @endif

                        <!-- Dates and Details -->
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Mulai: {{ \Carbon\Carbon::parse($investment->purchase_date)->format('d M Y') }}</span>
                            </div>
                            @if($investment->account_number)
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    <span>{{ $investment->account_number }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Notes -->
                        @if($investment->notes)
                            <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                <span class="font-medium">Catatan:</span> {{ Str::limit($investment->notes, 80) }}
                            </div>
                        @endif

                        <!-- Recent Transactions -->
                        @if($investment->transactions->count() > 0)
                            <div class="border-t pt-3">
                                <p class="text-sm font-medium text-gray-700 mb-2">Transaksi Terakhir:</p>
                                <div class="space-y-1">
                                    @foreach($investment->transactions->take(3) as $transaction)
                                        <div class="flex justify-between items-center text-xs text-gray-600">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium">{{ ucfirst($transaction->type) }}</span>
                                                <span>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}</span>
                                            </div>
                                            <span class="font-medium {{ $transaction->type === 'buy' ? 'text-red-600' : ($transaction->type === 'sell' ? 'text-green-600' : 'text-blue-600') }}">
                                                {{ $transaction->type === 'buy' ? '-' : '+' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endforeach
                                    @if($investment->transactions->count() > 3)
                                        <p class="text-xs text-gray-500">... dan {{ $investment->transactions->count() - 3 }} transaksi lainnya</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($investments->hasPages())
        <div class="mt-6">
            {{ $investments->links() }}
        </div>
    @endif

    <!-- Empty State -->
    @if($investments->count() === 0)
        <div class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">💼</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada investasi</h3>
            <p class="text-gray-600 mb-4">Mulai membangun portofolio investasi Anda</p>
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Tambah Investasi Pertama
            </button>
        </div>
    @endif

    <!-- Investment Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editingId ? 'Edit Investasi' : 'Tambah Investasi Baru' }}
                    </h3>
                    <button wire:click="cancel" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="{{ $editingId ? 'update' : 'store' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Investasi</label>
                            <input wire:model="name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Investasi</label>
                            <select wire:model="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                @foreach($this->getCommonInvestmentTypes() as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Broker</label>
                            <input wire:model="broker" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Opsional">
                            @error('broker') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Investasi Awal</label>
                            <input wire:model="initial_amount" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('initial_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Saat Ini</label>
                            <input wire:model="current_value" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Opsional, default = investasi awal">
                            @error('current_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pembelian</label>
                            <input wire:model="purchase_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('purchase_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Rekening</label>
                            <input wire:model="account_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Opsional">
                            @error('account_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea wire:model="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Deskripsi investasi..."></textarea>
                            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea wire:model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Catatan tambahan..."></textarea>
                            @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg">
                            {{ $editingId ? 'Update' : 'Simpan' }}
                        </button>
                        <button type="button" wire:click="cancel" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Transaction Modal -->
    @if($showTransactionModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah Transaksi</h3>
                    <button wire:click="cancel" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="addTransaction">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Transaksi</label>
                            <select wire:model="transaction_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="buy">Beli</option>
                                <option value="sell">Jual</option>
                                <option value="dividend">Dividen</option>
                                <option value="fee">Biaya</option>
                            </select>
                            @error('transaction_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                            <input wire:model="transaction_amount" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('transaction_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Transaksi</label>
                            <input wire:model="transaction_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('transaction_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kuantitas</label>
                                <input wire:model="quantity" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Opsional">
                                @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga per Unit</label>
                                <input wire:model="price_per_unit" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Opsional">
                                @error('price_per_unit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Transaksi</label>
                            <textarea wire:model="transaction_notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Opsional..."></textarea>
                            @error('transaction_notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg">
                            Simpan Transaksi
                        </button>
                        <button type="button" wire:click="cancel" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('flash-message', (event) => {
                const { type, message } = event;
                
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ${
                    type === 'success' ? 'bg-green-500 text-white' : (type === 'error' ? 'bg-red-500 text-white' : 'bg-blue-500 text-white')
                }`;
                toast.textContent = message;
                
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            });

            Livewire.on('confirm-delete', (event) => {
                const { title, message, id } = event;
                
                if (confirm(`${title}\n${message}`)) {
                    Livewire.dispatch('deleteConfirmed', { id });
                }
            });
        });
    </script>
</div>
