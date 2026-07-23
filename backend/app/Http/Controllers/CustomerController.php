<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $isActive = $request->get('is_active');

        $query = Customer::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($isActive !== null && $isActive !== 'all') {
            $query->where('is_active', $isActive === 'true');
        }

        $customers = $query->orderBy('name')->get();

        $customers->each(function ($customer) {
            $customer->total_sales = $customer->sales->sum('total_price');
            $customer->total_paid = $customer->sales->sum('paid_amount');
            $customer->total_due = $customer->sales->sum('due_amount');
        });

        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'delivery_address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = Customer::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'delivery_address' => $request->delivery_address,
            'contact_person' => $request->contact_person,
            'tax_id' => $request->tax_id,
            'payment_terms' => $request->payment_terms,
            'credit_limit' => $request->credit_limit,
            'notes' => $request->notes,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json($customer, 201);
    }

    public function show($id)
    {
        $customer = Customer::with(['sales' => function ($q) {
            $q->orderBy('date', 'desc');
        }])->findOrFail($id);

        $customer->total_sales = $customer->sales->sum('total_price');
        $customer->total_paid = $customer->sales->sum('paid_amount');
        $customer->total_due = $customer->sales->sum('due_amount');

        return response()->json($customer);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'delivery_address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer->update($request->all());
        return response()->json($customer);
    }

    public function destroy($id)
    {
        try {
            \Log::info('🔵 Force delete customer: ' . $id);
            
            // ✅ Disable foreign key checks temporarily
            \DB::statement('PRAGMA foreign_keys = OFF');
            
            // ✅ Delete the customer directly
            $deleted = \DB::delete("DELETE FROM customers WHERE id = ?", [$id]);
            
            // ✅ Re-enable foreign key checks
            \DB::statement('PRAGMA foreign_keys = ON');
            
            \Log::info('🔵 Deleted: ' . $deleted);
            
            if ($deleted) {
                return response()->json(['message' => 'Customer deleted successfully']);
            }
            
            return response()->json(['message' => 'Customer not found'], 404);
            
        } catch (\Exception $e) {
            \Log::error('❌ Customer delete error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function toggleActive($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->is_active = !$customer->is_active;
        $customer->save();

        return response()->json($customer);
    }

    public function getDuesSummary($id)
    {
        $customer = Customer::with(['sales'])->findOrFail($id);

        $totalSales = $customer->sales->sum('total_price');
        $totalPaid = $customer->sales->sum('paid_amount');
        $totalDues = $customer->sales->sum('due_amount');

        return response()->json([
            'customer' => $customer->name,
            'total_sales' => $totalSales,
            'total_paid' => $totalPaid,
            'total_due' => $totalDues,
            'sales' => $customer->sales->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'date' => $sale->date,
                    'total_price' => $sale->total_price,
                    'paid_amount' => $sale->paid_amount,
                    'due_amount' => $sale->due_amount,
                    'status' => $sale->due_amount > 0 ? 'Due' : 'Paid',
                ];
            }),
        ]);
    }
}