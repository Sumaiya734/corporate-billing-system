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
        // Check if customer_to_packages table exists and rename it to customer_to_products
        if (Schema::hasTable('customer_to_packages')) {
            Schema::rename('customer_to_packages', 'customer_to_products');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if customer_to_products table exists and rename it back to customer_to_packages
        if (Schema::hasTable('customer_to_products')) {
            Schema::rename('customer_to_products', 'customer_to_packages');
        }
    }
};