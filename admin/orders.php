<?php
$adminPageTitle = 'Orders';
require_once __DIR__ . '/includes/header.php';

// ── Filters ────────────────────────────────────────────────────
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search       = isset($_GET['q'])      ? sanitize($_GET['q'])      : '';

// Pagination
$perPage  = 12;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $perPage;

$params = [];
$where  = "WHERE 1=1";
if ($statusFilter && in_array($statusFilter, ['Pending','Processing','Shipped','Delivered','Cancelled'])) {
    $where .= " AND o.status = :status";
    $params[':status'] = $statusFilter;
}
if ($search) {
    $where .= " AND (o.full_name LIKE :q OR o.email LIKE :q2 OR o.id = :oid)";
    $params[':q']   = "%$search%";
    $params[':q2']  = "%$search%";
    $params[':oid'] = is_numeric($search) ? (int)$search : 0;
}

$totalSql = "SELECT COUNT(*) FROM orders o $where";
$totalStmt = $pdo->prepare($totalSql);
$totalStmt->execute($params);
$totalOrders = $totalStmt->fetchColumn();
$totalPages  = max(1, ceil($totalOrders / $perPage));

$sql = "SELECT o.*, (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
        FROM orders o $where ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<!-- Top Bar -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
  <div>
    <h2 style="font-size:1.1rem;">All Orders <span style="color:var(--text-muted);font-weight:400;font-size:0.875rem;">(<?= $totalOrders ?>)</span></h2>
  </div>
  <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
    <!-- Status Filter -->
    <form action="/ecommerce/admin/orders.php" method="GET" style="display:flex;gap:0.5rem;">
      <?php if ($search): ?><input type="hidden" name="q" value="<?= sanitize($search) ?>"><?php endif; ?>
      <select name="status" class="form-control" style="width:auto;font-size:0.82rem;padding:0.45rem 2rem 0.45rem 0.75rem;" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <?php foreach (['Pending','Processing','Shipped','Delivered','Cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </form>
    <!-- Search -->
    <form action="/ecommerce/admin/orders.php" method="GET" class="admin-search" style="position:relative;display:inline-flex;align-items:center;">
      <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= sanitize($statusFilter) ?>"><?php endif; ?>
      <span class="search-icon" style="position:absolute;left:0.65rem;color:var(--text-muted);font-size:0.85rem;">🔍</span>
      <input type="text" name="q" class="form-control" placeholder="Search orders…" value="<?= sanitize($search) ?>" style="padding-left:2.1rem;width:200px;">
    </form>
  </div>
</div>

<!-- Status Tabs -->
<div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.25rem;">
  <?php
    $tabs = ['All' => ''] + array_fill_keys(['Pending','Processing','Shipped','Delivered','Cancelled'],'');
    foreach (array_keys($tabs) as $tab) {
      $val   = $tab === 'All' ? '' : $tab;
      $qs    = $search ? "&q=$search" : '';
      $qs   .= $val ? "&status=$val" : '';
      $act   = $statusFilter === $val ? 'badge-primary' : 'badge-secondary';
  ?>
    <a href="/ecommerce/admin/orders.php?<?= ltrim($qs,'&') ?>"
       class="badge <?= $act ?>"
       style="padding:0.4rem 0.9rem;font-size:0.78rem;cursor:pointer;text-decoration:none;">
      <?= $tab ?>
    </a>
  <?php } ?>
</div>

<!-- Orders Table -->
<div class="admin-card">
  <div class="admin-table-wrap">
    <table class="admin-table" role="table">
      <thead>
        <tr>
          <th scope="col">Order ID</th>
          <th scope="col">Customer</th>
          <th scope="col">Date</th>
          <th scope="col">Items</th>
          <th scope="col">Total</th>
          <th scope="col">Status</th>
          <th scope="col">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
          <tr>
            <td><strong style="color:var(--accent);">#<?= str_pad($order['id'],5,'0',STR_PAD_LEFT) ?></strong></td>
            <td>
              <div style="font-weight:500;font-size:0.875rem;"><?= sanitize($order['full_name']) ?></div>
              <div style="font-size:0.75rem;color:var(--text-muted);"><?= sanitize($order['email']) ?></div>
            </td>
            <td style="color:var(--text-secondary);font-size:0.82rem;white-space:nowrap;">
              <?= date('M j, Y', strtotime($order['created_at'])) ?><br>
              <span style="font-size:0.72rem;"><?= date('H:i', strtotime($order['created_at'])) ?></span>
            </td>
            <td style="color:var(--text-secondary);"><?= $order['item_count'] ?> item<?= $order['item_count'] != 1 ? 's' : '' ?></td>
            <td><strong><?= formatPrice($order['total']) ?></strong></td>
            <td><span class="badge <?= getStatusBadgeClass($order['status']) ?>"><?= sanitize($order['status']) ?></span></td>
            <td class="actions-cell">
              <a href="/ecommerce/admin/order_detail.php?id=<?= $order['id'] ?>" class="action-btn action-view">View</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($orders)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:3rem;">No orders found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);">
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="?page=<?= $page-1 ?><?= $statusFilter ? '&status='.$statusFilter : '' ?><?= $search ? '&q='.$search : '' ?>" class="page-btn">← Prev</a>
        <?php endif; ?>
        <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
          <a href="?page=<?= $i ?><?= $statusFilter ? '&status='.$statusFilter : '' ?><?= $search ? '&q='.$search : '' ?>"
             class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a href="?page=<?= $page+1 ?><?= $statusFilter ? '&status='.$statusFilter : '' ?><?= $search ? '&q='.$search : '' ?>" class="page-btn">Next →</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
