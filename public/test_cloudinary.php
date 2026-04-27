<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../libs/cloudinary_handler.php';

echo "<h1>Test Cloudinary Upload Debug</h1>\n";

$url = getenv('CLOUDINARY_URL');
echo "URL found: " . ($url ? "YES" : "NO") . "\n";

try {
    $handler = CloudinaryHandler::getInstance();
    
    // Test with a very simple upload
    $test_image = "https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png";
    
    echo "Uploading...\n";
    $cloudinary = new Cloudinary\Cloudinary(getenv('CLOUDINARY_URL'));
    $result = $cloudinary->uploadApi()->upload($test_image);
    
    echo "Result: " . print_r($result, true) . "\n";
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "TRACE: " . $e->getTraceAsString() . "\n";
}
