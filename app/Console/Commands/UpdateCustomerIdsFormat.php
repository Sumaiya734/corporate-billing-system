<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class UpdateCustomerIdsFormat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:update-id-format';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all existing customer IDs to new format C-YY-XXXX';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting customer ID format update...');
        
        // Get all customers
        $customers = Customer::orderBy('c_id')->get();
        
        if ($customers->isEmpty()) {
            $this->warn('No customers found in database.');
            return 0;
        }
        
        $this->info("Found {$customers->count()} customers to update.");
        
        $currentYear = date('y'); // Last 2 digits of current year
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        
        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();
        
        foreach ($customers as $customer) {
            try {
                // Check if already in new format
                if (preg_match('/^C-\d{2}-\d{4}$/', $customer->customer_id)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }
                
                // Generate new customer ID: C-YY-XXXX
                $sequentialNumber = str_pad($customer->c_id, 4, '0', STR_PAD_LEFT);
                $newCustomerId = "C-{$currentYear}-{$sequentialNumber}";
                
                // Update customer ID
                DB::table('customers')
                    ->where('c_id', $customer->c_id)
                    ->update(['customer_id' => $newCustomerId]);
                
                $updated++;
                
            } catch (\Exception $e) {
                $this->error("\nError updating customer {$customer->c_id}: " . $e->getMessage());
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
                ['Total', $customers->count()],
            ]
        );
        
        if ($updated > 0) {
            $this->info("\n✓ Successfully updated {$updated} customer IDs to format C-YY-XXXX");
        }
        
        return 0;
    }
}
