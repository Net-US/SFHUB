<div class="bg-white dark:bg-stone-900 rounded-2xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
    <h3 class="font-bold text-stone-800 dark:text-white mb-4 flex items-center">
        <i class="fa-solid fa-landmark text-blue-500 mr-2"></i>
        Aset
    </h3>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="text-center p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
            <div class="text-lg font-bold text-stone-800 dark:text-white">Rp{{ number_format($totalValue, 0, ',', '.') }}</div>
            <div class="text-xs text-stone-500 dark:text-stone-400">Nilai Saat Ini</div>
        </div>
        <div class="text-center p-4 bg-stone-50 dark:bg-stone-800 rounded-xl">
            @php
                $appreciation = $totalValue - $totalPurchaseValue;
                $percentage = $totalPurchaseValue > 0 ? ($appreciation / $totalPurchaseValue) * 100 : 0;
            @endphp
            <div class="text-lg font-bold {{ $appreciation >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $appreciation >= 0 ? '+' : '' }}Rp{{ number_format($appreciation, 0, ',', '.') }}
            </div>
            <div class="text-xs text-stone-500 dark:text-stone-400">Depresiasi/Appresiasi</div>
        </div>
    </div>

    @if(!empty($byCategory))
        <div class="mb-4">
            <div class="text-sm font-semibold text-stone-700 dark:text-stone-300 mb-2">Kategori</div>
            <div class="space-y-2">
                @foreach($byCategory as $category => $data)
                    <div class="flex justify-between items-center p-2 bg-stone-50 dark:bg-stone-800 rounded text-sm">
                        <span class="text-stone-600 dark:text-stone-400">{{ ucfirst($category) }}</span>
                        <div class="text-right">
                            <div class="text-stone-800 dark:text-white font-medium">{{ $data['count'] }} item</div>
                            <div class="text-xs text-stone-500">Rp{{ number_format($data['value'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
