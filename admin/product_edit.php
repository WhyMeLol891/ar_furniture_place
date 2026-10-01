<?php
/**
 * Admin Product Add / Edit Controller & View
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../includes/products.php';
require_once __DIR__ . '/../includes/categories.php';

$db = getDB();
$baseUrl = getBaseUrl();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$product = [
    'id'          => null,
    'name'        => '',
    'slug'        => '',
    'category_id' => '',
    'description' => '',
    'price'       => '0.00',
    'currency'    => APP_CURRENCY,
    'width_cm'    => '0.0',
    'height_cm'   => '0.0',
    'depth_cm'    => '0.0',
    'sort_order'  => '0',
    'is_active'   => 1,
    'glb_path'    => '',
    'usdz_path'   => '',
    'thumb_path'  => ''
];

if ($id) {
    $existing = getProductById($id);
    if ($existing) {
        $product = $existing;
    } else {
        setFlashMessage('danger', 'Product not found.');
        header("Location: {$baseUrl}admin/index.php");
        exit();
    }
}

$categories = getAllCategories();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrfToken)) {
        $errors[] = 'Security token validation failed. Please try submitting the form again.';
    }

    $name = trim($_POST['name'] ?? '');
    $slugInput = trim($_POST['slug'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $currency = trim($_POST['currency'] ?? APP_CURRENCY);
    $width = (float)($_POST['width_cm'] ?? 0);
    $height = (float)($_POST['height_cm'] ?? 0);
    $depth = (float)($_POST['depth_cm'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '') {
        $errors[] = 'Product name is required.';
    }
    if ($catId <= 0) {
        $errors[] = 'Please select a valid category.';
    }
    if ($price < 0) {
        $errors[] = 'Price cannot be negative.';
    }

    // Generate or sanitize slug
    $slug = $slugInput !== '' ? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $slugInput)) : strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    $slug = trim($slug, '-');
    if ($slug === '') {
        $slug = 'product-' . time();
    }

    // Ensure slug uniqueness
    $originalSlug = $slug;
    $counter = 1;
    while (!isProductSlugUnique($slug, $id)) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }

    // Handle File Uploads
    $glbPath = $product['glb_path'];
    $usdzPath = $product['usdz_path'];
    $thumbPath = $product['thumb_path'];

    if (empty($errors)) {
        try {
            if (!empty($_FILES['glb_file']['name'])) {
                $uploadedGlb = handleFileUpload($_FILES['glb_file'], 'models', ['glb', 'gltf']);
                if ($uploadedGlb) {
                    $glbPath = $uploadedGlb;
                }
            }

            if (!empty($_FILES['usdz_file']['name'])) {
                $uploadedUsdz = handleFileUpload($_FILES['usdz_file'], 'models', ['usdz']);
                if ($uploadedUsdz) {
                    $usdzPath = $uploadedUsdz;
                }
            }

            if (!empty($_FILES['thumb_file']['name'])) {
                $uploadedThumb = handleFileUpload($_FILES['thumb_file'], 'uploads/thumbs', ALLOWED_IMAGE_EXTS);
                if ($uploadedThumb) {
                    $thumbPath = $uploadedThumb;
                }
            }

            // Compute meters from centimeters for 1:1 AR scaling metadata
            $glbXM = round($width / 100, 3);
            $glbYM = round($height / 100, 3);
            $glbZM = round($depth / 100, 3);

            if ($id) {
                $stmt = $db->prepare("UPDATE products 
                                      SET category_id = :cat, slug = :slug, name = :name, description = :desc, 
                                          price = :price, currency = :currency, glb_path = :glb, usdz_path = :usdz, 
                                          thumb_path = :thumb, width_cm = :w, height_cm = :h, depth_cm = :d, 
                                          glb_x_m = :gx, glb_y_m = :gy, glb_z_m = :gz, is_active = :active, sort_order = :sort 
                                      WHERE id = :id");
                $stmt->execute([
                    'cat'      => $catId,
                    'slug'     => $slug,
                    'name'     => $name,
                    'desc'     => $description,
                    'price'    => $price,
                    'currency' => $currency,
                    'glb'      => $glbPath,
                    'usdz'     => $usdzPath,
                    'thumb'    => $thumbPath,
                    'w'        => $width,
                    'h'        => $height,
                    'd'        => $depth,
                    'gx'       => $glbXM,
                    'gy'       => $glbYM,
                    'gz'       => $glbZM,
                    'active'   => $isActive,
                    'sort'     => $sortOrder,
                    'id'       => $id
                ]);
                setFlashMessage('success', "Product '{$name}' updated successfully.");
            } else {
                $stmt = $db->prepare("INSERT INTO products 
                                      (category_id, slug, name, description, price, currency, glb_path, usdz_path, 
                                       thumb_path, width_cm, height_cm, depth_cm, glb_x_m, glb_y_m, glb_z_m, is_active, sort_order) 
                                      VALUES (:cat, :slug, :name, :desc, :price, :currency, :glb, :usdz, 
                                              :thumb, :w, :h, :d, :gx, :gy, :gz, :active, :sort)");
                $stmt->execute([
                    'cat'      => $catId,
                    'slug'     => $slug,
                    'name'     => $name,
                    'desc'     => $description,
                    'price'    => $price,
                    'currency' => $currency,
                    'glb'      => $glbPath,
                    'usdz'     => $usdzPath,
                    'thumb'    => $thumbPath,
                    'w'        => $width,
                    'h'        => $height,
                    'd'        => $depth,
                    'gx'       => $glbXM,
                    'gy'       => $glbYM,
                    'gz'       => $glbZM,
                    'active'   => $isActive,
                    'sort'     => $sortOrder
                ]);
                $id = (int)$db->lastInsertId();
                setFlashMessage('success', "Product '{$name}' added to catalog successfully.");
            }

            header("Location: {$baseUrl}admin/index.php");
            exit();

        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    // Repopulate form with user inputs on failure
    $product['name'] = $name;
    $product['slug'] = $slugInput;
    $product['category_id'] = $catId;
    $product['price'] = $price;
    $product['currency'] = $currency;
    $product['width_cm'] = $width;
    $product['height_cm'] = $height;
    $product['depth_cm'] = $depth;
    $product['description'] = $description;
    $product['sort_order'] = $sortOrder;
    $product['is_active'] = $isActive;
}

renderAdminHeader($id ? 'Edit Product' : 'Add New Product', 'products');
?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="fw-bold text-dark mb-0">
                <i class="fa-solid <?= $id ? 'fa-pen-to-square' : 'fa-plus-circle' ?> text-primary me-2"></i>
                <?= $id ? 'Edit Product: ' . sanitize($product['name']) : 'Add New Product' ?>
            </h3>
            <a href="<?= $baseUrl ?>admin/index.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Inventory
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger shadow-sm">
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Please correct the following errors:</h6>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $err): ?>
                        <li><?= sanitize($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="card border-0 shadow-sm rounded-3">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <div class="card-body p-4">
                
                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-circle-info me-2"></i>Basic Information</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= sanitize($product['name']) ?>" required placeholder="e.g. Nordic Accent Lounge Chair">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">URL Slug <small class="text-muted">(Leave empty to auto-generate)</small></label>
                        <input type="text" name="slug" class="form-control font-monospace" value="<?= sanitize($product['slug']) ?>" placeholder="e.g. nordic-accent-lounge-chair">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ((int)$product['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                    <?= sanitize($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><?= sanitize($product['currency'] ?? APP_CURRENCY) ?></span>
                            <input type="number" step="0.01" min="0" name="price" class="form-control" value="<?= htmlspecialchars((string)$product['price'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Currency</label>
                        <input type="text" name="currency" class="form-control" value="<?= sanitize($product['currency'] ?? APP_CURRENCY) ?>" maxlength="10">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Detailed product specifications, materials, and features..."><?= sanitize($product['description']) ?></textarea>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-ruler-combined me-2"></i>Physical Dimensions (Real-World 1:1 AR Scale)</h5>
                <p class="text-muted small">Input dimensions in centimeters. These inform the user of real-world scale and physical room fit.</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Width (cm)</label>
                        <div class="input-group">
                            <input type="number" step="0.1" min="0" name="width_cm" class="form-control" value="<?= htmlspecialchars((string)$product['width_cm'], ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. 68.0">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Height (cm)</label>
                        <div class="input-group">
                            <input type="number" step="0.1" min="0" name="height_cm" class="form-control" value="<?= htmlspecialchars((string)$product['height_cm'], ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. 82.0">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Depth (cm)</label>
                        <div class="input-group">
                            <input type="number" step="0.1" min="0" name="depth_cm" class="form-control" value="<?= htmlspecialchars((string)$product['depth_cm'], ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. 75.0">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-cubes me-2"></i>3D Models & Media Assets</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <label class="form-label fw-bold text-dark d-block">
                                <i class="fa-solid fa-cube text-primary me-1"></i> WebXR 3D Model (.GLB / .GLTF)
                            </label>
                            <input type="file" name="glb_file" class="form-control mb-2" accept=".glb,.gltf">
                            <small class="text-muted d-block mb-2">Used for interactive 3D Web viewport & Android WebXR / Scene Viewer AR.</small>
                            <?php if (!empty($product['glb_path'])): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace text-wrap">
                                    <i class="fa-solid fa-file me-1"></i> <?= sanitize(basename($product['glb_path'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <label class="form-label fw-bold text-dark d-block">
                                <i class="fa-brands fa-apple text-dark me-1"></i> iOS AR Model (.USDZ) <span class="text-muted fw-normal small">(Optional)</span>
                            </label>
                            <input type="file" name="usdz_file" class="form-control mb-2" accept=".usdz">
                            <small class="text-muted d-block mb-2">Optimized for Apple iOS Quick Look Augmented Reality.</small>
                            <?php if (!empty($product['usdz_path'])): ?>
                                <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace text-wrap">
                                    <i class="fa-solid fa-file me-1"></i> <?= sanitize(basename($product['usdz_path'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <label class="form-label fw-bold text-dark d-block">
                                <i class="fa-solid fa-image text-success me-1"></i> Product Thumbnail Image
                            </label>
                            <input type="file" name="thumb_file" class="form-control mb-2" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted d-block mb-2">Displayed in public catalog grid cards.</small>
                            <?php if (!empty($product['thumb_path'])): ?>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <img src="<?= $baseUrl . sanitize($product['thumb_path']) ?>" alt="Thumbnail" width="45" height="45" class="rounded border object-fit-cover">
                                    <span class="badge bg-light text-dark font-monospace text-wrap"><?= sanitize(basename($product['thumb_path'])) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-sliders me-2"></i>Display Settings</h5>
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sort Priority</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int)$product['sort_order'] ?>">
                        <small class="text-muted">Lower numbers appear first in catalog.</small>
                    </div>
                    <div class="col-md-8">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="activeSwitch" <?= !empty($product['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold" for="activeSwitch">
                                Active & Visible in Public Storefront
                            </label>
                            <div class="text-muted small">Toggle to hide this product from public view without deleting its data.</div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-footer bg-light px-4 py-3 border-0 d-flex justify-content-between">
                <a href="<?= $baseUrl ?>admin/index.php" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-xmark me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> <?= $id ? 'Update Product' : 'Save New Product' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php renderAdminFooter(); ?>