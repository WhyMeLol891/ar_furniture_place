<?php
/**
 * Database Connection Handler (PDO MySQL)
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/../config/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $port = defined('DB_PORT') ? ';port=' . DB_PORT : '';
        $dsn = "mysql:host=" . DB_HOST . $port . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die('<div style="font-family:sans-serif;padding:30px;text-align:center;">' .
                '<h2 style="color:#e53e3e;">Database Connection Failed</h2>' .
                '<p>Unable to connect to the MySQL database. Please verify your settings in <code>config/config.php</code>.</p>' .
                '<p style="color:#718096;font-size:14px;">Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>' .
                '</div>');
        }
    }
    return $pdo;
}