<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) redirect('/ecommerce/profile.php');

$errors   = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';

    if (empty($name))     $errors['name']     = 'Full name is required.';
    if (empty($email))    $errors['email']    = 'Email is required.';
    if (empty($password)) $errors['password'] = 'Password is required.';
    if (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors['confirm'] = 'Passwords do not match.';

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }

    if (empty($errors)) {
        // Check if email exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            $errors['email'] = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins  = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :hash)");
            $ins->execute([':name' => $name, ':email' => $email, ':hash' => $hash]);

            $userId = $pdo->lastInsertId();
            loginUser(['id' => $userId, 'name' => $name, 'email' => $email]);
            setFlash('success', 'Welcome to Nexus Store, ' . $name . '! 🎉');
            redirect('/ecommerce/profile.php');
        }
    }

    $formData = compact('name','email');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account — Nexus Store</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="/ecommerce/css/style.css">
</head>
<body>

<div class="auth-page">
  <div class="auth-card fade-in">

    <div class="auth-logo">
      <div class="logo-icon">⚡</div>
      <h2>Create Account</h2>
      <p>Join Nexus Store and start shopping</p>
    </div>

    <!-- Tabs -->
    <div class="auth-tabs">
      <a href="/ecommerce/login.php"    class="auth-tab">Sign In</a>
      <a href="/ecommerce/register.php" class="auth-tab active">Register</a>
    </div>

    <form action="/ecommerce/register.php" method="POST" id="registerForm" novalidate>

      <div class="form-group">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" name="name" id="name" class="form-control"
               value="<?= sanitize($formData['name'] ?? '') ?>"
               placeholder="John Doe" required autocomplete="name" autofocus>
        <?php if (!empty($errors['name'])): ?>
          <div class="form-error"><?= sanitize($errors['name']) ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" name="email" id="email" class="form-control"
               value="<?= sanitize($formData['email'] ?? '') ?>"
               placeholder="john@example.com" required autocomplete="email">
        <?php if (!empty($errors['email'])): ?>
          <div class="form-error"><?= sanitize($errors['email']) ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control"
               placeholder="Min. 6 characters" required autocomplete="new-password">
        <?php if (!empty($errors['password'])): ?>
          <div class="form-error"><?= sanitize($errors['password']) ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="confirm" class="form-label">Confirm Password</label>
        <input type="password" name="confirm" id="confirm" class="form-control"
               placeholder="Repeat your password" required autocomplete="new-password">
        <?php if (!empty($errors['confirm'])): ?>
          <div class="form-error"><?= sanitize($errors['confirm']) ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label style="display:flex;align-items:flex-start;gap:0.5rem;cursor:pointer;font-size:0.82rem;color:var(--text-secondary);line-height:1.5;">
          <input type="checkbox" required style="accent-color:var(--primary);width:15px;height:15px;margin-top:2px;flex-shrink:0;">
          I agree to the <a href="#" style="color:var(--accent);">Terms of Service</a> and <a href="#" style="color:var(--accent);">Privacy Policy</a>
        </label>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg" id="registerBtn">
        Create Account →
      </button>
    </form>

    <div class="auth-footer-link" style="margin-top:1.25rem;">
      Already have an account? <a href="/ecommerce/login.php">Sign In →</a>
    </div>

  </div>
</div>

<script src="/ecommerce/js/main.js"></script>
</body>
</html>
