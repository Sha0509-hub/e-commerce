<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Shopping Cart';
$cart = getCart();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <h1>🛒 Your Cart
      <?php if (!empty($cart)): ?>
        <span style="font-size:1rem;font-weight:400;color:var(--text-muted);">(<?= getCartCount() ?> items)</span>
      <?php endif; ?>
    </h1>
    <nav class="breadcrumb" style="padding-top:0.5rem;" aria-label="Breadcrumb">
      <a href="/ecommerce/index.php">Home</a>
      <span class="sep">›</span>
      <span class="current">Cart</span>
    </nav>
  </div>
</div>

<div class="cart-page">
  <div class="container">

    <?php if (empty($cart)): ?>
      <!-- Empty Cart -->
      <div class="cart-empty fade-in" style="text-align:center;padding:5rem 2rem;">
        <div class="cart-empty-icon">🛒</div>
        <h3>Your cart is empty</h3>
        <p style="color:var(--text-secondary);margin:0.5rem 0 1.5rem;">Looks like you haven't added anything yet.</p>
        <a href="/ecommerce/index.php" class="btn btn-primary btn-lg">Browse Products</a>
      </div>

    <?php else: ?>
      <div class="cart-grid">

        <!-- Cart Items -->
        <div class="cart-items-list" id="cartItemsList">
          <?php foreach ($cart as $item): ?>
            <div class="cart-item fade-in">

              <!-- Image -->
              <a href="/ecommerce/product.php?id=<?= $item['id'] ?>" class="cart-item-img">
                <img src="<?= sanitize(getProductImageUrl($item['image'])) ?>"
                     alt="<?= sanitize($item['name']) ?>" loading="lazy">
              </a>

              <!-- Info -->
              <div>
                <a href="/ecommerce/product.php?id=<?= $item['id'] ?>">
                  <div class="cart-item-name"><?= sanitize($item['name']) ?></div>
                </a>
                <div class="cart-item-price"><?= formatPrice($item['price']) ?> each</div>

                <!-- Qty update form -->
                <form action="/ecommerce/cart_action.php" method="POST" class="cart-item-qty">
                  <input type="hidden" name="action"     value="update">
                  <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                  <input type="hidden" name="redirect"   value="/ecommerce/cart.php">
                  <label for="qty_<?= $item['id'] ?>" style="font-size:0.8rem;color:var(--text-muted);">Qty:</label>
                  <input type="number" name="qty" id="qty_<?= $item['id'] ?>"
                         class="cart-qty-input"
                         value="<?= (int)$item['qty'] ?>"
                         min="1" max="<?= (int)($item['stock'] ?? 99) ?>"
                         style="width:55px;height:32px;text-align:center;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text-primary);font-size:0.85rem;outline:none;"
                         aria-label="Quantity for <?= sanitize($item['name']) ?>">
                  <button type="submit" class="btn btn-ghost btn-sm">Update</button>
                </form>

                <!-- Remove -->
                <form action="/ecommerce/cart_action.php" method="POST" style="display:inline;">
                  <input type="hidden" name="action"     value="remove">
                  <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                  <input type="hidden" name="redirect"   value="/ecommerce/cart.php">
                  <button type="submit" class="cart-item-remove" data-confirm="Remove this item?">Remove</button>
                </form>
              </div>

              <!-- Line total -->
              <div class="cart-item-total">
                <?= formatPrice($item['price'] * $item['qty']) ?>
              </div>

            </div>
          <?php endforeach; ?>

          <!-- Clear cart -->
          <div style="margin-top:1rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
            <a href="/ecommerce/index.php" class="btn btn-ghost btn-sm">← Continue Shopping</a>
            <form action="/ecommerce/cart_action.php" method="POST">
              <input type="hidden" name="action"   value="clear">
              <input type="hidden" name="redirect" value="/ecommerce/cart.php">
              <button type="submit" class="btn btn-danger btn-sm" data-confirm="Clear your entire cart?">
                🗑 Clear Cart
              </button>
            </form>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="cart-summary fade-in-1">
          <h3>Order Summary</h3>

          <div class="summary-row">
            <span>Subtotal</span>
            <span><?= formatPrice(getCartTotal()) ?></span>
          </div>
          <div class="summary-row">
            <span>Shipping</span>
            <span><?= getCartTotal() >= 99 ? '<span style="color:var(--success);">Free</span>' : formatPrice(9.99) ?></span>
          </div>
          <div class="summary-row">
            <span>Tax (10%)</span>
            <span><?= formatPrice(getCartTotal() * 0.10) ?></span>
          </div>

          <hr class="summary-divider">

          <div class="summary-row">
            <span style="font-weight:600;">Total</span>
            <span class="summary-total">
              <?php
                $shipping = getCartTotal() >= 99 ? 0 : 9.99;
                $tax      = getCartTotal() * 0.10;
                echo formatPrice(getCartTotal() + $shipping + $tax);
              ?>
            </span>
          </div>

          <a href="/ecommerce/checkout.php" class="btn btn-primary btn-full btn-lg" style="margin-top:1.25rem;" id="checkoutBtn">
            Proceed to Checkout →
          </a>

          <div style="margin-top:1rem;font-size:0.78rem;color:var(--text-muted);text-align:center;">
            🔒 Secure checkout &nbsp;·&nbsp; 30-day returns
          </div>
        </div>

      </div><!-- /.cart-grid -->
    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
