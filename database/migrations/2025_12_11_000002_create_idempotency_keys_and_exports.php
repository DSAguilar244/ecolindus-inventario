<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('idempotency_keys')) {
            Schema::create('idempotency_keys', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->unsignedBigInteger('cash_session_id')->nullable();
                $table->timestamps();
                
                $table->foreign('cash_session_id')
                    ->references('id')
                    ->on('cash_sessions')
                    ->onDelete('set null');
            });
        }

        if (! Schema::hasTable('exports')) {
            Schema::create('exports', function (Blueprint $table) {
                $table->id();
                $table->string('token')->unique();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('type')->default('monthly');
                $table->string('path')->nullable();
                $table->string('status')->default('queued');
                $table->text('meta')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('exports')) {
            Schema::dropIfExists('exports');
        }
        if (Schema::hasTable('idempotency_keys')) {
            Schema::dropIfExists('idempotency_keys');
        }
    }
};
