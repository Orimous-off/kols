<?php
include 'includes/db.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    header('Content-Type: application/json');

    try {
        // Получаем и обрабатываем данные
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $comment = trim($_POST['comment'] ?? '');

        // Проверка обязательных полей
        if (empty($name)) {
            throw new Exception('Пожалуйста, укажите ваше имя');
        }

        if (empty($phone)) {
            throw new Exception('Пожалуйста, укажите ваш телефон');
        }

        if (!preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $phone)) {
            throw new Exception('Неверный формат телефона');
        }

        // Сохранение данных в базу
        $stmt = $pdo->prepare("INSERT INTO orders (name, phone, comment) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $comment]);

        // Отправка email-уведомления
        $to = 'zalyzi73@gmail.com';
        $subject = 'Новый заказ на сайте';
        $message = "Получен новый заказ:\n\nИмя: $name\nТелефон: $phone\nКомментарий: $comment\n";
        if (!mail($to, $subject, $message)) {
            throw new Exception('Не удалось отправить email.');
        }

        // Успешный ответ
        echo json_encode([
            'status' => 'success',
            'message' => 'Ваш заказ успешно отправлен!',
        ]);
    } catch (Exception $e) {
        // Ответ с ошибкой
        echo json_encode([
            'status' => 'error',
            'message' => 'Произошла ошибка: ' . $e->getMessage(),
        ]);
    }

    exit; // Завершаем выполнение скрипта для AJAX-запроса
}
?>
