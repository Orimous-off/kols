<?php
session_start();
require_once '../db.php';
global $pdo;

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Неизвестная ошибка'];

try {
    // Проверка авторизации
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Пользователь не авторизован');
    }

    $userId = $_SESSION['user_id'];

    // Подготовка данных
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Валидация данных
    if (empty($username)) {
        throw new Exception('Имя пользователя не может быть пустым');
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Некорректный email');
    }

    // Проверка пароля, если указан
    if (!empty($newPassword)) {
        if ($newPassword !== $confirmPassword) {
            throw new Exception('Пароли не совпадают');
        }

        // Хэширование нового пароля
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    // Начало транзакции для целостности данных
    $pdo->beginTransaction();

    // Подготовка SQL-запроса
    if (!empty($newPassword)) {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone_number = ?, password_hash = ? WHERE id = ?");
        $stmt->execute([$username, $email, $phone, $passwordHash, $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone_number = ? WHERE id = ?");
        $stmt->execute([$username, $email, $phone, $userId]);
    }

    $pdo->commit();

    $response = [
        'success' => true,
        'message' => 'Профиль успешно обновлен'
    ];

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
} finally {
    echo json_encode($response);
    exit;
}