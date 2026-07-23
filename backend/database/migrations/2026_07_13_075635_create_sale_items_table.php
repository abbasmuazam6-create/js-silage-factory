<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sale_id');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->enum('product_type', ['silage', 'wanda']);
            $table->uuid('bunker_id')->nullable();
            $table->foreign('bunker_id')->references('id')->on('bunkers')->onDelete('set null');
            $table->uuid('wanda_batch_id')->nullable();
            $table->foreign('wanda_batch_id')->references('id')->on('wanda_batches')->onDelete('set null');
            $table->decimal('weight_kg', 12, 2);
            $table->decimal('price_per_kg', 12, 2);
            $table->decimal('amount', 12, 2);
            $table->integer('bags_count')->default(0);
            $table->integer('bales_count')->default(0);
            $table->decimal('wanda_bag_size_kg', 10, 2)->nullable();
            $table->integer('wanda_bag_count')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};