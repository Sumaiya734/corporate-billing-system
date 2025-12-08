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
            // Add custom_due_date column after due_date
            $table->date('custom_due_date')->nullable()->after('due_date')->comment('Custom due date set by user, overrides calculated due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_to_products', function (Blueprint $table) {
            $table->dropColumn('custom_due_date');
        });
    }
};
