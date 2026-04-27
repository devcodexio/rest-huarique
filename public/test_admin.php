<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../libs/cloudinary_handler.php';

echo "<h1>Cloudinary Admin API Test</h1>\n";

try {
    $url = getenv('CLOUDINARY_URL');
    $cloudinary = new Cloudinary\Cloudinary($url);
    
    echo "Attempting to list root folders...\n";
    $result = $cloudinary->adminApi()->rootFolders();
    
    echo "Result: " . print_r($result, true) . "\n";
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
