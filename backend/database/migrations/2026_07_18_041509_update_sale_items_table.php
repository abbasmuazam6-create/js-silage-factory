<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('sale_items', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('sale_items', 'units')) {
                $table->decimal('units', 10, 2)->default(0)->after('weight_kg');
            }
            if (!Schema::hasColumn('sale_items', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('total_price');
            }
            if (!Schema::hasColumn('sale_items', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)->default(0)->after('discount');
            }
            if (!Schema::hasColumn('sale_items', 'due_amount')) {
                $table->decimal('due_amount', 10, 2)->default(0)->after('paid_amount');
            }
            if (!Schema::hasColumn('sale_items', 'cost_per_kg_at_sale')) {
                $table->decimal('cost_per_kg_at_sale', 10, 2)->default(0)->after('date');
            }
            if (!Schema::hasColumn('sale_items', 'profit')) {
                $table->decimal('profit', 10, 2)->default(0)->after('cost_per_kg_at_sale');
            }
            if (!Schema::hasColumn('sale_items', 'season_id')) {
                $table->uuid('season_id')->nullable()->after('date');
                $table->foreign('season_id')->references('id')->on('seasons')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_number', 'units', 'discount', 'paid_amount', 
                'due_amount', 'cost_per_kg_at_sale', 'profit', 'season_id'
            ]);
        });
    }
};