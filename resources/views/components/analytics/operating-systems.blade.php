@props([
    'operatingSystems' => [],
])

@php
    function getOperatingSystemImage($os): string {
        return match(strtolower($os)){
            'windows' => assert('operating-systems/windows-logo.png'),
            'linux' => assert('operating-systems/linux.png'),
            'macos' => assert('operating-systems/mac-logo.png'),
            'android' => assert('operating-systems/android-os.png'),
            'ios' => assert('operating-systems/iphone.png'),
            default => assert('operating-systems/unknown.png'),
        };
    }
@endphp
<x-request-analytics::stats.list primaryLabel="Os" secondaryLabel="Visitors">
    @forelse($operatingSystems as $os)
        <x-request-analytics::stats.item
            label="{{ $os['name'] }}"
            count="{{ $os['count'] }}"
            percentage="{{ $os['percentage'] }}"
            imgSrc="{{ getOperatingSystemImage($os['name']) }}"
        />
    @empty
        <p class="text-sm text-gray-500 text-center py-5">No operating systems</p>
    @endforelse
</x-request-analytics::stats.list>
