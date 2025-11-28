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
        Schema::create('customer_to_products', function (Blueprint $table) {
            $table->bigIncrements('cp_id');
            $table->unsignedInteger('c_id')->index('fk_customer_products_customer');
            $table->unsignedInteger('p_id')->index('fk_customer_products_product');
            $table->date('assign_date');
            $table->integer('billing_cycle_months')->default(1);
            $table->date('due_date')->nullable()->storedAs('(`assign_date` + interval `billing_cycle_months` month)');
            $table->enum('status', ['active', 'pending', 'expired'])->nullable()->default('active');
            $table->boolean('is_active')->nullable()->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_to_products');
    }
};