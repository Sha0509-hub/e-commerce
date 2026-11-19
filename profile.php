<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

// Fetch user orders
$stmt = $pdo->prepare("SELECT o.*, (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
                        FROM orders o WHERE o.user_id = :uid ORDER BY o.created_at DESC");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1>My Account</h1>
  </div>
</div>

<div class="profile-page">
  <div class="container">

    <!-- Profile Header -->
    <div class="profile-header fade-in">
      <div class="profile-avatar">
        <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
      </div>
      <div class="profile-info">
        <h2><?= sanitize($_SESSION['user_name']) ?></h2>
        <p><?= sanitize($_SESSION['user_email']) ?></p>
        <p style="margin-top:0.25rem;font-size:0.8rem;color:var(--text-muted);">
          Member since <?= date('F Y') ?>
        </p>
      </div>
      <div style="margin-left:auto;">
        <a href="/ecommerce/logout.php" class="btn btn-ghost btn-sm">Sign Out</a>
      </div>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:2rem;" class="fade-in-1">
      <?php
        $totalSpent = array_sum(array_column($orders, 'total'));
        $statuses   = array_count_values(array_column($orders, 'status'));
      ?>
      <div class="card card-body" style="text-align:center;">
        <div style="font-size:1.8rem;font-weight:800;color:var(--accent);"><?= count($orders) ?></div>
        <div style="font-size:0.8rem;color:var(--text-muted);margin-top:0.25rem;">Total Orders</div>
      </div>
      <div class="card card-body" style="text-align:center;">
        <div style="font-size:1.8rem;font-weight:800;color:var(--success);"><?= formatPrice($totalSpent) ?></div>
        <div style="font-size:0.8rem;color:var(--text-muted);margin-top:0.25rem;">Total Spent</div>
      </div>
      <div class="card card-body" style="text-align:center;">
        <div style="font-size:1.8rem;font-weight:800;color:var(--warning);"><?= $statuses['Pending'] ?? 0 ?></div>
        <div style="font-size:0.8rem;color:var(--text-muted);margin-top:0.25rem;">Pending</div>
      </div>
      <div class="card card-body" style="text-align:center;">
        <div style="font-size:1.8rem;font-weight:800;color:var(--info);"><?= $statuses['Delivered'] ?? 0 ?></div>
        <div style="font-size:0.8rem;color:var(--text-muted);margin-top:0.25rem;">Delivered</div>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="card fade-in-2">
      <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
        <span>📦 Order History</span>
        <a href="/ecommerce/index.php" class="btn btn-primary btn-sm">Shop Again</a>
      </div>

      <?php if (empty($orders)): ?>
        <div class="empty-state" style="padding:3rem 2rem;">
          <div class="empty-state-icon">📦</div>
          <h3>No orders yet</h3>
          <p>Start shopping to see your orders here.</p>
          <a href="/ecommerce/index.php" class="btn btn-primary">Browse Products</a>
        </div>
      <?php else: ?>
        <div style="overflow-x:auto;">
          <table class="orders-table" role="table">
            <thead>
              <tr>
                <th scope="col">Order ID</th>
                <th scope="col">Date</th>
                <th scope="col">Items</th>
                <th scope="col">Total</th>
                <th scope="col">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
                <tr>
                  <td>
                    <strong style="color:var(--accent);">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></strong>
                  </td>
                  <td style="color:var(--text-secondary);">
                    <?= date('M j, Y', strtotime($order['created_at'])) ?>
                  </td>
                  <td style="color:var(--text-secondary);">
                    <?= $order['item_count'] ?> item<?= $order['item_count'] != 1 ? 's' : '' ?>
                  </td>
                  <td>
                    <strong><?= formatPrice($order['total']) ?></strong>
                  </td>
                  <td>
                    <span class="badge <?= getStatusBadgeClass($order['status']) ?>">
                      <?= sanitize($order['status']) ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
