<?php
/**
 * Product Management Business Logic & File Handlers
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Retrieves products with optional filtering by category, active status, or search query.
 */
function getProducts(?int $categoryId = null, bool $activeOnly = true, ?string $search = null): array {
    $db = getDB();
    $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug 
            FROM products p 
            JOIN categories c ON p.category_id = c.id";
    $params = [];
    $conditions = [];

    if ($activeOnly) {
        $conditions[] = "p.is_active = 1 AND c.is_active = 1";
    }

    if ($categoryId !== null && $categoryId > 0) {
        $conditions[] = "p.category_id = :cat_id";
        $params['cat_id'] = $categoryId;
    }

    if (!empty($search)) {
        $conditions[] = "(p.name LIKE :search OR p.description LIKE :search)";
        $params['search'] = '%' . trim($search) . '%';
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY p.sort_order ASC, p.id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Retrieves a single product by ID.
 */
function getProductById(int $id): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug 
                          FROM products p 
                          JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $prod = $stmt->fetch();
    return $prod ?: null;
}

/**
 * Retrieves a single product by URL slug.
 */
function getProductBySlug(string $slug): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug 
                          FROM products p 
                          JOIN categories c ON p.category_id = c.id 
                          WHERE p.slug = :slug LIMIT 1");
    $stmt->execute(['slug' => trim($slug)]);
    $prod = $stmt->fetch();
    return $prod ?: null;
}

/**
 * Checks if a product slug is unique.
 */
function isProductSlugUnique(string $slug, ?int $excludeId = null): bool {
    $db = getDB();
    $sql = "SELECT id FROM products WHERE slug = :slug";
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
 * Formats monetary amounts with currency symbol.
 */
function formatPrice(float $price, string $currency = APP_CURRENCY): string {
    return $currency . ' ' . number_format($price, 2);
}

/**
 * Securely handles file uploads for 3D models and images.
 */
function handleFileUpload(array $file, string $targetFolder, array $allowedExts): ?string {
    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
            UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE specified in the form.",
            UPLOAD_ERR_PARTIAL    => "The uploaded file was only partially uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
            UPLOAD_ERR_EXTENSION  => "A PHP extension stopped the file upload.",
        ];
        throw new Exception($errorMessages[$file['error']] ?? "Unknown file upload error.");
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, array_map('strtolower', $allowedExts), true)) {
        throw new Exception("Invalid file extension: .$ext. Allowed extensions: " . implode(', ', $allowedExts));
    }

    $maxBytes = MAX_FILE_SIZE_MB * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        throw new Exception("File size exceeds the allowed limit of " . MAX_FILE_SIZE_MB . "MB.");
    }

    $uploadRoot = realpath(__DIR__ . '/..');
    $relFolder = trim(str_replace('\\', '/', $targetFolder), '/');
    $fullTargetDir = $uploadRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relFolder) . DIRECTORY_SEPARATOR;

    if (!is_dir($fullTargetDir)) {
        if (!mkdir($fullTargetDir, 0755, true) && !is_dir($fullTargetDir)) {
            throw new Exception("Failed to create upload target directory: " . $relFolder);
        }
    }

    $safeFilename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destination = $fullTargetDir . $safeFilename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Failed to save uploaded file on server disk.");
    }

    return $relFolder . '/' . $safeFilename;
}

/**
 * Permanently deletes a product and removes associated media assets from disk.
 */
function deleteProduct(int $id): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT glb_path, usdz_path, thumb_path FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $prod = $stmt->fetch();

    if ($prod) {
        $root = realpath(__DIR__ . '/..');
        foreach (['glb_path', 'usdz_path', 'thumb_path'] as $field) {
            $relPath = $prod[$field] ?? '';
            if (!empty($relPath)) {
                $fullPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
                // Do not delete default bundled sample assets if shared
                if (file_exists($fullPath) && !str_contains($relPath, 'sample_chair.glb') && !str_contains($relPath, 'sample_lamp.glb')) {
                    @unlink($fullPath);
                }
            }
        }
        $del = $db->prepare("DELETE FROM products WHERE id = :id");
        return $del->execute(['id' => $id]);
    }
    return false;
}