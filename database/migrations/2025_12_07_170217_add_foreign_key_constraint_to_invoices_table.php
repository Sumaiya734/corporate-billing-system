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
            // Add foreign key constraint for cp_id referencing customer_to_products.cp_id
            // We're assuming the column exists and the constraint doesn't
            $table->foreign('cp_id')
                  ->references('cp_id')
                  ->on('customer_to_products')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop foreign key constraint if it exists
            $table->dropForeign(['cp_id']);
        });
    }
};