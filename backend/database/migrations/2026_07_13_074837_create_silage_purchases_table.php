<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('silage_purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('purchase_code')->unique();
            $table->uuid('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->date('purchase_date');
            $table->decimal('area', 10, 2)->nullable();
            $table->decimal('weight_kg', 12, 2);
            $table->decimal('cost', 12, 2);
            $table->enum('quality_grade', ['A', 'B', 'C'])->nullable();
            $table->enum('status', ['available', 'in_bunker', 'used'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('silage_purchases');
    }
};