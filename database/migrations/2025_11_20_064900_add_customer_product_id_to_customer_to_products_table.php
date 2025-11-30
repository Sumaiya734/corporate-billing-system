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
        Schema::table('customer_to_products', function (Blueprint $table) {
            $table->string('customer_product_id', 50)->nullable()->after('p_id')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_to_products', function (Blueprint $table) {
            $table->dropColumn('customer_product_id');
        });
    }
};
