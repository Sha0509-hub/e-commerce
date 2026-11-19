<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) redirect('/ecommerce/index.php');

$stmt = $pdo->prepare("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('error', 'Product not found.');
    redirect('/ecommerce/index.php');
}

// Related products
$related = $pdo->prepare("SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = :cat AND p.id != :id LIMIT 4");
$related->execute([':cat' => $product['category_id'], ':id' => $id]);
$relatedProducts = $related->fetchAll();

$pageTitle = sanitize($product['name']);
$metaDesc  = substr(strip_tags($product['description']), 0, 155);
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
  <!-- Breadcrumb -->
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="/ecommerce/index.php">Home</a>
    <span class="sep">›</span>
    <a href="/ecommerce/index.php?cat=<?= urlencode($product['cat_slug'] ?? '') ?>"><?= sanitize($product['cat_name'] ?? 'Products') ?></a>
    <span class="sep">›</span>
    <span class="current"><?= sanitize($product['name']) ?></span>
  </nav>
</div>

<!-- ═══ PRODUCT DETAIL ═══ -->
<div class="product-detail">
  <div class="container">
    <div class="detail-grid">

      <!-- Image -->
      <div class="detail-img-main fade-in">
        <img src="<?= sanitize(getProductImageUrl($product['image'])) ?>"
             alt="<?= sanitize($product['name']) ?>">
      </div>

      <!-- Info -->
      <div class="detail-info fade-in-1">
        <div class="detail-cat"><?= sanitize($product['cat_name'] ?? 'General') ?></div>
        <h1 class="detail-name"><?= sanitize($product['name']) ?></h1>
        <div class="detail-price"><?= formatPrice($product['price']) ?></div>

        <!-- Meta -->
        <div class="detail-meta">
          <div class="detail-meta-item">
            <span>Availability</span>
            <?php if ($product['stock'] > 0): ?>
              <strong style="color:var(--success);">✓ In Stock (<?= $product['stock'] ?> left)</strong>
            <?php else: ?>
              <strong style="color:var(--danger);">✗ Out of Stock</strong>
            <?php endif; ?>
          </div>
          <div class="detail-meta-item">
            <span>Category</span>
            <strong><?= sanitize($product['cat_name'] ?? 'General') ?></strong>
          </div>
          <div class="detail-meta-item">
            <span>SKU</span>
            <strong>NST-<?= str_pad($product['id'], 4, '0', STR_PAD_LEFT) ?></strong>
          </div>
        </div>

        <!-- Description -->
        <p class="detail-desc"><?= nl2br(sanitize($product['description'])) ?></p>

        <!-- Add to Cart -->
        <?php if ($product['stock'] > 0): ?>
          <form action="/ecommerce/cart_action.php" method="POST" id="addToCartForm">
            <input type="hidden" name="action"     value="add">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="hidden" name="redirect"   value="/ecommerce/product.php?id=<?= $product['id'] ?>">
            <input type="hidden" name="qty" id="qtyInput" value="1">
            <input type="hidden" id="stockMax" value="<?= $product['stock'] ?>">

            <div style="margin-bottom:1.25rem;">
              <label class="form-label">Quantity</label>
              <div class="detail-qty">
                <button type="button" class="qty-btn" id="qtyMinus" aria-label="Decrease quantity">−</button>
                <span class="qty-display" id="qtyDisplay">1</span>
                <button type="button" class="qty-btn" id="qtyPlus" aria-label="Increase quantity">+</button>
              </div>
            </div>

            <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
              <button type="submit" class="btn btn-primary btn-lg" id="detailAddCart" style="flex:1;">
                🛒 Add to Cart
              </button>
              <a href="/ecommerce/cart.php" class="btn btn-outline btn-lg">View Cart</a>
            </div>
          </form>
        <?php else: ?>
          <div class="flash flash-error">This product is currently out of stock.</div>
        <?php endif; ?>

        <!-- Trust badges -->
        <div style="display:flex;gap:1.5rem;flex-wrap:wrap;padding-top:1rem;border-top:1px solid var(--border);">
          <span style="font-size:0.8rem;color:var(--text-muted);">🔒 Secure checkout</span>
          <span style="font-size:0.8rem;color:var(--text-muted);">🚚 Free shipping $99+</span>
          <span style="font-size:0.8rem;color:var(--text-muted);">↩️ 30-day returns</span>
        </div>
      </div>

    </div><!-- /.detail-grid -->

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
      <div class="section" style="padding-top:4rem;">
        <div class="section-header" style="text-align:left;margin-bottom:2rem;">
          <div class="section-tag">Related</div>
          <h2>You May Also <span class="gradient-text">Like</span></h2>
        </div>
        <div class="products-grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr));">
          <?php foreach ($relatedProducts as $rp): ?>
            <article class="product-card">
              <a href="/ecommerce/product.php?id=<?= $rp['id'] ?>" class="product-img-wrap">
                <img src="<?= sanitize(getProductImageUrl($rp['image'])) ?>" alt="<?= sanitize($rp['name']) ?>" loading="lazy">
              </a>
              <div class="product-info">
                <div class="product-cat"><?= sanitize($rp['cat_name'] ?? '') ?></div>
                <div class="product-name"><?= sanitize($rp['name']) ?></div>
                <div class="product-price-row">
                  <div class="product-price"><?= formatPrice($rp['price']) ?></div>
                </div>
              </div>
              <div class="product-actions">
                <form action="/ecommerce/cart_action.php" method="POST">
                  <input type="hidden" name="action"     value="add">
                  <input type="hidden" name="product_id" value="<?= $rp['id'] ?>">
                  <input type="hidden" name="qty"        value="1">
                  <input type="hidden" name="redirect"   value="<?= urlencode($_SERVER['REQUEST_URI']) ?>">
                  <button type="submit" class="btn btn-primary btn-sm">🛒 Add to Cart</button>
                </form>
                <a href="/ecommerce/product.php?id=<?= $rp['id'] ?>" class="btn-view" aria-label="View">👁</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
