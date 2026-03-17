<div>
    <!-- Header & Summary -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Manajemen Aset</h2>
                <p class="text-gray-600">Kelola dan pantau kondisi aset Anda</p>
            </div>
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Aset
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Aset</p>
                        <p class="text-xl font-bold text-blue-600">{{ $totalAssets }}</p>
                    </div>
                    <div class="text-2xl">📦</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Nilai Saat Ini</p>
                        <p class="text-xl font-bold text-green-600">Rp {{ number_format($totalValue, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-2xl">💰</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Pembelian</p>
                        <p class="text-xl font-bold text-gray-600">Rp {{ number_format($totalPurchaseValue, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-2xl">🛒</div>
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
                    placeholder="Cari aset..." 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            <div>
                <select wire:model.live="filterCategory" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Kategori</option>
                    @foreach($this->getCommonCategories() as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="filterCondition" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Kondisi</option>
                    <option value="Excellent">Excellent</option>
                    <option value="Good">Good</option>
                    <option value="Fair">Fair</option>
                    <option value="Poor">Poor</option>
                </select>
            </div>
            <div>
                <button wire:click="resetFilters" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Assets Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($assets as $asset)
            <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow">
                <!-- Asset Image -->
                <div class="aspect-square bg-gray-100 rounded-t-lg overflow-hidden">
                    @if($asset->image_url)
                        <img src="{{ $asset->image_url }}" alt="{{ $asset->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>

                <div class="p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 truncate">{{ $asset->name }}</h3>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $this->getConditionColor($asset->condition) }}-100 text-{{ $this->getConditionColor($asset->condition) }}-800">
                                {{ $this->getConditionIcon($asset->condition) }} {{ $asset->condition }}
                            </span>
                        </div>
                        <div class="flex gap-1 ml-2">
                            <button wire:click="edit({{ $asset->id }})" class="text-blue-600 hover:text-blue-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button wire:click="deleteConfirm({{ $asset->id }})" class="text-red-600 hover:text-red-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <!-- Category -->
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span>{{ $asset->category }}</span>
                        </div>

                        <!-- Value -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Nilai:</span>
                            <div class="text-right">
                                <div class="font-semibold text-green-600">Rp {{ number_format($asset->current_value, 0, ',', '.') }}</div>
                                @if($asset->current_value != $asset->purchase_price)
                                    <div class="text-xs {{ $this->getDepreciationPercentage($asset) > 0 ? 'text-red-500' : 'text-green-500' }}">
                                        {{ $this->getDepreciationPercentage($asset) > 0 ? '-' : '+' }} {{ number_format(abs($this->getDepreciationPercentage($asset)), 1) }}%
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Location -->
                        @if($asset->location)
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="truncate">{{ $asset->location }}</span>
                            </div>
                        @endif

                        <!-- Purchase Date -->
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ \Carbon\Carbon::parse($asset->purchase_date)->format('d M Y') }}</span>
                            <span class="text-xs text-gray-500">({{ $this->getAgeInDays($asset->purchase_date) }} hari)</span>
                        </div>

                        <!-- Warranty Status -->
                        @if($asset->warranty_expiry)
                            @php
                                $warrantyInfo = $this->getWarrantyStatus($asset->warranty_expiry);
                            @endphp
                            <div class="flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-{{ $warrantyInfo['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <span class="text-{{ $warrantyInfo['color'] }}-600 font-medium">{{ $warrantyInfo['text'] }}</span>
                            </div>
                        @endif

                        <!-- Description -->
                        @if($asset->description)
                            <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                {{ Str::limit($asset->description, 80) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($assets->hasPages())
        <div class="mt-6">
            {{ $assets->links() }}
        </div>
    @endif

    <!-- Empty State -->
    @if($assets->count() === 0)
        <div class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">📦</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada aset</h3>
            <p class="text-gray-600 mb-4">Mulai mencatat aset-aset Anda</p>
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Tambah Aset Pertama
            </button>
        </div>
    @endif

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editingId ? 'Edit Aset' : 'Tambah Aset Baru' }}
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Aset</label>
                            <input wire:model="name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select wire:model="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                @foreach($this->getCommonCategories() as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                            @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi</label>
                            <select wire:model="condition" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="Excellent">Excellent</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Poor">Poor</option>
                            </select>
                            @error('condition') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Pembelian</label>
                            <input wire:model="purchase_price" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('purchase_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Saat Ini</label>
                            <input wire:model="current_value" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Opsional, default = harga pembelian">
                            @error('current_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pembelian</label>
                            <input wire:model="purchase_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('purchase_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kadaluarsa Garansi</label>
                            <input wire:model="warranty_expiry" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('warranty_expiry') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                            <input wire:model="location" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Opsional">
                            @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL Gambar</label>
                            <input wire:model="image_url" type="url" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Opsional">
                            @error('image_url') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea wire:model="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Deskripsi aset..."></textarea>
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
