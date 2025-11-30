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
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('payment_id'); // Auto-incrementing primary key
            $table->unsignedInteger('invoice_id')->index('idx_invoice_id');
            $table->unsignedInteger('c_id')->index('idx_c_id');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 50);
            $table->date('payment_date');
            $table->string('transaction_id', 100)->nullable()->comment('Can be null if method is Cash');
            $table->unsignedInteger('collected_by')->nullable()->comment('User ID who collected the payment');
            $table->string('status', 20)->default('completed')->comment('completed, pending, cancelled');
            $table->text('notes')->nullable(); // Changed from 'note' to 'notes' to match model
            $table->timestamps(); // Creates created_at and updated_at
            
            // Add indexes for better performance
            $table->index('payment_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
