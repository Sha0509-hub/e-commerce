<?php
// ── Price Formatting ───────────────────────────────────────────
function formatPrice(float $price): string {
    return '$' . number_format($price, 2);
}

// ── Cart Helpers ───────────────────────────────────────────────
function getCart(): array {
    return $_SESSION['cart'] ?? [];
}

function getCartCount(): int {
    $count = 0;
    foreach (getCart() as $item) {
        $count += (int)$item['qty'];
    }
    return $count;
}

function getCartTotal(): float {
    $total = 0.0;
    foreach (getCart() as $item) {
        $total += (float)$item['price'] * (int)$item['qty'];
    }
    return $total;
}

function addToCart(array $product, int $qty = 1): void {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $id = (int)$product['id'];
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$id] = [
            'id'    => $id,
            'name'  => $product['name'],
            'price' => (float)$product['price'],
            'image' => $product['image'] ?? '',
            'qty'   => $qty,
            'stock' => (int)$product['stock'],
        ];
    }
}

function removeFromCart(int $product_id): void {
    unset($_SESSION['cart'][$product_id]);
}

function updateCartQty(int $product_id, int $qty): void {
    if ($qty <= 0) {
        removeFromCart($product_id);
    } else {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['qty'] = $qty;
        }
    }
}

function clearCart(): void {
    $_SESSION['cart'] = [];
}

// ── Flash Messages ─────────────────────────────────────────────
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ── Misc Helpers ───────────────────────────────────────────────
function sanitize(string $str): string {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

function getProductImageUrl(?string $image): string {
    if (empty($image)) {
        return 'https://picsum.photos/seed/default/600/600';
    }
    if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
        return $image;
    }
    // Local upload
    return '/ecommerce/uploads/products/' . ltrim($image, '/');
}

function getStatusBadgeClass(string $status): string {
    return match ($status) {
        'Pending'    => 'badge-warning',
        'Processing' => 'badge-info',
        'Shipped'    => 'badge-primary',
        'Delivered'  => 'badge-success',
        'Cancelled'  => 'badge-danger',
        default      => 'badge-secondary',
    };
}

function getStatusColor(string $status): string {
    return match ($status) {
        'Pending'    => '#f59e0b',
        'Processing' => '#3b82f6',
        'Shipped'    => '#8b5cf6',
        'Delivered'  => '#10b981',
        'Cancelled'  => '#ef4444',
        default      => '#64748b',
    };
}

function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);
    if ($diff->days === 0) {
        if ($diff->h === 0) return $diff->i . ' minutes ago';
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    }
    if ($diff->days < 30) return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
    return $then->format('M j, Y');
}

function generateSlug(string $text): string {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return $text;
}
