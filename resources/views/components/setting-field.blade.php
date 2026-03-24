@props([
    'id',
    'label',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'help' => null,
    'icon' => null,
])

<div>
    <label for="{{ $id }}" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-2">
        @if($icon)
            <i class="{{ $icon }} mr-1.5"></i>
        @endif
        {{ $label }}
    </label>

    <input
        id="{{ $id }}"
        type="{{ $type }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'setting-input']) }}
    >

    @if($help)
        <p class="mt-1.5 text-xs text-stone-500 dark:text-stone-400">{{ $help }}</p>
    @endif
</div>
