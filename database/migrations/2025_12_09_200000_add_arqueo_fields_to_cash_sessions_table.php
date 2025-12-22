<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->decimal('total_invoiced', 12, 2)->default(0)->after('closing_amount');
            $table->decimal('total_cash', 12, 2)->default(0)->after('total_invoiced');
            $table->decimal('total_transfer', 12, 2)->default(0)->after('total_cash');
            $table->decimal('expected_closing', 12, 2)->default(0)->after('total_transfer');
            $table->decimal('reported_closing_amount', 12, 2)->nullable()->after('expected_closing');
            $table->decimal('difference', 12, 2)->nullable()->after('reported_closing_amount');
        });
    }

    public function down(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'total_invoiced',
                'total_cash',
                'total_transfer',
                'expected_closing',
                'reported_closing_amount',
                'difference',
            ]);
        });
    }
};
