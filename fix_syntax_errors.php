<?php

echo "=== FIXING SYNTAX ERRORS ===\n\n";

$filePath = 'app/Http/Controllers/Admin/MonthlyBillController.php';

// Read the file
$content = file_get_contents($filePath);

if ($content === false) {
    echo "❌ Could not read file: $filePath\n";
    exit(1);
}

echo "✅ File read successfully\n";

// Let's completely rewrite the problematic sections
// Pattern 1: Fix the first occurrence (around line 300)
$pattern1 = '/(\s+)foreach \(\$products as \$product\) \{\s+if \(strpos\(\$product, \':\'\) !== false\) \{\s+\$productParts = explode\(\':\', \$product\);\s+if \(count\(\$productParts\) >= 4\) \{\s+list\(\$p_id, \$price, \$cycle, \$cp_id\) = \$productParts;\s+\$productDetails\[\] = \[\s+\'p_id\' => \$p_id,\s+\'cp_id\' => \$cp_id,\s+\'monthly_price\' => \$price,\s+\'billing_cycle_months\' => \$cycle\s+\];\s+\}\s+\}/';

$replacement1 = '$1foreach ($products as $product) {
$1    if (strpos($product, \':\') !== false) {
$1        $productParts = explode(\':\', $product);
$1        if (count($productParts) >= 4) {
$1            list($p_id, $price, $cycle, $cp_id) = $productParts;
$1            $productDetails[] = [
$1                \'p_id\' => $p_id,
$1                \'cp_id\' => $cp_id,
$1                \'monthly_price\' => $price,
$1                \'billing_cycle_months\' => $cycle
$1            ];
$1        }
$1    }
$1}';

// This regex approach is too complex, let's use a simpler string replacement approach

// Find and replace the malformed sections
$badSection1 = '                    foreach ($products as $product) {
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
                }';

$goodSection1 = '                    foreach ($products as $product) {
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

$content = str_replace($badSection1, $goodSection1, $content);

echo "✅ Fixed malformed foreach sections\n";

// Write the file back
$result = file_put_contents($filePath, $content);

if ($result === false) {
    echo "❌ Could not write file: $filePath\n";
    exit(1);
}

echo "✅ File written successfully\n";

// Check syntax
$output = [];
$returnCode = 0;
exec("php -l $filePath 2>&1", $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ PHP syntax is now valid!\n";
} else {
    echo "❌ PHP syntax still has errors:\n";
    foreach ($output as $line) {
        echo "   $line\n";
    }
}

echo "\n=== SYNTAX FIX COMPLETE ===\n";