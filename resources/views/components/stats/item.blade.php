@props([
    'label',
    'count' => 0,
    'percentage' => 0,
    'imgSrc' => null,
])

<div class="flex items-center justify-between hover:bg-gray-50">
    <div class="flex gap-2 items-center">
        @if (!is_null($imgSrc))
            <span>
                <img alt="icon" class="w-5 h-5 rounded" src="{{ $imgSrc }}"/>
            </span>
        @endif
        <span class="text-sm">{{ $label }}</span>
    </div>
    <div class="flex gap-2 items-center">
        <span class="font-bold text-sm z-20">{{ $count }}</span>
        <div class="relative w-full">
        <span
            class="absolute h-full w-[{{ $percentage }}%] left-0 bg-blue-100"
        ></span>
            <span
                class="font-semibold relative flex items-center justify-center text-gray-400 text-sm px-5 py-1 border-l-2 border-blue-200">{{ $percentage }}%</span>
        </div>
    </div>
</div>
