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
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('c_id');
            $table->unsignedInteger('user_id')->nullable()->index('user_id');
            $table->string('customer_id', 60)->nullable()->unique('customer_id');
            $table->string('name');
            $table->string('email')->nullable()->index('idx_customers_email');
            $table->string('phone', 30)->nullable()->index('idx_customers_phone');
            $table->text('address')->nullable();
            $table->text('connection_address')->nullable();
            $table->string('id_type', 60)->nullable();
            $table->string('id_number', 100)->nullable();
            $table->boolean('is_active')->nullable()->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
