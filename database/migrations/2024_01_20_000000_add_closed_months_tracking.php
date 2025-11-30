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
        // Add is_closed column to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_closed')->default(false)->after('status');
            $table->timestamp('closed_at')->nullable()->after('is_closed');
            $table->unsignedBigInteger('closed_by')->nullable()->after('closed_at');
            
            $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
        });

        // Create billing_periods table to track closed months
        Schema::create('billing_periods', function (Blueprint $table) {
            $table->id();
            $table->string('billing_month', 7); // Format: YYYY-MM
            $table->boolean('is_closed')->default(false);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('received_amount', 10, 2)->default(0);
            $table->decimal('carried_forward', 10, 2)->default(0);
            $table->integer('total_invoices')->default(0);
            $table->integer('affected_invoices')->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique('billing_month');
            $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['closed_by']);
            $table->dropColumn(['is_closed', 'closed_at', 'closed_by']);
        });
        
        Schema::dropIfExists('billing_periods');
    }
};
