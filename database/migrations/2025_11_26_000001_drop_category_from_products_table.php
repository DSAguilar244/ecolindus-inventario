<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('products', 'category')) {
            Schema::table('products', function (Blueprint $table) {
                // safe drop of column
                $table->dropColumn('category');
            });
        }
    }

    public function down()
    {
        if (! Schema::hasColumn('products', 'category')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('category')->default('')->after('name');
            });
        }
    }
};
