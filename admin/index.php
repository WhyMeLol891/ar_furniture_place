<?php
/**
 * Admin Product Management Dashboard
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../includes/products.php';
require_once __DIR__ . '/../includes/categories.php';

$baseUrl = getBaseUrl();
$selectedCat = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
$search = trim($_GET['search'] ?? '');

$products = getProducts($selectedCat, false, $search);
$categories = getAllCategories();

// Calculate Stats
$totalProducts = count($products);
$totalGlb = 0;
$activeCount = 0;
foreach ($products as $p) {
    if (!empty($p['glb_path'])) $totalGlb++;
    if ($p['is_active']) $activeCount++;
}

renderAdminHeader('Products Catalog', 'products');
?>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
                    <i class="fa-solid fa-chair fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Total Products</h6>
                    <h3 class="fw-bold mb-0"><?= $totalProducts ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
                    <i class="fa-solid fa-cube fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">3D GLB Models</h6>
                    <h3 class="fw-bold mb-0"><?= $totalGlb ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
                    <i class="fa-solid fa-layer-group fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Categories</h6>
                    <h3 class="fw-bold mb-0"><?= count($categories) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
                    <i class="fa-solid fa-eye fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted small mb-1">Active in Catalog</h6>
                    <h3 class="fw-bold mb-0"><?= $activeCount ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-0">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-5">
                <h4 class="fw-bold text-dark mb-0">Furniture Inventory</h4>
            </div>
            <div class="col-12 col-md-7 d-flex flex-wrap justify-content-md-end gap-2">
                <a href="<?= $baseUrl ?>admin/product_edit.php" class="btn btn-primary shadow-sm">
                    <i class="fa-solid fa-plus me-1"></i> Add New Product
                </a>
            </div>
        </div>
        
        <!-- Filter and Search Bar -->
        <form method="GET" action="<?= $baseUrl ?>admin/index.php" class="row g-2 mt-3 pt-3 border-top">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search product name..." value="<?= sanitize($search) ?>">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($selectedCat === (int)$cat['id']) ? 'selected' : '' ?>>
                            <?= sanitize($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-secondary flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                <?php if ($selectedCat !== null || $search !== ''): ?>
                    <a href="<?= $baseUrl ?>admin/index.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:70px;" class="ps-4">Preview</th>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Physical Dimensions</th>
                        <th>3D / AR Assets</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open fa-3x mb-3 text-secondary opacity-50"></i>
                                <p class="mb-0">No products found matching the criteria.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td class="ps-4">
                                    <?php 
                                    $thumbFile = $p['thumb_path'] ? realpath(__DIR__ . '/../' . $p['thumb_path']) : null;
                                    if ($thumbFile && file_exists($thumbFile)): ?>
                                        <img src="<?= $baseUrl . sanitize($p['thumb_path']) ?>" alt="<?= sanitize($p['name']) ?>" width="54" height="54" class="rounded object-fit-cover border shadow-sm">
                                    <?php else: ?>
                                        <div class="bg-light text-muted border rounded d-flex align-items-center justify-content-center" style="width:54px;height:54px;">
                                            <i class="fa-solid fa-chair text-secondary"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= sanitize($p['name']) ?></div>
                                    <small class="text-muted font-monospace">slug: <?= sanitize($p['slug']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border border-primary-subtle">
                                        <?= sanitize($p['category_name']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">
                                    <?= formatPrice((float)$p['price'], $p['currency']) ?>
                                </td>
                                <td class="small text-secondary">
                                    <i class="fa-solid fa-ruler-combined text-primary me-1"></i>
                                    <?= number_format((float)$p['width_cm'], 1) ?> × <?= number_format((float)$p['height_cm'], 1) ?> × <?= number_format((float)$p['depth_cm'], 1) ?> cm
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php if (!empty($p['glb_path'])): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" title="GLB / WebXR format">
                                                <i class="fa-solid fa-cube me-1"></i> GLB
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle" title="Missing WebXR GLB">
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i> No GLB
                                            </span>
                                        <?php endif; ?>

                                        <?php if (!empty($p['usdz_path'])): ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle" title="iOS QuickLook format">
                                                <i class="fa-brands fa-apple me-1"></i> USDZ
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($p['is_active']): ?>
                                        <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fa-solid fa-eye-slash me-1"></i> Hidden</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $baseUrl ?>product.php?slug=<?= urlencode($p['slug']) ?>" target="_blank" class="btn btn-outline-secondary" title="View Public AR Page">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                        <a href="<?= $baseUrl ?>admin/product_edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-outline-primary" title="Edit Product">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="<?= $baseUrl ?>admin/product_delete.php?id=<?= (int)$p['id'] ?>&token=<?= generateCSRFToken() ?>" 
                                           class="btn btn-outline-danger" 
                                           title="Delete Product"
                                           onclick="return confirm('Are you sure you want to delete \'<?= addslashes(sanitize($p['name'])) ?>\'? This action cannot be undone.');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php renderAdminFooter(); ?>