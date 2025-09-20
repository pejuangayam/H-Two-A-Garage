<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with analytics data
     */
    public function index(Request $request)
    {
        // Input validation
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:' . (date('Y') + 10)
        ]);

        try {
            // Also reduce cache for available years to 1 minute for real-time year detection
            $availableYears = Cache::remember('dashboard_years', 60, function () {
                $serviceYears = DB::table('services')
                    ->selectRaw('YEAR(service_date) as year')
                    ->whereNotNull('service_date')
                    ->distinct()
                    ->pluck('year')
                    ->toArray();

                $salesYears = DB::table('sell_tables')
                    ->selectRaw('YEAR(created_at) as year')
                    ->whereNotNull('created_at')
                    ->distinct()
                    ->pluck('year')
                    ->toArray();

                $years = array_unique(array_merge($serviceYears, $salesYears));

                // Add current + 2 future years
                $currentYear = date('Y');
                for ($i = 0; $i <= 2; $i++) {
                    $futureYear = $currentYear + $i;
                    if (!in_array($futureYear, $years)) {
                        $years[] = $futureYear;
                    }
                }
                rsort($years);
                return $years;
            });

            // --- Year filter ---
            $currentYear = date('Y');
            $year = (int) ($request->query('year') ?? $currentYear);
            $isFutureYear = $year > $currentYear;

            // --- Month labels ---
            $months = collect(range(1, 12))
                ->map(fn($m) => Carbon::create($year, $m, 1)->format('M'))
                ->toArray();

            // ---- A) Profit vs Sales with smart caching ----
            $cacheKey = "dashboard_sales_{$year}";
            $cacheTime = $isFutureYear ? 3600 : 60; // 1 hour for future, 1 MINUTE for current data


            $salesData = Cache::remember($cacheKey, $cacheTime, function () use ($year, $isFutureYear, $availableYears) {
                $revenueByMonth = array_fill(0, 12, 0);
                $salesByMonth = array_fill(0, 12, 0);
                $yearlySalesProfit = 0;

                // Cache year lists for 1 minute for real-time updates
                $salesYears = Cache::remember('sales_years_list', 60, function () {
                    return DB::table('sell_tables')
                        ->selectRaw('YEAR(created_at) as year')
                        ->whereNotNull('created_at')
                        ->distinct()
                        ->pluck('year')
                        ->toArray();
                });

                if (!$isFutureYear || in_array($year, $salesYears)) {
                    $salesData = DB::table('sell_tables')
                        ->selectRaw('MONTH(created_at) as month, COALESCE(SUM(revenue), 0) as total_revenue, COALESCE(SUM(total), 0) as total_sales')
                        ->whereYear('created_at', $year)
                        ->groupBy('month')
                        ->orderBy('month')
                        ->get();

                    foreach ($salesData as $data) {
                        $idx = $data->month - 1;
                        $revenueByMonth[$idx] = round((float) $data->total_revenue, 2); // Round to 2 decimal places
                        $salesByMonth[$idx] = round((float) $data->total_sales, 2); // Round to 2 decimal places
                    }

                    $yearlySalesProfit = array_sum($revenueByMonth);
                }

                return [
                    'revenue' => $revenueByMonth,
                    'sales' => $salesByMonth,
                    'yearly_profit' => $yearlySalesProfit
                ];
            });

            $salesChart = (new LarapexChart)
                ->setType('bar')
                ->setTitle("Profit vs Sales ($year)")
                ->setSubtitle($isFutureYear ? 'Future Projection' : 'Monthly totals')
                ->setXAxis($months)
                ->setColors(['#10B981', '#3B82F6']) // Modern colors
                ->setDataset([
                    ['name' => 'Profit', 'data' => $salesData['revenue']],
                    ['name' => 'Sales', 'data' => $salesData['sales']],
                ]);

            // ---- B) Service Revenue with smart caching ----
            $serviceCacheKey = "dashboard_services_{$year}";

            $serviceData = Cache::remember($serviceCacheKey, $cacheTime, function () use ($year, $isFutureYear) {
                $serviceRevenueData = array_fill(0, 12, 0);
                $yearlyServiceRevenue = 0;

                // Cache year lists for 1 minute for real-time updates  
                $serviceYears = Cache::remember('service_years_list', 60, function () {
                    return DB::table('services')
                        ->selectRaw('YEAR(service_date) as year')
                        ->whereNotNull('service_date')
                        ->distinct()
                        ->pluck('year')
                        ->toArray();
                });

                if (!$isFutureYear || in_array($year, $serviceYears)) {
                    $serviceRevenue = DB::table('services')
                        ->selectRaw('MONTH(service_date) as month, COALESCE(SUM(total + COALESCE(labour_total, 0)), 0) as monthly_total')
                        ->whereYear('service_date', $year)
                        ->groupBy(DB::raw('MONTH(service_date)'))
                        ->orderBy('month')
                        ->get();

                    foreach ($serviceRevenue as $revenue) {
                        $idx = $revenue->month - 1;
                        $serviceRevenueData[$idx] = (float) $revenue->monthly_total;
                    }

                    $yearlyServiceRevenue = array_sum($serviceRevenueData);
                }

                return [
                    'monthly' => $serviceRevenueData,
                    'yearly' => $yearlyServiceRevenue
                ];
            });

            $servicesChart = (new LarapexChart)
                ->setType('bar')
                ->setTitle("Service Revenue ($year)")
                ->setSubtitle($isFutureYear ? 'Future Projection' : 'Monthly totals')
                ->setXAxis($months)
                ->setColors(['#8B5CF6']) // Modern purple
                ->setDataset([
                    ['name' => 'Service Revenue', 'data' => $serviceData['monthly']],
                ]);

            // ---- C) Extra Stats with optimization ----
            $statsCacheKey = "dashboard_stats_{$year}";

            $stats = Cache::remember($statsCacheKey, $cacheTime, function () use ($year, $serviceData, $months, $isFutureYear) {
                // Optimize: Single query for both counts
                $counts = DB::selectOne("
                    SELECT 
                        (SELECT COUNT(*) FROM vehicles) as total_vehicles,
                        (SELECT COUNT(*) FROM services WHERE YEAR(service_date) = ?) as active_services
                ", [$year]);

                $totalVehicles = (int) $counts->total_vehicles;
                $activeServices = (int) $counts->active_services;

                // Calculate top performing month
                $maxRevenue = max($serviceData['monthly']);
                $topMonthIndex = $maxRevenue > 0 ? array_search($maxRevenue, $serviceData['monthly']) : false;
                $topPerformingMonth = $topMonthIndex !== false ? $months[$topMonthIndex] : 'N/A';

                return [
                    'total_vehicles' => $totalVehicles,
                    'active_services' => $activeServices,
                    'top_month' => $topPerformingMonth
                ];
            });

            // Extract variables for view (maintain exact same variable names)
            $yearlyServiceRevenue = $serviceData['yearly'];
            $yearlySalesProfit = $salesData['yearly_profit'];
            $totalVehicles = $stats['total_vehicles'];
            $activeServices = $stats['active_services'];
            $topPerformingMonth = $stats['top_month'];

            return view('dashboard.index', compact(
                'year',
                'availableYears',
                'isFutureYear',
                'salesChart',
                'servicesChart',
                'yearlyServiceRevenue',
                'yearlySalesProfit',
                'totalVehicles',
                'activeServices',
                'topPerformingMonth'
            ));
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Dashboard error: ' . $e->getMessage(), [
                'year' => $year ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            // Fallback to basic functionality without cache
            return $this->fallbackDashboard($request);
        }
    }

    /**
     * Fallback dashboard (your original logic) - ensures it always works
     */
    private function fallbackDashboard(Request $request)
    {
        // Your exact original code as fallback
        $serviceYears = DB::table('services')
            ->selectRaw('YEAR(service_date) as year')
            ->whereNotNull('service_date')
            ->distinct()
            ->pluck('year')
            ->toArray();

        $salesYears = DB::table('sell_tables')
            ->selectRaw('YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->distinct()
            ->pluck('year')
            ->toArray();

        $availableYears = array_unique(array_merge($serviceYears, $salesYears));

        $currentYear = date('Y');
        for ($i = 0; $i <= 2; $i++) {
            $futureYear = $currentYear + $i;
            if (!in_array($futureYear, $availableYears)) {
                $availableYears[] = $futureYear;
            }
        }
        rsort($availableYears);

        $year = (int) ($request->query('year') ?? $currentYear);
        $isFutureYear = $year > $currentYear;

        $months = collect(range(1, 12))
            ->map(fn($m) => Carbon::create($year, $m, 1)->format('M'))
            ->toArray();

        // ---- A) Profit vs Sales ----
        $revenueByMonth = array_fill(0, 12, 0);
        $salesByMonth = array_fill(0, 12, 0);
        $yearlySalesProfit = 0;

        if (!$isFutureYear || in_array($year, $salesYears)) {
            $salesData = DB::table('sell_tables')
                ->selectRaw('MONTH(created_at) as month, SUM(revenue) as total_revenue, SUM(total) as total_sales')
                ->whereYear('created_at', $year)
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            foreach ($salesData as $data) {
                $idx = $data->month - 1;
                $revenueByMonth[$idx] = round((float) $data->total_revenue, 2);
                $salesByMonth[$idx] = round((float) $data->total_sales, 2);
            }

            $yearlySalesProfit = array_sum($revenueByMonth);
        }

        $salesChart = (new LarapexChart)
            ->setType('bar')
            ->setTitle("Profit vs Sales ($year)")
            ->setSubtitle($isFutureYear ? 'Future Projection' : 'Monthly totals')
            ->setXAxis($months)
            ->setDataset([
                ['name' => 'Profit', 'data' => $revenueByMonth],
                ['name' => 'Sales', 'data' => $salesByMonth],
            ]);

        // ---- B) Service Revenue ----
        $serviceRevenueData = array_fill(0, 12, 0);
        $yearlyServiceRevenue = 0;

        if (!$isFutureYear || in_array($year, $serviceYears)) {
            $serviceRevenue = DB::table('services')
                ->selectRaw('MONTH(service_date) as month, SUM(total + COALESCE(labour_total, 0)) as monthly_total')
                ->whereYear('service_date', $year)
                ->groupBy(DB::raw('MONTH(service_date)'))
                ->orderBy('month')
                ->get();

            foreach ($serviceRevenue as $revenue) {
                $idx = $revenue->month - 1;
                $serviceRevenueData[$idx] = (float) $revenue->monthly_total;
            }

            $yearlyServiceRevenue = array_sum($serviceRevenueData);
        }

        $servicesChart = (new LarapexChart)
            ->setType('bar')
            ->setTitle("Service Revenue ($year)")
            ->setSubtitle($isFutureYear ? 'Future Projection' : 'Monthly totals')
            ->setXAxis($months)
            ->setDataset([
                ['name' => 'Service Revenue', 'data' => $serviceRevenueData],
            ]);

        // ---- C) Extra Stats ----
        $totalVehicles = DB::table('vehicles')->count();
        $activeServices = DB::table('services')
            ->whereYear('service_date', $year)
            ->count();

        $maxRevenue = max($serviceRevenueData);
        $topMonthIndex = $maxRevenue > 0 ? array_search($maxRevenue, $serviceRevenueData) : false;
        $topPerformingMonth = $topMonthIndex !== false ? $months[$topMonthIndex] : 'N/A';

        return view('dashboard.index', compact(
            'year',
            'availableYears',
            'isFutureYear',
            'salesChart',
            'servicesChart',
            'yearlyServiceRevenue',
            'yearlySalesProfit',
            'totalVehicles',
            'activeServices',
            'topPerformingMonth'
        ));
    }

    /**
     * Clear dashboard cache manually (useful for testing)
     */
    public function clearCache(Request $request)
    {
        $year = (int) ($request->query('year') ?? date('Y'));

        // Clear specific caches
        Cache::forget('dashboard_years');
        Cache::forget('sales_years_list');
        Cache::forget('service_years_list');
        Cache::forget("dashboard_sales_{$year}");
        Cache::forget("dashboard_services_{$year}");
        Cache::forget("dashboard_stats_{$year}");

        return redirect()->route('dashboard.index', ['year' => $year])
            ->with('success', 'Dashboard cache cleared successfully!');
    }

    /**
     * Call this method after creating/updating sales or services
     * Add this to your Services/Sales controllers after successful insert/update
     */
    public static function invalidateDashboardCache($year = null)
    {
        $year = $year ?? date('Y');

        // Clear only current year cache when data changes
        Cache::forget('dashboard_years');
        Cache::forget('sales_years_list');
        Cache::forget('service_years_list');
        Cache::forget("dashboard_sales_{$year}");
        Cache::forget("dashboard_services_{$year}");
        Cache::forget("dashboard_stats_{$year}");
    }

    /**
     * API endpoint for AJAX requests
     */
    public function apiData(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:' . (date('Y') + 10)
        ]);

        try {
            // Get the same data but return as JSON
            $dashboardData = $this->index($request);
            $data = $dashboardData->getData();

            return response()->json([
                'success' => true,
                'data' => [
                    'year' => $data['year'],
                    'yearlyServiceRevenue' => $data['yearlyServiceRevenue'],
                    'yearlySalesProfit' => $data['yearlySalesProfit'],
                    'totalVehicles' => $data['totalVehicles'],
                    'activeServices' => $data['activeServices'],
                    'topPerformingMonth' => $data['topPerformingMonth']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data'
            ], 500);
        }
    }
}
