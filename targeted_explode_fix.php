<?php

echo "=== TARGETED EXPLODE FIX ===\n\n";

$filePath = 'app/Http/Controllers/Admin/MonthlyBillController.php';

// Read the file as an array of lines
$lines = file($filePath, FILE_IGNORE_NEW_LINES);

if ($lines === false) {
    echo "❌ Could not read file: $filePath\n";
    exit(1);
}

echo "✅ File read successfully (" . count($lines) . " lines)\n";

// Fix line 302 (if condition)
if (isset($lines[301]) && strpos($lines[301], 'if ($customer->product_details)') !== false) {
    $lines[301] = str_replace(
        'if ($customer->product_details)',
        'if ($customer->product_details && is_string($customer->product_details))',
        $lines[301]
    );
    echo "✅ Fixed line 302 (if condition)\n";
}

// Fix line 304 (list assignment)
if (isset($lines[303]) && strpos($lines[303], 'list($p_id, $price, $cycle, $cp_id) = explode') !== false) {
    // Replace the list assignment with safe version
    $indent = str_repeat(' ', 24); // Match existing indentation
    $lines[303] = $indent . 'if (strpos($product, \':\') !== false) {';
    
    // Insert new lines after 303
    array_splice($lines, 304, 0, [
        $indent . '    $productParts = explode(\':\', $product);',
        $indent . '    if (count($productParts) >= 4) {',
        $indent . '        list($p_id, $price, $cycle, $cp_id) = $productParts;'
    ]);
    
    // Find the closing brace of the foreach and add our closing braces
    for ($i = 304; $i < count($lines); $i++) {
        if (strpos($lines[$i], '                    }') !== false && strpos($lines[$i-1], '];') !== false) {
            // This is the end of the foreach, add our closing braces
            array_splice($lines, $i, 0, [
                $indent . '    }',
                $indent . '}'
            ]);
            break;
        }
    }
    
    echo "✅ Fixed line 304 (list assignment) in first method\n";
}

// Fix the second occurrence around line 1686
for ($i = 1680; $i < count($lines); $i++) {
    if (strpos($lines[$i], 'if ($customer->product_details)') !== false) {
        $lines[$i] = str_replace(
            'if ($customer->product_details)',
            'if ($customer->product_details && is_string($customer->product_details))',
            $lines[$i]
        );
        echo "✅ Fixed second if condition at line " . ($i + 1) . "\n";
        break;
    }
}

// Fix the second list assignment
for ($i = 1680; $i < count($lines); $i++) {
    if (strpos($lines[$i], 'list($p_id, $price, $cycle, $cp_id) = explode') !== false) {
        $indent = str_repeat(' ', 24); // Match existing indentation
        $lines[$i] = $indent . 'if (strpos($product, \':\') !== false) {';
        
        // Insert new lines after current line
        array_splice($lines, $i + 1, 0, [
            $indent . '    $productParts = explode(\':\', $product);',
            $indent . '    if (count($productParts) >= 4) {',
            $indent . '        list($p_id, $price, $cycle, $cp_id) = $productParts;'
        ]);
        
        // Find the closing brace of the foreach and add our closing braces
        for ($j = $i + 1; $j < count($lines); $j++) {
            if (strpos($lines[$j], '                    }') !== false && strpos($lines[$j-1], '];') !== false) {
                // This is the end of the foreach, add our closing braces
                array_splice($lines, $j, 0, [
                    $indent . '    }',
                    $indent . '}'
                ]);
                break;
            }
        }
        
        echo "✅ Fixed second list assignment at line " . ($i + 1) . "\n";
        break;
    }
}

// Write the file back
$result = file_put_contents($filePath, implode("\n", $lines) . "\n");

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

echo "\n=== TARGETED FIX COMPLETE ===\n";