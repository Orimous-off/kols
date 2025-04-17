<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));

require_once ROOT_PATH . '/includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('Received POST request');
    error_log('POST data: ' . print_r($_POST, true));
    error_log('Raw POST data: ' . file_get_contents('php://input'));

    error_log('Inside update_order block');
    error_log('POST data inside block: ' . print_r($_POST, true));

    if (!isset($_POST['order_id'])) {
        echo json_encode(['success' => false, 'message' => 'Идентификатор заказа не указан']);
        exit;
    }

    $orderId = intval($_POST['order_id']);
    $status = isset($_POST['status']) ? trim($_POST['status']) : null;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : null;

    error_log("Order ID: $orderId, Status: $status, Comment: $comment");

    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'completed'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Некорректный статус заказа']);
        exit;
    }

    try {
        $checkQuery = $pdo->prepare('SELECT order_id FROM orders WHERE order_id = ?');
        $checkQuery->execute([$orderId]);

        if (!$checkQuery->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Заказ с указанным ID не найден']);
            exit;
        }

        $pdo->beginTransaction();

        $updateQuery = $pdo->prepare('
            UPDATE orders 
            SET status = ?, comment = ?, updated_at = NOW()
            WHERE order_id = ?
        ');

        $updateQuery->execute([$status, $comment, $orderId]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Заказ успешно обновлен']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
    exit;

}

echo json_encode(['success' => false, 'message' => 'Недопустимый запрос']);