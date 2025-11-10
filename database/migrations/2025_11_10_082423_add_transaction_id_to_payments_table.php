<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if transaction_id column exists
        if (!Schema::hasColumn('payments', 'transaction_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('transaction_id', 100)->nullable()->unique()->after('payment_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if transaction_id column exists before dropping
        if (Schema::hasColumn('payments', 'transaction_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('transaction_id');
            });
        }
    }
};