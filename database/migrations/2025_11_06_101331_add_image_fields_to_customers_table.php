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
            if (!Schema::hasColumn('customers', 'profile_picture')) {
                $table->string('profile_picture')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('customers', 'id_card_front')) {
                $table->string('id_card_front')->nullable()->after('profile_picture');
            }
            if (!Schema::hasColumn('customers', 'id_card_back')) {
                $table->string('id_card_back')->nullable()->after('id_card_front');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'profile_picture')) {
                $table->dropColumn('profile_picture');
            }
            if (Schema::hasColumn('customers', 'id_card_front')) {
                $table->dropColumn('id_card_front');
            }
            if (Schema::hasColumn('customers', 'id_card_back')) {
                $table->dropColumn('id_card_back');
            }
        });
    }
};