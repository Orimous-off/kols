<?php
require_once '../../includes/db.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->query("SELECT * FROM company_info LIMIT 1");
    $company_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company_info) {
        $stmt = $pdo->prepare("
            INSERT INTO campony_info (
                id, name, email, phone, address, logo, logoName
            ) VALUES (
                1, '', '', '', '', '', ''
            )");
        $stmt->execute();
        $company_info = [
            'id' => 1,
            'name' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'logo' => '',
            'logoName' => ''
        ];
    }

    $image_url = $company_info['logo'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../assets/images/';

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['svg', 'png'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $fileName = uniqid() . '.' . $fileExtension;
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $image_url = '/images/' . $fileName;
                if ($company_info['image_url'] && file_exists(__DIR__ . '/../../assets' . $company_info['image_url'])) {
                    unlink(__DIR__ . '/../../assets' . $company_info['image_url']);
                }
            }
        }
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE company_info 
            SET name = :name, 
                email = :email, 
                phone = :phone, 
                address = :address, 
                logo = :logo,  
                logoName = :logoName
            WHERE id = :id
        ");

        $updateResult = $stmt->execute([
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'logo' => $image_url,
            'logoName' => $_POST['logoName'] ?? '',
            'id' => $company_info['id']
        ]);

        if (!$updateResult) {
            throw new Exception("Database update failed");
        }


        header('Location: /admin-company');
    } catch (Exception $e) {
        header('Location: /admin-company');
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка: ' . $e->getMessage()
        ]);
    }
    exit;
}