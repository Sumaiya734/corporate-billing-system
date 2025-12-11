<?php

$filePath = 'app/Http/Controllers/Admin/BillingController.php';
$content = file_get_contents($filePath);

// Remove any trailing whitespace and extra closing braces
$content = rtrim($content);

// Ensure it ends with exactly one closing brace
while (substr($content, -1) === '}') {
    $content = substr($content, 0, -1);
    $content = rtrim($content);
}

// Add the final closing brace
$content .= "\n}\n";

file_put_contents($filePath, $content);

echo "Fixed closing brace in BillingController.php\n";