<?php
require_once __DIR__ . '/includes/header.php';  // also loads db, auth, functions

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) redirect('/ecommerce/admin/orders.php');

// ── Fetch Order ────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch();
if (!$order) redirect('/ecommerce/admin/orders.php');

// ── Handle Status Update ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $newStatus = $_POST['status'];
    $allowed   = ['Pending','Processing','Shipped','Delivered','Cancelled'];
    if (in_array($newStatus, $allowed)) {
        $upd = $pdo->prepare("UPDATE orders SET status = :s WHERE id = :id");
        $upd->execute([':s' => $newStatus, ':id' => $orderId]);
        setFlash('success', 'Order status updated to ' . $newStatus . '.');
        redirect('/ecommerce/admin/order_detail.php?id=' . $orderId);
    }
}

// ── Fetch Items ────────────────────────────────────────────────
$items = $pdo->prepare("SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :oid");
$items->execute([':oid' => $orderId]);
$orderItems = $items->fetchAll();

$adminPageTitle = 'Order #' . str_pad($orderId,5,'0',STR_PAD_LEFT);
?>

<!-- Breadcrumb -->
<div style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:var(--text-muted);margin-bottom:1.5rem;">
  <a href="/ecommerce/admin/orders.php" style="color:var(--text-muted);">Orders</a>
  <span>›</span>
  <span style="color:var(--text-secondary);">Order #<?= str_pad($orderId,5,'0',STR_PAD_LEFT) ?></span>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start;">

  <!-- ═══ LEFT — Items & Details ═══ -->
  <div style="display:flex;flex-direction:column;gap:1.5rem;">

    <!-- Order Items -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h3>📦 Order Items</h3>
        <span class="badge <?= getStatusBadgeClass($order['status']) ?>"><?= sanitize($order['status']) ?></span>
      </div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th scope="col" colspan="2">Product</th>
              <th scope="col">Unit Price</th>
              <th scope="col">Qty</th>
              <th scope="col">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orderItems as $item): ?>
              <tr>
                <td style="width:50px;">
                  <div class="product-thumb">
                    <img src="<?= sanitize(getProductImageUrl($item['image'])) ?>" alt="<?= sanitize($item['product_name']) ?>" loading="lazy">
                  </div>
                </td>
                <td>
                  <div style="font-weight:500;font-size:0.875rem;"><?= sanitize($item['product_name']) ?></div>
                  <div style="font-size:0.75rem;color:var(--text-muted);">ID: <?= $item['product_id'] ?></div>
                </td>
                <td><?= formatPrice($item['price']) ?></td>
                <td><?= $item['qty'] ?></td>
                <td><strong><?= formatPrice($item['price'] * $item['qty']) ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" style="text-align:right;font-weight:600;padding:1rem;">Order Total:</td>
              <td style="font-size:1.1rem;font-weight:700;color:var(--accent);"><?= formatPrice($order['total']) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Customer & Shipping -->
    <div class="admin-card">
      <div class="admin-card-header"><h3>📋 Customer & Shipping</h3></div>
      <div class="admin-card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
          <div>
            <h4 style="font-size:0.78rem;text-transform:uppercase;color:var(--text-muted);letter-spacing:0.06em;margin-bottom:0.75rem;">Customer</h4>
            <p style="font-size:0.875rem;font-weight:500;"><?= sanitize($order['full_name']) ?></p>
            <p style="font-size:0.82rem;color:var(--text-secondary);margin-top:0.25rem;"><?= sanitize($order['email']) ?></p>
            <p style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">
              <?= $order['user_id'] ? 'Registered User #' . $order['user_id'] : 'Guest Checkout' ?>
            </p>
          </div>
          <div>
            <h4 style="font-size:0.78rem;text-transform:uppercase;color:var(--text-muted);letter-spacing:0.06em;margin-bottom:0.75rem;">Shipping Address</h4>
            <p style="font-size:0.875rem;line-height:1.7;color:var(--text-secondary);">
              <?= sanitize($order['address']) ?><br>
              <?= sanitize($order['city']) ?>, <?= sanitize($order['zip']) ?><br>
              <?= sanitize($order['country']) ?>
            </p>
          </div>
        </div>
        <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border);display:flex;gap:2rem;font-size:0.82rem;">
          <div>
            <span style="color:var(--text-muted);">Order Date</span><br>
            <strong><?= date('F j, Y H:i', strtotime($order['created_at'])) ?></strong>
          </div>
          <div>
            <span style="color:var(--text-muted);">Order ID</span><br>
            <strong>#<?= str_pad($orderId,5,'0',STR_PAD_LEFT) ?></strong>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- ═══ RIGHT — Actions ═══ -->
  <div style="display:flex;flex-direction:column;gap:1.5rem;">

    <!-- Status Update -->
    <div class="admin-card">
      <div class="admin-card-header"><h3>🔄 Update Status</h3></div>
      <div class="admin-card-body">
        <div style="margin-bottom:1rem;padding:1rem;background:var(--bg-card);border-radius:var(--radius-sm);text-align:center;">
          <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:0.5rem;">Current Status</div>
          <span class="badge <?= getStatusBadgeClass($order['status']) ?>" style="font-size:0.85rem;padding:0.35rem 1rem;">
            <?= sanitize($order['status']) ?>
          </span>
        </div>

        <!-- Progress Steps -->
        <div style="margin-bottom:1.25rem;">
          <?php
            $steps = ['Pending','Processing','Shipped','Delivered'];
            $currentIdx = array_search($order['status'], $steps);
          ?>
          <div style="display:flex;flex-direction:column;gap:0.4rem;">
            <?php foreach ($steps as $i => $step): ?>
              <div style="display:flex;align-items:center;gap:0.6rem;font-size:0.82rem;">
                <div style="width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;flex-shrink:0;
                     background:<?= ($currentIdx !== false && $i <= $currentIdx) ? 'var(--primary)' : 'var(--border)' ?>;
                     color:<?= ($currentIdx !== false && $i <= $currentIdx) ? 'white' : 'var(--text-muted)' ?>;">
                  <?= ($currentIdx !== false && $i < $currentIdx) ? '✓' : ($i + 1) ?>
                </div>
                <span style="color:<?= ($currentIdx !== false && $i <= $currentIdx) ? 'var(--text-primary)' : 'var(--text-muted)' ?>;"><?= $step ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <form action="/ecommerce/admin/order_detail.php?id=<?= $orderId ?>" method="POST" id="statusForm">
          <div class="form-group">
            <label for="status" class="form-label">Change Status To</label>
            <select name="status" id="status" class="form-control">
              <?php foreach (['Pending','Processing','Shipped','Delivered','Cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary btn-full" id="updateStatusBtn">Update Status</button>
        </form>
      </div>
    </div>

    <!-- Quick Summary -->
    <div class="admin-card">
      <div class="admin-card-header"><h3>💰 Order Summary</h3></div>
      <div class="admin-card-body" style="display:flex;flex-direction:column;gap:0.6rem;">
        <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
          <span style="color:var(--text-secondary);">Items</span>
          <span><?= count($orderItems) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
          <span style="color:var(--text-secondary);">Total</span>
          <strong style="color:var(--accent);"><?= formatPrice($order['total']) ?></strong>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <div style="display:flex;flex-direction:column;gap:0.5rem;">
      <a href="/ecommerce/admin/orders.php" class="btn btn-ghost btn-sm">← Back to Orders</a>
      <a href="/ecommerce/admin/index.php" class="btn btn-ghost btn-sm">Dashboard</a>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
