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
        // Remove the foreign key constraint first
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['product_type_id']);
        });

        // Rename the column first
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('product_type_id', 'product_type');
        });

        // Convert the numeric IDs back to string values BEFORE changing the column type
        DB::statement("UPDATE products SET product_type = 'regular' WHERE product_type = '1'");
        DB::statement("UPDATE products SET product_type = 'special' WHERE product_type = '2'");

        // Change the column type to VARCHAR
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type')->default('regular')->change();
        });

        // Drop the product_types table since we're not using it anymore
        Schema::dropIfExists('product_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the product_types table
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Populate with existing product types
        DB::table('product_types')->insert([
            ['name' => 'regular', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'special', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Convert string values back to numeric IDs
        DB::statement("UPDATE products SET product_type = '1' WHERE product_type = 'regular'");
        DB::statement("UPDATE products SET product_type = '2' WHERE product_type = 'special'");

        // Change the column type back to unsignedBigInteger
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_type')->nullable()->change();
        });

        // Rename the column back to product_type_id
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('product_type', 'product_type_id');
        });

        // Add the foreign key constraint back
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('product_type_id')->references('id')->on('product_types')->onDelete('set null');
        });
    }
};