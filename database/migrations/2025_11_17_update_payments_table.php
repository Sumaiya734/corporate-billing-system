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
        Schema::table('payments', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('payments', 'collected_by')) {
                $table->unsignedInteger('collected_by')->nullable()->after('transaction_id')->comment('User ID who collected the payment');
            }
            
            if (!Schema::hasColumn('payments', 'status')) {
                $table->string('status', 20)->default('completed')->after('collected_by')->comment('completed, pending, cancelled');
            }
            
            // Rename 'note' to 'notes' if 'note' exists
            if (Schema::hasColumn('payments', 'note') && !Schema::hasColumn('payments', 'notes')) {
                $table->renameColumn('note', 'notes');
            }
            
            // Add indexes for better performance
            if (!Schema::hasColumn('payments', 'payment_date')) {
                $table->index('payment_date');
            }
            if (!Schema::hasColumn('payments', 'status')) {
                $table->index('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['collected_by', 'status']);
            if (Schema::hasColumn('payments', 'notes')) {
                $table->renameColumn('notes', 'note');
            }
        });
    }
};
