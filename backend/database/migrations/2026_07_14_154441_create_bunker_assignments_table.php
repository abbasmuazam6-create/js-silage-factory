<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('bunker_assignments', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->uuid('bunker_id');
        $table->uuid('silage_purchase_id');
        $table->uuid('season_id');
        $table->string('source')->nullable();
        $table->decimal('assigned_weight_kg', 12, 2);
        $table->date('date_assigned');
        $table->text('notes')->nullable();
        $table->timestamps();

        $table->foreign('bunker_id')->references('id')->on('bunkers')->onDelete('cascade');
        $table->foreign('silage_purchase_id')->references('id')->on('silage_purchases')->onDelete('cascade');
        $table->foreign('season_id')->references('id')->on('seasons')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::dropIfExists('bunker_assignments');
}
};
