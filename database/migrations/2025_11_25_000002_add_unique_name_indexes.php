<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('brands', function (Blueprint $table) {
            if (! Schema::hasColumn('brands', 'name')) {
                return;
            }
            try {
                $table->unique('name', 'brands_name_unique');
            } catch (\Throwable $e) { /* ignore */
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'name')) {
                return;
            }
            try {
                $table->unique('name', 'categories_name_unique');
            } catch (\Throwable $e) { /* ignore */
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'name')) {
                return;
            }
            try {
                $table->unique('name', 'suppliers_name_unique');
            } catch (\Throwable $e) { /* ignore */
            }
            if (Schema::hasColumn('suppliers', 'email')) {
                try {
                    $table->unique('email', 'suppliers_email_unique');
                } catch (\Throwable $e) { /* ignore */
                }
            }
        });
    }

    public function down()
    {
        Schema::table('brands', function (Blueprint $table) {
            try {
                $table->dropUnique('brands_name_unique');
            } catch (\Throwable $e) {
            }
        });
        Schema::table('categories', function (Blueprint $table) {
            try {
                $table->dropUnique('categories_name_unique');
            } catch (\Throwable $e) {
            }
        });
        Schema::table('suppliers', function (Blueprint $table) {
            try {
                $table->dropUnique('suppliers_name_unique');
            } catch (\Throwable $e) {
            } try {
                $table->dropUnique('suppliers_email_unique');
            } catch (\Throwable $e) {
            }
        });
    }
};
