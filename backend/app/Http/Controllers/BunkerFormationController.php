<?php

namespace App\Http\Controllers;

use App\Models\Bunker;
use App\Models\BunkerAssignment;
use App\Models\SilagePurchase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BunkerFormationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'season_id' => 'required|exists:seasons,id',
            'assignments' => 'required|array|min:1',
            'assignments.*.purchase_id' => 'required|exists:silage_purchases,id',
            'assignments.*.assigned_kg' => 'required|numeric|min:0.01',
            'assignments.*.source' => 'nullable|string|max:255',
            'assignments.*.date_assigned' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Create the bunker with is_sealed = true
            $bunker = Bunker::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'location' => $request->location,
                'season_id' => $request->season_id,
                'is_sealed' => true,
                'status' => 'active',
                'threshold_percentage' => 10,
            ]);

            // 2. Create assignments
            foreach ($request->assignments as $assignment) {
                // Optionally check if purchase has enough remaining kg
                // For simplicity, we assume user assigns only up to available (we can add check later)

                BunkerAssignment::create([
                    'id' => Str::uuid(),
                    'bunker_id' => $bunker->id,
                    'silage_purchase_id' => $assignment['purchase_id'],
                    'assigned_kg' => $assignment['assigned_kg'],
                    'source' => $assignment['source'] ?? null,
                    'date_assigned' => $assignment['date_assigned'],
                    'season_id' => $request->season_id,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Bunker formed successfully',
                'bunker' => $bunker,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to form bunker: ' . $e->getMessage()], 500);
        }
    }
}