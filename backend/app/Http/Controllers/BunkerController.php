<?php

namespace App\Http\Controllers;

use App\Models\Bunker;
use App\Models\BunkerVerification;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BunkerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $seasonId = $request->get('season_id');
        $search = $request->get('search');

        $query = Bunker::with(['season']);

        if ($seasonId && $seasonId !== 'all') {
            $query->where('season_id', $seasonId);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
        }

        $bunkers = $query->orderBy('created_at', 'desc')->get();

        $bunkers->each(function ($bunker) {
            // Add summary fields to each bunker (using accessors)
            $bunker->available_weight = $bunker->available_weight;
            $bunker->total_purchased_kg = $bunker->total_purchased_kg;
            $bunker->total_sold_kg = $bunker->total_sold_kg;
            $bunker->total_cost = $bunker->total_cost;
            $bunker->cost_per_kg = $bunker->cost_per_kg;
            $bunker->total_expenses = $bunker->total_expenses;
            $bunker->total_revenue = $bunker->total_revenue;
            $bunker->total_profit = $bunker->total_profit;
        });

        return response()->json($bunkers);
    }

    public function exportReport($id)
{
    try {
        $bunker = Bunker::with([
            'purchases.supplier',
            'expenses.category',
            'saleItems',
            'verifications',
            'season',
        ])->find($id);

        if (!$bunker) {
            return response()->json(['message' => 'Bunker not found'], 404);
        }

        // ✅ Logo URL
        $logo = 'https://i.ibb.co/xKG273Sm/JS-Final-logo.jpg';

        // Business name from settings
        $businessName = Setting::where('key', 'business_name')->first();
        $businessName = $businessName ? $businessName->value : 'JS Silage & Wanda Factory';

        // Compute summary fields
        $bunker->total_purchased_kg = $bunker->purchases->sum('weight_kg');
        $bunker->total_sold_kg = $bunker->saleItems->sum('weight_kg');
        $bunker->total_revenue = $bunker->saleItems->sum('total_price');
        $bunker->total_purchase_cost = $bunker->purchases->sum('cost');
        $bunker->total_expenses = $bunker->expenses->sum('amount');
        $bunker->total_cost_with_expenses = $bunker->total_purchase_cost + $bunker->total_expenses;
        $bunker->total_profit = $bunker->total_revenue - $bunker->total_cost_with_expenses;
        $bunker->shrinkage_kg = $bunker->verifications->sum('shrinkage_kg');
        $bunker->shrinkage_percentage = $bunker->total_purchased_kg > 0 
            ? ($bunker->shrinkage_kg / $bunker->total_purchased_kg) * 100 
            : 0;

        return view('reports.bunker', [
            'bunker' => $bunker,
            'logo' => $logo,
            'businessName' => $businessName,
        ]);

    } catch (\Exception $e) {
        \Log::error('Bunker report error: ' . $e->getMessage());
        return response()->json(['message' => 'Report generation failed: ' . $e->getMessage()], 500);
    }
}

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'season_id' => 'required|exists:seasons,id',
            'threshold_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $bunker = Bunker::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'location' => $request->location,
                'season_id' => $request->season_id,
                'threshold_percentage' => $request->threshold_percentage ?? 10,
                'notes' => $request->notes,
                'status' => 'active',
                'is_sealed' => false,
            ]);

            return response()->json($bunker, 201);
        } catch (\Exception $e) {
            Log::error('Bunker store error: ' . $e->getMessage());
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $seasonId = $request->get('season_id');

        $bunker = Bunker::find($id);

        if (!$bunker) {
            return response()->json(['message' => 'Bunker not found'], 404);
        }

        // Load all relations
        $bunker->load([
            'season',
            'purchases' => function ($q) use ($seasonId) {
                if ($seasonId && $seasonId !== 'all') {
                    $q->where('season_id', $seasonId);
                }
                $q->with(['supplier']);
            },
            'expenses' => function ($q) {
                $q->with(['category']);
            },
            'saleItems' => function ($q) use ($seasonId) {
                if ($seasonId && $seasonId !== 'all') {
                    $q->where('season_id', $seasonId);
                }
                $q->with(['customer']);
            },
            'verifications' => function ($q) use ($seasonId) {
                if ($seasonId && $seasonId !== 'all') {
                    $q->where('season_id', $seasonId);
                }
            }
        ]);

        // After loading relations:
        $shrinkageKg = $bunker->verifications->sum('shrinkage_kg');
        $totalPurchased = $bunker->purchases->sum('weight_kg');
        $shrinkagePercent = $totalPurchased > 0 ? ($shrinkageKg / $totalPurchased) * 100 : 0;

        $bunker->total_purchased_kg = $totalPurchased;
        $bunker->total_sold_kg = $bunker->saleItems->sum('weight_kg');
        $bunker->total_revenue = $bunker->saleItems->sum('total_price');
        $bunker->total_purchase_cost = $bunker->purchases->sum('cost');
        $bunker->total_expenses = $bunker->expenses->sum('amount');
        $bunker->total_cost = $bunker->total_purchase_cost + $bunker->total_expenses;
        $bunker->total_cost_with_expenses = $bunker->total_cost;
        $bunker->total_profit = $bunker->total_revenue - $bunker->total_cost;
        $bunker->available_weight = max(0, $bunker->total_purchased_kg - $bunker->total_sold_kg - $shrinkageKg);
        $bunker->shrinkage_kg = $shrinkageKg;
        $bunker->shrinkage_percentage = $shrinkagePercent;
        
        // ✅ FIX: Cost per KG should include expenses (Total Cost / Total Purchased)
        $bunker->cost_per_kg = $bunker->total_purchased_kg > 0 
            ? $bunker->total_cost / $bunker->total_purchased_kg 
            : 0;

        // Group purchases
        $groupedPurchases = $bunker->purchases->groupBy(function ($purchase) {
            return $purchase->supplier_id . '|' . $purchase->purchase_date;
        })->map(function ($items) {
            $first = $items->first();
            return [
                'supplier_id' => $first->supplier_id,
                'supplier_name' => $first->supplier?->name ?? 'Unknown',
                'date' => $first->purchase_date,
                'total_kg' => $items->sum('weight_kg'),
                'total_cost' => $items->sum('cost'),
                'cost_per_kg' => $items->sum('weight_kg') > 0 ? $items->sum('cost') / $items->sum('weight_kg') : 0,
                'items' => $items,
                'count' => $items->count(),
            ];
        })->values();

        $bunker->grouped_purchases = $groupedPurchases;

        return response()->json($bunker);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $bunker = Bunker::find($id);
        if (!$bunker) {
            return response()->json(['message' => 'Bunker not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:255',
            'threshold_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bunker->update($request->only(['name', 'location', 'threshold_percentage', 'notes']));
        return response()->json($bunker);
    }

    public function destroy(string $id): JsonResponse
    {
        $bunker = Bunker::find($id);
        if (!$bunker) {
            return response()->json(['message' => 'Bunker not found'], 404);
        }

        if ($bunker->saleItems()->count() > 0) {
            return response()->json(['message' => 'Cannot delete bunker with sales records'], 422);
        }

        $bunker->purchases()->delete();
        $bunker->expenses()->delete();
        $bunker->verifications()->delete();
        $bunker->delete();

        return response()->json(['message' => 'Bunker deleted']);
    }

    public function getLocations()
    {
        $locations = Bunker::select('location')
            ->whereNotNull('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');
        return response()->json($locations);
    }

    public function markEmpty($id)
    {
        $bunker = Bunker::with(['purchases', 'expenses', 'saleItems', 'verifications'])->find($id);
        if (!$bunker) {
            return response()->json(['message' => 'Bunker not found'], 404);
        }
        if ($bunker->status === 'empty') {
            return response()->json(['message' => 'Bunker already empty'], 422);
        }

        $shrinkageKg = $bunker->available_weight;
        $totalPurchased = $bunker->total_purchased_kg;
        $shrinkagePercent = $totalPurchased > 0 ? ($shrinkageKg / $totalPurchased) * 100 : 0;

        // Log the values for debugging
        Log::info('MarkEmpty: shrinkageKg=' . $shrinkageKg . ', totalPurchased=' . $totalPurchased . ', percent=' . $shrinkagePercent);

        DB::beginTransaction();
        try {
            $verificationData = [
                'id' => Str::uuid(),
                'bunker_id' => $bunker->id,
                'recorded_remaining_kg' => $shrinkageKg,
                'actual_remaining_kg' => 0,
                'shrinkage_kg' => $shrinkageKg,
                'shrinkage_percentage' => $shrinkagePercent,
                'date_verified' => now(),
                'season_id' => $bunker->season_id,
                'notes' => null,
            ];

            if (auth()->check()) {
                $verificationData['verified_by'] = auth()->id();
            } else {
                $verificationData['verified_by'] = null;
            }

            $verification = $bunker->verifications()->create($verificationData);

            Log::info('Verification created with ID: ' . $verification->id);

            $bunker->status = 'empty';
            $bunker->save();

            DB::commit();

            // Reload all relations
            $bunker->load(['purchases', 'expenses', 'saleItems', 'verifications', 'season']);

            // Manually compute all summary fields
            $shrinkageKg = $bunker->verifications->sum('shrinkage_kg');
            $totalPurchased = $bunker->purchases->sum('weight_kg');
            $shrinkagePercent = $totalPurchased > 0 ? ($shrinkageKg / $totalPurchased) * 100 : 0;

            $bunker->total_purchased_kg = $totalPurchased;
            $bunker->total_sold_kg = $bunker->saleItems->sum('weight_kg');
            $bunker->total_revenue = $bunker->saleItems->sum('total_price');
            $bunker->total_purchase_cost = $bunker->purchases->sum('cost');
            $bunker->total_expenses = $bunker->expenses->sum('amount');
            $bunker->total_cost = $bunker->total_purchase_cost + $bunker->total_expenses;
            $bunker->total_cost_with_expenses = $bunker->total_cost;
            $bunker->total_profit = $bunker->total_revenue - $bunker->total_cost;
            $bunker->available_weight = max(0, $bunker->total_purchased_kg - $bunker->total_sold_kg - $shrinkageKg);
            $bunker->shrinkage_kg = $shrinkageKg;
            $bunker->shrinkage_percentage = $shrinkagePercent;
            $bunker->cost_per_kg = $bunker->total_purchased_kg > 0 ? $bunker->total_purchase_cost / $bunker->total_purchased_kg : 0;

            return response()->json($bunker);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark empty error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function reopen(string $id): JsonResponse
    {
        $bunker = Bunker::find($id);
        if (!$bunker) {
            return response()->json(['message' => 'Bunker not found'], 404);
        }

        if ($bunker->status !== 'empty') {
            return response()->json(['message' => 'Bunker is not empty'], 422);
        }

        $bunker->status = 'active';
        $bunker->save();

        return response()->json(['message' => 'Bunker reopened']);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $ids = $request->ids;
        if (empty($ids)) {
            return response()->json(['message' => 'No bunkers selected'], 422);
        }

        $deleted = 0;
        foreach ($ids as $id) {
            $bunker = Bunker::find($id);
            if ($bunker && $bunker->saleItems()->count() === 0) {
                $bunker->purchases()->delete();
                $bunker->expenses()->delete();
                $bunker->verifications()->delete();
                $bunker->delete();
                $deleted++;
            }
        }

        return response()->json(['message' => "Deleted {$deleted} bunker(s)"]);
    }
}