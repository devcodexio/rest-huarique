<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../libs/cloudinary_handler.php';

echo "<h1>Test Upload Real Image</h1>\n";

try {
    $url = getenv('CLOUDINARY_URL');
    $cloudinary = new Cloudinary\Cloudinary($url);
    
    $test_image = "https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png";
    
    echo "Uploading real image with folder 'test'...\n";
    $result = $cloudinary->uploadApi()->upload($test_image, [
        'folder' => 'test'
    ]);
    
    echo "Result: " . print_r($result, true) . "\n";
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
