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
        // First, let's check if the column is storing string values instead of IDs
        $sampleValue = DB::table('products')->value('product_type_id');
        
        // If it's a string, we need to convert it
        if ($sampleValue && !is_numeric($sampleValue)) {
            // Get the product type IDs
            $regularTypeId = DB::table('product_types')->where('name', 'regular')->value('id');
            $specialTypeId = DB::table('product_types')->where('name', 'special')->value('id');
            
            // Update the values to use proper IDs
            DB::table('products')
                ->where('product_type_id', 'regular')
                ->update(['product_type_id' => $regularTypeId]);
                
            DB::table('products')
                ->where('product_type_id', 'special')
                ->update(['product_type_id' => $specialTypeId]);
        }
        
        // Make sure the column has the correct type and constraints
        Schema::table('products', function (Blueprint $table) {
            // Change the column type to unsignedBigInteger if it's not already
            $table->unsignedBigInteger('product_type_id')->nullable()->change();
            
            // Add foreign key constraint if it doesn't exist
            $table->foreign('product_type_id')->references('id')->on('product_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Remove foreign key constraint
            $table->dropForeign(['product_type_id']);
        });
    }
};