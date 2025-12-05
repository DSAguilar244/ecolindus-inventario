<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoice_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('prefix')->default('INV');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
        });

        // Seed initial row
        DB::table('invoice_numbers')->insert(['prefix' => 'INV', 'last_number' => 0, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down()
    {
        Schema::dropIfExists('invoice_numbers');
    }
};
