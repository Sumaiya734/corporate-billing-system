<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update foreign key references in customer_to_products table
        Schema::table('customer_to_products', function (Blueprint $table) {
            // Drop old foreign key constraints
            $table->dropForeign(['p_id']);
            $table->dropForeign(['c_id']);
            
            // Recreate foreign key constraints with proper references
            $table->foreign('p_id')->references('p_id')->on('products')->onDelete('cascade');
            $table->foreign('c_id')->references('c_id')->on('customers')->onDelete('cascade');
        });
        
        // Update any references in other tables
        // Update invoice_products table if it exists
        if (Schema::hasTable('invoice_products')) {
            Schema::table('invoice_products', function (Blueprint $table) {
                if (Schema::hasColumn('invoice_products', 'product_price')) {
                    $table->renameColumn('product_price', 'product_price');
                }
                if (Schema::hasColumn('invoice_products', 'total_product_amount')) {
                    $table->renameColumn('total_product_amount', 'total_product_amount');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert changes to invoice_products table if it exists
        if (Schema::hasTable('invoice_products')) {
            Schema::table('invoice_products', function (Blueprint $table) {
                if (Schema::hasColumn('invoice_products', 'product_price')) {
                    $table->renameColumn('product_price', 'product_price');
                }
                if (Schema::hasColumn('invoice_products', 'total_product_amount')) {
                    $table->renameColumn('total_product_amount', 'total_product_amount');
                }
            });
        }
        
        // Revert foreign key references in customer_to_products table
        Schema::table('customer_to_products', function (Blueprint $table) {
            // Drop new foreign key constraints
            $table->dropForeign(['p_id']);
            $table->dropForeign(['c_id']);
            
            // Recreate old foreign key constraints
            $table->foreign('p_id')->references('p_id')->on('products')->onDelete('cascade');
            $table->foreign('c_id')->references('c_id')->on('customers')->onDelete('cascade');
        });
    }
};