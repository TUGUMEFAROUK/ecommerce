<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StoreStatisticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with figures computed from real records.
     */
    public function index(StoreStatisticsService $statistics): View
    {
        return view('admin.dashboard', $statistics->summary());
    }
}
