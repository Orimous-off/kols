<?php
require_once '../../../includes/db.php';
global $pdo;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
    exit;
}

if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Неверные параметры']);
    exit;
}

$productId = (int)$_POST['product_id'];
$action = $_POST['action'];

if (!in_array($action, ['hide', 'show'])) {
    echo json_encode(['success' => false, 'message' => 'Неверное действие']);
    exit;
}

$newVisibly = ($action === 'hide') ? 0 : 1;

try {
    // Обновляем значение visibly
    $stmt = $pdo->prepare("UPDATE products SET visibly = ? WHERE product_id = ?");
    $stmt->execute([$newVisibly, $productId]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Товар не найден']);
        exit;
    }

    // Логируем изменение (опционально)
    error_log("Updated visibility for product ID $productId to $newVisibly");

    echo json_encode([
        'success' => true,
        'message' => 'Видимость товара обновлена',
        'new_action' => $newVisibly ? 'hide' : 'show'
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}