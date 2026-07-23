<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'notes')) {
                $table->text('notes')->nullable()->after('tax_id');
            }
            if (!Schema::hasColumn('customers', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('address');
            }
            if (!Schema::hasColumn('customers', 'tax_id')) {
                $table->string('tax_id')->nullable()->after('address');
            }
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['notes', 'contact_person', 'tax_id']);
        });
    }
};