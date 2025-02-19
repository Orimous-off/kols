<?php
header('Content-Type: application/json');
include 'includes/db.php';
global $pdo;

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['orderId'])) {
        throw new Exception('Missing required parameters');
    }

    // Формируем SQL запрос динамически в зависимости от переданных полей
    $updateFields = [];
    $params = [];

    if (isset($data['status'])) {
        $updateFields[] = 'status = ?';
        $params[] = $data['status'];
    }

    if (isset($data['comment'])) {
        $updateFields[] = 'admin_comment = ?';
        $params[] = $data['comment'];
    }

    if (isset($data['address'])) {
        $updateFields[] = 'address = ?';
        $params[] = $data['address'];
    }

    // Добавляем updated_at
    $updateFields[] = 'updated_at = NOW()';

    // Добавляем id в параметры
    $params[] = $data['orderId'];

    $sql = 'UPDATE orders SET ' . implode(', ', $updateFields) . ' WHERE id = ?';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}