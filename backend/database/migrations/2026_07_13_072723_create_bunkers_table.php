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
        Schema::create('bunkers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('location')->nullable();
            $table->uuid('season_id')->nullable();
            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('set null');
            
            // Cost & weight tracking
            $table->decimal('threshold_percentage', 5, 2)->default(10);
            $table->decimal('cost_per_kg', 10, 2)->default(0);
            $table->boolean('cost_locked')->default(false);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('total_kg', 12, 2)->default(0);
            
            $table->enum('status', ['active', 'warning', 'empty', 'blocked'])->default('active');
            $table->boolean('is_sealed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('season_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bunkers');
    }
};