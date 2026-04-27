<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../libs/cloudinary_handler.php';

echo "<h1>Test Upload Raw</h1>\n";

try {
    $url = getenv('CLOUDINARY_URL');
    $cloudinary = new Cloudinary\Cloudinary($url);
    
    $dummy_file = __DIR__ . '/dummy.txt';
    file_put_contents($dummy_file, 'Test content');
    
    echo "Uploading raw file...\n";
    $result = $cloudinary->uploadApi()->upload($dummy_file, [
        'resource_type' => 'raw'
    ]);
    
    echo "Result: " . print_r($result, true) . "\n";
    unlink($dummy_file);
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
