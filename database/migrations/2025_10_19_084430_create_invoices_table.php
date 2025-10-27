<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 100)->unique(); // Reduced length
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('billing_month');
            $table->date('issue_date');
            $table->date('due_date');
            
            // Bill Calculation Fields
            $table->decimal('fixed_charge', 10, 2)->default(50.00);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('vat_percentage', 5, 2)->default(7.00);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            
            // Payment Status
            $table->enum('status', ['draft', 'pending', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};