<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the view if it already exists
        DB::statement("DROP VIEW IF EXISTS `monthly_revenue_summary`");
        
        // Create the view
        DB::statement("CREATE VIEW `monthly_revenue_summary` AS select date_format(`i`.`issue_date`,'%Y-%m') AS `month_year`,count(`i`.`invoice_id`) AS `invoice_count`,sum(`i`.`total_amount`) AS `total_revenue`,sum(`i`.`received_amount`) AS `collected_revenue`,sum((`i`.`total_amount` - `i`.`received_amount`)) AS `pending_revenue` from `billing`.`invoices` `i` group by date_format(`i`.`issue_date`,'%Y-%m') order by `month_year` desc");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `monthly_revenue_summary`");
    }
};