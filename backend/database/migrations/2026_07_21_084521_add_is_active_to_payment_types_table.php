<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_types', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_types', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('color');
            }
        });
    }

    public function down()
    {
        Schema::table('payment_types', function (Blueprint $table) {
            if (Schema::hasColumn('payment_types', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};