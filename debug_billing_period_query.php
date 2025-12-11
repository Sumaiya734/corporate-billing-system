<?php

echo "=== DEBUGGING BILLING PERIOD QUERY ===\n\n";

// Test the BillingPeriod::isMonthClosed method directly
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BillingPeriod;
use Illuminate\Support\Facades\DB;

echo "✅ Laravel bootstrapped successfully\n\n";

// Test 1: Check the table structure
echo "1. CHECKING TABLE STRUCTURE:\n";
echo "   " . str_repeat("-", 50) . "\n";

try {
    $columns = DB::select("DESCRIBE billing_periods");
    echo "   Columns in billing_periods table:\n";
    foreach ($columns as $column) {
        echo "   - {$column->Field} ({$column->Type})\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error checking table structure: " . $e->getMessage() . "\n";
}

// Test 2: Test the BillingPeriod model directly
echo "\n2. TESTING BillingPeriod MODEL:\n";
echo "   " . str_repeat("-", 50) . "\n";

try {
    // Test with a simple query first
    $count = BillingPeriod::count();
    echo "   Total billing periods: $count\n";
    
    // Test the isMonthClosed method
    $testMonth = '2025-03';
    echo "   Testing isMonthClosed('$testMonth')...\n";
    
    // Enable query logging
    DB::enableQueryLog();
    
    $result = BillingPeriod::isMonthClosed($testMonth);
    
    // Get the executed queries
    $queries = DB::getQueryLog();
    
    echo "   Result: " . ($result ? 'true' : 'false') . "\n";
    echo "   Executed queries:\n";
    foreach ($queries as $query) {
        echo "   - SQL: " . $query['sql'] . "\n";
        echo "   - Bindings: " . json_encode($query['bindings']) . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error testing BillingPeriod model: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 3: Test with raw query
echo "\n3. TESTING RAW QUERY:\n";
echo "   " . str_repeat("-", 50) . "\n";

try {
    $testMonth = '2025-03';
    $result = DB::select("SELECT * FROM billing_periods WHERE billing_month = ?", [$testMonth]);
    echo "   Raw query result: " . count($result) . " records found\n";
    
    if (!empty($result)) {
        foreach ($result as $record) {
            echo "   - Month: {$record->billing_month}, Closed: {$record->is_closed}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error with raw query: " . $e->getMessage() . "\n";
}

// Test 4: Check if there are any global scopes
echo "\n4. CHECKING MODEL CONFIGURATION:\n";
echo "   " . str_repeat("-", 50) . "\n";

try {
    $model = new BillingPeriod();
    echo "   Table name: " . $model->getTable() . "\n";
    echo "   Primary key: " . $model->getKeyName() . "\n";
    echo "   Fillable: " . json_encode($model->getFillable()) . "\n";
    
    // Check if there are any global scopes
    $globalScopes = $model->getGlobalScopes();
    echo "   Global scopes: " . (empty($globalScopes) ? 'None' : json_encode(array_keys($globalScopes))) . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Error checking model configuration: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";