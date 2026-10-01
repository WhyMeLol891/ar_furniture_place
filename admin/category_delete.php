<?php
/**
 * Admin Category Delete Controller
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/categories.php';

requireAdminAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token = $_GET['token'] ?? '';

if ($id > 0 && verifyCSRFToken($token)) {
    if (deleteCategory($id)) {
        setFlashMessage('success', 'Category and linked products were successfully deleted.');
    } else {
        setFlashMessage('danger', 'Failed to delete category.');
    }
} else {
    setFlashMessage('danger', 'Invalid security token or request.');
}

header('Location: ' . getBaseUrl() . 'admin/categories.php');
exit();
