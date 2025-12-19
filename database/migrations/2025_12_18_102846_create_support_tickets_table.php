<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers', 'c_id');
            $table->string('ticket_number')->unique();
            $table->string('subject');
            $table->enum('category', ['billing', 'license', 'product', 'technical', 'account', 'integration', 'other']);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent']);
            $table->foreignId('product_id')->nullable()->constrained('customer_to_products', 'cp_id');
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};