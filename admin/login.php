<?php
/**
 * Admin Login Page
 * AR Spatial Furniture Catalog & Visualization System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$baseUrl = getBaseUrl();
$error = '';

if (isAdminLoggedIn()) {
    header("Location: {$baseUrl}admin/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } elseif (loginAdmin($username, $password)) {
        header("Location: {$baseUrl}admin/index.php");
        exit();
    } else {
        $error = 'Invalid username or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= sanitize(APP_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>css/style.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>css/styles.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="bg-primary text-white text-center py-4 px-3">
                    <div class="bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow" style="width:60px;height:60px;">
                        <i class="fa-solid fa-cube fa-2x"></i>
                    </div>
                    <h4 class="fw-bold mb-1"><?= sanitize(APP_NAME) ?></h4>
                    <p class="small text-white-50 mb-0">Management Portal Authentication</p>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2 small d-flex align-items-center" role="alert">
                            <i class="fa-solid fa-circle-exclamation me-2"></i>
                            <div><?= sanitize($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= $baseUrl ?>admin/login.php">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary small">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-user text-muted"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="admin" required autofocus value="<?= sanitize($_POST['username'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary small">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="••••••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                            <i class="fa-solid fa-right-to-bracket me-2"></i> Sign In to Dashboard
                        </button>
                    </form>

                    <div class="mt-4 pt-3 border-top text-center">
                        <a href="<?= $baseUrl ?>" class="text-decoration-none small text-muted">
                            <i class="fa-solid fa-arrow-left me-1"></i> Return to Storefront
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3 text-muted small">
                Default Credentials: <code>admin</code> / <code>adminpassword123</code>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>