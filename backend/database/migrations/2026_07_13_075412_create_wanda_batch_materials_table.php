<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wanda_batch_materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('batch_id');
            $table->foreign('batch_id')->references('id')->on('wanda_batches')->onDelete('cascade');
            $table->uuid('material_id');
            $table->foreign('material_id')->references('id')->on('wanda_materials');
            $table->decimal('quantity_kg', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wanda_batch_materials');
    }
};