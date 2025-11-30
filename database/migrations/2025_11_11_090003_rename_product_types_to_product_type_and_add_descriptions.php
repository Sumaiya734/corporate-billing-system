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
        // Rename the table from product_types to product_type
        Schema::rename('product_types', 'product_type');
        
        // Add the descriptions column
        Schema::table('product_type', function (Blueprint $table) {
            $table->text('descriptions')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the descriptions column
        Schema::table('product_type', function (Blueprint $table) {
            $table->dropColumn('descriptions');
        });
        
        // Rename the table back from product_type to product_types
        Schema::rename('product_type', 'product_types');
    }
};