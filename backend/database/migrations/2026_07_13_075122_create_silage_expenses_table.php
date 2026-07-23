<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('silage_expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('expense_code')->unique();
            $table->uuid('bunker_id');
            $table->foreign('bunker_id')->references('id')->on('bunkers')->onDelete('cascade');
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
        Schema::dropIfExists('silage_expenses');
    }
};