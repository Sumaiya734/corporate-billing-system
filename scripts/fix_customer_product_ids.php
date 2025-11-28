<?php
/**
 * Script to fix missing customer_product_id values in existing records
 * Run this script once to populate customer_product_id for existing records
 * 
 * Usage: php artisan tinker < scripts/fix_customer_product_ids.php
 * Or: php scripts/fix_customer_product_ids.php (if running standalone)
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CustomerProduct;
use Illuminate\Support\Facades\DB;

echo "Starting to fix customer_product_id values...\n\n";

try {
    DB::beginTransaction();
    
    // Get all customer products without customer_product_id
    $customerProducts = CustomerProduct::whereNull('customer_product_id')
        ->orWhere('customer_product_id', '')
        ->get();
    
    echo "Found " . $customerProducts->count() . " records without customer_product_id\n\n";
    
    $updated = 0;
    foreach ($customerProducts as $cp) {
        // Generate unique customer-product ID in format: C-YY-XXXX-PYY
        $year = date('y'); // Last 2 digits of year
        $customerSequence = str_pad($cp->c_id, 4, '0', STR_PAD_LEFT);
        $customerProductId = "C-{$year}-{$customerSequence}-P{$cp->p_id}";
        
        // Check if this ID already exists
        $exists = CustomerProduct::where('customer_product_id', $customerProductId)
            ->where('cp_id', '!=', $cp->cp_id)
            ->exists();
        
        if ($exists) {
            // Add a unique suffix if duplicate
            $suffix = 1;
            while (CustomerProduct::where('customer_product_id', "{$customerProductId}-{$suffix}")->exists()) {
                $suffix++;
            }
            $customerProductId = "{$customerProductId}-{$suffix}";
        }
        
        $cp->update(['customer_product_id' => $customerProductId]);
        $updated++;
        
        echo "Updated CP ID {$cp->cp_id}: {$customerProductId}\n";
    }
    
    DB::commit();
    
    echo "\n✅ Successfully updated {$updated} records!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
