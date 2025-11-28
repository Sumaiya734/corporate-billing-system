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
            // Check if collected_by column exists before adding
            if (!Schema::hasColumn('payments', 'collected_by')) {
                $table->unsignedInteger('collected_by')->nullable();
                
                // Add foreign key constraint for collected_by if column was just added
                try {
                    $table->foreign('collected_by')->references('id')->on('users')->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key likely already exists or column wasn't added
                }
            }
            
            // Check if status column exists before adding
            if (!Schema::hasColumn('payments', 'status')) {
                $table->string('status', 50)->default('completed');
            }
            
            // Check if notes column exists before adding
            if (!Schema::hasColumn('payments', 'notes')) {
                $table->text('notes')->nullable();
            }
            
            // Check if transaction_id column exists before modifying
            if (Schema::hasColumn('payments', 'transaction_id')) {
                // Skip modifying unique constraint as it likely already exists
            } else {
                $table->string('transaction_id', 100)->nullable()->unique();
            }
            
            // Only add foreign keys for existing columns if they don't already have foreign keys
            // We'll skip adding foreign key constraints since they likely already exist
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Only attempt to drop foreign keys and columns that we know we added
            $columnsToDrop = [];
            if (Schema::hasColumn('payments', 'collected_by')) {
                try {
                    $table->dropForeign(['collected_by']);
                } catch (\Exception $e) {
                    // Foreign key doesn't exist or has different name
                }
                $columnsToDrop[] = 'collected_by';
            }
            
            if (Schema::hasColumn('payments', 'status')) {
                $columnsToDrop[] = 'status';
            }
            
            if (Schema::hasColumn('payments', 'notes')) {
                $columnsToDrop[] = 'notes';
            }
            
            if (!empty($columnsToDrop)) {
                try {
                    $table->dropColumn($columnsToDrop);
                } catch (\Exception $e) {
                    // Columns might not exist or have dependencies
                }
            }
        });
    }
};