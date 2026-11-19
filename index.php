<?php
$pageTitle = 'Shop Premium Products';
$metaDesc  = 'Nexus Store — Shop the latest electronics, clothing, accessories and more.';

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// ── Filters ────────────────────────────────────────────────────
$catSlug = isset($_GET['cat']) ? sanitize($_GET['cat']) : 'all';
$search  = isset($_GET['q'])   ? sanitize($_GET['q'])   : '';

// ── Fetch Categories ───────────────────────────────────────────
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// ── Fetch Products ─────────────────────────────────────────────
$params = [];
$sql    = "SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
           FROM products p
           LEFT JOIN categories c ON p.category_id = c.id
           WHERE 1=1";

if ($catSlug !== 'all') {
    $sql     .= " AND c.slug = :cat";
    $params[':cat'] = $catSlug;
}
if ($search !== '') {
    $sql     .= " AND (p.name LIKE :q OR p.description LIKE :q2)";
    $params[':q']  = "%$search%";
    $params[':q2'] = "%$search%";
}
$sql .= " ORDER BY p.featured DESC, p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ── Featured for hero ──────────────────────────────────────────
$featured = $pdo->query("SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.featured = 1 LIMIT 1")->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<!-- ═══ HERO ═══ -->
<section class="hero" id="heroSection">
  <div class="hero-orb hero-orb-1"></div>
  <div class="hero-orb hero-orb-2"></div>
  <div class="container">
    <div class="hero-content fade-in">
      <div class="hero-tag">✨ New Arrivals 2026</div>
      <h1>Shop the <span class="gradient-text">Future</span> of Tech &amp; Style</h1>
      <p>Discover hand-picked electronics, fashion, and lifestyle products crafted for those who demand the best.</p>
      <div class="hero-btns">
        <a href="#products" class="btn btn-primary btn-lg btn-pill" id="shopNowBtn">Shop Now ↓</a>
        <a href="/ecommerce/index.php?cat=electronics" class="btn btn-outline btn-lg btn-pill">Electronics</a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat">
          <strong><?= count($products) ?>+</strong>
          <span>Products</span>
        </div>
        <div class="hero-stat">
          <strong>4.9★</strong>
          <span>Avg Rating</span>
        </div>
        <div class="hero-stat">
          <strong>Free</strong>
          <span>Shipping over $99</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ PRODUCTS SECTION ═══ -->
