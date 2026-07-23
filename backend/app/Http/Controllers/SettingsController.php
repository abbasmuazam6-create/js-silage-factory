<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Get all settings (authenticated)
     */
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return response()->json($settings);
    }

    /**
     * Public endpoint to get logo only (no authentication)
     */
    public function getLogo()
    {
        $logo = Setting::where('key', 'logo')->first();
        return response()->json(['logo' => $logo ? $logo->value : null]);
    }

    /**
     * Update settings (authenticated)
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'nullable|string|max:255',
            'business_address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'currency_symbol' => 'nullable|string|max:10',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'invoice_prefix' => 'nullable|string|max:50',
            'date_format' => 'nullable|string|max:20',
            'invoice_footer' => 'nullable|string',
            'logo' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->all() as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json(['message' => 'Settings saved successfully']);
    }

    /**
     * Upload logo (authenticated)
     */
    public function uploadLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $path = $request->file('logo')->store('logos', 'public');
        $url = asset('storage/' . $path);

        Setting::updateOrCreate(
            ['key' => 'logo'],
            ['value' => $url]
        );

        return response()->json(['logo_url' => $url]);
    }
}