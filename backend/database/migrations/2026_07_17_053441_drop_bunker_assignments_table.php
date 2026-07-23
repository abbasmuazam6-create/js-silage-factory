// database/migrations/xxxx_drop_bunker_assignments_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('bunker_assignments');
    }

    public function down(): void
    {
        Schema::create('bunker_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bunker_id');
            $table->uuid('silage_purchase_id');
            $table->uuid('season_id')->nullable();
            $table->decimal('assigned_weight_kg', 10, 2);
            $table->date('date_assigned');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
};