<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerProduct;
use Illuminate\Support\Facades\DB;

class UpdateCustomerProductIdsFormat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer-products:update-id-format';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all existing customer-product IDs to new format C-YY-XXXX-PYY';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting customer-product ID format update...');
        
        // Get all customer products
        $customerProducts = CustomerProduct::orderBy('cp_id')->get();
        
        if ($customerProducts->isEmpty()) {
            $this->warn('No customer products found in database.');
            return 0;
        }
        
        $this->info("Found {$customerProducts->count()} customer-product assignments to update.");
        
        $currentYear = date('y'); // Last 2 digits of current year
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        
        $bar = $this->output->createProgressBar($customerProducts->count());
        $bar->start();
        
        foreach ($customerProducts as $cp) {
            try {
                // Check if already in new format
                if ($cp->customer_product_id && preg_match('/^C-\d{2}-\d{4}-P\d+$/', $cp->customer_product_id)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }
                
                // Generate new customer-product ID: C-YY-XXXX-PYY
                $customerSequence = str_pad($cp->c_id, 4, '0', STR_PAD_LEFT);
                $newCustomerProductId = "C-{$currentYear}-{$customerSequence}-P{$cp->p_id}";
                
                // Update customer-product ID
                DB::table('customer_to_products')
                    ->where('cp_id', $cp->cp_id)
                    ->update(['customer_product_id' => $newCustomerProductId]);
                
                $updated++;
                
            } catch (\Exception $e) {
                $this->error("\nError updating customer-product {$cp->cp_id}: " . $e->getMessage());
                $errors++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Summary
        $this->info('Update completed!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Updated', $updated],
                ['Skipped (already in new format)', $skipped],
                ['Errors', $errors],
                ['Total', $customerProducts->count()],
            ]
        );
        
        if ($updated > 0) {
            $this->info("\n✓ Successfully updated {$updated} customer-product IDs to format C-YY-XXXX-PYY");
        }
        
        return 0;
    }
}
