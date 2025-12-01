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
            // Modify the status column to include 'confirmed' value
            $table->enum('status', ['unpaid', 'paid', 'partial', 'cancelled', 'confirmed'])->nullable()->default('unpaid')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Revert to original enum values
            $table->enum('status', ['unpaid', 'paid', 'partial', 'cancelled'])->nullable()->default('unpaid')->change();
        });
    }
};