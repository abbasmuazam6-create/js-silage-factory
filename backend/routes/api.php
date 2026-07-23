<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BunkerController;
use App\Http\Controllers\BunkerFormationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SilagePurchaseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Public route for logo (no authentication needed)
Route::get('/settings/logo', [SettingsController::class, 'getLogo']);
Route::post('/bunkers/formation', [BunkerFormationController::class, 'store']);
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/sales/export-list', [SaleController::class, 'exportList']);
Route::get('/purchases/export-list', [SilagePurchaseController::class, 'exportList']);



// Authenticated routes
Route::middleware('token.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/seasons', [SeasonController::class, 'index']);

    // ========== BUNKERS ==========
    Route::prefix('bunkers')->group(function () {
        Route::get('/', [BunkerController::class, 'index']);
        Route::post('/', [BunkerController::class, 'store']);
        Route::get('{id}', [BunkerController::class, 'show']);
        Route::put('{id}', [BunkerController::class, 'update']);
        Route::delete('{id}', [BunkerController::class, 'destroy']);
        Route::post('{id}/mark-empty', [BunkerController::class, 'markEmpty']);
        Route::post('{id}/reopen', [BunkerController::class, 'reopen']);
        Route::post('/bulk-delete', [BunkerController::class, 'bulkDelete']);
        Route::get('{id}/report', [BunkerController::class, 'exportReport']);
        Route::get('/locations', [BunkerController::class, 'getLocations']); // ✅ Keep this if used
        Route::get('{id}/report', [BunkerController::class, 'exportReport']);
    });

    // ========== SILAGE PURCHASES ==========
    Route::prefix('silage-purchases')->group(function () {
        Route::get('/', [SilagePurchaseController::class, 'index']);
        Route::post('/', [SilagePurchaseController::class, 'store']);
        Route::get('{id}', [SilagePurchaseController::class, 'show']);
        Route::put('{id}', [SilagePurchaseController::class, 'update']);
        Route::delete('{id}', [SilagePurchaseController::class, 'destroy']);
        Route::post('/bulk-delete', [SilagePurchaseController::class, 'bulkDelete']);
       Route::get('/purchases/export-list', [SilagePurchaseController::class, 'exportList']);
    });

// ===== SUPPLIERS =====
Route::prefix('suppliers')->group(function () {
    Route::get('/', [SupplierController::class, 'index']);
    Route::post('/', [SupplierController::class, 'store']);
    Route::get('{id}', [SupplierController::class, 'show']);
    Route::put('{id}', [SupplierController::class, 'update']);
    Route::delete('{id}', [SupplierController::class, 'destroy']);
    Route::post('{id}/toggle-active', [SupplierController::class, 'toggleActive']);
});

    // ========== EXPENSES ==========
    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
        Route::put('{id}', [ExpenseController::class, 'update']);
        Route::delete('{id}', [ExpenseController::class, 'destroy']);
    });

    // ========== EXPENSE CATEGORIES ==========
    Route::prefix('expense-categories')->group(function () {
        Route::get('/', [ExpenseController::class, 'categories']);
        Route::post('/', [ExpenseController::class, 'storeCategory']);
    });

// ===== CUSTOMERS =====
Route::prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'index']);
    Route::post('/', [CustomerController::class, 'store']);
    Route::get('{id}', [CustomerController::class, 'show']);
    Route::put('{id}', [CustomerController::class, 'update']);
    Route::delete('{id}', [CustomerController::class, 'destroy']); // ✅ This must exist
    Route::post('{id}/toggle-active', [CustomerController::class, 'toggleActive']);
    Route::get('{id}/dues-summary', [CustomerController::class, 'getDuesSummary']);
});
    // ========== SALES ==========
    Route::prefix('sales')->group(function () {
        Route::get('/', [SaleController::class, 'index']);
        Route::post('/', [SaleController::class, 'store']);
        Route::get('{id}/invoice', [SaleController::class, 'exportInvoice']);
        Route::get('{id}', [SaleController::class, 'show']);
        Route::put('{id}', [SaleController::class, 'update']);
        Route::delete('{id}', [SaleController::class, 'destroy']);
        Route::post('/sales/payment', [SaleController::class, 'recordPayment']);
            });

    // ========== SETTINGS ========== ✅ MOVED HERE
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::post('/', [SettingsController::class, 'update']);
        Route::post('/upload-logo', [SettingsController::class, 'uploadLogo']);
    });

    // ========== LOCATIONS ========== ✅ MOVED HERE
    Route::prefix('locations')->group(function () {
        Route::get('/', [LocationController::class, 'index']);
        Route::post('/', [LocationController::class, 'store']);
        Route::put('{id}', [LocationController::class, 'update']);
        Route::delete('{id}', [LocationController::class, 'destroy']);
    });
    // ===== SEASONS =====
Route::prefix('seasons')->group(function () {
    Route::get('/', [SeasonController::class, 'index']);
    Route::post('/', [SeasonController::class, 'store']);
    Route::put('{id}', [SeasonController::class, 'update']);
    Route::delete('{id}', [SeasonController::class, 'destroy']);
    Route::post('{id}/set-current', [SeasonController::class, 'setCurrent']);
    });
// ===== EXPENSE CATEGORIES =====
Route::prefix('expense-categories')->group(function () {
    Route::get('/', [ExpenseController::class, 'categories']);
    Route::post('/', [ExpenseController::class, 'storeCategory']);
    Route::put('{id}', [ExpenseController::class, 'updateCategory']);
    Route::delete('{id}', [ExpenseController::class, 'deleteCategory']);
    });
// ===== PAYMENT TYPES =====
Route::prefix('payment-types')->group(function () {
    Route::get('/', [PaymentTypeController::class, 'index']);
    Route::post('/', [PaymentTypeController::class, 'store']);
    Route::put('{id}', [PaymentTypeController::class, 'update']);
    Route::delete('{id}', [PaymentTypeController::class, 'destroy']);
    Route::post('{id}/toggle-active', [PaymentTypeController::class, 'toggleActive']);
});
// ===== REPORTS =====
Route::prefix('reports')->group(function () {
    Route::get('/bunkers', [ReportController::class, 'bunkersReport']);
    Route::get('/seasonal', [ReportController::class, 'seasonalReport']);
    Route::get('/bunkers/export', [ReportController::class, 'exportBunkersReport']);
    Route::get('/seasonal/export', [ReportController::class, 'exportSeasonalReport']);
});
// ===== USER MANAGEMENT =====
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::put('{id}', [UserController::class, 'update']);
    Route::delete('{id}', [UserController::class, 'destroy']);
    Route::post('{id}/toggle-active', [UserController::class, 'toggleActive']);
});
});