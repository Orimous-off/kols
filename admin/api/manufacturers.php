<?php
require_once '../../includes/db.php';
global $pdo;

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('POST request received for categories');
    error_log('POST data: ' . print_r($_POST, true));

    // Очищаем буфер вывода
    ob_clean();

    try {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            // Validate input
            $name = trim($_POST['name'] ?? '');
            $country = trim($_POST['country'] ?? '');
            $contact_info = trim($_POST['contact_info'] ?? '');

            if (empty($name)) {
                throw new Exception("Название производителя обязательно");
            }

            $stmt = $pdo->prepare("
                INSERT INTO manufacturers (name, country, contact_info)
                VALUES (:name, :country, :contact_info)
            ");
            $result = $stmt->execute([
                'name' => $name,
                'country' => $country,
                'contact_info' => $contact_info
            ]);

            if (!$result) {
                throw new Exception("Не удалось добавить категорию");
            }

            $_SESSION['success'] = "Категория успешно добавлена";
        } elseif ($action === 'edit') {
            // Validate input
            $manufacturer_id = (int)($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $country = trim($_POST['country'] ?? '');
            $contact_info = trim($_POST['contact_info'] ?? '');

            if ($manufacturer_id <= 0) {
                throw new Exception("Неверный ID категории");
            }
            if (empty($name)) {
                throw new Exception("Название категории обязательно");
            }

            // Check if category exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE category_id = ?");
            $stmt->execute([$manufacturer_id]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception("Категория не найдена");
            }

            // Update category
            $stmt = $pdo->prepare("
                UPDATE manufacturers 
                SET name = :name,
                    country = :country,
                    contact_info = :contact_info
                WHERE category_id = :category_id
            ");
            $result = $stmt->execute([
                'name' => $name,
                'country' => $country,
                'contact_info' => $contact_info
            ]);

            if (!$result) {
                throw new Exception("Не удалось обновить категорию");
            }

            $_SESSION['success'] = "Категория успешно обновлена";
        } else {
            throw new Exception("Неверное действие");
        }

        header('Location: /admin-manufacturers');
    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header('Location: /admin-manufacturers');
    }
    exit;
}