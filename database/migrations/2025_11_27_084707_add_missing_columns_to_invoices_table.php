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
            // Add only the missing columns based on the error message
            if (!Schema::hasColumn('invoices', 'service_charge')) {
                $table->decimal('service_charge', 10, 2)->default(0.00)->after('previous_due');
            }
            
            if (!Schema::hasColumn('invoices', 'vat_percentage')) {
                $table->decimal('vat_percentage', 5, 2)->default(0.00)->after('service_charge');
            }
            
            if (!Schema::hasColumn('invoices', 'vat_amount')) {
                $table->decimal('vat_amount', 10, 2)->default(0.00)->after('vat_percentage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'service_charge')) {
                $table->dropColumn('service_charge');
            }
            
            if (Schema::hasColumn('invoices', 'vat_percentage')) {
                $table->dropColumn('vat_percentage');
            }
            
            if (Schema::hasColumn('invoices', 'vat_amount')) {
                $table->dropColumn('vat_amount');
            }
        });
    }
};