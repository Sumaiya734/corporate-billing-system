<?php
$content = file_get_contents('resources/views/admin/billing/monthly-bills.blade.php');
$pattern = '/<div class="alert alert-info mb-4">\s*<strong><i class="fas fa-info-circle me-1"><\/i>How to read this table:<\/strong>\s*<ul class="mb-0 mt-1">.*?<\/ul>\s*<\/div>\s*<div class="alert alert-info mb-4">/s';
$replacement = '<div class="alert alert-info mb-4">';
$content = preg_replace($pattern, $replacement, $content);
file_put_contents('resources/views/admin/billing/monthly-bills.blade.php', $content);
echo "Duplicate section removed successfully.\n";
?>