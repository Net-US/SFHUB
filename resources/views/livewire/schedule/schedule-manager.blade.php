<div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
    <div class="flex justify-between items-center mb-6">
        <h3 class="font-bold text-stone-800 dark:text-white">Manajer Jadwal</h3>
        <button wire:click="openModal" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Jadwal
        </button>
    </div>

    <div class="grid grid-cols-7 gap-2 mb-4">
        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
            <div class="text-center">
                <div class="text-xs font-semibold text-stone-500 dark:text-stone-400 mb-2">{{ $day }}</div>
                <div class="space-y-1">
                    @if(isset($schedules[$day]))
                        @foreach($schedules[$day] as $schedule)
                            <div wire:click="edit({{ $schedule['id'] }})"
                                 class="p-2 rounded text-xs cursor-pointer {{ $schedule['color'] ?? 'bg-stone-100 dark:bg-stone-700' }}"
                                 style="background-color: {{ $schedule['color'] ?? '' }}">
                                <div class="font-medium truncate">{{ $schedule['title'] ?? $schedule['activity'] }}</div>
                                <div class="opacity-75">{{ \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if($showModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white dark:bg-stone-800 rounded-2xl p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
                <h4 class="font-bold text-stone-800 dark:text-white mb-4">{{ $editingSchedule ? 'Edit' : 'Tambah' }} Jadwal</h4>

                <form wire:submit.prevent="save">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Hari</label>
                            <select wire:model="day_of_week" class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 dark:text-white">
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                            @error('day_of_week') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Mulai</label>
                                <input type="time" wire:model="start_time" class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 dark:text-white">
                                @error('start_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Selesai</label>
                                <input type="time" wire:model="end_time" class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 dark:text-white">
                                @error('end_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Judul</label>
                            <input type="text" wire:model="title" class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 dark:text-white" placeholder="Nama jadwal">
                            @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Aktivitas</label>
                            <input type="text" wire:model="activity" class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 dark:text-white" placeholder="Detail aktivitas">
                            @error('activity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Tipe</label>
                            <select wire:model="type" class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-stone-700 dark:text-white">
                                <option value="academic">Akademik</option>
                                <option value="pkl">PKL</option>
                                <option value="creative">Kreatif</option>
                                <option value="personal">Personal</option>
                                <option value="routine">Rutin</option>
                            </select>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="is_recurring" class="rounded border-stone-300 dark:border-stone-600 mr-2">
                                <span class="text-sm text-stone-700 dark:text-stone-300">Jadwal rutin (berulang)</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-stone-600 dark:text-stone-400 hover:text-stone-800">Batal</button>
                        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
