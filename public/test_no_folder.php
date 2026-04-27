<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../libs/cloudinary_handler.php';

echo "<h1>Test Upload No Folder</h1>\n";

try {
    $url = getenv('CLOUDINARY_URL');
    $cloudinary = new Cloudinary\Cloudinary($url);
    
    // Create a dummy image file
    $dummy_file = __DIR__ . '/dummy.txt';
    file_put_contents($dummy_file, 'This is a test file for Cloudinary upload.');
    
    echo "Uploading local file with NO folder...\n";
    $result = $cloudinary->uploadApi()->upload($dummy_file);
    
    echo "Result: " . print_r($result, true) . "\n";
    
    unlink($dummy_file);
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
