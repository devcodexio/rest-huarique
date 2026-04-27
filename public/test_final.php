<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../libs/cloudinary_handler.php';

echo "<h1>Test Upload Real Image No Folder</h1>\n";

try {
    $url = getenv('CLOUDINARY_URL');
    $cloudinary = new Cloudinary\Cloudinary($url);
    
    $test_image = __DIR__ . '/test.png';
    
    echo "Uploading local image: $test_image with NO folder...\n";
    $result = $cloudinary->uploadApi()->upload($test_image);
    
    echo "Result: " . print_r($result, true) . "\n";
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
