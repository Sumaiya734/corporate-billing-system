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
        Schema::table('customer_to_products', function (Blueprint $table) {
            // Drop the stored generated column
            $table->dropColumn('due_date');
            
            // Add a regular nullable date column for due_date
            $table->date('due_date')->nullable()->after('billing_cycle_months');
        });
        
        // Update existing records with calculated due dates
        DB::table('customer_to_products')->whereNull('due_date')->update([
            'due_date' => DB::raw('DATE_ADD(assign_date, INTERVAL billing_cycle_months MONTH)')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_to_products', function (Blueprint $table) {
            // Drop the regular column
            $table->dropColumn('due_date');
            
            // Add back the stored generated column
            $table->date('due_date')->nullable()->storedAs('(`assign_date` + interval `billing_cycle_months` month)')->after('billing_cycle_months');
        });
    }
};