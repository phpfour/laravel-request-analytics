@props([
    'browsers' => [],
])

@php
    function getBrowserImage($browser): string {
        return match(strtolower($browser)){
            'chrome' => 'https://img.icons8.com/color/48/000000/chrome.png',
            'firefox' => 'https://img.icons8.com/color/48/000000/firefox.png',
            'safari' => 'https://img.icons8.com/color/48/000000/safari.png',
            'edge' => 'https://img.icons8.com/color/48/000000/edge.png',
            default => 'https://img.icons8.com/color/48/000000/unknown.png',
        };
    }
@endphp

<x-request-analytics::stats.list primaryLabel="Browser" secondaryLabel="Visitors">
    @forelse($browsers as $browser)
        <x-request-analytics::stats.item
            label="{{ $browser['browser'] }}"
            count="{{ $browser['visitorCount'] }}"
            percentage="{{ $browser['percentage'] }}"
            imgSrc="{{ getBrowserImage($browser['browser']) }}"
        />
    @empty
        <p class="text-sm text-gray-500 text-center py-5">No browsers</p>
    @endforelse
</x-request-analytics::stats.list>
