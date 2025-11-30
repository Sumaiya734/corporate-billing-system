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
        Schema::table('payments', function (Blueprint $table) {
            // Laravel automatically generates index names, so we need to drop them by column name
            // Add foreign key constraints directly without dropping indexes first
            try {
                $table->foreign('invoice_id')->references('invoice_id')->on('invoices')->onDelete('cascade');
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
            
            try {
                $table->foreign('c_id')->references('c_id')->on('customers')->onDelete('cascade');
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
            
            try {
                $table->foreign('collected_by')->references('id')->on('users')->onDelete('set null');
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop foreign key constraints by column name
            try {
                $table->dropForeign(['invoice_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist
            }
            
            try {
                $table->dropForeign(['c_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist
            }
            
            try {
                $table->dropForeign(['collected_by']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist
            }
        });
    }
};