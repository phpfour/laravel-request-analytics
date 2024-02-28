@props([
    'devices' => [],
])

@php
    function getDeviceImage($device): string {
        return match(strtolower($device)){
            'mobile' => 'https://img.icons8.com/color/48/000000/smartphone.png',
            'tablet' => 'https://img.icons8.com/color/48/000000/ipad.png',
            'desktop' => 'https://img.icons8.com/color/48/000000/laptop.png',
            'tv' => 'https://img.icons8.com/color/48/000000/tv.png',
            'smartwatch' => 'https://img.icons8.com/color/48/000000/smart-watch.png',
            default => 'https://img.icons8.com/color/48/000000/unknown.png',
        };
    }
@endphp
<x-request-analytics::stats.list primaryLabel="Devices" secondaryLabel="Visitors">
    @forelse($devices as $device)
        <x-request-analytics::stats.item
            label="{{ $device['name'] }}"
            count="{{ $device['visitorCount'] }}"
            percentage="{{ $device['percentage'] }}"
            imgSrc="{{ getDeviceImage($device['name']) }}"
        />
    @empty
        <p class="text-sm text-gray-500 text-center py-5">No devices</p>
    @endforelse
</x-request-analytics::stats.list>
