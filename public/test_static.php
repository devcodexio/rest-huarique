<?php
require_once __DIR__ . '/../config/db.php';

echo "<h1>Test Static Uploader</h1>\n";

try {
    $url = getenv('CLOUDINARY_URL');
    
    // In v2/v3, Configuration must be set for static classes to work
    \Cloudinary\Configuration\Configuration::instance($url);
    
    $test_image = "https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png";
    
    echo "Uploading via static Uploader...\n";
    $result = \Cloudinary\Uploader::upload($test_image, [
        'folder' => 'test_static'
    ]);
    
    echo "Result: " . print_r($result, true) . "\n";
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
