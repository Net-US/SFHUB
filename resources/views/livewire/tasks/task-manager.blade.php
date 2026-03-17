<div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Manajemen Tugas</h2>
            <p class="text-gray-600">Kelola semua tugas dan deadline Anda</p>
        </div>
        <button wire:click="create"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Tugas
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <input wire:model.live="search" type="text" placeholder="Cari tugas..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <select wire:model.live="filterStatus"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="Todo">Todo</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Done">Done</option>
                </select>
            </div>
            <div>
                <select wire:model.live="filterPriority"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Prioritas</option>
                    <option value="High">High</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                </select>
            </div>
            <div>
                <select wire:model.live="filterCategory"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Kategori</option>
                    <option value="Tugas Kuliah">Tugas Kuliah</option>
                    <option value="Project">Project</option>
                    <option value="Konten">Konten</option>
                    <option value="PKL">PKL</option>
                    <option value="Personal">Personal</option>
                </select>
            </div>
            <div>
                <button wire:click="resetFilters"
                    class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Tasks Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($tasks as $task)
            <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="text-2xl">{{ $this->getCategoryIcon($task->category) }}</div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $task->title }}</h3>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $this->getCategoryColor($task->category) }}-100 text-{{ $this->getCategoryColor($task->category) }}-800">
                                    {{ $task->category }}
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="edit({{ $task->id }})" class="text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                            </button>
                            <button wire:click="deleteConfirm({{ $task->id }})"
                                class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <!-- Status and Priority -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <button wire:click="toggleStatus({{ $task->id }})"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $this->getStatusColor($task->status) }}-100 text-{{ $this->getStatusColor($task->status) }}-800 hover:opacity-80">
                                    {{ $task->status }}
                                </button>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $this->getPriorityColor($task->priority) }}-100 text-{{ $this->getPriorityColor($task->priority) }}-800">
                                    {{ $task->priority }}
                                </span>
                            </div>
                        </div>

                        <!-- Description -->
                        @if ($task->description)
                            <div class="text-sm text-gray-600">
                                {{ Str::limit($task->description, 100) }}
                            </div>
                        @endif

                        <!-- Deadline -->
                        @if ($task->deadline)
                            @php
                                $deadlineInfo = $this->getDaysUntilDeadline($task->deadline);
                            @endphp
                            <div class="flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-{{ $deadlineInfo['color'] }}-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <span
                                    class="text-{{ $deadlineInfo['color'] }}-600 font-medium">{{ $deadlineInfo['text'] }}</span>
                                <span
                                    class="text-gray-500">{{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}</span>
                            </div>
                        @endif

                        <!-- Subject -->
                        @if ($task->subject)
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                    </path>
                                </svg>
                                {{ $task->subject->name }}
                            </div>
                        @endif

                        <!-- Notes -->
                        @if ($task->notes)
                            <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                <span class="font-medium">Catatan:</span> {{ Str::limit($task->notes, 80) }}
                            </div>
                        @endif

                        <!-- Created date -->
                        <div class="text-xs text-gray-400">
                            Dibuat: {{ $task->created_at->format('d M Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if ($tasks->hasPages())
        <div class="mt-6">
            {{ $tasks->links() }}
        </div>
    @endif

    <!-- Empty State -->
    @if ($tasks->count() === 0)
        <div class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">📝</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada tugas</h3>
            <p class="text-gray-600 mb-4">Mulai dengan menambahkan tugas pertama Anda</p>
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Tambah Tugas Pertama
            </button>
        </div>
    @endif

    <!-- Modal -->
    @if ($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div
                class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editingId ? 'Edit Tugas' : 'Tambah Tugas Baru' }}
                    </h3>
                    <button wire:click="cancel" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="{{ $editingId ? 'update' : 'store' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Tugas</label>
                            <input wire:model="title" type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            @error('title')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select wire:model="category"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                                <option value="Tugas Kuliah">Tugas Kuliah</option>
                                <option value="Project">Project</option>
                                <option value="Konten">Konten</option>
                                <option value="PKL">PKL</option>
                                <option value="Personal">Personal</option>
                            </select>
                            @error('category')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
                            <select wire:model="priority"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                                <option value="High">High</option>
                                <option value="Medium">Medium</option>
                                <option value="Low">Low</option>
                            </select>
                            @error('priority')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select wire:model="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                                <option value="Todo">Todo</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Done">Done</option>
                            </select>
                            @error('status')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                            <input wire:model="deadline" type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('deadline')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mata Kuliah</label>
                            <select wire:model="subject_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Pilih mata kuliah</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea wire:model="description" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Deskripsi tugas..."></textarea>
                            @error('description')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea wire:model="notes" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Catatan tambahan..."></textarea>
                            @error('notes')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg">
                            {{ $editingId ? 'Update' : 'Simpan' }}
                        </button>
                        <button type="button" wire:click="cancel"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg">
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
                const {
                    type,
                    message
                } = event;

                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ${
                    type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
                }`;
                toast.textContent = message;

                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            });

            Livewire.on('confirm-delete', (event) => {
                const {
                    title,
                    message,
                    id
                } = event;

                if (confirm(`${title}\n${message}`)) {
                    Livewire.dispatch('deleteConfirmed', {
                        id
                    });
                }
            });
        });
    </script>
</div>
