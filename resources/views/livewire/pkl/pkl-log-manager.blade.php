<div>
    <!-- Header & Summary -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Log PKL</h2>
                <p class="text-gray-600">Catat kegiatan dan kehadiran PKL Anda</p>
            </div>
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Log
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Hari</p>
                        <p class="text-xl font-bold text-blue-600">{{ $totalDays }}</p>
                    </div>
                    <div class="text-2xl">📅</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Hadir</p>
                        <p class="text-xl font-bold text-green-600">{{ $presentDays }}</p>
                    </div>
                    <div class="text-2xl">✅</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Tidak Hadir</p>
                        <p class="text-xl font-bold text-red-600">{{ $absentDays }}</p>
                    </div>
                    <div class="text-2xl">❌</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Jam</p>
                        <p class="text-xl font-bold text-purple-600">{{ $totalHours }}</p>
                    </div>
                    <div class="text-2xl">⏰</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Rating Rata-rata</p>
                        <p class="text-xl font-bold text-yellow-600">{{ number_format($avgRating, 1) }}</p>
                    </div>
                    <div class="text-2xl">⭐</div>
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
                    placeholder="Cari log..." 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            <div>
                <select wire:model.live="filterMonth" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Bulan</option>
                    @foreach($this->getMonthOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="filterStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="present">Hadir</option>
                    <option value="absent">Tidak Hadir</option>
                    <option value="late">Terlambat</option>
                    <option value="sick">Sakit</option>
                    <option value="permission">Izin</option>
                </select>
            </div>
            <div>
                <button wire:click="resetFilters" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    <!-- PKL Logs List -->
    <div class="space-y-4">
        @foreach($pklLogs as $log)
            <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="text-2xl">{{ $this->getAttendanceStatusIcon($log->attendance_status) }}</div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($log->log_date)->format('d F Y') }}</h3>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $this->getAttendanceStatusColor($log->attendance_status) }}-100 text-{{ $this->getAttendanceStatusColor($log->attendance_status) }}-800">
                                    {{ $this->getAttendanceStatusLabel($log->attendance_status) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="edit({{ $log->id }})" class="text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button wire:click="deleteConfirm({{ $log->id }})" class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <!-- Time and Hours -->
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            @if($log->check_in)
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Check-in: {{ $log->check_in }}</span>
                                </div>
                            @endif
                            @if($log->check_out)
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Check-out: {{ $log->check_out }}</span>
                                </div>
                            @endif
                            @if($log->working_hours > 0)
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ $log->working_hours }} jam</span>
                                </div>
                            @endif
                        </div>

                        <!-- Performance Rating -->
                        @if($log->performance_rating)
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600">Rating:</span>
                                <span class="text-{{ $this->getPerformanceRatingColor($log->performance_rating) }}-600">
                                    {{ $this->getPerformanceStars($log->performance_rating) }}
                                </span>
                            </div>
                        @endif

                        <!-- Daily Tasks -->
                        @if($log->daily_tasks)
                            <div class="border-l-4 border-blue-500 pl-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-1">Tugas Harian:</h4>
                                <p class="text-sm text-gray-600">{{ Str::limit($log->daily_tasks, 200) }}</p>
                            </div>
                        @endif

                        <!-- Achievements -->
                        @if($log->achievements)
                            <div class="border-l-4 border-green-500 pl-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-1">Pencapaian:</h4>
                                <p class="text-sm text-gray-600">{{ Str::limit($log->achievements, 200) }}</p>
                            </div>
                        @endif

                        <!-- Challenges -->
                        @if($log->challenges)
                            <div class="border-l-4 border-yellow-500 pl-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-1">Tantangan:</h4>
                                <p class="text-sm text-gray-600">{{ Str::limit($log->challenges, 200) }}</p>
                            </div>
                        @endif

                        <!-- Learnings -->
                        @if($log->learnings)
                            <div class="border-l-4 border-purple-500 pl-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-1">Pembelajaran:</h4>
                                <p class="text-sm text-gray-600">{{ Str::limit($log->learnings, 200) }}</p>
                            </div>
                        @endif

                        <!-- Supervisor -->
                        @if($log->supervisor_name)
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>Pembimbing: {{ $log->supervisor_name }}</span>
                            </div>
                        @endif

                        <!-- Notes -->
                        @if($log->notes)
                            <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded">
                                <span class="font-medium">Catatan:</span> {{ Str::limit($log->notes, 100) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($pklLogs->hasPages())
        <div class="mt-6">
            {{ $pklLogs->links() }}
        </div>
    @endif

    <!-- Empty State -->
    @if($pklLogs->count() === 0)
        <div class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">📝</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada log PKL</h3>
            <p class="text-gray-600 mb-4">Mulai mencatat kegiatan PKL Anda</p>
            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Tambah Log Pertama
            </button>
        </div>
    @endif

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editingId ? 'Edit Log PKL' : 'Tambah Log PKL Baru' }}
                    </h3>
                    <button wire:click="cancel" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="{{ $editingId ? 'update' : 'store' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Basic Info -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                            <input wire:model="log_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('log_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Kehadiran</label>
                            <select wire:model="attendance_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="present">Hadir</option>
                                <option value="absent">Tidak Hadir</option>
                                <option value="late">Terlambat</option>
                                <option value="sick">Sakit</option>
                                <option value="permission">Izin</option>
                            </select>
                            @error('attendance_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Check In</label>
                            <input wire:model="check_in" type="time" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('check_in') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Check Out</label>
                            <input wire:model="check_out" type="time" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('check_out') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Kerja</label>
                            <input wire:model="working_hours" type="number" step="0.1" min="0" max="24" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            @error('working_hours') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pembimbing</label>
                            <input wire:model="supervisor_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Opsional">
                            @error('supervisor_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rating Kinerja (1-5)</label>
                            <select wire:model="performance_rating" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="1">1 - Sangat Buruk</option>
                                <option value="2">2 - Buruk</option>
                                <option value="3">3 - Cukup</option>
                                <option value="4">4 - Baik</option>
                                <option value="5">5 - Sangat Baik</option>
                            </select>
                            @error('performance_rating') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Activities -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tugas Harian</label>
                            <textarea wire:model="daily_tasks" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Apa saja tugas yang dikerjakan hari ini?"></textarea>
                            @error('daily_tasks') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pencapaian</label>
                            <textarea wire:model="achievements" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Apa saja yang berhasil dicapai hari ini?"></textarea>
                            @error('achievements') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tantangan</label>
                            <textarea wire:model="challenges" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Apakah ada tantangan atau kesulitan yang dihadapi?"></textarea>
                            @error('challenges') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pembelajaran</label>
                            <textarea wire:model="learnings" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Apa saja yang dipelajari hari ini?"></textarea>
                            @error('learnings') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan</label>
                            <textarea wire:model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Catatan lainnya..."></textarea>
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
