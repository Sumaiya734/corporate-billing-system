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
        // First, we need to update the string values to match the product_type table IDs
        // Get all product types
        $productTypes = DB::table('product_type')->get();
        
        // Update the products table to use the correct IDs
        foreach ($productTypes as $type) {
            DB::table('products')
                ->where('product_type_id', $type->name)
                ->update(['product_type_id' => $type->id]);
        }
        
        // Change the column type back to unsignedBigInteger to work as foreign key
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_type_id')->nullable()->change();
        });
        
        // Add foreign key constraint
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('product_type_id')->references('id')->on('product_type')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key constraint
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['product_type_id']);
        });
        
        // Update the product_type_id values back to string values
        $productTypes = DB::table('product_type')->get();
        foreach ($productTypes as $type) {
            DB::table('products')
                ->where('product_type_id', $type->id)
                ->update(['product_type_id' => $type->name]);
        }
        
        // Change the column type back to string
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type_id')->default('regular')->change();
        });
    }
};