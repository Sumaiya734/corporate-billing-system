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
        DB::statement("CREATE VIEW `monthly_billing_summaries_dynamic` AS select `m`.`id` AS `id`,`m`.`billing_month` AS `billing_month`,`m`.`display_month` AS `display_month`,(select count(0) from `billing`.`customers` `c` where ((`c`.`is_active` = 1) and (`c`.`created_at` <= last_day(str_to_date(`m`.`billing_month`,'%Y-%m-%d'))))) AS `total_customers`,`m`.`total_amount` AS `total_amount`,`m`.`received_amount` AS `received_amount`,`m`.`due_amount` AS `due_amount`,`m`.`status` AS `status`,`m`.`notes` AS `notes`,`m`.`is_locked` AS `is_locked`,`m`.`created_by` AS `created_by`,`m`.`created_at` AS `created_at`,`m`.`updated_at` AS `updated_at` from `billing`.`monthly_billing_summaries` `m` order by `m`.`billing_month` desc");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `monthly_billing_summaries_dynamic`");
    }
};
