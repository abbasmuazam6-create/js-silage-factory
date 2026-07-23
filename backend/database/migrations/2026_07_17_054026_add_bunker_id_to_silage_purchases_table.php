// database/migrations/xxxx_add_bunker_id_to_silage_purchases_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('silage_purchases', function (Blueprint $table) {
            $table->uuid('bunker_id')->nullable()->after('supplier_id');
            $table->foreign('bunker_id')->references('id')->on('bunkers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('silage_purchases', function (Blueprint $table) {
            $table->dropForeign(['bunker_id']);
            $table->dropColumn('bunker_id');
        });
    }
};