<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wanda_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('batch_code')->unique();
            $table->date('production_date');
            $table->decimal('input_total_kg', 12, 2);
            $table->decimal('output_kg', 12, 2);
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wanda_batches');
    }
};