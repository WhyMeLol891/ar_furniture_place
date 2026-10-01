<?php
/**
 * Authentication and Security Helper Functions
 * AR Spatial Furniture Catalog & Visualization System
 */

if (session_status() === PHP_SESSION_NONE) {
    // Enforce secure session cookies
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

require_once __DIR__ . '/db.php';

/**
 * Checks if admin is logged in, redirects to login if not.
 */
function requireAdminAuth(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . getBaseUrl() . 'admin/login.php');
        exit();
    }
}

/**
 * Checks if current session is an authenticated admin.
 */
function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

/**
 * Attempts admin authentication.
 */
function loginAdmin(string $username, string $password): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => trim($username)]);
    $admin = $stmt->fetch();

    if ($admin && password_verify(trim($password), $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return true;
    }
    return false;
}

/**
 * Logs out the admin user and invalidates session.
 */
function logoutAdmin(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Generates or retrieves existing CSRF token.
 */
function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifies the provided CSRF token against session token.
 */
function verifyCSRFToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Escapes HTML strings to prevent Cross-Site Scripting (XSS).
 */
function sanitize(?string $str): string {
    return htmlspecialchars(trim((string)$str), ENT_QUOTES, 'UTF-8');
}

/**
 * Flash message helpers for UI feedback.
 */
function setFlashMessage(string $type, string $message): void {
    $_SESSION['flash_message'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'text' => $message
    ];
}

function getFlashMessage(): ?array {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }
    return null;
}