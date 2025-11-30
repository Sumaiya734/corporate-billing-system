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
        // Temporarily drop foreign key constraint from invoices table
        DB::statement('ALTER TABLE invoices DROP FOREIGN KEY fk_invoices_cp_id');
        
        // Fix cp_id to be auto-increment
        DB::statement('ALTER TABLE customer_to_products MODIFY cp_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        
        // Recreate the foreign key constraint
        DB::statement('ALTER TABLE invoices ADD CONSTRAINT fk_invoices_cp_id FOREIGN KEY (cp_id) REFERENCES customer_to_products(cp_id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key
        DB::statement('ALTER TABLE invoices DROP FOREIGN KEY fk_invoices_cp_id');
        
        // Remove auto-increment
        DB::statement('ALTER TABLE customer_to_products MODIFY cp_id BIGINT UNSIGNED NOT NULL');
        
        // Recreate foreign key
        DB::statement('ALTER TABLE invoices ADD CONSTRAINT fk_invoices_cp_id FOREIGN KEY (cp_id) REFERENCES customer_to_products(cp_id) ON DELETE CASCADE ON UPDATE CASCADE');
    }
};
