<?php

namespace App\Http\Controllers;

use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SeasonController extends Controller
{
    public function index()
    {
        $seasons = Season::orderBy('start_month')->get();
        return response()->json($seasons);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_month' => 'required|integer|min:1|max:12',
            'start_day' => 'required|integer|min:1|max:31',
            'end_month' => 'required|integer|min:1|max:12',
            'end_day' => 'required|integer|min:1|max:31',
            'color' => 'nullable|string|max:20',
            'is_current' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If this season is set as current, remove current from others
        if ($request->is_current) {
            Season::where('is_current', true)->update(['is_current' => false]);
        }

        $season = Season::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'start_month' => $request->start_month,
            'start_day' => $request->start_day,
            'end_month' => $request->end_month,
            'end_day' => $request->end_day,
            'color' => $request->color ?? '#3B82F6',
            'is_current' => $request->is_current ?? false,
        ]);

        return response()->json($season, 201);
    }

    public function update(Request $request, $id)
    {
        $season = Season::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'start_month' => 'sometimes|integer|min:1|max:12',
            'start_day' => 'sometimes|integer|min:1|max:31',
            'end_month' => 'sometimes|integer|min:1|max:12',
            'end_day' => 'sometimes|integer|min:1|max:31',
            'color' => 'nullable|string|max:20',
            'is_current' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If this season is set as current, remove current from others
        if ($request->has('is_current') && $request->is_current) {
            Season::where('is_current', true)->where('id', '!=', $id)->update(['is_current' => false]);
        }

        $season->update($request->all());

        return response()->json($season);
    }

    public function destroy($id)
    {
        $season = Season::findOrFail($id);

        // Check if any bunkers use this season
        $bunkers = \App\Models\Bunker::where('season_id', $id)->count();
        if ($bunkers > 0) {
            return response()->json([
                'message' => 'Cannot delete season that is in use by bunkers'
            ], 422);
        }

        $season->delete();
        return response()->json(['message' => 'Season deleted']);
    }

    public function setCurrent($id)
    {
        $season = Season::findOrFail($id);
        
        // Remove current from all other seasons
        Season::where('is_current', true)->update(['is_current' => false]);
        
        // Set this season as current
        $season->is_current = true;
        $season->save();

        return response()->json($season);
    }
}