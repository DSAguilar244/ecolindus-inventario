<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'session_id')) {
                // session_id may not exist in older schemas; skip if absent
                return;
            }
            $table->index(['session_id'], 'idx_invoices_session_id');
            $table->index(['created_at'], 'idx_invoices_created_at');
        });

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (Schema::hasColumn('payments', 'invoice_id')) {
                    $table->index(['invoice_id'], 'idx_payments_invoice_id');
                }
                if (Schema::hasColumn('payments', 'method')) {
                    $table->index(['method'], 'idx_payments_method');
                }
            });
        }

        Schema::table('cash_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('cash_sessions', 'user_id')) {
                $table->index(['user_id'], 'idx_cash_sessions_user_id');
            }
            if (Schema::hasColumn('cash_sessions', 'status')) {
                $table->index(['status'], 'idx_cash_sessions_status');
            }
            if (Schema::hasColumn('cash_sessions', 'opened_at')) {
                $table->index(['opened_at'], 'idx_cash_sessions_opened_at');
            }
            if (Schema::hasColumn('cash_sessions', 'closed_at')) {
                $table->index(['closed_at'], 'idx_cash_sessions_closed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasIndex('invoices', 'idx_invoices_session_id')) {
                $table->dropIndex('idx_invoices_session_id');
            }
            if (Schema::hasIndex('invoices', 'idx_invoices_created_at')) {
                $table->dropIndex('idx_invoices_created_at');
            }
        });

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (Schema::hasIndex('payments', 'idx_payments_invoice_id')) {
                    $table->dropIndex('idx_payments_invoice_id');
                }
                if (Schema::hasIndex('payments', 'idx_payments_method')) {
                    $table->dropIndex('idx_payments_method');
                }
            });
        }

        Schema::table('cash_sessions', function (Blueprint $table) {
            if (Schema::hasIndex('cash_sessions', 'idx_cash_sessions_user_id')) {
                $table->dropIndex('idx_cash_sessions_user_id');
            }
            if (Schema::hasIndex('cash_sessions', 'idx_cash_sessions_status')) {
                $table->dropIndex('idx_cash_sessions_status');
            }
            if (Schema::hasIndex('cash_sessions', 'idx_cash_sessions_opened_at')) {
                $table->dropIndex('idx_cash_sessions_opened_at');
            }
            if (Schema::hasIndex('cash_sessions', 'idx_cash_sessions_closed_at')) {
                $table->dropIndex('idx_cash_sessions_closed_at');
            }
        });
    }
};
