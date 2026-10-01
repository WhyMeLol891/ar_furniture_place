<?php
/**
 * Public Storefront & 3D Furniture Catalog
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/products.php';
require_once __DIR__ . '/includes/categories.php';
require_once __DIR__ . '/includes/auth.php';

$baseUrl = getBaseUrl();
$selectedCatSlug = sanitize($_GET['category'] ?? '');
$searchQuery = trim($_GET['q'] ?? '');

$categories = getAllCategories(true);

$selectedCatId = null;
$selectedCategoryName = 'All Furniture';

if (!empty($selectedCatSlug)) {
    $cat = getCategoryBySlug($selectedCatSlug);
    if ($cat) {
        $selectedCatId = (int)$cat['id'];
        $selectedCategoryName = $cat['name'];
    }
}

$products = getProducts($selectedCatId, true, $searchQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize(APP_NAME) ?> | 3D Spatial Furniture & AR Experience</title>
    <meta name="description" content="Explore life-sized 3D furniture models and visualize them in your real room using mobile Augmented Reality (WebXR).">
    
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Application Stylesheets -->
    <link rel="stylesheet" href="<?= $baseUrl ?>css/style.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>css/styles.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<!-- Main Navigation -->
<header class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary fs-4 d-flex align-items-center" href="<?= $baseUrl ?>">
            <i class="fa-solid fa-cube text-primary me-2"></i>
            <span><?= sanitize(APP_NAME) ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="mainNav">
            <form class="d-flex mx-auto my-2 my-lg-0 w-50" method="GET" action="<?= $baseUrl ?>">
                <?php if (!empty($selectedCatSlug)): ?>
                    <input type="hidden" name="category" value="<?= sanitize($selectedCatSlug) ?>">
                <?php endif; ?>
                <div class="input-group">
                    <input class="form-control rounded-start-pill ps-3" type="search" name="q" placeholder="Search furniture..." value="<?= sanitize($searchQuery) ?>" aria-label="Search">
                    <button class="btn btn-primary rounded-end-pill px-3" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </form>
            
            <div class="d-flex align-items-center gap-2">
                <?php if (isAdminLoggedIn()): ?>
                    <a href="<?= $baseUrl ?>admin/index.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-gauge-high me-1"></i> Admin Console
                    </a>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>admin/login.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-lock me-1"></i> Admin Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="hero-section text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-light text-primary px-3 py-2 rounded-pill fw-bold mb-3 shadow-sm">
                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Web-Based Augmented Reality
                </span>
                <h1 class="display-5 fw-bold mb-3">See Furniture in Your Space Before You Buy.</h1>
                <p class="lead text-light mb-4 opacity-90">
                    Interact with photorealistic 3D models directly in your browser. Launch instantaneous, life-sized 1:1 Augmented Reality (AR) on iOS and Android without installing any app.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#catalog" class="btn btn-light btn-lg text-primary fw-bold rounded-pill px-4 shadow">
                        <i class="fa-solid fa-couch me-2"></i> Browse Collection
                    </a>
                    <a href="<?= $baseUrl ?>api/products.php" target="_blank" class="btn btn-outline-light btn-lg rounded-pill px-4">
                        <i class="fa-solid fa-code me-2"></i> REST API
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center desktop-qr-block">
                <div class="p-4 bg-white text-dark rounded-4 shadow-lg d-inline-block text-center border">
                    <p class="small fw-bold mb-2 text-primary">
                        <i class="fa-solid fa-mobile-screen-button me-1"></i> Scan with Phone Camera
                    </p>
                    <div id="qr-container" class="my-2 d-flex justify-content-center"></div>
                    <p class="text-muted extra-small mb-0">Experience full WebXR AR on your mobile room camera.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Catalog Section -->
<main class="container mb-5 flex-grow-1" id="catalog">
    
    <!-- Category Filter Bar -->
    <div class="d-flex flex-wrap gap-2 mb-4 justify-content-center">
        <a href="<?= $baseUrl ?>" class="btn <?= empty($selectedCatSlug) ? 'btn-primary shadow-sm' : 'btn-outline-primary bg-white' ?> rounded-pill px-4">
            <i class="fa-solid fa-border-all me-1"></i> All Products
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= $baseUrl ?>?category=<?= urlencode($cat['slug']) ?>" 
               class="btn <?= $selectedCatSlug === $cat['slug'] ? 'btn-primary shadow-sm' : 'btn-outline-primary bg-white' ?> rounded-pill px-4">
                <?= sanitize($cat['name']) ?>
                <span class="badge bg-secondary-subtle text-dark ms-1"><?= (int)$cat['product_count'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Search / Filter Indicator -->
    <?php if ($searchQuery !== '' || !empty($selectedCatSlug)): ?>
        <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-white rounded-3 shadow-sm">
            <span class="text-muted">
                Showing results for <strong>"<?= sanitize($selectedCategoryName) ?>"</strong>
                <?= $searchQuery !== '' ? ' matching "<em>' . sanitize($searchQuery) . '</em>"' : '' ?>
                (<?= count($products) ?> items)
            </span>
            <a href="<?= $baseUrl ?>" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="fa-solid fa-xmark me-1"></i> Clear Filters
            </a>
        </div>
    <?php endif; ?>

    <!-- Product Grid -->
    <div class="row g-4">
        <?php if (empty($products)): ?>
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-white rounded-4 shadow-sm">
                    <i class="fa-solid fa-couch fa-4x text-muted mb-3 opacity-50"></i>
                    <h4 class="text-muted fw-bold">No products found.</h4>
                    <p class="text-secondary small mb-4">Try choosing a different category or clearing your search filters.</p>
                    <a href="<?= $baseUrl ?>" class="btn btn-primary rounded-pill px-4">View All Products</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $prod): ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card product-card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative d-flex flex-column">
                        
                        <!-- Top Badges -->
                        <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-1" style="z-index: 2;">
                            <span class="badge bg-dark bg-opacity-75 text-white backdrop-blur rounded-pill px-3 py-2 shadow-sm">
                                <?= sanitize($prod['category_name']) ?>
                            </span>
                            <?php if (!empty($prod['glb_path'])): ?>
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1 shadow-sm">
                                    <i class="fa-solid fa-vr-cardboard me-1"></i> 3D / AR Ready
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Thumbnail Link -->
                        <a href="<?= $baseUrl ?>product.php?slug=<?= urlencode($prod['slug']) ?>" class="product-thumb-link overflow-hidden bg-light text-center d-block">
                            <?php 
                            $thumbFile = $prod['thumb_path'] ? realpath(__DIR__ . '/' . $prod['thumb_path']) : null;
                            if ($thumbFile && file_exists($thumbFile)): ?>
                                <img src="<?= $baseUrl . sanitize($prod['thumb_path']) ?>" alt="<?= sanitize($prod['name']) ?>" class="w-100 object-fit-cover transition-zoom" style="height: 240px;" loading="lazy">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center bg-light text-secondary" style="height: 240px;">
                                    <i class="fa-solid fa-cube fa-4x opacity-25"></i>
                                </div>
                            <?php endif; ?>
                        </a>

                        <!-- Card Body -->
                        <div class="card-body p-4 d-flex flex-column flex-grow-1 bg-white">
                            <h5 class="fw-bold text-dark mb-2 text-truncate">
                                <a href="<?= $baseUrl ?>product.php?slug=<?= urlencode($prod['slug']) ?>" class="text-decoration-none text-dark hover-primary">
                                    <?= sanitize($prod['name']) ?>
                                </a>
                            </h5>
                            
                            <p class="text-muted small mb-3 flex-grow-1 line-clamp-2">
                                <?= sanitize(mb_substr($prod['description'] ?? '', 0, 95)) ?>...
                            </p>

                            <!-- Dimension pill -->
                            <div class="mb-3">
                                <span class="badge bg-light text-secondary border font-monospace small px-2 py-1">
                                    <i class="fa-solid fa-ruler-combined text-primary me-1"></i>
                                    <?= number_format((float)$prod['width_cm'], 1) ?> × <?= number_format((float)$prod['height_cm'], 1) ?> × <?= number_format((float)$prod['depth_cm'], 1) ?> cm
                                </span>
                            </div>

                            <!-- Price and Action -->
                            <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto">
                                <div>
                                    <small class="text-muted d-block extra-small">Price</small>
                                    <span class="fs-5 fw-bold text-primary"><?= formatPrice((float)$prod['price'], $prod['currency']) ?></span>
                                </div>
                                <a href="<?= $baseUrl ?>product.php?slug=<?= urlencode($prod['slug']) ?>" class="btn btn-primary rounded-pill px-3 shadow-sm fw-semibold">
                                    <span>Preview in AR</span>
                                    <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- Footer -->
<footer class="bg-white border-top py-4 text-center text-muted mt-auto">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-md-start mb-2 mb-md-0">
                &copy; <?= date('Y') ?> <strong><?= sanitize(APP_NAME) ?></strong>. All rights reserved.
            </div>
            <div class="col-md-6 text-md-end small">
                <span>Powered by WebXR, Google <code>&lt;model-viewer&gt;</code> & Apple QuickLook</span>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $baseUrl ?>js/qr.js"></script>
</body>
</html>