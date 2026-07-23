<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old sale_items table (and the parent sales table if it exists)
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales'); // optional – we won't use it

        // Create new sale_items table matching the model
        Schema::create('sale_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Foreign keys
            $table->uuid('bunker_id')->nullable();
            $table->uuid('customer_id')->nullable();
            $table->uuid('season_id')->nullable();

            // Sale details
            $table->string('invoice_number')->unique();
            $table->enum('sale_type', ['open', 'bags', 'bales'])->default('open');
            $table->decimal('weight_kg', 10, 2);
            $table->decimal('units', 10, 2)->default(0); // number of bags/bales
            $table->decimal('price_per_kg', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->date('date');

            // Cost & profit at time of sale
            $table->decimal('cost_per_kg_at_sale', 10, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);

            // Timestamps
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('bunker_id')
                  ->references('id')
                  ->on('bunkers')
                  ->onDelete('set null');
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('set null');
            $table->foreign('season_id')
                  ->references('id')
                  ->on('seasons')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};