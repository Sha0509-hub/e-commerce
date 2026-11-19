<?php
$adminPageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// ── Dashboard Stats ─────────────────────────────────────────────
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'Cancelled'")->fetchColumn();
$totalOrders  = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalProducts= $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalUsers   = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pendingOrders= $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();

// Recent orders
$recentOrders = $pdo->query("SELECT o.*, (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count FROM orders o ORDER BY o.created_at DESC LIMIT 8")->fetchAll();

// Low stock products
$lowStock = $pdo->query("SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock < 10 ORDER BY p.stock ASC LIMIT 5")->fetchAll();

// Revenue by status
$statusStats = $pdo->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!-- ═══ STATS GRID ═══ -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-data">
      <div class="stat-label">Total Revenue</div>
      <div class="stat-value"><?= formatPrice($totalRevenue) ?></div>
      <div class="stat-trend trend-up">↑ All time</div>
    </div>
    <div class="stat-icon stat-icon-purple">💰</div>
  </div>
  <div class="stat-card">
    <div class="stat-data">
      <div class="stat-label">Total Orders</div>
      <div class="stat-value"><?= $totalOrders ?></div>
      <div class="stat-trend trend-up">
        <span style="color:var(--warning);"><?= $pendingOrders ?></span> pending
      </div>
    </div>
    <div class="stat-icon stat-icon-green">🛒</div>
  </div>
  <div class="stat-card">
    <div class="stat-data">
      <div class="stat-label">Products</div>
      <div class="stat-value"><?= $totalProducts ?></div>
      <div class="stat-trend trend-up">In catalog</div>
    </div>
    <div class="stat-icon stat-icon-yellow">📦</div>
  </div>
  <div class="stat-card">
    <div class="stat-data">
      <div class="stat-label">Customers</div>
      <div class="stat-value"><?= $totalUsers ?></div>
      <div class="stat-trend trend-up">Registered users</div>
    </div>
    <div class="stat-icon stat-icon-blue">👥</div>
  </div>
</div>

<!-- ═══ TWO-COL GRID ═══ -->
<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;">

  <!-- Recent Orders -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h3>📋 Recent Orders</h3>
      <a href="/ecommerce/admin/orders.php" class="btn btn-ghost btn-sm">View All →</a>
    </div>
    <div class="admin-table-wrap">
      <table class="admin-table" role="table">
        <thead>
          <tr>
            <th scope="col">Order</th>
            <th scope="col">Customer</th>
            <th scope="col">Items</th>
            <th scope="col">Total</th>
            <th scope="col">Status</th>
            <th scope="col">Date</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $order): ?>
            <tr>
              <td><strong style="color:var(--accent);">#<?= str_pad($order['id'],5,'0',STR_PAD_LEFT) ?></strong></td>
              <td>
                <div style="font-size:0.875rem;font-weight:500;"><?= sanitize($order['full_name']) ?></div>
                <div style="font-size:0.75rem;color:var(--text-muted);"><?= sanitize($order['email']) ?></div>
              </td>
              <td style="color:var(--text-secondary);"><?= $order['item_count'] ?></td>
              <td><strong><?= formatPrice($order['total']) ?></strong></td>
              <td><span class="badge <?= getStatusBadgeClass($order['status']) ?>"><?= sanitize($order['status']) ?></span></td>
              <td style="color:var(--text-secondary);font-size:0.8rem;"><?= timeAgo($order['created_at']) ?></td>
              <td>
                <a href="/ecommerce/admin/order_detail.php?id=<?= $order['id'] ?>" class="action-btn action-view">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($recentOrders)): ?>
            <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">No orders yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Right Column -->
  <div style="display:flex;flex-direction:column;gap:1.5rem;">

    <!-- Order Status Breakdown -->
    <div class="admin-card">
      <div class="admin-card-header"><h3>📊 Order Status</h3></div>
      <div class="admin-card-body" style="display:flex;flex-direction:column;gap:0.6rem;">
        <?php
          $allStatuses = ['Pending','Processing','Shipped','Delivered','Cancelled'];
          foreach ($allStatuses as $s):
            $cnt   = $statusStats[$s] ?? 0;
            $color = getStatusColor($s);
        ?>
          <div style="display:flex;align-items:center;justify-content:space-between;font-size:0.875rem;">
            <span style="color:var(--text-secondary);"><?= $s ?></span>
            <span class="badge <?= getStatusBadgeClass($s) ?>"><?= $cnt ?></span>
          </div>
          <div style="height:4px;background:var(--border);border-radius:2px;">
            <?php if ($totalOrders > 0): ?>
              <div style="height:100%;border-radius:2px;background:<?= $color ?>;width:<?= round($cnt/$totalOrders*100) ?>%;transition:width 0.5s ease;"></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h3>⚠️ Low Stock</h3>
        <a href="/ecommerce/admin/products.php" class="btn btn-ghost btn-sm">Manage →</a>
      </div>
      <div class="admin-card-body" style="padding:0.5rem 1rem;">
        <?php if (empty($lowStock)): ?>
          <p style="color:var(--text-muted);font-size:0.85rem;padding:0.5rem 0;">All products well-stocked ✓</p>
        <?php else: ?>
          <?php foreach ($lowStock as $lp): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid var(--border);">
              <div>
                <div style="font-size:0.82rem;font-weight:500;"><?= sanitize($lp['name']) ?></div>
                <div style="font-size:0.72rem;color:var(--text-muted);"><?= sanitize($lp['cat_name'] ?? '') ?></div>
              </div>
              <span class="badge <?= $lp['stock'] === 0 ? 'badge-danger' : 'badge-warning' ?>">
                <?= $lp['stock'] === 0 ? 'Out of Stock' : $lp['stock'] . ' left' ?>
              </span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
