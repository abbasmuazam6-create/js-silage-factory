<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wanda_purchase_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wanda_purchase_id');
            $table->foreign('wanda_purchase_id')->references('id')->on('wanda_purchases')->onDelete('cascade');
            $table->uuid('material_id');
            $table->foreign('material_id')->references('id')->on('wanda_materials');
            $table->decimal('quantity_kg', 12, 2);
            $table->decimal('cost', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wanda_purchase_items');
    }
};