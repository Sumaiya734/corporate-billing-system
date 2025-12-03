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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('profile_picture')->nullable()->after('is_active');
            $table->string('id_card_front')->nullable()->after('profile_picture');
            $table->string('id_card_back')->nullable()->after('id_card_front');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['profile_picture', 'id_card_front', 'id_card_back']);
        });
    }
};
