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
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('payment_id');
            $table->unsignedInteger('invoice_id')->index('idx_invoice_id');
            $table->unsignedInteger('c_id')->index('idx_c_id');
            $table->decimal('amount', 12);
            $table->string('payment_method', 50);
            $table->dateTime('payment_date');
            $table->string('transaction_id', 100)->nullable()->unique('idx_transaction_id')->comment('Can be null if method is Cash');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
