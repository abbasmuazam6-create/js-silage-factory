<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('silage_purchases', function (Blueprint $table) {
            $table->uuid('season_id')->nullable();
            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('silage_purchases', function (Blueprint $table) {
            $table->dropForeign(['season_id']);
            $table->dropColumn('season_id');
        });
    }
};