<?php

namespace App\Http\Controllers;

use App\Models\SaleItem;
use App\Models\Bunker;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $seasonId = $request->get('season_id');
        $bunkerId = $request->get('bunker_id');
        $location = $request->get('location');
        $customerId = $request->get('customer_id');
        $saleType = $request->get('sale_type');
        $paymentTypeId = $request->get('payment_type_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $search = $request->get('search');

        $query = SaleItem::with(['bunker', 'customer', 'season', 'paymentType']);

        if ($seasonId && $seasonId !== 'all') {
            $query->where('season_id', $seasonId);
        }

        if ($bunkerId && $bunkerId !== 'all') {
            $query->where('bunker_id', $bunkerId);
        }

        if ($location && $location !== 'all') {
            $query->whereHas('bunker', function ($q) use ($location) {
                $q->where('location', $location);
            });
        }

        if ($customerId && $customerId !== 'all') {
            $query->where('customer_id', $customerId);
        }

        if ($saleType && $saleType !== 'all') {
            $query->where('sale_type', $saleType);
        }

        if ($paymentTypeId && $paymentTypeId !== 'all') {
            $query->where('payment_type_id', $paymentTypeId);
        }

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('bunker', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $sales = $query->orderBy('date', 'desc')->get();

        return response()->json($sales);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bunker_id' => 'required|exists:bunkers,id',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_type_id' => 'nullable|exists:payment_types,id',
            'sale_type' => 'required|in:open,bags,bales',
            'weight_kg' => 'required|numeric|min:0.01',
            'units' => 'nullable|numeric|min:0',
            'price_per_kg' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'date' => 'required|date',
            'season_id' => 'nullable|exists:seasons,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $bunker = Bunker::find($request->bunker_id);
            if (!$bunker) {
                return response()->json(['message' => 'Bunker not found'], 404);
            }

            $available = $bunker->available_weight;
            if ($request->weight_kg > $available) {
                return response()->json([
                    'message' => 'Insufficient stock. Available: ' . $available . ' kg'
                ], 422);
            }

            $year = now()->year;
            $prefix = 'INV-' . $year . '-';

            $lastSale = SaleItem::where('invoice_number', 'like', $prefix . '%')
                ->orderBy('invoice_number', 'desc')
                ->first();

            if ($lastSale) {
                $lastNumber = (int) substr($lastSale->invoice_number, -4);
                $next = $lastNumber + 1;
            } else {
                $next = 1;
            }

            $invoiceNumber = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);

            // ✅ Calculate due_amount based on Net Total (total_price - discount)
            $discount = $request->discount ?? 0;
            $paidAmount = $request->paid_amount ?? 0;
            $netTotal = $request->total_price - $discount;
            $dueAmount = $netTotal - $paidAmount;
            if ($dueAmount < 0) $dueAmount = 0;

            $sale = SaleItem::create([
                'id' => Str::uuid(),
                'bunker_id' => $request->bunker_id,
                'customer_id' => $request->customer_id,
                'payment_type_id' => $request->payment_type_id ?? null,
                'invoice_number' => $invoiceNumber,
                'sale_type' => $request->sale_type,
                'weight_kg' => $request->weight_kg,
                'units' => $request->units ?? 0,
                'price_per_kg' => $request->price_per_kg,
                'total_price' => $request->total_price,
                'discount' => $discount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'date' => $request->date,
                'season_id' => $request->season_id,
                'cost_per_kg_at_sale' => $bunker->cost_per_kg ?? 0,
                'profit' => ($request->price_per_kg - ($bunker->cost_per_kg ?? 0)) * $request->weight_kg,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Sale saved successfully',
                'sale' => $sale,
                'invoice_number' => $invoiceNumber,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale store error: ' . $e->getMessage());
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        $sale = SaleItem::with(['bunker', 'customer', 'season', 'paymentType'])->find($id);
        if (!$sale) {
            return response()->json(['message' => 'Sale not found'], 404);
        }
        return response()->json($sale);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $sale = SaleItem::find($id);
        if (!$sale) {
            return response()->json(['message' => 'Sale not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'payment_type_id' => 'nullable|exists:payment_types,id',
            'weight_kg' => 'sometimes|numeric|min:0.01',
            'units' => 'sometimes|numeric|min:0',
            'price_per_kg' => 'sometimes|numeric|min:0',
            'total_price' => 'sometimes|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'date' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sale->update($request->only([
            'customer_id',
            'payment_type_id',
            'weight_kg',
            'units',
            'price_per_kg',
            'total_price',
            'discount',
            'paid_amount',
            'date'
        ]));

        // ✅ Recalculate due_amount after update
        $netTotal = $sale->total_price - ($sale->discount ?? 0);
        $sale->due_amount = $netTotal - $sale->paid_amount;
        if ($sale->due_amount < 0) $sale->due_amount = 0;
        $sale->save();

        return response()->json($sale);
    }

    public function destroy(string $id): JsonResponse
    {
        $sale = SaleItem::find($id);
        if (!$sale) {
            return response()->json(['message' => 'Sale not found'], 404);
        }
        $sale->delete();
        return response()->json(['message' => 'Sale deleted successfully']);
    }

    /**
     * Export sale invoice as HTML (for print/PDF)
     */
    public function exportInvoice($id)
    {
        try {
            $token = request()->get('token');
            if ($token) {
                request()->headers->set('Authorization', 'Bearer ' . $token);
            }

            $sale = SaleItem::with(['bunker', 'customer', 'season', 'paymentType'])->find($id);
            
            if (!$sale) {
                return response()->json(['message' => 'Sale not found'], 404);
            }

            $logo = Setting::where('key', 'logo')->first();
            $logo = $logo ? $logo->value : null;
            $businessName = Setting::where('key', 'business_name')->first();
            $businessName = $businessName ? $businessName->value : 'JS Silage & Wanda Factory';
            
            $invoiceFooter = Setting::where('key', 'invoice_footer')->first();
            $invoiceFooter = $invoiceFooter ? $invoiceFooter->value : 'Thank you for your business!';

            return view('reports.invoice', [
                'sale' => $sale,
                'logo' => $logo,
                'businessName' => $businessName,
                'invoiceFooter' => $invoiceFooter,
            ]);
        } catch (\Exception $e) {
            \Log::error('Invoice export error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate invoice: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Record payment for a sale
     */
    public function recordPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sale_id' => 'required|exists:sale_items,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sale = SaleItem::findOrFail($request->sale_id);
        
        $sale->paid_amount += $request->amount;
        // ✅ Calculate due_amount based on Net Total (total_price - discount)
        $netTotal = $sale->total_price - ($sale->discount ?? 0);
        $sale->due_amount = $netTotal - $sale->paid_amount;
        if ($sale->due_amount < 0) $sale->due_amount = 0;
        $sale->save();

        return response()->json([
            'message' => 'Payment recorded successfully',
            'sale' => $sale
        ]);
    }
   /**
 * Export sales list as PDF
 */
public function exportList(Request $request)
{
    try {
        $query = SaleItem::with(['customer', 'bunker', 'season', 'paymentType']);

        // Apply filters (same as index method)
        if ($request->filled('season_id') && $request->season_id !== 'all') {
            $query->where('season_id', $request->season_id);
        }
        if ($request->filled('bunker_id') && $request->bunker_id !== 'all') {
            $query->where('bunker_id', $request->bunker_id);
        }
        if ($request->filled('location') && $request->location !== 'all') {
            $query->whereHas('bunker', function ($q) use ($request) {
                $q->where('location', $request->location);
            });
        }
        if ($request->filled('customer_id') && $request->customer_id !== 'all') {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('sale_type') && $request->sale_type !== 'all') {
            $query->where('sale_type', $request->sale_type);
        }
        if ($request->filled('payment_type_id') && $request->payment_type_id !== 'all') {
            $query->where('payment_type_id', $request->payment_type_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('bunker', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $sales = $query->orderBy('date', 'desc')->get();

        // Build filters array for display
        $filters = [];
        if ($request->filled('season_id') && $request->season_id !== 'all') {
            $season = \App\Models\Season::find($request->season_id);
            $filters['season'] = $season ? $season->name : 'N/A';
        }
        if ($request->filled('location') && $request->location !== 'all') {
            $filters['location'] = $request->location;
        }
        if ($request->filled('bunker_id') && $request->bunker_id !== 'all') {
            $bunker = \App\Models\Bunker::find($request->bunker_id);
            $filters['bunker'] = $bunker ? $bunker->name : 'N/A';
        }
        if ($request->filled('customer_id') && $request->customer_id !== 'all') {
            $customer = \App\Models\Customer::find($request->customer_id);
            $filters['customer'] = $customer ? $customer->name : 'N/A';
        }
        if ($request->filled('sale_type') && $request->sale_type !== 'all') {
            $filters['sale_type'] = $request->sale_type;
        }
        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->date_from;
        }
        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->date_to;
        }

        $businessName = Setting::where('key', 'business_name')->first();
        $businessName = $businessName ? $businessName->value : 'JS Silage & Wanda Factory';

        return view('reports.sales-list', [
            'sales' => $sales,
            'filters' => $filters,
            'businessName' => $businessName,
        ]);
    } catch (\Exception $e) {
        \Log::error('Sales list export error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
    }
}
}