<?php
session_start();
include '../db.php';
global $pdo;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
    exit;
}

$userId = $_SESSION['user_id'];
$phone = $_POST['phone'] ?? '';

// Validate phone number
if (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Неверный формат номера телефона']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Get active cart
    $stmt = $pdo->prepare("
        SELECT c.cart_id, c.user_id
        FROM carts c
        WHERE c.user_id = ? AND c.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cart) {
        throw new Exception('Корзина не найдена');
    }

    // Get cart items with product and color details
    $stmt = $pdo->prepare("
        SELECT 
            ci.cart_item_id,
            ci.product_id,
            ci.quantity,
            ci.color_name,
            p.price,
            p.discount_percentage,
            p.stock_quantity AS product_stock,
            pc.stock_quantity AS color_stock
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        LEFT JOIN product_colors pc ON ci.product_id = pc.product_id AND ci.color_name = pc.color_name
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cart['cart_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        throw new Exception('Корзина пуста');
    }

    // Calculate total amount and validate stock
    $totalAmount = 0;
    foreach ($items as $item) {
        // Check if enough product stock
        if ($item['product_stock'] < $item['quantity']) {
            throw new Exception("Недостаточно товара на складе для продукта ID {$item['product_id']}. Доступно: {$item['product_stock']}, требуется: {$item['quantity']}");
        }

        // Check if enough color stock (if color is specified)
        if (!empty($item['color_name']) && $item['color_stock'] !== null && $item['color_stock'] < $item['quantity']) {
            throw new Exception("Недостаточно товара цвета '{$item['color_name']}' для продукта ID {$item['product_id']}. Доступно: {$item['color_stock']}, требуется: {$item['quantity']}");
        }

        // Calculate price with discount
        $price = $item['price'];
        $discount = $price * ($item['discount_percentage'] / 100);
        $finalPrice = $price - $discount;
        $totalAmount += $finalPrice * $item['quantity'];
    }

    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, cart_id, phone, total_amount)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $cart['cart_id'], $phone, $totalAmount]);
    $orderId = $pdo->lastInsertId();

    // Copy cart items to order items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, color_name, price, discount_percentage)
        SELECT ?, ci.product_id, ci.quantity, ci.color_name, p.price, p.discount_percentage
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$orderId, $cart['cart_id']]);

    // Update stock quantities in products and product_colors
    foreach ($items as $item) {
        // Decrease product stock
        $stmt = $pdo->prepare("
            UPDATE products 
            SET stock_quantity = stock_quantity - ? 
            WHERE product_id = ?
        ");
        $stmt->execute([$item['quantity'], $item['product_id']]);

        // Decrease color stock (if color is specified)
        if (!empty($item['color_name'])) {
            $stmt = $pdo->prepare("
                UPDATE product_colors 
                SET stock_quantity = stock_quantity - ? 
                WHERE product_id = ? AND color_name = ?
            ");
            $stmt->execute([$item['quantity'], $item['product_id'], $item['color_name']]);
        }
    }

    // Update cart status
    $stmt = $pdo->prepare("UPDATE carts SET status = 'ordered' WHERE cart_id = ?");
    $stmt->execute([$cart['cart_id']]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Заказ успешно создан', 'orderId' => $orderId]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Ошибка при создании заказа: ' . $e->getMessage()]);
}