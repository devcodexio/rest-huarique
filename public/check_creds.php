<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../libs/cloudinary_handler.php';

echo "<h1>Cloudinary Credentials Check</h1>\n";

$url = getenv('CLOUDINARY_URL');
if (preg_match('/cloudinary:\/\/([^:]+):([^@]+)@(.+)/', $url, $matches)) {
    echo "API Key: " . $matches[1] . "\n";
    echo "API Secret: " . $matches[2] . "\n";
    echo "Cloud Name: " . $matches[3] . "\n";
} else {
    echo "Regex failed to match URL: $url\n";
}
