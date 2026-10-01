<?php
/**
 * Admin Panel Layout Wrapper
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/config.php';
requireAdminAuth();

function renderAdminHeader(string $title = 'Admin Dashboard', string $activeNav = 'products'): void {
    $baseUrl = getBaseUrl();
    $adminUser = sanitize($_SESSION['admin_username'] ?? 'Admin');
    $flash = getFlashMessage();
    
    $productsActive = ($activeNav === 'products') ? 'active fw-bold text-white' : 'text-white-50';
    $categoriesActive = ($activeNav === 'categories') ? 'active fw-bold text-white' : 'text-white-50';

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - AR Furniture Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Application Stylesheets -->
    <link rel="stylesheet" href="{$baseUrl}css/style.css">
    <link rel="stylesheet" href="{$baseUrl}css/styles.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="{$baseUrl}admin/index.php">
            <i class="fa-solid fa-cube text-primary me-2 fs-5"></i>
            <span>AR Admin Console</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {$productsActive}" href="{$baseUrl}admin/index.php">
                        <i class="fa-solid fa-chair me-1"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {$categoriesActive}" href="{$baseUrl}admin/categories.php">
                        <i class="fa-solid fa-layer-group me-1"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50" href="{$baseUrl}" target="_blank">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Catalog
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <span class="badge bg-secondary me-3 px-3 py-2">
                    <i class="fa-solid fa-user-shield me-1 text-info"></i> {$adminUser}
                </span>
                <a href="{$baseUrl}admin/logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="container-fluid px-4 py-4 flex-grow-1">
HTML;

    if ($flash) {
        $alertType = sanitize($flash['type']);
        $alertText = sanitize($flash['text']);
        echo <<<HTML
    <div class="alert alert-{$alertType} alert-dismissible fade show shadow-sm" role="alert">
        {$alertText}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
HTML;
    }
}

function renderAdminFooter(): void {
    $year = date('Y');
    echo <<<HTML
</main>

<footer class="bg-white border-top py-3 text-center text-muted small mt-auto">
    <div class="container-fluid px-4">
        &copy; {$year} AR Spatial Furniture Catalog & Visualization System &bull; Admin Console
    </div>
</footer>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
HTML;
}