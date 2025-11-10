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
            // Add missing columns
            $table->unsignedInteger('collected_by')->nullable();
            $table->string('status', 50)->default('completed');
            $table->text('notes')->nullable();
            $table->string('transaction_id', 100)->nullable()->unique()->change();
            
            // Add foreign key constraints
            $table->foreign('invoice_id')->references('invoice_id')->on('invoices')->onDelete('cascade');
            $table->foreign('c_id')->references('c_id')->on('customers')->onDelete('cascade');
            $table->foreign('collected_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['c_id']);
            $table->dropForeign(['collected_by']);
            
            $table->dropColumn(['collected_by', 'status', 'notes']);
        });
    }
};