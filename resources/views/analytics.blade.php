<x-request-analytics::layouts.app>
    <main class="container px-5 sm:px-0">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-14 py-10 flex-wrap">
                <x-request-analytics::stats.count label="Views" :value='$average["views"]'/>
                <x-request-analytics::stats.count label="Visitors" :value='$average["visitors"]'/>
                <x-request-analytics::stats.count label="Bounce Rate" :value='$average["bounce-rate"]'/>
                <x-request-analytics::stats.count label="Average Visit Time" :value='$average["average-visit-time"]'/>
            </div>
            <div class="flex items-center flex-wrap gap-4">
                <form method="GET" action="{{ route(config('request-analytics.route.name')) }}" class="flex items-center gap-2">
                    <select name="date_range" class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="7" {{ $dateRange == 7 ? 'selected' : '' }}>Last 7 days</option>
                        <option value="30" {{ $dateRange == 30 ? 'selected' : '' }}>Last 30 days</option>
                        <option value="90" {{ $dateRange == 90 ? 'selected' : '' }}>Last 90 days</option>
                        <option value="365" {{ $dateRange == 365 ? 'selected' : '' }}>Last year</option>
                    </select>
                    <x-request-analytics::core.button type="submit" color="primary">Apply Filter</x-request-analytics::core.button>
                </form>
            </div>
        </div>

        <div class="border-b">
            <x-request-analytics::stats.chart :labels='$labels' :datasets='$datasets'/>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:divide-x-2">
            <div class="border-b">
                <x-request-analytics::analytics.pages :pages='$pages'/>
            </div>
            <div class="border-b">
                <x-request-analytics::analytics.referrers :referrers='$referrers'/>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 divide-y md:divide-x">
            <div>
                <x-request-analytics::analytics.broswers :browsers='$browsers'/>
            </div>
            <div>
                <x-request-analytics::analytics.operating-systems :operatingSystems='$operatingSystems'/>
            </div>
            <div>
                <x-request-analytics::analytics.devices :devices='$devices'/>
            </div>
            <div>
                <x-request-analytics::analytics.countries :countries='$countries'/>
            </div>
        </div>
    </main>
</x-request-analytics::layouts.app>
