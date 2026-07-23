<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Helper function to check if index exists
        $hasIndex = function($table, $indexName) {
            $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
            return count($result) > 0;
        };

        // ===== BUNKERS =====
        Schema::table('bunkers', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('bunkers', 'bunkers_location_id_index')) {
                $table->index('location_id');
            }
            if (!$hasIndex('bunkers', 'bunkers_season_id_index')) {
                $table->index('season_id');
            }
            if (!$hasIndex('bunkers', 'bunkers_status_index')) {
                $table->index('status');
            }
            if (!$hasIndex('bunkers', 'bunkers_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ===== SILAGE PURCHASES =====
        Schema::table('silage_purchases', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('silage_purchases', 'silage_purchases_bunker_id_index')) {
                $table->index('bunker_id');
            }
            if (!$hasIndex('silage_purchases', 'silage_purchases_supplier_id_index')) {
                $table->index('supplier_id');
            }
            if (!$hasIndex('silage_purchases', 'silage_purchases_season_id_index')) {
                $table->index('season_id');
            }
            if (!$hasIndex('silage_purchases', 'silage_purchases_purchase_date_index')) {
                $table->index('purchase_date');
            }
            if (!$hasIndex('silage_purchases', 'silage_purchases_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ===== SALE ITEMS =====
        Schema::table('sale_items', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('sale_items', 'sale_items_bunker_id_index')) {
                $table->index('bunker_id');
            }
            if (!$hasIndex('sale_items', 'sale_items_customer_id_index')) {
                $table->index('customer_id');
            }
            if (!$hasIndex('sale_items', 'sale_items_season_id_index')) {
                $table->index('season_id');
            }
            if (!$hasIndex('sale_items', 'sale_items_sale_type_index')) {
                $table->index('sale_type');
            }
            if (!$hasIndex('sale_items', 'sale_items_payment_type_id_index')) {
                $table->index('payment_type_id');
            }
            if (!$hasIndex('sale_items', 'sale_items_date_index')) {
                $table->index('date');
            }
            if (!$hasIndex('sale_items', 'sale_items_invoice_number_index')) {
                $table->index('invoice_number');
            }
            if (!$hasIndex('sale_items', 'sale_items_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ===== EXPENSES =====
        Schema::table('expenses', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('expenses', 'expenses_bunker_id_index')) {
                $table->index('bunker_id');
            }
            if (!$hasIndex('expenses', 'expenses_category_id_index')) {
                $table->index('category_id');
            }
            if (!$hasIndex('expenses', 'expenses_date_index')) {
                $table->index('date');
            }
            if (!$hasIndex('expenses', 'expenses_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ===== EXPENSE CATEGORIES =====
        Schema::table('expense_categories', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('expense_categories', 'expense_categories_available_in_index')) {
                $table->index('available_in');
            }
            if (!$hasIndex('expense_categories', 'expense_categories_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ===== CUSTOMERS =====
        Schema::table('customers', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('customers', 'customers_is_active_index')) {
                $table->index('is_active');
            }
            if (!$hasIndex('customers', 'customers_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ===== SUPPLIERS =====
        Schema::table('suppliers', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('suppliers', 'suppliers_is_active_index')) {
                $table->index('is_active');
            }
            if (!$hasIndex('suppliers', 'suppliers_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ===== LOCATIONS =====
        Schema::table('locations', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('locations', 'locations_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ===== SEASONS =====
        Schema::table('seasons', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('seasons', 'seasons_is_current_index')) {
                $table->index('is_current');
            }
            if (!$hasIndex('seasons', 'seasons_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ===== USERS =====
        Schema::table('users', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('users', 'users_role_index')) {
                $table->index('role');
            }
            if (!$hasIndex('users', 'users_is_active_index')) {
                $table->index('is_active');
            }
            if (!$hasIndex('users', 'users_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ===== PAYMENT TYPES =====
        Schema::table('payment_types', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('payment_types', 'payment_types_is_active_index')) {
                $table->index('is_active');
            }
            if (!$hasIndex('payment_types', 'payment_types_created_at_index')) {
                $table->index('created_at');
            }
        });

        // ===== BUNKER VERIFICATIONS =====
        Schema::table('bunker_verifications', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('bunker_verifications', 'bunker_verifications_bunker_id_index')) {
                $table->index('bunker_id');
            }
            if (!$hasIndex('bunker_verifications', 'bunker_verifications_verified_at_index')) {
                $table->index('verified_at');
            }
            if (!$hasIndex('bunker_verifications', 'bunker_verifications_created_at_index')) {
                $table->index('created_at');
            }
        });
    }

    public function down()
    {
        // Helper function to check if index exists
        $hasIndex = function($table, $indexName) {
            $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
            return count($result) > 0;
        };

        // ===== BUNKERS =====
        Schema::table('bunkers', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('bunkers', 'bunkers_location_id_index')) {
                $table->dropIndex('bunkers_location_id_index');
            }
            if ($hasIndex('bunkers', 'bunkers_season_id_index')) {
                $table->dropIndex('bunkers_season_id_index');
            }
            if ($hasIndex('bunkers', 'bunkers_status_index')) {
                $table->dropIndex('bunkers_status_index');
            }
            if ($hasIndex('bunkers', 'bunkers_created_at_index')) {
                $table->dropIndex('bunkers_created_at_index');
            }
        });

        // ===== SILAGE PURCHASES =====
        Schema::table('silage_purchases', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('silage_purchases', 'silage_purchases_bunker_id_index')) {
                $table->dropIndex('silage_purchases_bunker_id_index');
            }
            if ($hasIndex('silage_purchases', 'silage_purchases_supplier_id_index')) {
                $table->dropIndex('silage_purchases_supplier_id_index');
            }
            if ($hasIndex('silage_purchases', 'silage_purchases_season_id_index')) {
                $table->dropIndex('silage_purchases_season_id_index');
            }
            if ($hasIndex('silage_purchases', 'silage_purchases_purchase_date_index')) {
                $table->dropIndex('silage_purchases_purchase_date_index');
            }
            if ($hasIndex('silage_purchases', 'silage_purchases_created_at_index')) {
                $table->dropIndex('silage_purchases_created_at_index');
            }
        });

        // ===== SALE ITEMS =====
        Schema::table('sale_items', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('sale_items', 'sale_items_bunker_id_index')) {
                $table->dropIndex('sale_items_bunker_id_index');
            }
            if ($hasIndex('sale_items', 'sale_items_customer_id_index')) {
                $table->dropIndex('sale_items_customer_id_index');
            }
            if ($hasIndex('sale_items', 'sale_items_season_id_index')) {
                $table->dropIndex('sale_items_season_id_index');
            }
            if ($hasIndex('sale_items', 'sale_items_sale_type_index')) {
                $table->dropIndex('sale_items_sale_type_index');
            }
            if ($hasIndex('sale_items', 'sale_items_payment_type_id_index')) {
                $table->dropIndex('sale_items_payment_type_id_index');
            }
            if ($hasIndex('sale_items', 'sale_items_date_index')) {
                $table->dropIndex('sale_items_date_index');
            }
            if ($hasIndex('sale_items', 'sale_items_invoice_number_index')) {
                $table->dropIndex('sale_items_invoice_number_index');
            }
            if ($hasIndex('sale_items', 'sale_items_created_at_index')) {
                $table->dropIndex('sale_items_created_at_index');
            }
        });

        // ===== EXPENSES =====
        Schema::table('expenses', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('expenses', 'expenses_bunker_id_index')) {
                $table->dropIndex('expenses_bunker_id_index');
            }
            if ($hasIndex('expenses', 'expenses_category_id_index')) {
                $table->dropIndex('expenses_category_id_index');
            }
            if ($hasIndex('expenses', 'expenses_date_index')) {
                $table->dropIndex('expenses_date_index');
            }
            if ($hasIndex('expenses', 'expenses_created_at_index')) {
                $table->dropIndex('expenses_created_at_index');
            }
        });

        // ===== EXPENSE CATEGORIES =====
        Schema::table('expense_categories', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('expense_categories', 'expense_categories_available_in_index')) {
                $table->dropIndex('expense_categories_available_in_index');
            }
            if ($hasIndex('expense_categories', 'expense_categories_created_at_index')) {
                $table->dropIndex('expense_categories_created_at_index');
            }
        });

        // ===== CUSTOMERS =====
        Schema::table('customers', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('customers', 'customers_is_active_index')) {
                $table->dropIndex('customers_is_active_index');
            }
            if ($hasIndex('customers', 'customers_created_at_index')) {
                $table->dropIndex('customers_created_at_index');
            }
        });

        // ===== SUPPLIERS =====
        Schema::table('suppliers', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('suppliers', 'suppliers_is_active_index')) {
                $table->dropIndex('suppliers_is_active_index');
            }
            if ($hasIndex('suppliers', 'suppliers_created_at_index')) {
                $table->dropIndex('suppliers_created_at_index');
            }
        });

        // ===== LOCATIONS =====
        Schema::table('locations', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('locations', 'locations_created_at_index')) {
                $table->dropIndex('locations_created_at_index');
            }
        });

        // ===== SEASONS =====
        Schema::table('seasons', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('seasons', 'seasons_is_current_index')) {
                $table->dropIndex('seasons_is_current_index');
            }
            if ($hasIndex('seasons', 'seasons_created_at_index')) {
                $table->dropIndex('seasons_created_at_index');
            }
        });

        // ===== USERS =====
        Schema::table('users', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('users', 'users_role_index')) {
                $table->dropIndex('users_role_index');
            }
            if ($hasIndex('users', 'users_is_active_index')) {
                $table->dropIndex('users_is_active_index');
            }
            if ($hasIndex('users', 'users_created_at_index')) {
                $table->dropIndex('users_created_at_index');
            }
        });

        // ===== PAYMENT TYPES =====
        Schema::table('payment_types', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('payment_types', 'payment_types_is_active_index')) {
                $table->dropIndex('payment_types_is_active_index');
            }
            if ($hasIndex('payment_types', 'payment_types_created_at_index')) {
                $table->dropIndex('payment_types_created_at_index');
            }
        });

        // ===== BUNKER VERIFICATIONS =====
        Schema::table('bunker_verifications', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('bunker_verifications', 'bunker_verifications_bunker_id_index')) {
                $table->dropIndex('bunker_verifications_bunker_id_index');
            }
            if ($hasIndex('bunker_verifications', 'bunker_verifications_verified_at_index')) {
                $table->dropIndex('bunker_verifications_verified_at_index');
            }
            if ($hasIndex('bunker_verifications', 'bunker_verifications_created_at_index')) {
                $table->dropIndex('bunker_verifications_created_at_index');
            }
        });
    }
};