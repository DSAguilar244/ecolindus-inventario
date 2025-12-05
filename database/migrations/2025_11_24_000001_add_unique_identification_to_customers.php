<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIdentificationToCustomers extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // add unique index to identification to avoid duplicates at DB level
            if (! Schema::hasColumn('customers', 'identification')) {
                return;
            }
            try {
                $table->unique('identification');
            } catch (\Throwable $e) {
                // ignore index already exists or any platform specific limitation
            }
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            try {
                $table->dropUnique(['identification']);
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
}
