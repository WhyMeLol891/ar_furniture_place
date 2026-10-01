<?php
/**
 * Admin Category Add / Edit Controller & View
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../includes/categories.php';

$baseUrl = getBaseUrl();
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$category = [
    'id'         => null,
    'name'       => '',
    'slug'       => '',
    'sort_order' => 0,
    'is_active'  => 1
];

if ($id) {
    $existing = getCategoryById($id);
    if ($existing) {
        $category = $existing;
    } else {
        setFlashMessage('danger', 'Category not found.');
        header("Location: {$baseUrl}admin/categories.php");
        exit();
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrfToken)) {
        $errors[] = 'Security token validation failed. Please try again.';
    }

    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '') {
        $errors[] = 'Category name is required.';
    }

    if (empty($errors)) {
        try {
            saveCategory([
                'name'       => $name,
                'slug'       => $slug,
                'sort_order' => $sortOrder,
                'is_active'  => $isActive
            ], $id);

            setFlashMessage('success', $id ? "Category '{$name}' updated successfully." : "Category '{$name}' created successfully.");
            header("Location: {$baseUrl}admin/categories.php");
            exit();
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    // Repopulate form
    $category['name'] = $name;
    $category['slug'] = $slug;
    $category['sort_order'] = $sortOrder;
    $category['is_active'] = $isActive;
}

renderAdminHeader($id ? 'Edit Category' : 'Add New Category', 'categories');
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="fw-bold text-dark mb-0">
                <i class="fa-solid <?= $id ? 'fa-pen-to-square' : 'fa-plus-circle' ?> text-primary me-2"></i>
                <?= $id ? 'Edit Category: ' . sanitize($category['name']) : 'Add New Category' ?>
            </h3>
            <a href="<?= $baseUrl ?>admin/categories.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Categories
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger shadow-sm">
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Please fix the errors:</h6>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $err): ?>
                        <li><?= sanitize($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="card border-0 shadow-sm rounded-3">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <div class="card-body p-4">
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= sanitize($category['name']) ?>" required placeholder="e.g. Living Room Sofas">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">URL Slug <small class="text-muted">(Optional, auto-generated)</small></label>
                    <input type="text" name="slug" class="form-control font-monospace" value="<?= sanitize($category['slug']) ?>" placeholder="e.g. living-room-sofas">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Display Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= (int)$category['sort_order'] ?>">
                    <small class="text-muted">Determines ordering on the public storefront navigation tabs.</small>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="activeSwitch" <?= !empty($category['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="activeSwitch">Active in Catalog</label>
                    </div>
                </div>

            </div>
            <div class="card-footer bg-light px-4 py-3 border-0 d-flex justify-content-between">
                <a href="<?= $baseUrl ?>admin/categories.php" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-xmark me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> <?= $id ? 'Update Category' : 'Save Category' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php renderAdminFooter(); ?>
