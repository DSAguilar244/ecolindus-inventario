<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropSlugFromBrandsCategories extends Migration
{
    public function up()
    {
        Schema::table('brands', function (Blueprint $table) {
            if (Schema::hasColumn('brands', 'slug')) {
                // drop unique index if exists
                try {
                    $table->dropUnique('brands_slug_unique');
                } catch (\Throwable $e) {
                    // ignore if index doesn't exist
                }
                try {
                    $table->dropColumn('slug');
                } catch (\Throwable $e) {
                    // ignore if column already dropped
                }
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'slug')) {
                try {
                    $table->dropUnique('categories_slug_unique');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropColumn('slug');
                } catch (\Throwable $e) {
                }
            }
        });
    }

    public function down()
    {
        Schema::table('brands', function (Blueprint $table) {
            if (! Schema::hasColumn('brands', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
        });
    }
}
