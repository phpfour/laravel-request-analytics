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
                <x-request-analytics::core.button color="secondary">Filter</x-request-analytics::core.button>
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
