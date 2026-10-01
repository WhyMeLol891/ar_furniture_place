<?php
/**
 * Global Configuration Settings
 * AR Spatial Furniture Catalog & Visualization System
 */

// Application Constants
define('APP_NAME', 'AR Spatial Furniture');
define('APP_TAGLINE', 'Web-Based 3D Catalog & Augmented Reality Visualization');
define('APP_CURRENCY', 'RM');

// Database Configuration (Default XAMPP settings)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'synergy1_derricklim_ar_furniture_place');
define('DB_USER', 'synergy1_yenping');
define('DB_PASS', 'R.zb0ZwEuGZ}*fW2');
define('DB_CHARSET', 'utf8mb4');

// File Upload Limits & Allowed Formats
define('MAX_FILE_SIZE_MB', 50);
define('ALLOWED_MODEL_EXTS', ['glb', 'gltf', 'usdz']);
define('ALLOWED_IMAGE_EXTS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

/**
 * Dynamically resolves the project Base URL across XAMPP subdirectories and cPanel root domains.
 *
 * @return string Normalized Base URL with trailing slash (e.g. "http://localhost/ar_furniture_place/")
 */
function getBaseUrl(): string {
    if (php_sapi_name() === 'cli' && empty($_SERVER['HTTP_HOST'])) {
        return 'http://localhost/ar_furniture_place/';
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    $protocol = $isHttps ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Normalize SCRIPT_NAME on Windows / Linux
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $dir = str_replace('\\', '/', dirname($scriptName));
    
    // Remove trailing /admin, /api, /includes, /config if called from nested folders
    $dir = preg_replace('#/(admin|api|includes|config)$#i', '', $dir);
    $path = ($dir === '/' || $dir === '.') ? '' : $dir;

    return rtrim($protocol . $host . $path, '/') . '/';
}