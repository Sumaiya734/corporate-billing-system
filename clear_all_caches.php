<?php

echo "=== CLEARING ALL CACHES ===\n\n";

// Clear Laravel caches
$commands = [
    'php artisan cache:clear',
    'php artisan config:clear', 
    'php artisan view:clear',
    'php artisan route:clear'
];

foreach ($commands as $command) {
    echo "Running: $command\n";
    $output = shell_exec($command . ' 2>&1');
    echo "Output: " . trim($output) . "\n\n";
}

echo "✅ All caches cleared!\n\n";

echo "NEXT STEPS:\n";
echo "1. Hard refresh your browser (Ctrl+F5 or Cmd+Shift+R)\n";
echo "2. Visit the billing-invoices page\n";
echo "3. Check February 2025 monthly-bills page\n";
echo "4. Verify the amounts match the database values:\n";
echo "   - February 2025: 2 customers, ৳3,500 total, ৳3,500 due\n";
echo "   - January 2025: 1 customer, ৳3,000 total, ৳2,000 due\n";
echo "   - Imteaz February invoice: ৳2,000 total, ৳2,000 due\n";

echo "\nIf the issue persists, it might be browser cache or session data.\n";