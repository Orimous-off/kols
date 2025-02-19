<?php
// includes/autoUpdate/getUserPhone.php
session_start();
include '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT phone FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['phone']) {
        echo json_encode(['success' => true, 'phone' => $result['phone']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Телефон не найден']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при получении телефона']);
}