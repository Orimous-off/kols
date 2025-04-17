<?php
require_once '../../includes/db.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('POST request received');
    error_log('POST data: ' . print_r($_POST, true));
    error_log('FILES data: ' . print_r($_FILES, true));
    // Очищаем буфер вывода перед отправкой JSON
    ob_clean();

    try {
        // Обновление данных
        $stmt = $pdo->prepare("
                UPDATE about_content 
                SET heading = :heading,
                    title = :title, 
                    paragraph_first = :paragraph_first, 
                    paragraph_second = :paragraph_second
                WHERE id = :id
            ");

        $updateResult = $stmt->execute([
            'heading' => $_POST['heading'],
            'title' => $_POST['title'],
            'paragraph_first' => $_POST['paragraph_first'],
            'paragraph_second' => $_POST['paragraph_second'],
            'id' => 1
        ]);

        if (!$updateResult) {
            throw new Exception("Database update failed");
        }

        header('Location: /admin-about-content');
    } catch (Exception $e) {
        header('Location: /admin-about-content');
        throw $e;
    }
    exit;
}