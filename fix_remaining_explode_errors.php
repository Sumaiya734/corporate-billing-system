<?php

echo "=== FIXING REMAINING EXPLODE ERRORS ===\n\n";

$filePath = 'app/Http/Controllers/Admin/MonthlyBillController.php';

// Read the file
$content = file_get_contents($filePath);

if ($content === false) {
    echo "❌ Could not read file: $filePath\n";
    exit(1);
}

echo "✅ File read successfully\n";

// Fix the remaining foreach loops that still use direct explode
$oldPattern = 'foreach ($products as $product) {
                        list($p_id, $price, $cycle, $cp_id) = explode(\':\', $product);
                        $productDetails[] = [
                            \'p_id\' => $p_id,
                            \'cp_id\' => $cp_id,
                            \'monthly_price\' => $price,
                            \'billing_cycle_months\' => $cycle
                        ];
                    }';

$newPattern = 'foreach ($products as $product) {
                        if (strpos($product, \':\') !== false) {
                            $productParts = explode(\':\', $product);
                            if (count($productParts) >= 4) {
                                list($p_id, $price, $cycle, $cp_id) = $productParts;
                                $productDetails[] = [
                                    \'p_id\' => $p_id,
                                    \'cp_id\' => $cp_id,
                                    \'monthly_price\' => $price,
                                    \'billing_cycle_months\' => $cycle
                                ];
                            }
                        }
                    }';

$content = str_replace($oldPattern, $newPattern, $content);

echo "✅ Fixed remaining foreach loops with explode\n";

// Write the file back
$result = file_put_contents($filePath, $content);

if ($result === false) {
    echo "❌ Could not write file: $filePath\n";
    exit(1);
}

echo "✅ File written successfully\n";

// Verify by checking for any remaining unsafe explode calls
$unsafePatterns = [
    '/list\([^)]+\) = explode\([^)]+\);/',
    '/explode\([^)]+, \$[^)]+\)(?!\s*;?\s*if)/'
];

$hasUnsafePatterns = false;
foreach ($unsafePatterns as $pattern) {
    if (preg_match($pattern, $content)) {
        echo "⚠️  Warning: Still found potentially unsafe explode pattern: $pattern\n";
        $hasUnsafePatterns = true;
    }
}

if (!$hasUnsafePatterns) {
    echo "✅ No unsafe explode patterns found\n";
}

echo "\n=== FIX COMPLETE ===\n";