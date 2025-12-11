<?php

echo "=== FINAL EXPLODE FIX ===\n\n";

$filePath = 'app/Http/Controllers/Admin/MonthlyBillController.php';

// Read the file
$content = file_get_contents($filePath);

if ($content === false) {
    echo "❌ Could not read file: $filePath\n";
    exit(1);
}

echo "✅ File read successfully\n";

// Replace the unsafe list assignment with safe version
$unsafePattern = 'list($p_id, $price, $cycle, $cp_id) = explode(\':\', $product);';
$safeReplacement = 'if (strpos($product, \':\') !== false) {
                            $productParts = explode(\':\', $product);
                            if (count($productParts) >= 4) {
                                list($p_id, $price, $cycle, $cp_id) = $productParts;';

$content = str_replace($unsafePattern, $safeReplacement, $content);

echo "✅ Replaced unsafe list assignments\n";

// Now we need to close the if statements we opened
// Find the pattern where we need to close the if statements
$oldClosingPattern = '                        $productDetails[] = [
                            \'p_id\' => $p_id,
                            \'cp_id\' => $cp_id,
                            \'monthly_price\' => $price,
                            \'billing_cycle_months\' => $cycle
                        ];';

$newClosingPattern = '                                $productDetails[] = [
                                    \'p_id\' => $p_id,
                                    \'cp_id\' => $cp_id,
                                    \'monthly_price\' => $price,
                                    \'billing_cycle_months\' => $cycle
                                ];
                            }
                        }';

$content = str_replace($oldClosingPattern, $newClosingPattern, $content);

echo "✅ Fixed closing braces for new if statements\n";

// Write the file back
$result = file_put_contents($filePath, $content);

if ($result === false) {
    echo "❌ Could not write file: $filePath\n";
    exit(1);
}

echo "✅ File written successfully\n";

// Final verification
$remainingUnsafe = substr_count($content, 'list($p_id, $price, $cycle, $cp_id) = explode');
echo "Remaining unsafe explode calls: $remainingUnsafe\n";

if ($remainingUnsafe === 0) {
    echo "✅ All unsafe explode calls have been fixed!\n";
} else {
    echo "⚠️  Still have $remainingUnsafe unsafe explode calls\n";
}

echo "\n=== FINAL FIX COMPLETE ===\n";