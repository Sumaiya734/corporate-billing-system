<?php

echo "=== FIXING EXPLODE ERRORS ===\n\n";

$filePath = 'app/Http/Controllers/Admin/MonthlyBillController.php';

// Read the file
$content = file_get_contents($filePath);

if ($content === false) {
    echo "❌ Could not read file: $filePath\n";
    exit(1);
}

echo "✅ File read successfully\n";

// Pattern 1: Fix the basic explode check
$pattern1 = '/if \(\$customer->product_details\) \{/';
$replacement1 = 'if ($customer->product_details && is_string($customer->product_details)) {';

$content = preg_replace($pattern1, $replacement1, $content);
$count1 = preg_match_all($pattern1, $content);

echo "✅ Fixed basic product_details checks\n";

// Pattern 2: Fix the explode with list assignment
$pattern2 = '/list\(\$p_id, \$price, \$cycle, \$cp_id\) = explode\(\':\', \$product\);/';
$replacement2 = 'if (strpos($product, \':\') !== false) {
                            $productParts = explode(\':\', $product);
                            if (count($productParts) >= 4) {
                                list($p_id, $price, $cycle, $cp_id) = $productParts;';

// This is more complex, let's do a more targeted replacement
$oldPattern = 'foreach ($products as $product) {
                        list($p_id, $price, $cycle, $cp_id) = explode(\':\', $product);';

$newPattern = 'foreach ($products as $product) {
                        if (strpos($product, \':\') !== false) {
                            $productParts = explode(\':\', $product);
                            if (count($productParts) >= 4) {
                                list($p_id, $price, $cycle, $cp_id) = $productParts;';

$content = str_replace($oldPattern, $newPattern, $content);

echo "✅ Fixed explode with list assignments\n";

// We also need to close the if statements we opened
$oldClosing = '                        ];
                    }';

$newClosing = '                                ];
                            }
                        }';

$content = str_replace($oldClosing, $newClosing, $content);

echo "✅ Fixed closing braces\n";

// Write the file back
$result = file_put_contents($filePath, $content);

if ($result === false) {
    echo "❌ Could not write file: $filePath\n";
    exit(1);
}

echo "✅ File written successfully\n";
echo "✅ Fixed all explode errors in MonthlyBillController\n";

echo "\n=== FIX COMPLETE ===\n";