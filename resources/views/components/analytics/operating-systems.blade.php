@props([
    'operatingSystems' => [],
])

@php
    function getOperatingSystemImage($os): string {
        return match(strtolower($os)){
            'windows' => 'https://img.icons8.com/color/48/000000/windows-logo.png',
            'linux' => 'https://img.icons8.com/color/48/000000/linux.png',
            'macos' => 'https://img.icons8.com/color/48/000000/mac-logo.png',
            'android' => 'https://img.icons8.com/color/48/000000/android.png',
            'ios' => 'https://img.icons8.com/color/48/000000/iphone.png',
            'symbian' => 'https://img.icons8.com/color/48/000000/symbianos.png',
            'webos' => 'https://img.icons8.com/color/48/000000/webos.png',
            default => 'https://img.icons8.com/color/48/000000/unknown.png',
        };
    }
@endphp
<x-request-analytics::stats.list primaryLabel="Os" secondaryLabel="Visitors">
    @forelse($operatingSystems as $os)
        <x-request-analytics::stats.item
            label="{{ $os['name'] }}"
            count="{{ $os['visitorCount'] }}"
            percentage="{{ $os['percentage'] }}"
            imgSrc="{{ getOperatingSystemImage($os['name']) }}"
        />
    @empty
        <p class="text-sm text-gray-500 text-center py-5">No operating systems</p>
    @endforelse
</x-request-analytics::stats.list>
