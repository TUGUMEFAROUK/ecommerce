<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StoreStatisticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * The sales chart windows an administrator can choose from.
     *
     * @var list<int>
     */
    private const RANGES = [7, 30, 90];

    /**
     * Display the admin dashboard with figures computed from real records.
     */
    public function index(Request $request, StoreStatisticsService $statistics): View
    {
        $range = (int) $request->query('range', 30);

        if (! in_array($range, self::RANGES, true)) {
            $range = 30;
        }

        $series = $statistics->salesSeries($range);

        $totals = array_column($series, 'total');

        return view('admin.dashboard', [
            ...$statistics->summary(),
            'range' => $range,
            'ranges' => self::RANGES,
            'series' => $series,
            'series_total' => round(array_sum($totals), 2),
            'series_max' => $totals === [] ? 0.0 : max($totals),
            'series_orders' => array_sum(array_column($series, 'orders')),
            'top_products' => $statistics->topSellingProducts(),
        ]);
    }
}
