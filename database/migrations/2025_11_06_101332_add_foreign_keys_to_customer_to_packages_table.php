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
        Schema::table('customer_to_packages', function (Blueprint $table) {
            $table->foreign(['c_id'], 'fk_customer_packages_customer')->references(['c_id'])->on('customers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['p_id'], 'fk_customer_packages_package')->references(['p_id'])->on('packages')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_to_packages', function (Blueprint $table) {
            $table->dropForeign('fk_customer_packages_customer');
            $table->dropForeign('fk_customer_packages_package');
        });
    }
};