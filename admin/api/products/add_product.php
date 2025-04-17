<?php
require_once '../../../includes/db.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('POST request received');
    error_log('POST data: ' . print_r($_POST, true));
    error_log('FILES data: ' . print_r($_FILES, true));

    ob_clean();

    $pdo->beginTransaction();
    try {
        // Добавление продукта в таблицу products
        $stmt = $pdo->prepare("
            INSERT INTO products (
                name, description, category_id, manufacturer_id, price, discount_percentage, 
                stock_quantity, material, dimensions, weight, is_new, is_featured
            ) VALUES (
                :name, :description, :category_id, :manufacturer_id, :price, :discount_percentage, 
                :stock_quantity, :material, :dimensions, :weight, :is_new, :is_featured
            )
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
        ]);

        $product_id = $pdo->lastInsertId();
        error_log("Product added with ID: $product_id");

        // Добавление цветов в таблицу product_colors
        if (isset($_POST['colors']) && isset($_POST['color_quantities'])) {
            $colors = $_POST['colors'];
            $quantities = $_POST['color_quantities'];

            $total_color_quantity = array_sum(array_map('intval', $quantities));
            if ($total_color_quantity != $_POST['stock_quantity']) {
                throw new Exception("Общее количество цветов ($total_color_quantity) не совпадает с общим количеством на складе ({$_POST['stock_quantity']})");
            }

            for ($i = 0; $i < count($colors); $i++) {
                if (!empty($colors[$i]) && isset($quantities[$i]) && $quantities[$i] >= 0) {
                    $stmt = $pdo->prepare("
                        INSERT INTO product_colors (product_id, color_name, stock_quantity)
                        VALUES (:product_id, :color_name, :stock_quantity)
                    ");
                    $stmt->execute([
                        'product_id' => $product_id,
                        'color_name' => trim($colors[$i]),
                        'stock_quantity' => (int)$quantities[$i],
                    ]);
                    error_log("Added color: {$colors[$i]} with quantity: {$quantities[$i]} for product ID: $product_id");
                }
            }
        }

        // Обработка загрузки изображений
        $uploadDir = __DIR__ . '/../../../assets/images/catalog/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $isFirstImage = true;

        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileExtension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (!in_array($fileExtension, $allowedExtensions)) {
                        throw new Exception("Недопустимый формат файла: $name. Допустимые форматы: " . implode(', ', $allowedExtensions));
                    }

                    $fileName = uniqid() . '.' . $fileExtension;
                    $uploadFile = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $uploadFile)) {
                        $image_url = '/images/catalog/' . $fileName;

                        // Добавляем путь к изображению в таблицу product_images
                        $stmt = $pdo->prepare("
                            INSERT INTO product_images (product_id, image_path, is_main_image)
                            VALUES (:product_id, :image_path, :is_main_image)
                        ");
                        $stmt->execute([
                            'product_id' => $product_id,
                            'image_path' => $image_url,
                            'is_main_image' => $isFirstImage ? 1 : 0,
                        ]);

                        $isFirstImage = false; // Первое изображение будет основным
                        error_log("Uploaded image: $image_url for product ID: $product_id");
                    } else {
                        throw new Exception("Не удалось загрузить файл: $name");
                    }
                }
            }
        }

        $pdo->commit();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Товар успешно добавлен',
            'reload' => true
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error: " . $e->getMessage());

        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка: ' . $e->getMessage()
        ]);
    }
    exit;
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Недопустимый метод запроса'
    ]);
    exit;
}