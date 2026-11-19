<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$cart = getCart();
if (empty($cart)) {
    setFlash('info', 'Your cart is empty. Add some products first!');
    redirect('/ecommerce/index.php');
}

$errors = [];
$formData = [];

// ── Process Order ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['full_name','email','address','city','zip','country'];
    foreach ($fields as $f) {
        $formData[$f] = trim($_POST[$f] ?? '');
        if (empty($formData[$f])) {
            $errors[$f] = 'This field is required.';
        }
    }
    if (!empty($formData['email']) && !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        $shipping = getCartTotal() >= 99 ? 0 : 9.99;
        $tax      = getCartTotal() * 0.10;
        $total    = getCartTotal() + $shipping + $tax;
        $userId   = isLoggedIn() ? $_SESSION['user_id'] : null;

        try {
            $pdo->beginTransaction();

            // Insert order
            $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, full_name, email, address, city, zip, country, total, status)
                                        VALUES (:user_id, :full_name, :email, :address, :city, :zip, :country, :total, 'Pending')");
            $orderStmt->execute([
                ':user_id'   => $userId,
                ':full_name' => $formData['full_name'],
                ':email'     => $formData['email'],
                ':address'   => $formData['address'],
                ':city'      => $formData['city'],
                ':zip'       => $formData['zip'],
                ':country'   => $formData['country'],
                ':total'     => round($total, 2),
            ]);
            $orderId = $pdo->lastInsertId();

            // Insert order items
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, qty)
                                       VALUES (:order_id, :product_id, :product_name, :price, :qty)");
            foreach ($cart as $item) {
                $itemStmt->execute([
                    ':order_id'    => $orderId,
                    ':product_id'  => $item['id'],
                    ':product_name'=> $item['name'],
                    ':price'       => $item['price'],
                    ':qty'         => $item['qty'],
                ]);
                // Decrement stock
                $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :id AND stock >= :qty")
                    ->execute([':qty' => $item['qty'], ':id' => $item['id']]);
            }

            $pdo->commit();
            clearCart();
            redirect('/ecommerce/order_success.php?id=' . $orderId);

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Checkout error: ' . $e->getMessage());
            $errors['general'] = 'Order failed. Please try again.';
        }
    }
}

$pageTitle = 'Checkout';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1>Checkout</h1>
    <nav class="breadcrumb" style="padding-top:0.5rem;" aria-label="Breadcrumb">
      <a href="/ecommerce/index.php">Home</a>
      <span class="sep">›</span>
      <a href="/ecommerce/cart.php">Cart</a>
      <span class="sep">›</span>
      <span class="current">Checkout</span>
    </nav>
  </div>
</div>

