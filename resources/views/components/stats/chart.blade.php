@props([
    'datasets' => [],
    'labels' => [],
    'type' => 'bar',
    'height' => 200,
    'width' => 500,
])

<div>
    <canvas id="stats-chart" width="{{ $width }}" height="{{ $height }}"></canvas>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('stats-chart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: '{{ $type }}',
            data: {
                labels: @js($labels),
                datasets: @js($datasets)
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        })
    </script>
@endpush
