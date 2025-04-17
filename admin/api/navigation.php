<?php
require_once '../../includes/db.php';
global $pdo;

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('POST request received for navigation');
    error_log('POST data: ' . print_r($_POST, true));

    // Очищаем буфер вывода
    ob_clean();

    try {
        $action = $_POST['action'] ?? '';

        if ($action === 'edit') {
            // Validate input
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $link = trim($_POST['link'] ?? '');
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;

            if ($id <= 0) {
                throw new Exception("Неверный ID пункта навигации");
            }
            if (empty($title)) {
                throw new Exception("Название обязательно");
            }
            if (empty($link)) {
                throw new Exception("Ссылка обязательна");
            }

            // Check if navigation item exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM navigation WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception("Пункт навигации не найден");
            }

            // Update navigation item
            $stmt = $pdo->prepare("
                UPDATE navigation 
                SET title = :title,
                    link = :link,
                    is_visible = :is_visible
                WHERE id = :id
            ");
            $result = $stmt->execute([
                'title' => $title,
                'link' => $link,
                'is_visible' => $is_visible,
                'id' => $id
            ]);

            if (!$result) {
                throw new Exception("Не удалось обновить пункт навигации");
            }

            $_SESSION['success'] = "Пункт навигации успешно обновлен";
            header('Location: /admin-navigation');
        } elseif ($action === 'reorder') {
            // Handle AJAX reorder request
            header('Content-Type: application/json');
            $order = isset($_POST['order']) ? $_POST['order'] : [];

            if (empty($order) || !is_array($order)) {
                echo json_encode(['success' => false, 'error' => 'Неверный порядок']);
                exit;
            }

            // Validate all IDs exist
            $stmt = $pdo->prepare("SELECT id FROM navigation");
            $stmt->execute();
            $valid_ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');
            foreach ($order as $id) {
                if (!in_array($id, $valid_ids)) {
                    echo json_encode(['success' => false, 'error' => 'Неверный ID пункта']);
                    exit;
                }
            }

            // Update positions
            $pdo->beginTransaction();
            foreach ($order as $position => $id) {
                $stmt = $pdo->prepare("UPDATE navigation SET position = ? WHERE id = ?");
                $stmt->execute([$position + 1, $id]);
            }
            $pdo->commit();

            echo json_encode(['success' => true]);
            exit;
        } else {
            throw new Exception("Неверное действие");
        }
    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
        if ($action === 'reorder') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } else {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /admin-navigation');
        }
    }
    exit;
}