<?php
require_once '../../includes/db.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->query("SELECT * FROM main_hero LIMIT 1");
    $hero = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hero) {
        $stmt = $pdo->prepare("INSERT INTO main_hero (id, title, subtitle, image_url) VALUES (1, '', '', '')");
        $stmt->execute();
        $hero = ['id' => 1, 'title' => '', 'subtitle' => '', 'image_url' => ''];
    }

    $image_url = $hero['image_url'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../assets/images/';

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $fileName = uniqid() . '.' . $fileExtension;
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $image_url = '/images/' . $fileName;
                if ($hero['image_url'] && file_exists(__DIR__ . '/../../assets' . $hero['image_url'])) {
                    unlink(__DIR__ . '/../../assets' . $hero['image_url']);
                }
            }
        }
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE main_hero 
            SET title = :title, 
                subtitle = :subtitle, 
                image_url = :image_url 
            WHERE id = :id
        ");

        $updateResult = $stmt->execute([
            'title' => $_POST['title'] ?? '',
            'subtitle' => $_POST['subtitle'] ?? '',
            'image_url' => $image_url,
            'id' => $hero['id']
        ]);

        if (!$updateResult) {
            throw new Exception("Database update failed");
        }


        header('Location: /admin-main-hero');
    } catch (Exception $e) {
        header('Location: /admin-main-hero');
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка: ' . $e->getMessage()
        ]);
    }
    exit;
}