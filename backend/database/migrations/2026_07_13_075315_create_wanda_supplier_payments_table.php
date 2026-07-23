<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wanda_supplier_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wanda_purchase_id');
            $table->foreign('wanda_purchase_id')->references('id')->on('wanda_purchases')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('method');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wanda_supplier_payments');
    }
};