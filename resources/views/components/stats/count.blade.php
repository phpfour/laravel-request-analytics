@props([
    'label',
    'value' => 0,
    'badgeText' => null,
    'badgeColor' => 'green',
])

<div>
    <h1 class="text-4xl font-bold text-black">{{ $value }}</h1>
    <div class="flex items-center justify-start gap-2 mt-2">
        <p class="font-semibold text-black">{{ $label }}</p>
        @if (!is_null($badgeText))
            <span
                class="px-2 py-1 text-xs rounded bg-{{ $badgeColor }}-100 text-{{ $badgeColor }}-600">{{ $badgeText }}</span>
        @endif
    </div>
</div>
