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
        Schema::create('monthly_billing_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('billing_month', 7); // Format: YYYY-MM
            $table->string('display_month'); // Format: "Month Year" (e.g., "November 2025")
            $table->integer('total_customers');
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('received_amount', 12, 2)->default(0.00);
            $table->decimal('due_amount', 12, 2)->default(0.00);
            $table->string('status'); // e.g., "All Paid", "Pending", "Overdue"
            $table->text('notes')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->index('billing_month');
            $table->index('is_locked');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_billing_summaries');
    }
};