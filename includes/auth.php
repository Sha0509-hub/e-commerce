<?php
// ── Session Bootstrap ──────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,   // Set true on HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── Customer Auth ──────────────────────────────────────────────
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: /ecommerce/login.php?redirect=' . $redirect);
        exit;
    }
}

function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
}

function logoutUser(): void {
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
}

// ── Admin Auth ─────────────────────────────────────────────────
function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

function requireAdminLogin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: /ecommerce/admin/login.php');
        exit;
    }
}

function loginAdmin(array $admin): void {
    session_regenerate_id(true);
    $_SESSION['admin_id']       = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
}

function logoutAdmin(): void {
    unset($_SESSION['admin_id'], $_SESSION['admin_username']);
}
