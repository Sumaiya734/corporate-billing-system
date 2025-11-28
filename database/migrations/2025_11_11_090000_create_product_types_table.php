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
        Schema::create('product_type', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('descriptions')->nullable();
            $table->timestamps();
        });

        // Populate with existing product types
        DB::table('product_type')->insert([
            ['name' => 'regular', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'special', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_type');
    }
};