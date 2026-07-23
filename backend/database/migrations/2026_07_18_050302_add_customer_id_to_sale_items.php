<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Add customer_id as UUID (since you use UUIDs)
            if (!Schema::hasColumn('sale_items', 'customer_id')) {
                $table->uuid('customer_id')->nullable()->after('bunker_id');
                $table->foreign('customer_id')
                      ->references('id')
                      ->on('customers')
                      ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};