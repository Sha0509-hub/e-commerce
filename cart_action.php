<?php
// cart_action.php — handles add / update / remove / clear cart
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/ecommerce/index.php');
}

$action     = $_POST['action']     ?? '';
$product_id = (int)($_POST['product_id'] ?? 0);
$qty        = max(1, (int)($_POST['qty'] ?? 1));
$redirectTo = $_POST['redirect']   ?? '/ecommerce/cart.php';

// Sanitize redirect (only allow local paths)
if (!str_starts_with($redirectTo, '/ecommerce/') && !str_starts_with($redirectTo, 'http')) {
    $redirectTo = '/ecommerce/cart.php';
}

switch ($action) {
    case 'add':
        if ($product_id <= 0) break;
        // Fetch product from DB
        $stmt = $pdo->prepare("SELECT id, name, price, stock, image FROM products WHERE id = :id AND stock > 0 LIMIT 1");
        $stmt->execute([':id' => $product_id]);
        $product = $stmt->fetch();
        if ($product) {
            addToCart($product, $qty);
            setFlash('success', '"' . $product['name'] . '" added to cart!');
        } else {
            setFlash('error', 'Product is unavailable or out of stock.');
        }
        break;

    case 'update':
        if ($product_id <= 0) break;
        updateCartQty($product_id, $qty);
        setFlash('info', 'Cart updated.');
        break;

    case 'remove':
        if ($product_id <= 0) break;
        removeFromCart($product_id);
        setFlash('info', 'Item removed from cart.');
        break;

    case 'clear':
        clearCart();
        setFlash('info', 'Cart cleared.');
        $redirectTo = '/ecommerce/cart.php';
        break;

    default:
        setFlash('error', 'Invalid action.');
}

redirect($redirectTo);
