<?php
$adminPageTitle = 'Products';
require_once __DIR__ . '/includes/header.php';

// ── Filters & Pagination ───────────────────────────────────────
$search   = isset($_GET['q'])   ? sanitize($_GET['q'])   : '';
$catFilter= isset($_GET['cat']) ? (int)$_GET['cat']      : 0;
$perPage  = 12;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $perPage;

$where  = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND p.name LIKE :q";
    $params[':q'] = "%$search%";
}
if ($catFilter) {
    $where .= " AND p.category_id = :cat";
    $params[':cat'] = $catFilter;
}

$total = $pdo->prepare("SELECT COUNT(*) FROM products p $where");
$total->execute($params);
$totalProducts = $total->fetchColumn();
$totalPages    = max(1, ceil($totalProducts / $perPage));

$stmt = $pdo->prepare("SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<!-- Top Bar -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
  <h2 style="font-size:1.1rem;">
    Products <span style="color:var(--text-muted);font-weight:400;font-size:0.875rem;">(<?= $totalProducts ?>)</span>
  </h2>
  <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
    <!-- Search -->
    <form action="/ecommerce/admin/products.php" method="GET" style="display:flex;gap:0.5rem;">
      <?php if ($catFilter): ?><input type="hidden" name="cat" value="<?= $catFilter ?>"><?php endif; ?>
      <input type="text" name="q" class="form-control" placeholder="Search products…" value="<?= sanitize($search) ?>" style="width:200px;font-size:0.82rem;padding:0.45rem 0.75rem;">
      <button type="submit" class="btn btn-ghost btn-sm">Go</button>
    </form>
    <!-- Category filter -->
    <form method="GET">
      <?php if ($search): ?><input type="hidden" name="q" value="<?= sanitize($search) ?>"><?php endif; ?>
      <select name="cat" class="form-control" style="width:auto;font-size:0.82rem;padding:0.45rem 2rem 0.45rem 0.75rem;" onchange="this.form.submit()">
        <option value="0">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $catFilter === (int)$cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
    <button class="btn btn-primary btn-sm" data-modal-open="addProductModal" id="openAddProduct">
      + Add Product
    </button>
  </div>
</div>

<!-- Products Table -->
<div class="admin-card">
  <div class="admin-table-wrap">
    <table class="admin-table" role="table">
      <thead>
        <tr>
          <th scope="col" colspan="2">Product</th>
          <th scope="col">Category</th>
          <th scope="col">Price</th>
          <th scope="col">Stock</th>
          <th scope="col">Featured</th>
          <th scope="col">Added</th>
          <th scope="col">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td style="width:50px;">
              <div class="product-thumb">
                <img src="<?= sanitize(getProductImageUrl($p['image'])) ?>" alt="<?= sanitize($p['name']) ?>" loading="lazy">
              </div>
            </td>
            <td>
              <div class="product-cell-name"><?= sanitize($p['name']) ?></div>
              <div class="product-cell-cat" style="font-size:0.72rem;color:var(--text-muted);">ID: <?= $p['id'] ?></div>
            </td>
            <td>
              <span class="badge badge-secondary"><?= sanitize($p['cat_name'] ?? '—') ?></span>
            </td>
            <td><strong><?= formatPrice($p['price']) ?></strong></td>
            <td>
              <?php if ($p['stock'] === 0): ?>
                <span class="badge badge-danger">Out</span>
              <?php elseif ($p['stock'] < 10): ?>
                <span class="badge badge-warning"><?= $p['stock'] ?> low</span>
              <?php else: ?>
                <span style="color:var(--success);font-size:0.875rem;"><?= $p['stock'] ?></span>
              <?php endif; ?>
            </td>
            <td>
              <?= $p['featured'] ? '<span class="badge badge-primary">⭐ Yes</span>' : '<span style="color:var(--text-muted);font-size:0.82rem;">No</span>' ?>
            </td>
            <td style="color:var(--text-muted);font-size:0.78rem;"><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
            <td class="actions-cell">
              <button class="action-btn action-edit edit-product-btn"
                      data-id="<?= $p['id'] ?>"
                      data-name="<?= sanitize($p['name']) ?>"
                      data-category="<?= $p['category_id'] ?>"
                      data-price="<?= $p['price'] ?>"
                      data-stock="<?= $p['stock'] ?>"
                      data-description="<?= sanitize($p['description']) ?>"
                      data-image="<?= sanitize($p['image']) ?>"
                      data-featured="<?= $p['featured'] ?>"
                      aria-label="Edit <?= sanitize($p['name']) ?>">
                Edit
              </button>
              <form action="/ecommerce/admin/product_action.php" method="POST" style="display:inline;">
                <input type="hidden" name="action"     value="delete">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <button type="submit" class="action-btn action-delete"
                        data-confirm="Delete '<?= sanitize($p['name']) ?>'? This cannot be undone.">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
          <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:3rem;">No products found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);">
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="?page=<?= $page-1 ?><?= $search ? '&q='.$search : '' ?><?= $catFilter ? '&cat='.$catFilter : '' ?>" class="page-btn">← Prev</a>
        <?php endif; ?>
        <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
          <a href="?page=<?= $i ?><?= $search ? '&q='.$search : '' ?><?= $catFilter ? '&cat='.$catFilter : '' ?>"
             class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a href="?page=<?= $page+1 ?><?= $search ? '&q='.$search : '' ?><?= $catFilter ? '&cat='.$catFilter : '' ?>" class="page-btn">Next →</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- ══════════════════════════════════════
     ADD PRODUCT MODAL
══════════════════════════════════════ -->
<div class="modal-overlay" id="addProductModal" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
  <div class="modal">
    <div class="modal-header">
      <h3 id="addModalTitle">Add New Product</h3>
      <button class="modal-close" data-modal-close aria-label="Close">✕</button>
    </div>
    <form action="/ecommerce/admin/product_action.php" method="POST" id="addProductForm">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-group">
          <label for="add_name" class="form-label">Product Name *</label>
          <input type="text" name="name" id="add_name" class="form-control" placeholder="e.g. ProBook Laptop 15" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="add_category_id" class="form-label">Category</label>
            <select name="category_id" id="add_category_id" class="form-control">
              <option value="">— Select Category —</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="add_price" class="form-label">Price ($) *</label>
            <input type="number" name="price" id="add_price" class="form-control" placeholder="0.00" step="0.01" min="0" required>
          </div>
        </div>
        <div class="form-group">
          <label for="add_description" class="form-label">Description</label>
          <textarea name="description" id="add_description" class="form-control" rows="3" placeholder="Product description…"></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="add_stock" class="form-label">Stock Quantity *</label>
            <input type="number" name="stock" id="add_stock" class="form-control" placeholder="0" min="0" required>
          </div>
          <div class="form-group">
            <label class="form-label" style="display:block;margin-bottom:0.5rem;">Featured?</label>
            <label class="form-check">
              <input type="checkbox" name="featured" value="1" id="add_featured">
              <span>Mark as featured</span>
            </label>
          </div>
        </div>
        <div class="form-group">
          <label for="product_image_url" class="form-label">Image URL</label>
          <input type="url" name="image" id="product_image_url" class="form-control" placeholder="https://example.com/image.jpg">
          <div class="form-hint">Paste an image URL. Leave blank for placeholder.</div>
          <img id="imagePreview" src="" alt="Preview" style="display:none;margin-top:0.75rem;width:100%;max-height:160px;object-fit:cover;border-radius:var(--radius-sm);border:1px solid var(--border);">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary" id="submitAddProduct">Add Product</button>
      </div>
    </form>
  </div>
</div>

<!-- ══════════════════════════════════════
     EDIT PRODUCT MODAL
══════════════════════════════════════ -->
<div class="modal-overlay" id="editProductModal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
  <div class="modal">
    <div class="modal-header">
      <h3 id="editModalTitle">Edit Product</h3>
      <button class="modal-close" data-modal-close aria-label="Close">✕</button>
    </div>
    <form action="/ecommerce/admin/product_action.php" method="POST" id="editProductForm">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="product_id" id="edit_product_id">
      <div class="modal-body">
        <div class="form-group">
          <label for="edit_name" class="form-label">Product Name *</label>
          <input type="text" name="name" id="edit_name" class="form-control" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="edit_category_id" class="form-label">Category</label>
            <select name="category_id" id="edit_category_id" class="form-control">
              <option value="">— Select Category —</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="edit_price" class="form-label">Price ($) *</label>
            <input type="number" name="price" id="edit_price" class="form-control" step="0.01" min="0" required>
          </div>
        </div>
        <div class="form-group">
          <label for="edit_description" class="form-label">Description</label>
          <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="edit_stock" class="form-label">Stock Quantity *</label>
            <input type="number" name="stock" id="edit_stock" class="form-control" min="0" required>
          </div>
          <div class="form-group">
            <label class="form-label" style="display:block;margin-bottom:0.5rem;">Featured?</label>
            <label class="form-check">
              <input type="checkbox" name="featured" value="1" id="edit_featured">
              <span>Mark as featured</span>
            </label>
          </div>
        </div>
        <div class="form-group">
          <label for="edit_image" class="form-label">Image URL</label>
          <input type="url" name="image" id="edit_image" class="form-control" placeholder="https://…">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary" id="submitEditProduct">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
