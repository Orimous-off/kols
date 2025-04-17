<?php
require_once '../../../includes/db.php';
global $pdo;

header('Content-Type: application/json');

if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
    exit;
}

$productId = (int)$_GET['product_id'];

try {
    // Получаем данные о товаре
    $stmt = $pdo->prepare("
        SELECT *
        FROM products
        WHERE product_id = ?
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Товар не найден']);
        exit;
    }

    // Получаем цвета товара
    $stmt = $pdo->prepare("
        SELECT color_id, color_name, stock_quantity
        FROM product_colors
        WHERE product_id = ?
    ");
    $stmt->execute([$productId]);
    $product['colors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получаем изображения товара
    $stmt = $pdo->prepare("
        SELECT image_id, image_path, is_main_image
        FROM product_images
        WHERE product_id = ?
    ");
    $stmt->execute([$productId]);
    $product['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'product' => $product]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}