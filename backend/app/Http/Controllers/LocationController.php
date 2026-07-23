<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function index()
    {
        return response()->json(Location::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:locations,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location = Location::create(['name' => $request->name]);
        return response()->json($location, 201);
    }

    public function update(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:locations,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location->update(['name' => $request->name]);
        return response()->json($location);
    }

    public function destroy($id)
    {
        $location = Location::findOrFail($id);

        // Check if any bunker uses this location
        $used = \App\Models\Bunker::where('location', $location->name)->exists();
        if ($used) {
            return response()->json(['message' => 'Cannot delete location that is in use by bunkers'], 422);
        }

        $location->delete();
        return response()->json(['message' => 'Location deleted']);
    }
}