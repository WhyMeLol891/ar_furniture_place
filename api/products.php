<?php
/**
 * RESTful JSON API Endpoint for Products & 3D AR Assets
 * AR Spatial Furniture Catalog & Visualization System
 */
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/products.php';
require_once __DIR__ . '/../includes/categories.php';

$baseUrl = getBaseUrl();

try {
    // Single product by ID
    if (!empty($_GET['id'])) {
        $prod = getProductById((int)$_GET['id']);
        if (!$prod || !$prod['is_active']) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Product not found.'], JSON_PRETTY_PRINT);
            exit();
        }
        $formatted = formatProductForApi($prod, $baseUrl);
        echo json_encode(['status' => 'success', 'product' => $formatted], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit();
    }

    // Single product by Slug
    if (!empty($_GET['slug'])) {
        $prod = getProductBySlug(trim($_GET['slug']));
        if (!$prod || !$prod['is_active']) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Product not found.'], JSON_PRETTY_PRINT);
            exit();
        }
        $formatted = formatProductForApi($prod, $baseUrl);
        echo json_encode(['status' => 'success', 'product' => $formatted], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit();
    }

    // Filter by category
    $catId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
    if (!empty($_GET['category_slug'])) {
        $cat = getCategoryBySlug(trim($_GET['category_slug']));
        if ($cat) {
            $catId = (int)$cat['id'];
        }
    }

    $search = trim($_GET['search'] ?? $_GET['q'] ?? '');
    $rawProducts = getProducts($catId, true, $search);

    $formattedList = array_map(function($p) use ($baseUrl) {
        return formatProductForApi($p, $baseUrl);
    }, $rawProducts);

    echo json_encode([
        'status'    => 'success',
        'count'     => count($formattedList),
        'timestamp' => date('c'),
        'products'  => $formattedList
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Server encountered an error processing API request.',
        'debug'   => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

function formatProductForApi(array $p, string $baseUrl): array {
    return [
        'id'            => (int)$p['id'],
        'name'          => $p['name'],
        'slug'          => $p['slug'],
        'category'      => [
            'id'   => (int)$p['category_id'],
            'name' => $p['category_name'],
            'slug' => $p['category_slug']
        ],
        'description'   => $p['description'],
        'price'         => (float)$p['price'],
        'currency'      => $p['currency'] ?? APP_CURRENCY,
        'dimensions_cm' => [
            'width'  => (float)$p['width_cm'],
            'height' => (float)$p['height_cm'],
            'depth'  => (float)$p['depth_cm']
        ],
        'dimensions_meters' => [
            'width'  => (float)($p['glb_x_m'] ?? round($p['width_cm']/100, 3)),
            'height' => (float)($p['glb_y_m'] ?? round($p['height_cm']/100, 3)),
            'depth'  => (float)($p['glb_z_m'] ?? round($p['depth_cm']/100, 3)),
        ],
        'assets' => [
            'glb_url'   => !empty($p['glb_path']) ? $baseUrl . $p['glb_path'] : null,
            'usdz_url'  => !empty($p['usdz_path']) ? $baseUrl . $p['usdz_path'] : null,
            'thumb_url' => !empty($p['thumb_path']) ? $baseUrl . $p['thumb_path'] : null,
        ],
        'ar_ready'      => !empty($p['glb_path'])
    ];
}