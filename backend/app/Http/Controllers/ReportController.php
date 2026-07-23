<?php

namespace App\Http\Controllers;

use App\Models\Bunker;
use App\Models\Expense;
use App\Models\Season;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Bunkers Report – all bunkers with performance metrics
     */
    public function bunkersReport(Request $request)
    {
        try {
            $seasonId = $request->get('season_id');
            $location = $request->get('location');

            $query = Bunker::with(['purchases', 'saleItems', 'expenses', 'verifications']);

            // ✅ Get season name for filter display
            $seasonName = 'All';
            if ($seasonId && $seasonId !== 'all') {
                $season = Season::find($seasonId);
                $seasonName = $season ? $season->name : 'All';
                $query->where('season_id', $seasonId);
            }

            if ($location && $location !== 'all') {
                $query->where('location', $location);
            }

            $bunkers = $query->orderBy('name')->get();

            $reportData = [];
            $totalBunkers = 0;
            $totalPurchased = 0;
            $totalSold = 0;
            $totalAvailable = 0;
            $totalRevenue = 0;
            $totalCost = 0;
            $totalProfit = 0;
            $totalShrinkage = 0;

            foreach ($bunkers as $bunker) {
                $purchased = $bunker->purchases->sum('weight_kg');
                $sold = $bunker->saleItems->sum('weight_kg');
                $revenue = $bunker->saleItems->sum('total_price');
                $cost = $bunker->purchases->sum('cost') + $bunker->expenses->sum('amount');
                $profit = $revenue - $cost;
                $available = $bunker->available_weight;
                $shrinkage = $bunker->verifications->sum('shrinkage_kg');
                $shrinkagePercent = $purchased > 0 ? ($shrinkage / $purchased) * 100 : 0;

                $totalBunkers++;
                $totalPurchased += $purchased;
                $totalSold += $sold;
                $totalAvailable += $available;
                $totalRevenue += $revenue;
                $totalCost += $cost;
                $totalProfit += $profit;
                $totalShrinkage += $shrinkage;

                $reportData[] = [
                    'id' => $bunker->id,
                    'name' => $bunker->name,
                    'location' => $bunker->location ?? 'No location',
                    'status' => $bunker->status,
                    'purchased' => (float) $purchased,
                    'sold' => (float) $sold,
                    'available' => (float) $available,
                    'cost_per_kg' => (float) $bunker->cost_per_kg,
                    'revenue' => (float) $revenue,
                    'profit' => (float) $profit,
                    'shrinkage_kg' => (float) $shrinkage,
                    'shrinkage_percentage' => (float) $shrinkagePercent,
                ];
            }

            return response()->json([
                'bunkers' => $reportData,
                'total_bunkers' => $totalBunkers,
                'total_purchased' => (float) $totalPurchased,
                'total_sold' => (float) $totalSold,
                'total_available' => (float) $totalAvailable,
                'total_revenue' => (float) $totalRevenue,
                'total_cost' => (float) $totalCost,
                'total_profit' => (float) $totalProfit,
                'total_shrinkage' => (float) $totalShrinkage,
                'filter_season' => $seasonName,
                'filter_location' => $location ?? 'All',
            ]);
        } catch (\Exception $e) {
            Log::error('Bunkers Report Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate bunkers report'], 500);
        }
    }

    /**
     * Seasonal Report – compare performance across seasons
     */
    public function seasonalReport(Request $request)
    {
        try {
            $seasons = Season::all();

            $reportData = [];
            $totalSeasons = 0;
            $totalBunkers = 0;
            $totalPurchased = 0;
            $totalSold = 0;
            $totalRevenue = 0;
            $totalCost = 0;
            $totalProfit = 0;
            $totalShrinkage = 0;

            foreach ($seasons as $season) {
                $bunkers = Bunker::where('season_id', $season->id)->get();

                $purchased = 0;
                $sold = 0;
                $revenue = 0;
                $cost = 0;
                $shrinkage = 0;

                foreach ($bunkers as $bunker) {
                    $purchased += $bunker->purchases->sum('weight_kg');
                    $sold += $bunker->saleItems->sum('weight_kg');
                    $revenue += $bunker->saleItems->sum('total_price');
                    $cost += $bunker->purchases->sum('cost') + $bunker->expenses->sum('amount');
                    $shrinkage += $bunker->verifications->sum('shrinkage_kg');
                }

                $profit = $revenue - $cost;
                $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
                $shrinkagePercent = $purchased > 0 ? ($shrinkage / $purchased) * 100 : 0;

                $totalSeasons++;
                $totalBunkers += $bunkers->count();
                $totalPurchased += $purchased;
                $totalSold += $sold;
                $totalRevenue += $revenue;
                $totalCost += $cost;
                $totalProfit += $profit;
                $totalShrinkage += $shrinkage;

                $reportData[] = [
                    'id' => $season->id,
                    'name' => $season->name,
                    'bunkers' => $bunkers->count(),
                    'purchased' => (float) $purchased,
                    'sold' => (float) $sold,
                    'revenue' => (float) $revenue,
                    'cost' => (float) $cost,
                    'profit' => (float) $profit,
                    'margin' => (float) $margin,
                    'shrinkage_kg' => (float) $shrinkage,
                    'shrinkage_percentage' => (float) $shrinkagePercent,
                ];
            }

            $maxProfit = !empty($reportData) ? max(array_column($reportData, 'profit')) : 0;

            return response()->json([
                'seasons' => $reportData,
                'total_seasons' => $totalSeasons,
                'total_bunkers' => $totalBunkers,
                'total_purchased' => (float) $totalPurchased,
                'total_sold' => (float) $totalSold,
                'total_revenue' => (float) $totalRevenue,
                'total_cost' => (float) $totalCost,
                'total_profit' => (float) $totalProfit,
                'total_shrinkage' => (float) $totalShrinkage,
                'max_profit' => (float) $maxProfit,
            ]);
        } catch (\Exception $e) {
            Log::error('Seasonal Report Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate seasonal report'], 500);
        }
    }

    /**
     * Export Bunkers Report as HTML (for print/PDF)
     */
    public function exportBunkersReport(Request $request)
{
    try {
        $seasonId = $request->get('season_id');
        $location = $request->get('location');

        $query = Bunker::with(['purchases', 'saleItems', 'expenses', 'verifications']);

        $seasonName = 'All';
        if ($seasonId && $seasonId !== 'all') {
            $season = Season::find($seasonId);
            $seasonName = $season ? $season->name : 'All';
            $query->where('season_id', $seasonId);
        }

        if ($location && $location !== 'all') {
            $query->where('location', $location);
        }

        $bunkers = $query->orderBy('name')->get();

        $reportData = [];
        $totalPurchased = 0;
        $totalSold = 0;
        $totalAvailable = 0;
        $totalRevenue = 0;
        $totalCost = 0;
        $totalProfit = 0;
        $totalShrinkage = 0;

        foreach ($bunkers as $bunker) {
            $purchased = $bunker->purchases->sum('weight_kg');
            $sold = $bunker->saleItems->sum('weight_kg');
            $revenue = $bunker->saleItems->sum('total_price');
            $cost = $bunker->purchases->sum('cost') + $bunker->expenses->sum('amount');
            $profit = $revenue - $cost;
            $available = $bunker->available_weight;
            $shrinkage = $bunker->verifications->sum('shrinkage_kg');
            $shrinkagePercent = $purchased > 0 ? ($shrinkage / $purchased) * 100 : 0;

            $totalPurchased += $purchased;
            $totalSold += $sold;
            $totalAvailable += $available;
            $totalRevenue += $revenue;
            $totalCost += $cost;
            $totalProfit += $profit;
            $totalShrinkage += $shrinkage;

            $reportData[] = [
                'name' => $bunker->name,
                'location' => $bunker->location ?? 'No location',
                'status' => $bunker->status,
                'purchased' => (float) $purchased,
                'sold' => (float) $sold,
                'available' => (float) $available,
                'cost_per_kg' => (float) $bunker->cost_per_kg,
                'revenue' => (float) $revenue,
                'profit' => (float) $profit,
                'shrinkage' => $shrinkage . ' kg (' . number_format($shrinkagePercent, 1) . '%)',
            ];
        }

        // ✅ Hardcoded logo URL (same as login page)
        $logo = 'https://i.ibb.co/xKG273Sm/JS-Final-logo.jpg';
        $businessName = Setting::where('key', 'business_name')->first();
        $businessName = $businessName ? $businessName->value : 'JS Silage & Wanda Factory';

        return view('reports.bunkers-list', [
            'bunkers' => $reportData,
            'total_bunkers' => $bunkers->count(),
            'total_purchased' => (float) $totalPurchased,
            'total_sold' => (float) $totalSold,
            'total_available' => (float) $totalAvailable,
            'total_revenue' => (float) $totalRevenue,
            'total_cost' => (float) $totalCost,
            'total_profit' => (float) $totalProfit,
            'total_shrinkage' => (float) $totalShrinkage,
            'logo' => $logo,
            'businessName' => $businessName,
            'filters' => [
                'season' => $seasonName,
                'location' => $location ?? 'All',
            ],
        ]);
    } catch (\Exception $e) {
        Log::error('Export Bunkers Report Error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to export bunkers report'], 500);
    }
}
    /**
     * Export Seasonal Report as HTML (for print/PDF)
     */
    public function exportSeasonalReport(Request $request)
{
    try {
        $seasons = Season::all();

        $reportData = [];
        $totalBunkers = 0;
        $totalPurchased = 0;
        $totalSold = 0;
        $totalRevenue = 0;
        $totalCost = 0;
        $totalProfit = 0;
        $totalShrinkage = 0;
        $maxProfit = 0;

        foreach ($seasons as $season) {
            $bunkers = Bunker::where('season_id', $season->id)->get();

            $purchased = 0;
            $sold = 0;
            $revenue = 0;
            $cost = 0;
            $shrinkage = 0;

            foreach ($bunkers as $bunker) {
                $purchased += $bunker->purchases->sum('weight_kg');
                $sold += $bunker->saleItems->sum('weight_kg');
                $revenue += $bunker->saleItems->sum('total_price');
                $cost += $bunker->purchases->sum('cost') + $bunker->expenses->sum('amount');
                $shrinkage += $bunker->verifications->sum('shrinkage_kg');
            }

            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
            $shrinkagePercent = $purchased > 0 ? ($shrinkage / $purchased) * 100 : 0;

            $totalBunkers += $bunkers->count();
            $totalPurchased += $purchased;
            $totalSold += $sold;
            $totalRevenue += $revenue;
            $totalCost += $cost;
            $totalProfit += $profit;
            $totalShrinkage += $shrinkage;

            if ($profit > $maxProfit) $maxProfit = $profit;

            $reportData[] = [
                'name' => $season->name,
                'bunkers' => $bunkers->count(),
                'purchased' => (float) $purchased,
                'sold' => (float) $sold,
                'revenue' => (float) $revenue,
                'cost' => (float) $cost,
                'profit' => (float) $profit,
                'margin' => (float) $margin,
                'shrinkage' => $shrinkage . ' kg (' . number_format($shrinkagePercent, 1) . '%)',
            ];
        }

        // ✅ Hardcoded logo URL (same as login page)
        $logo = 'https://i.ibb.co/xKG273Sm/JS-Final-logo.jpg';
        $businessName = Setting::where('key', 'business_name')->first();
        $businessName = $businessName ? $businessName->value : 'JS Silage & Wanda Factory';

        return view('reports.seasonal-report', [
            'seasons' => $reportData,
            'total_seasons' => $seasons->count(),
            'total_bunkers' => $totalBunkers,
            'total_purchased' => (float) $totalPurchased,
            'total_sold' => (float) $totalSold,
            'total_revenue' => (float) $totalRevenue,
            'total_cost' => (float) $totalCost,
            'total_profit' => (float) $totalProfit,
            'total_shrinkage' => (float) $totalShrinkage,
            'max_profit' => (float) $maxProfit,
            'logo' => $logo,
            'businessName' => $businessName,
        ]);
    } catch (\Exception $e) {
        Log::error('Export Seasonal Report Error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to export seasonal report'], 500);
    }
}
}