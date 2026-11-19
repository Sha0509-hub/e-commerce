<?php
// product_action.php — handles add / edit / delete products
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/ecommerce/admin/products.php');

$action = $_POST['action'] ?? '';

switch ($action) {

    // ── ADD ─────────────────────────────────────────────────────
    case 'add':
        $name        = trim($_POST['name']        ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = (float)($_POST['price']    ?? 0);
        $stock       = (int)($_POST['stock']      ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0) ?: null;
        $image       = trim($_POST['image']       ?? '');
        $featured    = isset($_POST['featured'])  ? 1 : 0;

        if (empty($name) || $price < 0) {
            setFlash('error', 'Product name and a valid price are required.');
            redirect('/ecommerce/admin/products.php');
        }

        $slug = generateSlug($name);
        // Ensure unique slug
        $existing = $pdo->prepare("SELECT id FROM products WHERE slug = :s LIMIT 1");
        $existing->execute([':s' => $slug]);
        if ($existing->fetch()) $slug .= '-' . time();

        $stmt = $pdo->prepare("INSERT INTO products (category_id, name, slug, description, price, stock, image, featured)
                                VALUES (:cat, :name, :slug, :desc, :price, :stock, :image, :featured)");
        $stmt->execute([
            ':cat'      => $category_id,
            ':name'     => $name,
            ':slug'     => $slug,
            ':desc'     => $description,
            ':price'    => round($price, 2),
            ':stock'    => $stock,
            ':image'    => $image,
            ':featured' => $featured,
        ]);
        setFlash('success', '"' . $name . '" added successfully!');
        redirect('/ecommerce/admin/products.php');

    // ── EDIT ─────────────────────────────────────────────────────
    case 'edit':
        $product_id  = (int)($_POST['product_id'] ?? 0);
        $name        = trim($_POST['name']        ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = (float)($_POST['price']    ?? 0);
        $stock       = (int)($_POST['stock']      ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0) ?: null;
        $image       = trim($_POST['image']       ?? '');
        $featured    = isset($_POST['featured'])  ? 1 : 0;

        if ($product_id <= 0 || empty($name) || $price < 0) {
            setFlash('error', 'Invalid product data.');
            redirect('/ecommerce/admin/products.php');
        }

        $stmt = $pdo->prepare("UPDATE products SET
                                category_id = :cat,
                                name        = :name,
                                description = :desc,
                                price       = :price,
                                stock       = :stock,
                                image       = :image,
                                featured    = :featured
                               WHERE id = :id");
        $stmt->execute([
            ':cat'      => $category_id,
            ':name'     => $name,
            ':desc'     => $description,
            ':price'    => round($price, 2),
            ':stock'    => $stock,
            ':image'    => $image,
            ':featured' => $featured,
            ':id'       => $product_id,
        ]);
        setFlash('success', '"' . $name . '" updated successfully!');
        redirect('/ecommerce/admin/products.php');

    // ── DELETE ───────────────────────────────────────────────────
    case 'delete':
        $product_id = (int)($_POST['product_id'] ?? 0);
        if ($product_id <= 0) {
            setFlash('error', 'Invalid product ID.');
            redirect('/ecommerce/admin/products.php');
        }
        // Fetch name for flash message
        $nameRow = $pdo->prepare("SELECT name FROM products WHERE id = :id LIMIT 1");
        $nameRow->execute([':id' => $product_id]);
        $productName = $nameRow->fetchColumn() ?: 'Product';

        $pdo->prepare("DELETE FROM products WHERE id = :id")->execute([':id' => $product_id]);
        setFlash('success', '"' . $productName . '" deleted.');
        redirect('/ecommerce/admin/products.php');

    default:
        setFlash('error', 'Unknown action.');
        redirect('/ecommerce/admin/products.php');
}
