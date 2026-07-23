<?php

namespace App\Http\Controllers;

use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentTypeController extends Controller
{
    public function index()
    {
        $paymentTypes = PaymentType::orderBy('name')->get();
        return response()->json($paymentTypes);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:payment_types,name',
            'code' => 'nullable|string|max:50|unique:payment_types,code',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $paymentType = PaymentType::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'code' => $request->code ?? strtoupper(substr($request->name, 0, 3)),
            'color' => $request->color ?? '#6B7280',
            'is_active' => $request->is_active ?? true,
            'description' => $request->description,
        ]);

        return response()->json($paymentType, 201);
    }

    public function update(Request $request, $id)
    {
        $paymentType = PaymentType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:payment_types,name,' . $id,
            'code' => 'nullable|string|max:50|unique:payment_types,code,' . $id,
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $paymentType->update($request->all());
        return response()->json($paymentType);
    }

    public function destroy($id)
    {
        $paymentType = PaymentType::findOrFail($id);
        $paymentType->delete();
        return response()->json(['message' => 'Payment type deleted']);
    }

    public function toggleActive($id)
    {
        $paymentType = PaymentType::findOrFail($id);
        $paymentType->is_active = !$paymentType->is_active;
        $paymentType->save();

        return response()->json($paymentType);
    }
}