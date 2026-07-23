<?php

namespace App\Http\Controllers;

use App\Models\Bunker;
use App\Models\Expense;
use App\Models\SaleItem;
use App\Models\SilagePurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $seasonId = $request->get('season_id', 'all');

        $locations = Bunker::select('location')
            ->whereNotNull('location')
            ->distinct()
            ->pluck('location')
            ->toArray();

        if (empty($locations)) {
            return response()->json([
                'total_bunkers' => 0,
                'active_bunkers' => 0,
                'empty_bunkers' => 0,
                'total_purchased_kg' => 0,
                'total_sold_kg' => 0,
                'total_available_kg' => 0,
                'locations' => [],
            ]);
        }

        $locationData = [];
        $totalBunkers = 0;
        $totalActive = 0;
        $totalEmpty = 0;
        $totalPurchased = 0;
        $totalSold = 0;
        $totalAvailable = 0;

        foreach ($locations as $location) {
            $bunkersQuery = Bunker::where('location', $location);
            if ($seasonId !== 'all') {
                $bunkersQuery->where('season_id', $seasonId);
            }
            $bunkers = $bunkersQuery->get();

            $active = $bunkers->where('status', 'active')->count();
            $empty = $bunkers->where('status', 'empty')->count();
            $total = $bunkers->count();

            $totalBunkers += $total;
            $totalActive += $active;
            $totalEmpty += $empty;

            $bunkerIds = $bunkers->pluck('id')->toArray();

            if (empty($bunkerIds)) {
                $locationData[] = [
                    'name' => $location,
                    'active_bunkers' => 0,
                    'empty_bunkers' => 0,
                    'total_bunkers' => 0,
                    'shrinkage_kg' => 0,
                    'shrinkage_percentage' => 0,
                    'purchased_kg' => 0,
                    'sold_kg' => 0,
                    'available_kg' => 0,
                    'expenses' => 0,
                ];
                continue;
            }

            // Purchases
            $purchasedQuery = SilagePurchase::whereIn('bunker_id', $bunkerIds);
            if ($seasonId !== 'all') {
                $purchasedQuery->where('season_id', $seasonId);
            }
            $purchasedKg = $purchasedQuery->sum('weight_kg');

            // Sales
            $salesQuery = SaleItem::whereIn('bunker_id', $bunkerIds);
            if ($seasonId !== 'all') {
                $salesQuery->where('season_id', $seasonId);
            }
            $soldKg = $salesQuery->sum('weight_kg');

            // Shrinkage
            $shrinkageQuery = DB::table('bunker_verifications')
                ->whereIn('bunker_id', $bunkerIds);
            if ($seasonId !== 'all') {
                $shrinkageQuery->where('season_id', $seasonId);
            }
            $shrinkageKg = $shrinkageQuery->sum('shrinkage_kg');
            $shrinkagePercent = $purchasedKg > 0 ? ($shrinkageKg / $purchasedKg) * 100 : 0;

            // Expenses
            $expensesQuery = Expense::whereIn('bunker_id', $bunkerIds);
            if ($seasonId !== 'all') {
                $expensesQuery->where('season_id', $seasonId);
            }
            $expenses = $expensesQuery->sum('amount');

            $availableKg = max(0, $purchasedKg - $soldKg - $shrinkageKg);

            $totalPurchased += $purchasedKg;
            $totalSold += $soldKg;
            $totalAvailable += $availableKg;

            $locationData[] = [
                'name' => $location,
                'active_bunkers' => $active,
                'empty_bunkers' => $empty,
                'total_bunkers' => $total,
                'shrinkage_kg' => round($shrinkageKg, 2),
                'shrinkage_percentage' => round($shrinkagePercent, 2),
                'purchased_kg' => round($purchasedKg, 2),
                'sold_kg' => round($soldKg, 2),
                'available_kg' => round($availableKg, 2),
                'expenses' => round($expenses, 2),
            ];
        }

        return response()->json([
            'total_bunkers' => $totalBunkers,
            'active_bunkers' => $totalActive,
            'empty_bunkers' => $totalEmpty,
            'total_purchased_kg' => round($totalPurchased, 2),
            'total_sold_kg' => round($totalSold, 2),
            'total_available_kg' => round($totalAvailable, 2),
            'locations' => $locationData,
        ]);
    }
}