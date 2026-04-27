<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../libs/cloudinary_handler.php';

echo "<h1>Listing Cloudinary Resources</h1>\n";

try {
    $url = getenv('CLOUDINARY_URL');
    $cloudinary = new Cloudinary\Cloudinary($url);
    
    echo "Attempting to list 5 resources...\n";
    $result = $cloudinary->adminApi()->assets([
        'max_results' => 5
    ]);
    
    echo "Result: " . print_r($result, true) . "\n";
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
