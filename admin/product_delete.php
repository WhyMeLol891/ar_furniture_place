<?php
/**
 * Admin Product Delete Handler
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/products.php';

requireAdminAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token = $_GET['token'] ?? '';

if ($id > 0 && verifyCSRFToken($token)) {
    if (deleteProduct($id)) {
        setFlashMessage('success', 'Product and associated 3D models were successfully deleted.');
    } else {
        setFlashMessage('danger', 'Failed to delete product. Item not found.');
    }
} else {
    setFlashMessage('danger', 'Invalid security token or invalid request.');
}

header('Location: ' . getBaseUrl() . 'admin/index.php');
exit();