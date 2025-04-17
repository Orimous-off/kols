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
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                throw new Exception("Название категории обязательно");
            }


            // Insert new category
            $stmt = $pdo->prepare("
                INSERT INTO categories (name, description)
                VALUES (:name, :description)
            ");
            $result = $stmt->execute([
                'name' => $name,
                'description' => $description ?: null
            ]);

            if (!$result) {
                throw new Exception("Не удалось добавить категорию");
            }

            $_SESSION['success'] = "Категория успешно добавлена";
        } elseif ($action === 'edit') {
            // Validate input
            $category_id = (int)($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($category_id <= 0) {
                throw new Exception("Неверный ID категории");
            }
            if (empty($name)) {
                throw new Exception("Название категории обязательно");
            }

            // Check if category exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE category_id = ?");
            $stmt->execute([$category_id]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception("Категория не найдена");
            }

            // Update category
            $stmt = $pdo->prepare("
                UPDATE categories 
                SET name = :name,
                    description = :description
                WHERE category_id = :category_id
            ");
            $result = $stmt->execute([
                'name' => $name,
                'description' => $description ?: null,
                'category_id' => $category_id
            ]);

            if (!$result) {
                throw new Exception("Не удалось обновить категорию");
            }

            $_SESSION['success'] = "Категория успешно обновлена";
        } else {
            throw new Exception("Неверное действие");
        }

        header('Location: /admin-categories');
    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header('Location: /admin-categories');
    }
    exit;
}