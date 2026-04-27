<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../libs/cloudinary_handler.php';

echo "<h1>Test Local File Upload</h1>\n";

try {
    $handler = CloudinaryHandler::getInstance();
    
    // Create a dummy image file
    $dummy_file = __DIR__ . '/dummy.txt';
    file_put_contents($dummy_file, 'This is a test file for Cloudinary upload.');
    
    echo "Uploading local file: $dummy_file\n";
    $result = $handler->upload($dummy_file, 'test');
    
    if ($result) {
        echo "SUCCESS: $result\n";
    } else {
        echo "FAILED\n";
    }
    
    unlink($dummy_file);
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
