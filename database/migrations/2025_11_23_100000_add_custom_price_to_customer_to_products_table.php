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
            $table->decimal('custom_price', 12, 2)->nullable()->after('p_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_to_products', function (Blueprint $table) {
            $table->dropColumn('custom_price');
        });
    }
};