<div class="checkout-page">
  <div class="container">

    <?php if (!empty($errors['general'])): ?>
      <div class="flash flash-error">⚠️ <?= sanitize($errors['general']) ?></div>
    <?php endif; ?>

    <div class="checkout-grid">

      <!-- ═══ FORM ═══ -->
      <form action="/ecommerce/checkout.php" method="POST" id="checkoutForm" novalidate>

        <!-- Shipping -->
        <div class="checkout-section card card-body fade-in" style="margin-bottom:1.5rem;">
          <h3><span class="step-num">1</span> Shipping Information</h3>

          <div class="form-group">
            <label for="full_name" class="form-label">Full Name *</label>
            <input type="text" name="full_name" id="full_name" class="form-control"
                   value="<?= sanitize($formData['full_name'] ?? (isLoggedIn() ? $_SESSION['user_name'] : '')) ?>"
                   placeholder="John Doe" required autocomplete="name">
            <?php if (!empty($errors['full_name'])): ?>
              <div class="form-error"><?= sanitize($errors['full_name']) ?></div>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="email" class="form-label">Email Address *</label>
            <input type="email" name="email" id="email" class="form-control"
                   value="<?= sanitize($formData['email'] ?? (isLoggedIn() ? $_SESSION['user_email'] : '')) ?>"
                   placeholder="john@example.com" required autocomplete="email">
            <?php if (!empty($errors['email'])): ?>
              <div class="form-error"><?= sanitize($errors['email']) ?></div>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label for="address" class="form-label">Street Address *</label>
            <input type="text" name="address" id="address" class="form-control"
                   value="<?= sanitize($formData['address'] ?? '') ?>"
                   placeholder="123 Main Street, Apt 4B" required autocomplete="street-address">
            <?php if (!empty($errors['address'])): ?>
              <div class="form-error"><?= sanitize($errors['address']) ?></div>
            <?php endif; ?>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="city" class="form-label">City *</label>
              <input type="text" name="city" id="city" class="form-control"
                     value="<?= sanitize($formData['city'] ?? '') ?>"
                     placeholder="New York" required autocomplete="address-level2">
              <?php if (!empty($errors['city'])): ?>
                <div class="form-error"><?= sanitize($errors['city']) ?></div>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label for="zip" class="form-label">ZIP / Postal Code *</label>
              <input type="text" name="zip" id="zip" class="form-control"
                     value="<?= sanitize($formData['zip'] ?? '') ?>"
                     placeholder="10001" required autocomplete="postal-code">
              <?php if (!empty($errors['zip'])): ?>
                <div class="form-error"><?= sanitize($errors['zip']) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <label for="country" class="form-label">Country *</label>
            <select name="country" id="country" class="form-control" required autocomplete="country">
              <?php
                $countries = ['United States','United Kingdom','Canada','Australia','Germany','France','India','Japan','Other'];
                $selected  = $formData['country'] ?? 'United States';
                foreach ($countries as $c) {
                    $sel = $c === $selected ? 'selected' : '';
                    echo "<option value=\"$c\" $sel>" . htmlspecialchars($c) . "</option>";
                }
              ?>
            </select>
          </div>
        </div>

        <!-- Payment (mock) -->
        <div class="checkout-section card card-body fade-in-1" style="margin-bottom:1.5rem;">
          <h3><span class="step-num">2</span> Payment Method</h3>
          <div style="display:flex;flex-direction:column;gap:0.75rem;">
            <?php
              $payMethods = [
                ['credit_card', '💳 Credit / Debit Card'],
                ['paypal',      '🅿️ PayPal'],
                ['bank',        '🏦 Bank Transfer'],
              ];
            ?>
            <?php foreach ($payMethods as [$val, $label]): ?>
              <label style="display:flex;align-items:center;gap:0.75rem;padding:0.9rem;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;transition:var(--transition);"
                     onmouseover="this.style.borderColor='var(--border-hover)'" onmouseout="this.style.borderColor='var(--border)'">
                <input type="radio" name="payment" value="<?= $val ?>" <?= $val === 'credit_card' ? 'checked' : '' ?> style="accent-color:var(--primary);">
                <span style="font-size:0.9rem;font-weight:500;"><?= $label ?></span>
                <?php if ($val === 'credit_card'): ?>
                  <span style="margin-left:auto;font-size:0.75rem;color:var(--text-muted);">Demo — no real charge</span>
                <?php endif; ?>
              </label>
            <?php endforeach; ?>
          </div>

          <!-- Mock card fields -->
          <div style="margin-top:1.25rem;padding:1rem;background:var(--bg-card);border-radius:var(--radius-sm);">
            <div class="form-group" style="margin-bottom:0.75rem;">
              <label class="form-label">Card Number</label>
              <input type="text" class="form-control" placeholder="4242 4242 4242 4242" maxlength="19">
            </div>
            <div class="form-row">
              <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Expiry</label>
                <input type="text" class="form-control" placeholder="MM / YY" maxlength="7">
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">CVV</label>
                <input type="text" class="form-control" placeholder="•••" maxlength="4">
              </div>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg" id="placeOrderBtn">
          🔒 Place Order — <?php
            $shipping = getCartTotal() >= 99 ? 0 : 9.99;
            $tax      = getCartTotal() * 0.10;
            echo formatPrice(getCartTotal() + $shipping + $tax);
          ?>
        </button>
      </form>

      <!-- ═══ ORDER SUMMARY ═══ -->
      <div class="order-summary-card fade-in-2">
        <div class="order-summary-header">Order Summary (<?= getCartCount() ?> items)</div>

        <?php foreach ($cart as $item): ?>
          <div class="order-summary-item">
            <div class="order-summary-img">
              <img src="<?= sanitize(getProductImageUrl($item['image'])) ?>" alt="<?= sanitize($item['name']) ?>" loading="lazy">
            </div>
            <div style="flex:1;">
              <div class="order-summary-name"><?= sanitize($item['name']) ?></div>
              <div class="order-summary-price">Qty: <?= $item['qty'] ?></div>
            </div>
            <div style="font-weight:600;font-size:0.9rem;"><?= formatPrice($item['price'] * $item['qty']) ?></div>
          </div>
        <?php endforeach; ?>

        <?php
          $shipping = getCartTotal() >= 99 ? 0 : 9.99;
          $tax      = getCartTotal() * 0.10;
          $total    = getCartTotal() + $shipping + $tax;
        ?>
        <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);">
          <div class="summary-row" style="padding:0.3rem 0;">
            <span style="font-size:0.875rem;">Subtotal</span>
            <span style="font-size:0.875rem;"><?= formatPrice(getCartTotal()) ?></span>
          </div>
          <div class="summary-row" style="padding:0.3rem 0;">
            <span style="font-size:0.875rem;">Shipping</span>
            <span style="font-size:0.875rem;"><?= $shipping === 0 ? '<span style="color:var(--success);">Free</span>' : formatPrice($shipping) ?></span>
          </div>
          <div class="summary-row" style="padding:0.3rem 0;">
            <span style="font-size:0.875rem;">Tax (10%)</span>
            <span style="font-size:0.875rem;"><?= formatPrice($tax) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid var(--border);">
            <span style="font-weight:700;">Total</span>
            <span style="font-weight:700;font-size:1.1rem;color:var(--accent);"><?= formatPrice($total) ?></span>
          </div>
        </div>
        <div class="order-summary-footer" style="text-align:center;font-size:0.78rem;color:var(--text-muted);">
          🔒 256-bit SSL encryption &nbsp;·&nbsp; ↩️ 30-day returns
        </div>
      </div>

    </div><!-- /.checkout-grid -->
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
