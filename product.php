<?php
/**
 * Product Details & Interactive 3D / AR Spatial Viewer
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/products.php';
require_once __DIR__ . '/includes/categories.php';
require_once __DIR__ . '/includes/auth.php';

$baseUrl = getBaseUrl();
$slug = sanitize($_GET['slug'] ?? '');
$product = $slug ? getProductBySlug($slug) : null;

// Handle 404
if (!$product || (!$product['is_active'] && !isAdminLoggedIn())) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Product Not Found - <?= sanitize(APP_NAME) ?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?= $baseUrl ?>css/style.css">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center min-vh-100 text-center p-4">
        <div class="card border-0 shadow-lg p-5 rounded-4" style="max-width: 500px;">
            <i class="fa-solid fa-triangle-exclamation fa-4x text-warning mb-3"></i>
            <h2 class="fw-bold text-dark">Furniture Item Not Found</h2>
            <p class="text-muted">The product you requested might have been moved, disabled, or removed from the catalog.</p>
            <a href="<?= $baseUrl ?>" class="btn btn-primary rounded-pill px-4 mt-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Catalog
            </a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$hasGlb = !empty($product['glb_path']) && file_exists(__DIR__ . '/' . $product['glb_path']);
$hasUsdz = !empty($product['usdz_path']) && file_exists(__DIR__ . '/' . $product['usdz_path']);
$glbUrl = $hasGlb ? $baseUrl . sanitize($product['glb_path']) : '';
$usdzUrl = $hasUsdz ? $baseUrl . sanitize($product['usdz_path']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($product['name']) ?> | 3D AR Spatial Viewer</title>
    <meta name="description" content="View <?= sanitize($product['name']) ?> in full interactive 3D and place it in your room using Augmented Reality.">
    
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Application Stylesheets -->
    <link rel="stylesheet" href="<?= $baseUrl ?>css/style.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>css/styles.css">

    <!-- Google <model-viewer> library for WebXR & AR QuickLook -->
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.4.0/model-viewer.min.js"></script>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<!-- Header Navigation -->
<header class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top shadow-sm">
    <div class="container">
        <a class="btn btn-outline-secondary btn-sm rounded-pill px-3" href="<?= $baseUrl ?>">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Catalog
        </a>
        <a class="navbar-brand fw-bold text-primary mx-auto fs-5" href="<?= $baseUrl ?>">
            <i class="fa-solid fa-cube me-1"></i> <?= sanitize(APP_NAME) ?>
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-none d-sm-inline-block">
                <?= sanitize($product['category_name']) ?>
            </span>
        </div>
    </div>
</header>

<main class="container my-4 flex-grow-1">
    
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>" class="text-decoration-none">Catalog</a></li>
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>?category=<?= urlencode($product['category_slug']) ?>" class="text-decoration-none"><?= sanitize($product['category_name']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= sanitize($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-4 align-items-stretch">
        
        <!-- Left Column: 3D Model Viewport & AR Controls -->
        <div class="col-lg-7 d-flex flex-column">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden flex-grow-1 bg-white p-2">
                <div class="model-viewer-wrapper position-relative w-100 rounded-3" style="min-height: 480px; height: 100%;">
                    <?php if ($hasGlb): ?>
                        <model-viewer 
                            id="furnitureViewer"
                            src="<?= $glbUrl ?>"
                            <?= $hasUsdz ? 'ios-src="' . $usdzUrl . '"' : '' ?>
                            alt="3D Spatial model of <?= sanitize($product['name']) ?>"
                            ar
                            ar-modes="webxr scene-viewer quick-look"
                            ar-scale="fixed"
                            camera-controls
                            touch-action="pan-y"
                            auto-rotate
                            rotation-per-second="20deg"
                            shadow-intensity="1.3"
                            shadow-softness="0.7"
                            exposure="1.0"
                            interaction-prompt="auto">
                            
                            <!-- Custom AR Launch Button -->
                            <button slot="ar-button" class="ar-button btn btn-primary shadow-lg d-flex align-items-center gap-2">
                                <i class="fa-solid fa-vr-cardboard fs-5"></i>
                                <span>Place in Your Room (AR)</span>
                            </button>

                            <!-- Loading Progress Indicator -->
                            <div slot="progress-bar" class="progress position-absolute bottom-0 start-0 w-100" style="height: 4px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%;"></div>
                            </div>
                        </model-viewer>

                        <!-- 3D Viewport Controls Toolbar -->
                        <div class="viewer-toolbar position-absolute top-0 end-0 m-3 d-flex flex-column gap-2" style="z-index: 5;">
                            <button id="btnResetCamera" class="btn btn-sm btn-white bg-white shadow-sm border rounded-circle" title="Reset View" style="width:38px;height:38px;">
                                <i class="fa-solid fa-rotate-left text-dark"></i>
                            </button>
                            <button id="btnToggleRotate" class="btn btn-sm btn-white bg-white shadow-sm border rounded-circle" title="Toggle Auto-Rotation" style="width:38px;height:38px;">
                                <i class="fa-solid fa-arrows-rotate text-dark"></i>
                            </button>
                            <button id="btnFullscreen" class="btn btn-sm btn-white bg-white shadow-sm border rounded-circle" title="Toggle Fullscreen" style="width:38px;height:38px;">
                                <i class="fa-solid fa-expand text-dark"></i>
                            </button>
                        </div>

                    <?php else: ?>
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted p-5 text-center">
                            <i class="fa-solid fa-cube fa-4x mb-3 text-secondary opacity-50"></i>
                            <h5 class="fw-bold text-dark">3D Model Being Prepared</h5>
                            <p class="small text-muted mb-0">The 3D GLB model for this furniture item is currently being optimized.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- AR Compatibility Alert -->
            <div id="ar-notice" class="alert alert-primary border-primary-subtle mt-3 mb-0 small d-flex align-items-center shadow-sm">
                <i class="fa-solid fa-circle-info fa-lg me-2 text-primary"></i>
                <div>
                    <strong>Real-World 1:1 Scale AR:</strong> Tap <em>"Place in Your Room"</em> on any AR-enabled smartphone (Google Chrome on Android / Safari on iOS) to test spatial fit and dimensions.
                </div>
            </div>
        </div>

        <!-- Right Column: Specs, Dimensions & Mobile Handoff -->
        <div class="col-lg-5 d-flex flex-column">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white flex-grow-1">
                
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                        <?= sanitize($product['category_name']) ?>
                    </span>
                    <span class="text-muted small font-monospace">SKU: <?= strtoupper(substr(md5($product['id']), 0, 8)) ?></span>
                </div>

                <h2 class="fw-bold text-dark mb-2"><?= sanitize($product['name']) ?></h2>
                <h3 class="text-primary fw-bold mb-3"><?= formatPrice((float)$product['price'], $product['currency']) ?></h3>
                
                <hr class="my-3">

                <!-- Physical Dimensions Block -->
                <h6 class="fw-bold text-dark mb-2">
                    <i class="fa-solid fa-ruler-combined me-2 text-primary"></i>Real-World Physical Dimensions
                </h6>
                <div class="row text-center g-2 my-2">
                    <div class="col-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <span class="d-block text-muted small fw-semibold">Width</span>
                            <strong class="fs-6 text-dark"><?= number_format((float)$product['width_cm'], 1) ?> cm</strong>
                            <small class="d-block text-secondary extra-small">(<?= number_format((float)$product['width_cm']/100, 2) ?> m)</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <span class="d-block text-muted small fw-semibold">Height</span>
                            <strong class="fs-6 text-dark"><?= number_format((float)$product['height_cm'], 1) ?> cm</strong>
                            <small class="d-block text-secondary extra-small">(<?= number_format((float)$product['height_cm']/100, 2) ?> m)</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <span class="d-block text-muted small fw-semibold">Depth</span>
                            <strong class="fs-6 text-dark"><?= number_format((float)$product['depth_cm'], 1) ?> cm</strong>
                            <small class="d-block text-secondary extra-small">(<?= number_format((float)$product['depth_cm']/100, 2) ?> m)</small>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <h6 class="fw-bold text-dark mt-3 mb-2">Product Description</h6>
                <div class="text-secondary small lh-base mb-3">
                    <?= nl2br(sanitize($product['description'])) ?>
                </div>

                <!-- Desktop-to-Mobile AR Handoff Card -->
                <div class="desktop-qr-block mt-auto p-3 bg-light border border-secondary-subtle rounded-3 text-center shadow-sm">
                    <h6 class="fw-bold small mb-1 text-primary">
                        <i class="fa-solid fa-qrcode me-1"></i> Scan with Phone Camera
                    </h6>
                    <p class="text-muted extra-small mb-2">
                        Point your mobile camera at this QR code to immediately launch Augmented Reality in your room.
                    </p>
                    <div id="qr-container" class="d-flex justify-content-center my-2"></div>
                </div>

            </div>
        </div>

    </div>
</main>

<footer class="bg-white border-top py-3 text-center text-muted small mt-auto">
    <div class="container">
        &copy; <?= date('Y') ?> <?= sanitize(APP_NAME) ?> &bull; Web-Based 3D AR Spatial Furniture Catalog
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $baseUrl ?>js/app.js"></script>
<script src="<?= $baseUrl ?>js/qr.js"></script>
</body>
</html>