<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wanda_expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('expense_code')->unique();
            $table->uuid('batch_id');
            $table->foreign('batch_id')->references('id')->on('wanda_batches')->onDelete('cascade');
            $table->string('category');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wanda_expenses');
    }
};