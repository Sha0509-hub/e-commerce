<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) redirect('/ecommerce/profile.php');

$errors   = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email))    $errors['email']    = 'Email is required.';
    if (empty($password)) $errors['password'] = 'Password is required.';

    if (empty($errors)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                loginUser($user);
                $redirect = $_GET['redirect'] ?? '/ecommerce/profile.php';
                // Validate redirect
                if (!str_starts_with($redirect, '/ecommerce/')) $redirect = '/ecommerce/profile.php';
                redirect($redirect);
            } else {
                $errors['general'] = 'Invalid email or password.';
            }
        }
    }
}

$pageTitle = 'Sign In';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — Nexus Store</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="/ecommerce/css/style.css">
</head>
<body>

<div class="auth-page">
  <div class="auth-card fade-in">

    <div class="auth-logo">
      <div class="logo-icon">⚡</div>
      <h2>Welcome back</h2>
      <p>Sign in to your Nexus Store account</p>
    </div>

    <!-- Tabs -->
    <div class="auth-tabs">
      <a href="/ecommerce/login.php"    class="auth-tab active">Sign In</a>
      <a href="/ecommerce/register.php" class="auth-tab">Register</a>
    </div>

    <?php if (!empty($errors['general'])): ?>
      <div class="flash flash-error">⚠️ <?= sanitize($errors['general']) ?></div>
    <?php endif; ?>

    <form action="/ecommerce/login.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>"
          method="POST" id="loginForm" novalidate>

      <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" name="email" id="email" class="form-control"
               value="<?= sanitize($email ?? '') ?>"
               placeholder="john@example.com"
               required autocomplete="email" autofocus>
        <?php if (!empty($errors['email'])): ?>
          <div class="form-error"><?= sanitize($errors['email']) ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="password" class="form-label">
          Password
          <a href="#" style="float:right;font-size:0.78rem;color:var(--accent);">Forgot password?</a>
        </label>
        <input type="password" name="password" id="password" class="form-control"
               placeholder="Your password"
               required autocomplete="current-password">
        <?php if (!empty($errors['password'])): ?>
          <div class="form-error"><?= sanitize($errors['password']) ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group" style="margin-bottom:1.5rem;">
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem;color:var(--text-secondary);">
          <input type="checkbox" name="remember" style="accent-color:var(--primary);width:15px;height:15px;">
          Remember me
        </label>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg" id="loginBtn">
        Sign In →
      </button>
    </form>

    <div class="auth-divider">or continue as</div>

    <a href="/ecommerce/index.php" class="btn btn-ghost btn-full">
      🛍️ Browse as Guest
    </a>

    <div class="auth-footer-link">
      <p>Demo account: <strong>john@example.com</strong> / <strong>password</strong></p>
    </div>

    <div class="auth-footer-link" style="margin-top:0.5rem;">
      Don't have an account? <a href="/ecommerce/register.php">Create one →</a>
    </div>

  </div>
</div>

<script src="/ecommerce/js/main.js"></script>
</body>
</html>
