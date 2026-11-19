<?php
// Ensure session is started and includes are loaded
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$cartCount = getCartCount();
$flash     = getFlash();

// Current page for nav active state
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= isset($metaDesc) ? sanitize($metaDesc) : 'Nexus Store — Shop the latest electronics, clothing, accessories and more with premium quality and fast delivery.' ?>">
  <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | Nexus Store' : 'Nexus Store — Premium Online Shopping' ?></title>

  <!-- Preconnect to Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Stylesheet -->
  <link rel="stylesheet" href="/ecommerce/css/style.css">

  <!-- Favicon (inline SVG data URI) -->
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%237c3aed'/><text y='24' x='6' font-size='20' fill='white'>N</text></svg>">
</head>
<body>

<!-- Flash data element (JS reads this) -->
<?php if ($flash): ?>
<span id="flashMessage" data-type="<?= sanitize($flash['type']) ?>" data-message="<?= sanitize($flash['message']) ?>" hidden></span>
<?php endif; ?>

<!-- ══════════════ NAVBAR ══════════════ -->
<nav class="navbar" role="navigation" aria-label="Main navigation">
  <div class="container navbar-inner">

    <!-- Logo -->
    <a href="/ecommerce/index.php" class="nav-logo" id="navLogo">
      <div class="logo-icon" aria-hidden="true">⚡</div>
      Nexus<span>Store</span>
    </a>

    <!-- Search -->
    <form class="nav-search" id="navSearchForm" action="/ecommerce/index.php" method="GET" role="search">
      <span class="search-icon" aria-hidden="true">🔍</span>
      <input type="search" name="q" id="navSearchInput"
             placeholder="Search products…"
             value="<?= isset($_GET['q']) ? sanitize($_GET['q']) : '' ?>"
             aria-label="Search products"
             autocomplete="off">
    </form>

    <!-- Nav Links -->
    <div class="nav-links" id="navLinks" role="menubar">
      <a href="/ecommerce/index.php"
         class="<?= $currentPage === 'index.php' ? 'active' : '' ?>"
         role="menuitem">
        🏠 Home
      </a>
      <a href="/ecommerce/index.php?cat=electronics" role="menuitem">Electronics</a>
      <a href="/ecommerce/index.php?cat=clothing" role="menuitem">Clothing</a>
      <a href="/ecommerce/index.php?cat=accessories" role="menuitem">Accessories</a>

      <!-- Cart -->
      <a href="/ecommerce/cart.php" class="cart-link" id="cartLink" role="menuitem" aria-label="Shopping cart">
        🛒
        <span class="cart-badge <?= $cartCount === 0 ? 'empty' : '' ?>" id="cartBadge">
          <?= $cartCount ?>
        </span>
      </a>

      <!-- Auth links -->
      <?php if (isLoggedIn()): ?>
        <a href="/ecommerce/profile.php" role="menuitem">👤 <?= sanitize($_SESSION['user_name']) ?></a>
        <a href="/ecommerce/logout.php" role="menuitem">Sign Out</a>
      <?php else: ?>
        <a href="/ecommerce/login.php" class="btn-nav-login" role="menuitem" id="navLoginBtn">Sign In</a>
      <?php endif; ?>
    </div>

    <!-- Hamburger -->
    <button class="hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false" aria-controls="navLinks">
      <span></span>
      <span></span>
      <span></span>
    </button>

  </div>
</nav>
<!-- ════════════════════════════════════ -->

<main id="main-content">
