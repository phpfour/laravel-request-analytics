@props([
    'primaryLabel',
    'secondaryLabel',
    'footer' => null
])

<div class="px-5 py-10 min-h-[400px] flex flex-col ">
    <div class="flex items-center justify-between">
        <h6 class="font-bold text-sm">{{ $primaryLabel }}</h6>
        <span class="font-bold text-sm">{{ $secondaryLabel }}</span>
    </div>
    <div class="flex-1 flex flex-col mt-3 gap-3">
        {{ $slot }}
    </div>
    <div class="mt-3">
        {{ $footer }}
    </div>
</div>
