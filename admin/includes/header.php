<?php
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
requireAdminLogin();

// Active page detection
$adminPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($adminPageTitle) ? sanitize($adminPageTitle) . ' — Admin' : 'Admin Panel — Nexus Store' ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="/ecommerce/css/admin.css">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%237c3aed'/><text y='24' x='6' font-size='20' fill='white'>A</text></svg>">
</head>
<body>

<div class="admin-layout">

  <!-- ══════ SIDEBAR ══════ -->
  <aside class="admin-sidebar" id="adminSidebar" role="navigation" aria-label="Admin navigation">

    <div class="sidebar-brand">
      <div class="logo-icon" aria-hidden="true">⚡</div>
      <div class="brand-text">
        <strong>NexusStore</strong>
        <span>Admin Panel</span>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-title">Overview</div>

      <a href="/ecommerce/admin/index.php"
         class="sidebar-nav-item <?= $adminPage === 'index.php' ? 'active' : '' ?>"
         role="menuitem">
        <span class="nav-icon">📊</span> Dashboard
      </a>

      <div class="nav-section-title">Catalog</div>

      <a href="/ecommerce/admin/products.php"
         class="sidebar-nav-item <?= $adminPage === 'products.php' ? 'active' : '' ?>"
         role="menuitem">
        <span class="nav-icon">📦</span> Products
      </a>

      <div class="nav-section-title">Sales</div>

      <a href="/ecommerce/admin/orders.php"
         class="sidebar-nav-item <?= $adminPage === 'orders.php' || $adminPage === 'order_detail.php' ? 'active' : '' ?>"
         role="menuitem">
        <span class="nav-icon">🛒</span> Orders
      </a>

      <div class="nav-section-title">Store</div>

      <a href="/ecommerce/index.php" class="sidebar-nav-item" target="_blank" role="menuitem">
        <span class="nav-icon">🌐</span> View Store
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)) ?></div>
        <div class="sidebar-user-info">
          <strong><?= sanitize($_SESSION['admin_username'] ?? 'Admin') ?></strong>
          <span>Administrator</span>
        </div>
      </div>
      <a href="/ecommerce/admin/logout.php" class="sidebar-nav-item" style="margin-top:0.5rem;">
        <span class="nav-icon">🚪</span> Logout
      </a>
    </div>

  </aside>
  <!-- ════════════════════ -->

  <!-- ══════ MAIN ══════ -->
  <div class="admin-main">

    <!-- Top Bar -->
    <header class="admin-topbar">
      <div style="display:flex;align-items:center;gap:0.75rem;">
        <button class="topbar-btn topbar-btn-outline" id="sidebarToggle" aria-label="Toggle sidebar" style="display:none;">☰</button>
        <div class="topbar-left">
          <h2><?= isset($adminPageTitle) ? sanitize($adminPageTitle) : 'Dashboard' ?></h2>
          <p><?= date('l, F j, Y') ?></p>
        </div>
      </div>
      <div class="topbar-right">
        <?php $flash = getFlash(); if ($flash): ?>
          <span id="flashMessage" data-type="<?= sanitize($flash['type']) ?>" data-message="<?= sanitize($flash['message']) ?>" hidden></span>
        <?php endif; ?>
        <a href="/ecommerce/index.php" class="topbar-btn topbar-btn-outline" target="_blank">🌐 View Store</a>
        <a href="/ecommerce/admin/logout.php" class="topbar-btn topbar-btn-outline">🚪 Logout</a>
      </div>
    </header>

    <div class="admin-content">
