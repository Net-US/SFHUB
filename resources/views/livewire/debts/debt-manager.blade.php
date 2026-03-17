<div>
    <!-- Header & Summary -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Manajemen Utang</h2>
                <p class="text-gray-600">Kelola utang dan piutang Anda</p>
            </div>
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Utang
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Utang</p>
                        <p class="text-xl font-bold text-red-600">Rp {{ number_format($totalDebts, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-2xl">📉</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Piutang</p>
                        <p class="text-xl font-bold text-green-600">Rp {{ number_format($totalLendings, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-2xl">📈</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Dibayar</p>
                        <p class="text-xl font-bold text-blue-600">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
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
                    placeholder="Cari utang..." 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            <div>
                <select wire:model.live="filterType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Tipe</option>
                    <option value="borrower">Utang</option>
                    <option value="lender">Piutang</option>
                </select>
            </div>
            <div>
                <select wire:model.live="filterStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="completed">Lunas</option>
                </select>
            </div>
            <div>
                <button wire:click="resetFilters" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Debts List -->
    <div class="space-y-4">
        @foreach($debts as $debt)
            <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="text-2xl">{{ $this->getDebtTypeIcon($debt->debt_type) }}</div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $debt->creditor_name }}</h3>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $this->getDebtTypeColor($debt->debt_type) }}-100 text-{{ $this->getDebtTypeColor($debt->debt_type) }}-800">
                                    {{ $debt->debt_type === 'borrower' ? 'Utang' : 'Piutang' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="openPaymentModal({{ $debt->id }})" class="text-green-600 hover:text-green-800" title="Tambah Pembayaran">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>
                            <button wire:click="edit({{ $debt->id }})" class="text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button wire:click="deleteConfirm({{ $debt->id }})" class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <!-- Amount and Progress -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-gray-600">Total:</span>
                                    <span class="font-semibold {{ $debt->debt_type === 'borrower' ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $debt->debt_type === 'borrower' ? '-' : '+' }} Rp {{ number_format($debt->total_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-gray-600">Sisa:</span>
                                    <span class="font-semibold {{ $debt->debt_type === 'borrower' ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $debt->debt_type === 'borrower' ? '-' : '+' }} Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                                @if($debt->interest_rate > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Bunga:</span>
                                        <span class="text-sm font-medium">{{ $debt->interest_rate }}%</span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <!-- Progress Bar -->
                                <div class="mb-2">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600">Progress Pembayaran</span>
                                        <span class="text-gray-800 font-medium">{{ number_format($this->getPaymentProgress($debt), 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $this->getPaymentProgress($debt) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($debt->description)
                            <div class="text-sm text-gray-600">
                                <span class="font-medium">Deskripsi:</span> {{ Str::limit($debt->description, 100) }}
                            </div>
                        @endif

                        <!-- Dates -->
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Mulai: {{ \Carbon\Carbon::parse($debt->start_date)->format('d M Y') }}</span>
                            </div>
                            @if($debt->due_date)
                                @php
                                    $dueInfo = $this->getDaysUntilDue($debt->due_date);
                                @endphp
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-{{ $dueInfo['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-{{ $dueInfo['color'] }}-600 font-medium">{{ $dueInfo['text'] }}</span>
                                    <span class="text-gray-500">{{ \Carbon\Carbon::parse($debt->due_date)->format('d M Y') }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Notes -->
                        @if($debt->notes)
                            <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                <span class="font-medium">Catatan:</span> {{ Str::limit($debt->notes, 80) }}
                            </div>
                        @endif

                        <!-- Payment History -->
                        @if($debt->payments->count() > 0)
                            <div class="border-t pt-3">
                                <p class="text-sm font-medium text-gray-700 mb-2">Riwayat Pembayaran:</p>
                                <div class="space-y-1">
                                    @foreach($debt->payments->take(3) as $payment)
                                        <div class="flex justify-between items-center text-xs text-gray-600">
                                            <span>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</span>
                                            <span class="font-medium">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                    @if($debt->payments->count() > 3)
                                        <p class="text-xs text-gray-500">... dan {{ $debt->payments->count() - 3 }} pembayaran lainnya</p>
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
    @if($debts->hasPages())
        <div class="mt-6">
            {{ $debts->links() }}
        </div>
    @endif

    <!-- Empty State -->
    @if($debts->count() === 0)
        <div class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">💰</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data utang</h3>
            <p class="text-gray-600 mb-4">Mulai mencatat utang dan piutang Anda</p>
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Tambah Utang Pertama
            </button>
        </div>
    @endif

    <!-- Debt Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editingId ? 'Edit Utang' : 'Tambah Utang Baru' }}
                    </h3>
                    <button wire:click="cancel" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="{{ $editingId ? 'update' : 'store' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kreditur/Debitur</label>
                            <input wire:model="creditor_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('creditor_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                            <select wire:model="debt_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="borrower">Utang (Saya berutang)</option>
                                <option value="lender">Piutang (Orang berutang ke saya)</option>
                            </select>
                            @error('debt_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Total</label>
                            <input wire:model="total_amount" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('total_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Suku Bunga (%)</label>
                            <input wire:model="interest_rate" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('interest_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                            <input wire:model="start_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jatuh Tempo</label>
                            <input wire:model="due_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea wire:model="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Deskripsi utang..."></textarea>
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

    <!-- Payment Modal -->
    @if($showPaymentModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah Pembayaran</h3>
                    <button wire:click="cancel" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="addPayment">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pembayaran</label>
                            <input wire:model="payment_amount" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('payment_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pembayaran</label>
                            <input wire:model="payment_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('payment_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pembayaran</label>
                            <textarea wire:model="payment_notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Opsional..."></textarea>
                            @error('payment_notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg">
                            Simpan Pembayaran
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
