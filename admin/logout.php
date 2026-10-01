<?php
/**
 * Admin Logout Handler
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

logoutAdmin();
header('Location: ' . getBaseUrl() . 'admin/login.php');
exit();