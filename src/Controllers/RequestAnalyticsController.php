<?php

namespace MeShaon\RequestAnalytics\Controllers;
use Illuminate\Routing\Controller as BaseController;

class RequestAnalyticsController extends BaseController
{
    public function show()
    {
        $chartData = $this->getChartData();
        return view('request-analytics::analytics', [
            'browsers' => $this->getBrowsers(),
            'operatingSystems' => $this->getOperatingSystems(),
            'devices' => $this->getDevices(),
            'pages' => $this->getPages(),
            'referrers' => $this->getReferrers(),
            'labels' => $chartData['labels'],
            'datasets' => $chartData['datasets'],
            'average' => $this->getAverage(),
            'countries' => $this->getCountries()
        ]);
    }

    private function getCountries(): array
    {
        return [
            [
                "name" => "United States",
                "visitorCount" => 100,
                "percentage" => 50,
                "code" => "us"

            ],
            [
                "name" => "Canada",
                "visitorCount" => 100,
                "percentage" => 50,
                "code" => "ca"
            ],
            [
                "name" => "United Kingdom",
                "visitorCount" => 100,
                "percentage" => 50,
                "code" => "gb"
            ],
            [
                "name" => "Australia",
                "visitorCount" => 100,
                "percentage" => 50,
                "code" => "au"
            ]
        ];
    }

    private function getBrowsers(): array
    {
        return [
            [
                "browser" => "Chrome",
                "visitorCount" => 100,
                "percentage" => 50
            ],
            [
                "browser" => "Firefox",
                "visitorCount" => 100,
                "percentage" => 50
            ]
        ];
    }

    private function getOperatingSystems(): array
    {
        return [
            [
                "name" => "Windows",
                "visitorCount" => 100,
                "percentage" => 50
            ],
            [
                "name" => "Linux",
                "visitorCount" => 100,
                "percentage" => 50
            ],
            [
                "name" => "MacOS",
                "visitorCount" => 100,
                "percentage" => 50
            ]
        ];
    }

    private function getDevices(): array
    {
        return [
            [
                "name" => "Mobile",
                "visitorCount" => 100,
                "percentage" => 50
            ],
            [
                "name" => "Tablet",
                "visitorCount" => 100,
                "percentage" => 50
            ],
            [
                "name" => "Desktop",
                "visitorCount" => 100,
                "percentage" => 50
            ],
        ];
    }

    private function getPages(): array
    {
        return [
            [
                "path" => "/",
                "visitorCount" => 100,
                "percentage" => 50
            ],
            [
                "path" => "/about",
                "visitorCount" => 100,
                "percentage" => 50
            ],
            [
                "path" => "/contact",
                "visitorCount" => 100,
                "percentage" => 50
            ]
        ];
    }

    private function getReferrers()
    {
        return [
            [
                "domain" => "google.com",
                "visitorCount" => 100,
                "percentage" => 50
            ],
            [
                "domain" => "facebook.com",
                "visitorCount" => 100,
                "percentage" => 50
            ],
            [
                "domain" => "twitter.com",
                "visitorCount" => 100,
                "percentage" => 50
            ]
        ];
    }

    private function getChartData(): array
    {
        $labels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October'];
        $datasets = [
            [
                "label" => "Views",
                "data" => [10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
                "backgroundColor" => "rgba(255, 99, 132, 0.2)",
                "borderColor" => "rgba(255, 99, 132, 1)",
                "borderWidth" => 1
            ],
            [
                "label" => "Visitors",
                "data" => [100, 200, 300, 400, 500, 600, 700, 800, 900, 1000],
                "backgroundColor" => "rgba(54, 162, 235, 0.2)",
                "borderColor" => "rgba(54, 162, 235, 1)",
                "borderWidth" => 1
            ]
        ];
        return compact('labels', 'datasets');
    }

    private function getAverage()
    {
        return [
            "views" => 100,
            "visitors" => 100,
            "bounce-rate" => 100,
            "average-visit-time" => 100
        ];
    }
}
