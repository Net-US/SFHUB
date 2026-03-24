@props([
    'id',
    'label' => '',
    'subtext' => null,
    'checked' => false,
])

<label class="flex items-start justify-between gap-3 p-3 rounded-xl border border-stone-200 dark:border-stone-700 bg-white/60 dark:bg-stone-800/40 {{ $attributes->get('class') }}">
    <span>
        @if($label)
            <span class="block text-sm font-medium text-stone-800 dark:text-stone-200">{{ $label }}</span>
        @endif
        @if($subtext)
            <span class="block mt-0.5 text-xs text-stone-500 dark:text-stone-400">{{ $subtext }}</span>
        @endif
    </span>

    <input
        id="{{ $id }}"
        type="checkbox"
        @checked((bool) $checked)
        class="mt-0.5 h-5 w-5 rounded border-stone-300 text-emerald-600 focus:ring-emerald-500"
    >
</label>
