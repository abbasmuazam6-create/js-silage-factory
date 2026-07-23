<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    // ========== EXPENSES ==========

    public function index(Request $request)
    {
        $seasonId = $request->get('season_id');
        $bunkerId = $request->get('bunker_id');
        $location = $request->get('location');
        $categoryId = $request->get('category_id');
        $search = $request->get('search');

        $query = Expense::with(['category', 'bunker', 'season']);

        // Season filter
        if ($seasonId && $seasonId !== 'all') {
            $query->where('season_id', $seasonId);
        }

        // Bunker filter
        if ($bunkerId && $bunkerId !== 'all') {
            $query->where('bunker_id', $bunkerId);
        }

        // ✅ Location filter (via bunker relationship)
        if ($location && $location !== 'all') {
            $query->whereHas('bunker', function ($q) use ($location) {
                $q->where('location', $location);
            });
        }

        // ✅ Category filter
        if ($categoryId && $categoryId !== 'all') {
            $query->where('expense_category_id', $categoryId);
        }

        // ✅ Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('bunker', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $expenses = $query->orderBy('date', 'desc')->get();

        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bunker_id' => 'required|exists:bunkers,id',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'season_id' => 'nullable|exists:seasons,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expense = Expense::create([
            'id' => Str::uuid(),
            'bunker_id' => $request->bunker_id,
            'expense_category_id' => $request->expense_category_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'season_id' => $request->season_id,
            'notes' => $request->notes,
        ]);

        // Recalculate bunker cost
        $bunker = \App\Models\Bunker::find($request->bunker_id);
        if ($bunker) {
            $bunker->recalculateCost();
        }

        return response()->json($expense, 201);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'expense_category_id' => 'sometimes|exists:expense_categories,id',
            'amount' => 'sometimes|numeric|min:0.01',
            'date' => 'sometimes|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expense->update($request->all());

        // Recalculate bunker cost
        if ($expense->bunker_id) {
            $bunker = \App\Models\Bunker::find($expense->bunker_id);
            if ($bunker) {
                $bunker->recalculateCost();
            }
        }

        return response()->json($expense);
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $bunkerId = $expense->bunker_id;
        $expense->delete();

        // Recalculate bunker cost
        if ($bunkerId) {
            $bunker = \App\Models\Bunker::find($bunkerId);
            if ($bunker) {
                $bunker->recalculateCost();
            }
        }

        return response()->json(['message' => 'Expense deleted']);
    }

    // ========== EXPENSE CATEGORIES ==========

    public function categories()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return response()->json($categories);
    }

    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'color' => 'nullable|string|max:20',
            'available_in' => 'nullable|in:silage,wanda,both',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = ExpenseCategory::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'color' => $request->color ?? '#6B7280',
            'available_in' => $request->available_in ?? 'both',
        ]);

        return response()->json($category, 201);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = ExpenseCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:expense_categories,name,' . $id,
            'color' => 'nullable|string|max:20',
            'available_in' => 'nullable|in:silage,wanda,both',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category->update($request->all());
        return response()->json($category);
    }

    public function deleteCategory($id)
    {
        $category = ExpenseCategory::findOrFail($id);

        // Check if any expenses use this category
        $expenses = Expense::where('expense_category_id', $id)->count();
        if ($expenses > 0) {
            return response()->json([
                'message' => 'Cannot delete category that is in use by expenses'
            ], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}