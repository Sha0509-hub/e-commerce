<?php
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Redirect if already logged in
if (isAdminLoggedIn()) redirect('/ecommerce/admin/index.php');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']      ?? '';

    if (empty($username) || empty($password)) {
        $errors['general'] = 'Username and password are required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            loginAdmin($admin);
            redirect('/ecommerce/admin/index.php');
        } else {
            $errors['general'] = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — Nexus Store</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="/ecommerce/css/admin.css">
</head>
<body>

<div class="admin-login-page">
  <div class="admin-login-card">

    <div class="admin-login-logo">
      <div class="logo-icon">⚡</div>
      <h2>Admin Panel</h2>
      <p>Sign in to manage your store</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
      <div class="flash flash-error">⚠️ <?= sanitize($errors['general']) ?></div>
    <?php endif; ?>

    <form action="/ecommerce/admin/login.php" method="POST" id="adminLoginForm">

      <div class="form-group">
        <label for="username" class="form-label">Username</label>
        <input type="text" name="username" id="username" class="form-control"
               placeholder="admin" required autocomplete="username" autofocus
               value="<?= sanitize($_POST['username'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control"
               placeholder="Your password" required autocomplete="current-password">
      </div>

      <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem;padding:0.8rem;" id="adminLoginBtn">
        Sign In →
      </button>
    </form>

    <p style="text-align:center;margin-top:1.25rem;font-size:0.78rem;color:var(--text-muted);">
      Default: <strong style="color:var(--text-secondary);">admin</strong> / <strong style="color:var(--text-secondary);">password</strong>
    </p>

    <div style="text-align:center;margin-top:1rem;">
      <a href="/ecommerce/index.php" style="font-size:0.82rem;color:var(--text-muted);">← Back to Store</a>
    </div>

  </div>
</div>

<script src="/ecommerce/js/main.js"></script>
</body>
</html>