<section class="section" id="products">
  <div class="container">

    <!-- Header -->
    <div class="section-header fade-in">
      <div class="section-tag">🛍️ Catalog</div>
      <h2>
        <?php if ($search): ?>
          Results for "<span class="gradient-text"><?= sanitize($search) ?></span>"
        <?php elseif ($catSlug !== 'all'): ?>
          <span class="gradient-text"><?= sanitize(ucwords(str_replace('-', ' ', $catSlug))) ?></span>
        <?php else: ?>
          All <span class="gradient-text">Products</span>
        <?php endif; ?>
      </h2>
      <p><?= count($products) ?> products available</p>
    </div>

    <!-- Category Filter -->
    <div class="cat-filter fade-in-1" id="catFilter" role="group" aria-label="Filter by category">
      <button class="cat-btn <?= $catSlug === 'all' ? 'active' : '' ?>"
              data-cat="all"
              onclick="window.location.href='/ecommerce/index.php'">
        All Products
      </button>
      <?php foreach ($categories as $cat): ?>
        <button class="cat-btn <?= $catSlug === $cat['slug'] ? 'active' : '' ?>"
                data-cat="<?= sanitize($cat['slug']) ?>"
                onclick="window.location.href='/ecommerce/index.php?cat=<?= urlencode($cat['slug']) ?>'">
          <?= sanitize($cat['name']) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Search (mobile) -->
    <div style="margin-bottom:1.5rem;" class="fade-in-2">
      <form action="/ecommerce/index.php" method="GET" style="display:flex;gap:0.5rem;max-width:400px;">
        <?php if ($catSlug !== 'all'): ?>
          <input type="hidden" name="cat" value="<?= sanitize($catSlug) ?>">
        <?php endif; ?>
        <input type="search" name="q" id="searchInput"
               class="form-control" placeholder="Search products…"
               value="<?= $search ?>" autocomplete="off">
        <button type="submit" class="btn btn-primary">Go</button>
      </form>
    </div>

    <!-- Product Grid -->
    <?php if (empty($products)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">🔍</div>
        <h3>No products found</h3>
        <p>Try a different search term or browse all categories.</p>
        <a href="/ecommerce/index.php" class="btn btn-primary">Browse All</a>
      </div>
    <?php else: ?>
      <div class="products-grid" id="productsGrid">
        <?php foreach ($products as $p): ?>
          <article class="product-card fade-in" data-cat="<?= sanitize($p['cat_slug'] ?? '') ?>" data-product-id="<?= $p['id'] ?>">

            <!-- Image -->
            <a href="/ecommerce/product.php?id=<?= $p['id'] ?>" class="product-img-wrap">
              <img src="<?= sanitize(getProductImageUrl($p['image'])) ?>"
                   alt="<?= sanitize($p['name']) ?>"
                   loading="lazy">
              <div class="product-badges">
                <?php if ($p['featured']): ?>
                  <span class="badge-featured">⭐ Featured</span>
                <?php endif; ?>
                <?php if ($p['stock'] === 0): ?>
                  <span class="badge-out-stock">Out of Stock</span>
                <?php elseif ($p['stock'] < 10): ?>
                  <span class="badge-low-stock">Low Stock</span>
                <?php endif; ?>
              </div>
              <button class="product-wishlist" aria-label="Add to wishlist">♡</button>
            </a>

            <!-- Info -->
            <div class="product-info">
              <div class="product-cat"><?= sanitize($p['cat_name'] ?? 'General') ?></div>
              <div class="product-name"><?= sanitize($p['name']) ?></div>
              <div class="product-price-row">
                <div class="product-price"><?= formatPrice($p['price']) ?></div>
                <div class="product-stock"><?= $p['stock'] > 0 ? $p['stock'] . ' in stock' : 'Sold out' ?></div>
              </div>
            </div>

            <!-- Actions -->
            <div class="product-actions">
              <?php if ($p['stock'] > 0): ?>
                <form action="/ecommerce/cart_action.php" method="POST">
                  <input type="hidden" name="action"     value="add">
                  <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                  <input type="hidden" name="qty"        value="1">
                  <input type="hidden" name="redirect"   value="<?= urlencode($_SERVER['REQUEST_URI']) ?>">
                  <button type="submit" class="btn btn-primary btn-sm" id="addCart<?= $p['id'] ?>">
                    🛒 Add to Cart
                  </button>
                </form>
              <?php else: ?>
                <button class="btn btn-ghost btn-sm" disabled>Sold Out</button>
              <?php endif; ?>
              <a href="/ecommerce/product.php?id=<?= $p['id'] ?>" class="btn-view" title="View Details" aria-label="View <?= sanitize($p['name']) ?>">👁</a>
            </div>

          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- ═══ FEATURES STRIP ═══ -->
<section style="background:rgba(255,255,255,0.02);border-top:1px solid rgba(255,255,255,0.07);border-bottom:1px solid rgba(255,255,255,0.07);padding:2.5rem 0;">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:2rem;text-align:center;">
      <?php
        $features = [
          ['🚚', 'Free Shipping', 'On orders over $99'],
          ['🔒', 'Secure Payment', '256-bit SSL encryption'],
          ['↩️',  '30-Day Returns', 'Hassle-free returns'],
          ['🎧', '24/7 Support',   'Always here to help'],
        ];
      ?>
      <?php foreach ($features as $f): ?>
        <div style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;">
          <div style="font-size:1.75rem;"><?= $f[0] ?></div>
          <strong style="font-size:0.9rem;"><?= $f[1] ?></strong>
          <span style="font-size:0.8rem;color:var(--text-muted);"><?= $f[2] ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
