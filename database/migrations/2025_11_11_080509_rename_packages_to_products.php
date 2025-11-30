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
        // Check if packages table exists and rename it to products
        if (Schema::hasTable('packages')) {
            Schema::rename('packages', 'products');
        }
        
        // Check if the column exists before trying to rename it
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'package_type')) {
            // Update the package_type column name to product_type_id
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('package_type', 'product_type_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if products table exists
        if (Schema::hasTable('products')) {
            // Check if the column exists before trying to rename it
            if (Schema::hasColumn('products', 'product_type_id')) {
                // Revert product_type_id column back to package_type
                Schema::table('products', function (Blueprint $table) {
                    $table->renameColumn('product_type_id', 'package_type');
                });
            }
            
            // Rename products table back to packages
            Schema::rename('products', 'packages');
        }
    }
};