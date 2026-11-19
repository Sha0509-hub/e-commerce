<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) redirect('/ecommerce/index.php');

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch();
if (!$order) redirect('/ecommerce/index.php');

// Fetch order items
$items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = :oid");
$items->execute([':oid' => $orderId]);
$orderItems = $items->fetchAll();

$pageTitle = 'Order Confirmed!';
require_once __DIR__ . '/includes/header.php';
?>

<div class="success-page">
  <div class="card" style="max-width:600px;width:100%;margin:0 auto;text-align:center;padding:0;">

    <!-- Header -->
    <div style="padding:2.5rem 2.5rem 2rem;border-bottom:1px solid var(--border);">
      <div class="success-icon-wrap">✓</div>
      <h1 style="font-size:1.75rem;margin-bottom:0.5rem;">Order Confirmed!</h1>
      <p style="color:var(--text-secondary);font-size:0.95rem;">Thank you for your purchase. We'll process your order right away.</p>
      <div class="success-order-id" style="margin-top:0.75rem;">
        Order ID: <strong>#<?= str_pad($orderId, 5, '0', STR_PAD_LEFT) ?></strong>
      </div>
    </div>

    <!-- Details -->
    <div style="padding:1.5rem 2.5rem;border-bottom:1px solid var(--border);">
      <div class="success-details" style="text-align:left;margin-bottom:0;">
        <div class="success-detail-row">
          <span>Customer</span>
          <strong><?= sanitize($order['full_name']) ?></strong>
        </div>
        <div class="success-detail-row">
          <span>Email</span>
          <strong><?= sanitize($order['email']) ?></strong>
        </div>
        <div class="success-detail-row">
          <span>Ship to</span>
          <strong><?= sanitize($order['address'] . ', ' . $order['city'] . ' ' . $order['zip']) ?></strong>
        </div>
        <div class="success-detail-row">
          <span>Status</span>
          <span class="badge badge-warning">⏳ <?= sanitize($order['status']) ?></span>
        </div>
        <div class="success-detail-row">
          <span>Date</span>
          <strong><?= date('F j, Y', strtotime($order['created_at'])) ?></strong>
        </div>
        <div class="success-detail-row" style="font-size:1rem;">
          <span>Total Paid</span>
          <strong style="color:var(--accent);font-size:1.1rem;"><?= formatPrice($order['total']) ?></strong>
        </div>
      </div>
    </div>

    <!-- Items -->
    <div style="padding:1.5rem 2.5rem;border-bottom:1px solid var(--border);text-align:left;">
      <h3 style="font-size:0.875rem;color:var(--text-muted);margin-bottom:1rem;text-transform:uppercase;letter-spacing:0.05em;">Items Ordered</h3>
      <?php foreach ($orderItems as $item): ?>
        <div style="display:flex;justify-content:space-between;padding:0.5rem 0;font-size:0.875rem;border-bottom:1px solid var(--border);">
          <span><?= sanitize($item['product_name']) ?> <span style="color:var(--text-muted);">×<?= $item['qty'] ?></span></span>
          <span style="font-weight:600;"><?= formatPrice($item['price'] * $item['qty']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Actions -->
    <div style="padding:2rem 2.5rem;display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
      <a href="/ecommerce/index.php" class="btn btn-primary btn-lg">Continue Shopping</a>
      <?php if (isLoggedIn()): ?>
        <a href="/ecommerce/profile.php" class="btn btn-outline btn-lg">View My Orders</a>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
