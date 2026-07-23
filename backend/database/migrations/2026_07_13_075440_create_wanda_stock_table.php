<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wanda_stock', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('batch_id')->unique();
            $table->foreign('batch_id')->references('id')->on('wanda_batches')->onDelete('cascade');
            $table->string('product_name')->default('Wanda');
            $table->decimal('quantity_kg', 12, 2);
            $table->decimal('price_per_kg', 12, 2)->default(0.25);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wanda_stock');
    }
};