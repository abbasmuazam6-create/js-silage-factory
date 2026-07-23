<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bunker_verifications', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('bunker_verifications', 'shrinkage_percentage')) {
                $table->decimal('shrinkage_percentage', 8, 2)->default(0)->after('shrinkage_kg');
            }
            if (!Schema::hasColumn('bunker_verifications', 'recorded_kg')) {
                $table->decimal('recorded_kg', 10, 2)->default(0)->after('bunker_id');
            }
            if (!Schema::hasColumn('bunker_verifications', 'actual_kg')) {
                $table->decimal('actual_kg', 10, 2)->default(0)->after('recorded_kg');
            }
            if (!Schema::hasColumn('bunker_verifications', 'date')) {
                $table->date('date')->nullable()->after('shrinkage_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bunker_verifications', function (Blueprint $table) {
            $table->dropColumn([
                'shrinkage_percentage',
                'recorded_kg',
                'actual_kg',
                'date'
            ]);
        });
    }
};