<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('silage_purchases', function (Blueprint $table) {
        if (!Schema::hasColumn('silage_purchases', 'supplier_id')) {
            $table->uuid('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
        }
        // Add any other missing columns
    });
}
    public function down(): void
    {
        Schema::table('silage_purchases', function (Blueprint $table) {
            //
        });
    }
};
