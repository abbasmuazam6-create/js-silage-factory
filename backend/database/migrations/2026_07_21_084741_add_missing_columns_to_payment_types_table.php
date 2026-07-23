<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_types', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_types', 'code')) {
                $table->string('code')->nullable()->after('name');
            }
            if (!Schema::hasColumn('payment_types', 'color')) {
                $table->string('color')->nullable()->after('code');
            }
            if (!Schema::hasColumn('payment_types', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('color');
            }
            if (!Schema::hasColumn('payment_types', 'description')) {
                $table->text('description')->nullable()->after('is_active');
            }
        });
    }

    public function down()
    {
        Schema::table('payment_types', function (Blueprint $table) {
            $table->dropColumn(['code', 'color', 'is_active', 'description']);
        });
    }
};