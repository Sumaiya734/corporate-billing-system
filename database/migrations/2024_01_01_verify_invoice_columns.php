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
            // Check and add next_due column if it doesn't exist
            if (!Schema::hasColumn('invoices', 'next_due')) {
                $table->decimal('next_due', 10, 2)->default(0)->after('received_amount');
            }
            
            // Check and add received_amount column if it doesn't exist
            if (!Schema::hasColumn('invoices', 'received_amount')) {
                $table->decimal('received_amount', 10, 2)->default(0)->after('total_amount');
            }
            
            // Check and add total_amount column if it doesn't exist
            if (!Schema::hasColumn('invoices', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->default(0)->after('subtotal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Only drop columns if they exist
            if (Schema::hasColumn('invoices', 'next_due')) {
                $table->dropColumn('next_due');
            }
            
            if (Schema::hasColumn('invoices', 'received_amount')) {
                $table->dropColumn('received_amount');
            }
            
            if (Schema::hasColumn('invoices', 'total_amount')) {
                $table->dropColumn('total_amount');
            }
        });
    }
};