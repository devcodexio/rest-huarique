<?php
require_once __DIR__ . '/../config/db.php';

echo "<h1>Manual CURL Cloudinary Upload</h1>\n";

$url = getenv('CLOUDINARY_URL');
if (!$url) die("No URL");

// Parse parts
preg_match('/cloudinary:\/\/([^:]+):([^@]+)@(.+)/', $url, $matches);
$key = $matches[1];
$secret = $matches[2];
$cloud = $matches[3];

$timestamp = time();
$params = [
    'timestamp' => $timestamp,
    'folder' => 'test_manual'
];
ksort($params);

$string_to_sign = "";
foreach($params as $k => $v) {
    $string_to_sign .= "$k=$v&";
}
$string_to_sign = rtrim($string_to_sign, '&') . $secret;
$signature = sha1($string_to_sign);

$post_fields = $params;
$post_fields['signature'] = $signature;
$post_fields['api_key'] = $key;
$post_fields['file'] = "https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png";

$ch = curl_init("https://api.cloudinary.com/v1_1/$cloud/image/upload");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass SSL for testing

$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "HTTP Code: " . $info['http_code'] . "\n";
echo "Response: " . $response . "\n";
