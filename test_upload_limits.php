<?php
echo "Current PHP Upload Limits:\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";

// Test file upload simulation
if ($_FILES) {
    echo "File upload attempted:\n";
    print_r($_FILES);
} else {
    echo "No file upload detected.\n";
    echo "To test file upload, use: curl -F 'file=@path/to/test.jpg' http://localhost/test_upload_limits.php\n";
}
?>