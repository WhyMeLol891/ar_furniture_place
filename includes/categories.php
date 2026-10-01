<?php
/**
 * Category Management Business Logic
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/db.php';

/**
 * Retrieves all categories with optional product counts.
 */
function getAllCategories(bool $activeOnly = false): array {
    $db = getDB();
    $sql = "SELECT c.*, COUNT(p.id) AS product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id ";
    
    if ($activeOnly) {
        $sql .= " WHERE c.is_active = 1 ";
    }
    
    $sql .= " GROUP BY c.id ORDER BY c.sort_order ASC, c.name ASC";
    return $db->query($sql)->fetchAll();
}

/**
 * Retrieves single category by ID.
 */
function getCategoryById(int $id): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $cat = $stmt->fetch();
    return $cat ?: null;
}

/**
 * Retrieves single category by URL slug.
 */
function getCategoryBySlug(string $slug): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = :slug LIMIT 1");
    $stmt->execute(['slug' => trim($slug)]);
    $cat = $stmt->fetch();
    return $cat ?: null;
}

/**
 * Checks if a category slug is unique.
 */
function isCategorySlugUnique(string $slug, ?int $excludeId = null): bool {
    $db = getDB();
    $sql = "SELECT id FROM categories WHERE slug = :slug";
    $params = ['slug' => $slug];
    if ($excludeId !== null) {
        $sql .= " AND id != :id";
        $params['id'] = $excludeId;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() === false;
}

/**
 * Creates or updates category record.
 */
function saveCategory(array $data, ?int $id = null): int {
    $db = getDB();
    $name = trim($data['name'] ?? '');
    if ($name === '') {
        throw new InvalidArgumentException("Category name is required.");
    }

    $slug = trim($data['slug'] ?? '');
    if ($slug === '') {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $slug = trim($slug, '-');
    }

    // Ensure slug uniqueness
    $originalSlug = $slug;
    $counter = 1;
    while (!isCategorySlugUnique($slug, $id)) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }

    $sortOrder = (int)($data['sort_order'] ?? 0);
    $isActive = isset($data['is_active']) && (int)$data['is_active'] === 1 ? 1 : 0;

    if ($id) {
        $stmt = $db->prepare("UPDATE categories 
                              SET name = :name, slug = :slug, sort_order = :sort_order, is_active = :is_active 
                              WHERE id = :id");
        $stmt->execute([
            'name'       => $name,
            'slug'       => $slug,
            'sort_order' => $sortOrder,
            'is_active'  => $isActive,
            'id'         => $id
        ]);
        return $id;
    } else {
        $stmt = $db->prepare("INSERT INTO categories (name, slug, sort_order, is_active) 
                              VALUES (:name, :slug, :sort_order, :is_active)");
        $stmt->execute([
            'name'       => $name,
            'slug'       => $slug,
            'sort_order' => $sortOrder,
            'is_active'  => $isActive
        ]);
        return (int)$db->lastInsertId();
    }
}

/**
 * Deletes category by ID.
 */
function deleteCategory(int $id): bool {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
    return $stmt->execute(['id' => $id]);
}