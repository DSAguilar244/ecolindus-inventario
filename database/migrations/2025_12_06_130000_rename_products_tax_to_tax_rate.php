<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'tax')) {
            Schema::table('products', function ($table) {
                $table->renameColumn('tax', 'tax_rate');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'tax_rate')) {
            Schema::table('products', function ($table) {
                $table->renameColumn('tax_rate', 'tax');
            });
        }
    }
};
