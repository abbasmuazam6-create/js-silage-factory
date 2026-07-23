<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('silage_purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('silage_purchases', 'supplier_id')) {
                $table->uuid('supplier_id')->nullable();
                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            }
            if (!Schema::hasColumn('silage_purchases', 'area')) {
                $table->decimal('area', 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('silage_purchases', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
            $table->dropColumn('area');
        });
    }
};