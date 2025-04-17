<?php
require_once '../../../includes/db.php';
global $pdo;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
    exit;
}

if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
    exit;
}

$productId = (int)$_POST['product_id'];

$pdo->beginTransaction();
try {
    // Обновление основной информации о товаре
    $stmt = $pdo->prepare("
        UPDATE products
        SET 
            name = :name,
            description = :description,
            category_id = :category_id,
            manufacturer_id = :manufacturer_id,
            price = :price,
            discount_percentage = :discount_percentage,
            stock_quantity = :stock_quantity,
            material = :material,
            dimensions = :dimensions,
            weight = :weight,
            is_new = :is_new,
            is_featured = :is_featured,
            updated_at = NOW()
        WHERE product_id = :product_id
    ");
    $stmt->execute([
        'name' => $_POST['name'],
        'description' => $_POST['description'] ?? '',
        'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
        'manufacturer_id' => !empty($_POST['manufacturer_id']) ? (int)$_POST['manufacturer_id'] : null,
        'price' => (float)$_POST['price'],
        'discount_percentage' => isset($_POST['discount_percentage']) ? (float)$_POST['discount_percentage'] : 0.00,
        'stock_quantity' => (int)$_POST['stock_quantity'],
        'material' => $_POST['material'] ?? null,
        'dimensions' => $_POST['dimensions'] ?? null,
        'weight' => isset($_POST['weight']) ? (float)$_POST['weight'] : null,
        'is_new' => isset($_POST['is_new']) ? 1 : 0,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'product_id' => $productId,
    ]);

    // Обновление существующих цветов
    $existingColors = isset($_POST['existing_colors']) ? $_POST['existing_colors'] : [];
    $existingColorQuantities = isset($_POST['existing_color_quantities']) ? $_POST['existing_color_quantities'] : [];

    // Получаем текущие цвета товара
    $stmt = $pdo->prepare("SELECT color_id FROM product_colors WHERE product_id = ?");
    $stmt->execute([$productId]);
    $currentColors = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Обновляем или удаляем существующие цвета
    foreach ($currentColors as $colorId) {
        if (isset($existingColors[$colorId])) {
            // Обновляем цвет
            $stmt = $pdo->prepare("
                UPDATE product_colors
                SET color_name = ?, stock_quantity = ?
                WHERE color_id = ?
            ");
            $stmt->execute([
                trim($existingColors[$colorId]),
                (int)$existingColorQuantities[$colorId],
                $colorId,
            ]);
        } else {
            // Удаляем цвет
            $stmt = $pdo->prepare("DELETE FROM product_colors WHERE color_id = ?");
            $stmt->execute([$colorId]);
        }
    }

    // Добавляем новые цвета
    if (isset($_POST['new_colors']) && isset($_POST['new_color_quantities'])) {
        $newColors = $_POST['new_colors'];
        $newQuantities = $_POST['new_color_quantities'];

        for ($i = 0; $i < count($newColors); $i++) {
            if (!empty($newColors[$i]) && isset($newQuantities[$i]) && $newQuantities[$i] >= 0) {
                $stmt = $pdo->prepare("
                    INSERT INTO product_colors (product_id, color_name, stock_quantity)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$productId, trim($newColors[$i]), (int)$newQuantities[$i]]);
            }
        }
    }

    // Обработка изображений
    $uploadDir = __DIR__ . '/../../../assets/images/catalog/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    // Получаем текущие изображения
    $stmt = $pdo->prepare("SELECT image_id, image_path FROM product_images WHERE product_id = ?");
    $stmt->execute([$productId]);
    $currentImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $currentImageIds = array_column($currentImages, 'image_id');

    // Обновляем существующие изображения (например, основное изображение)
    $isMainImage = isset($_POST['is_main_image']) ? $_POST['is_main_image'] : [];
    $hasMainImage = false;

    foreach ($currentImages as $image) {
        $imageId = $image['image_id'];
        if (array_key_exists($imageId, $isMainImage)) {
            // Изображение не удалено, обновляем is_main_image
            $isMain = !$hasMainImage && isset($isMainImage[$imageId]) ? 1 : 0;
            if ($isMain) $hasMainImage = true;

            $stmt = $pdo->prepare("UPDATE product_images SET is_main_image = ? WHERE image_id = ?");
            $stmt->execute([$isMain, $imageId]);
        } else {
            // Изображение удалено, удаляем его из базы и с сервера
            $stmt = $pdo->prepare("DELETE FROM product_images WHERE image_id = ?");
            $stmt->execute([$imageId]);

            $filePath = __DIR__ . '/../../../assets' . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    // Добавляем новые изображения
    if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
        foreach ($_FILES['new_images']['name'] as $key => $name) {
            if ($_FILES['new_images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileExtension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new Exception("Недопустимый формат файла: $name. Допустимые форматы: " . implode(', ', $allowedExtensions));
                }

                $fileName = uniqid() . '.' . $fileExtension;
                $uploadFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['new_images']['tmp_name'][$key], $uploadFile)) {
                    $image_url = '/images/catalog/' . $fileName;

                    $isMain = !$hasMainImage ? 1 : 0;
                    if ($isMain) $hasMainImage = true;

                    $stmt = $pdo->prepare("
                        INSERT INTO product_images (product_id, image_path, is_main_image)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$productId, $image_url, $isMain]);
                } else {
                    throw new Exception("Не удалось загрузить файл: $name");
                }
            }
        }
    }

    // Убедимся, что есть основное изображение
    if (!$hasMainImage) {
        $stmt = $pdo->prepare("
            UPDATE product_images
            SET is_main_image = 1
            WHERE product_id = ?
            LIMIT 1
        ");
        $stmt->execute([$productId]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Товар успешно обновлен', 'reload' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}