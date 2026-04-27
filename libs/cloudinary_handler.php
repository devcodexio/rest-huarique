<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Cloudinary;

class CloudinaryHandler {
    private static $instance = null;
    private $cloudinary;

    private function __construct() {
        if (file_exists(__DIR__ . '/../.env')) {
            require_once __DIR__ . '/../config/env_loader.php';
            EnvLoader::load(__DIR__ . '/../.env');
        }

        $url = getenv('CLOUDINARY_URL');
        if (!$url) {
            throw new Exception("CLOUDINARY_URL not found in environment.");
        }
        
        $this->cloudinary = new Cloudinary($url);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function upload($filePath, $folder = 'rest-huarique') {
        if (!file_exists($filePath)) {
            error_log("Cloudinary Upload Error: File not found at $filePath");
            return null;
        }

        try {
            $result = $this->cloudinary->uploadApi()->upload($filePath, [
                'folder' => $folder,
                'resource_type' => 'auto'
            ]);
            
            return $result['secure_url'] ?? null;
        } catch (Exception $e) {
            error_log("Cloudinary Upload Exception: " . $e->getMessage());
            // Do not throw here to avoid crashing the admin panel, just return null
            return null;
        }
    }
}
