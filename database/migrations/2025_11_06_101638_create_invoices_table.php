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
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('invoice_id');
            $table->string('invoice_number', 100)->unique();
            $table->unsignedInteger('c_id')->index();
            $table->date('issue_date');
            $table->decimal('previous_due', 12)->nullable()->default(0);
            $table->decimal('service_charge', 12)->nullable()->default(50);
            $table->decimal('vat_percentage', 5)->nullable()->default(5);
            $table->decimal('vat_amount', 12)->nullable()->default(0);
            $table->decimal('subtotal', 12)->nullable()->default(0);
            $table->decimal('total_amount', 12)->nullable()->default(0);
            $table->decimal('received_amount', 12)->nullable()->default(0);
            $table->decimal('next_due', 12)->nullable()->default(0);
            $table->enum('status', ['unpaid', 'paid', 'partial', 'cancelled'])->nullable()->default('unpaid')->index();
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by')->nullable()->index();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
