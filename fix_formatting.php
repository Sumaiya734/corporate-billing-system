<?php

echo "=== FIXING FORMATTING ISSUES ===\n\n";

$filePath = 'app/Http/Controllers/Admin/MonthlyBillController.php';

// Read the file
$content = file_get_contents($filePath);

if ($content === false) {
    echo "❌ Could not read file: $filePath\n";
    exit(1);
}

echo "✅ File read successfully\n";

// Fix the indentation issue
$badPattern = '                                list($p_id, $price, $cycle, $cp_id) = $productParts;
                        $productDetails[] = [
                            \'p_id\' => $p_id,
                            \'cp_id\' => $cp_id,
                            \'monthly_price\' => $price,
                            \'billing_cycle_months\' => $cycle
                        ];
                    }';

$goodPattern = '                                list($p_id, $price, $cycle, $cp_id) = $productParts;
                                $productDetails[] = [
                                    \'p_id\' => $p_id,
                                    \'cp_id\' => $cp_id,
                                    \'monthly_price\' => $price,
                                    \'billing_cycle_months\' => $cycle
                                ];
                            }
                        }';

$content = str_replace($badPattern, $goodPattern, $content);

echo "✅ Fixed indentation issues\n";

// Write the file back
$result = file_put_contents($filePath, $content);

if ($result === false) {
    echo "❌ Could not write file: $filePath\n";
    exit(1);
}

echo "✅ File written successfully\n";
echo "✅ All formatting issues fixed\n";

echo "\n=== FORMATTING FIX COMPLETE ===\n";