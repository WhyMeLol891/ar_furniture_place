<?php
/**
 * Admin Categories List & Management
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../includes/categories.php';

$baseUrl = getBaseUrl();
$categories = getAllCategories(false);

renderAdminHeader('Categories Management', 'categories');
?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-dark mb-0">Furniture Categories</h4>
            <p class="text-muted small mb-0">Organize and group 3D furniture items for customer discovery.</p>
        </div>
        <a href="<?= $baseUrl ?>admin/category_edit.php" class="btn btn-primary shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Add New Category
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 80px;">Order</th>
                        <th>Category Name</th>
                        <th>URL Slug</th>
                        <th>Products Linked</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-layer-group fa-3x mb-3 text-secondary opacity-50"></i>
                                <p class="mb-0">No categories found. Click "Add New Category" above to create one.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="ps-4 fw-semibold text-secondary">
                                    #<?= (int)$cat['sort_order'] ?>
                                </td>
                                <td class="fw-bold text-dark">
                                    <i class="fa-solid fa-folder text-warning me-2"></i>
                                    <?= sanitize($cat['name']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark font-monospace border">
                                        <?= sanitize($cat['slug']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                                        <i class="fa-solid fa-cube me-1"></i> <?= (int)($cat['product_count'] ?? 0) ?> items
                                    </span>
                                </td>
                                <td>
                                    <?php if ($cat['is_active']): ?>
                                        <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fa-solid fa-eye-slash me-1"></i> Hidden</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $baseUrl ?>?category=<?= urlencode($cat['slug']) ?>" target="_blank" class="btn btn-outline-secondary" title="View in Catalog">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                        <a href="<?= $baseUrl ?>admin/category_edit.php?id=<?= (int)$cat['id'] ?>" class="btn btn-outline-primary" title="Edit Category">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="<?= $baseUrl ?>admin/category_delete.php?id=<?= (int)$cat['id'] ?>&token=<?= generateCSRFToken() ?>" 
                                           class="btn btn-outline-danger" 
                                           title="Delete Category"
                                           onclick="return confirm('Deleting category \'<?= addslashes(sanitize($cat['name'])) ?>\' will also remove linked products. Continue?');">
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
