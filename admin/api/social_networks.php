<?php
require_once '../../includes/db.php';
global $pdo;

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('POST request received for social-networks');
    error_log('POST data: ' . print_r($_POST, true));

    // Очищаем буфер вывода
    ob_clean();

    try {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            // Validate input
            $title = trim($_POST['title'] ?? '');
            $link = trim($_POST['link'] ?? '');

            if (empty($title)) {
                throw new Exception("Название соцсети обязательно");
            }
            if (empty($link) || !filter_var($link, FILTER_VALIDATE_URL)) {
                throw new Exception("Введите действительную URL-ссылку");
            }

            // Check if title already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM social_networks WHERE title = ?");
            $stmt->execute([$title]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Соцсеть с таким названием уже существует");
            }

            // Insert new social network
            $stmt = $pdo->prepare("
                INSERT INTO social_networks (title, link)
                VALUES (:title, :link)
            ");
            $result = $stmt->execute([
                'title' => $title,
                'link' => $link
            ]);

            if (!$result) {
                throw new Exception("Не удалось добавить соцсеть");
            }

            $_SESSION['success'] = "Соцсеть успешно добавлена";
        } elseif ($action === 'edit') {
            // Validate input
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $link = trim($_POST['link'] ?? '');

            if ($id <= 0) {
                throw new Exception("Неверный ID соцсети");
            }
            if (empty($title)) {
                throw new Exception("Название соцсети обязательно");
            }
            if (empty($link) || !filter_var($link, FILTER_VALIDATE_URL)) {
                throw new Exception("Введите действительную URL-ссылку");
            }

            // Check if social network exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM social_networks WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception("Соцсеть не найдена");
            }

            // Check if title is unique (excluding current record)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM social_networks WHERE title = ? AND id != ?");
            $stmt->execute([$title, $id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Соцсеть с таким названием уже существует");
            }

            // Update social network
            $stmt = $pdo->prepare("
                UPDATE social_networks 
                SET title = :title,
                    link = :link
                WHERE id = :id
            ");
            $result = $stmt->execute([
                'title' => $title,
                'link' => $link,
                'id' => $id
            ]);

            if (!$result) {
                throw new Exception("Не удалось обновить соцсеть");
            }

            $_SESSION['success'] = "Соцсеть успешно обновлена";
        } else {
            throw new Exception("Неверное действие");
        }

        header('Location: /admin-social-networks');
    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header('Location: /admin-social-networks');
    }
    exit;
}