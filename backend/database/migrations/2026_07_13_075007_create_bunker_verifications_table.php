<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('bunker_verifications', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->uuid('bunker_id');
        $table->decimal('recorded_remaining_kg', 12, 2);
        $table->decimal('actual_remaining_kg', 12, 2);
        $table->decimal('shrinkage_kg', 12, 2);
        $table->uuid('verified_by')->nullable();
        $table->date('date_verified');
        $table->text('notes')->nullable();
        $table->uuid('season_id')->nullable();
        $table->timestamps();

        $table->foreign('bunker_id')->references('id')->on('bunkers')->onDelete('cascade');
        $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        $table->foreign('season_id')->references('id')->on('seasons')->onDelete('set null');
    });
}
    public function down(): void
    {
        Schema::dropIfExists('bunker_verifications');
    }
};