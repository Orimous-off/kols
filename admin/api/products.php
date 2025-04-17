<?php
// Подключение к базе данных
global $pdo;
if (!isset($pdo) || !($pdo instanceof PDO)) {
    error_log('PDO connection not available');
    die('Ошибка соединения с базой данных');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

    switch ($action) {
        case 'getProduct':
            // Получить информацию о конкретном товаре
            $productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

            if ($productId <= 0) {
                throw new Exception('Некорректный ID товара');
            }

            $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
            $stmt->execute(['product_id' => $productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception('Товар не найден');
            }

            echo json_encode([
                'success' => true,
                'product' => $product
            ]);
            break;

        case 'addProduct':
            // Добавить новый товар
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Неверный метод запроса');
            }

            // Проверка обязательных полей
            if (empty($_POST['name']) || !isset($_POST['price']) || !isset($_POST['stock_quantity'])) {
                throw new Exception('Не заполнены обязательные поля');
            }

            // Начинаем транзакцию
            $pdo->beginTransaction();

            // Подготовка данных
            $data = [
                'name' => $_POST['name'],
                'price' => floatval($_POST['price']),
                'category_id' => !empty($_POST['category_id']) ? intval($_POST['category_id']) : null,
                'manufacturer_id' => !empty($_POST['manufacturer_id']) ? intval($_POST['manufacturer_id']) : null,
                'stock_quantity' => intval($_POST['stock_quantity']),
                'discount_percentage' => !empty($_POST['discount_percentage']) ? floatval($_POST['discount_percentage']) : 0,
                'material' => $_POST['material'] ?? null,
                'dimensions' => $_POST['dimensions'] ?? null,
                'weight' => !empty($_POST['weight']) ? floatval($_POST['weight']) : null,
                'description' => $_POST['description'] ?? null,
                'is_new' => isset($_POST['is_new']) ? intval($_POST['is_new']) : 0,
                'is_featured' => isset($_POST['is_featured']) ? intval($_POST['is_featured']) : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Создание SQL запроса
            $fields = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));

            $stmt = $pdo->prepare("INSERT INTO products ($fields) VALUES ($placeholders)");
            $result = $stmt->execute($data);

            if (!$result) {
                throw new Exception('Ошибка при добавлении товара');
            }

            $productId = $pdo->lastInsertId();

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Товар успешно добавлен',
                'product_id' => $productId
            ]);
            break;

        case 'updateProduct':
            // Обновить существующий товар
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Неверный метод запроса');
            }

            // Проверка обязательных полей
            if (empty($_POST['product_id']) || empty($_POST['name']) || !isset($_POST['price']) || !isset($_POST['stock_quantity'])) {
                throw new Exception('Не заполнены обязательные поля');
            }

            $productId = intval($_POST['product_id']);

            // Начинаем транзакцию
            $pdo->beginTransaction();

            // Подготовка данных
            $data = [
                'name' => $_POST['name'],
                'price' => floatval($_POST['price']),
                'category_id' => !empty($_POST['category_id']) ? intval($_POST['category_id']) : null,
                'manufacturer_id' => !empty($_POST['manufacturer_id']) ? intval($_POST['manufacturer_id']) : null,
                'stock_quantity' => intval($_POST['stock_quantity']),
                'discount_percentage' => !empty($_POST['discount_percentage']) ? floatval($_POST['discount_percentage']) : 0,
                'material' => $_POST['material'] ?? null,
                'dimensions' => $_POST['dimensions'] ?? null,
                'weight' => !empty($_POST['weight']) ? floatval($_POST['weight']) : null,
                'description' => $_POST['description'] ?? null,
                'is_new' => isset($_POST['is_new']) ? intval($_POST['is_new']) : 0,
                'is_featured' => isset($_POST['is_featured']) ? intval($_POST['is_featured']) : 0,
                'updated_at' => date('Y-m-d H:i:s'),
                'product_id' => $productId
            ];

            // Создание SQL запроса для обновления
            $setClause = '';
            foreach (array_keys($data) as $key) {
                if ($key !== 'product_id') {
                    $setClause .= ($setClause ? ', ' : '') . "$key = :$key";
                }
            }

            $stmt = $pdo->prepare("UPDATE products SET $setClause WHERE product_id = :product_id");
            $result = $stmt->execute($data);

            if (!$result) {
                throw new Exception('Ошибка при обновлении товара');
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Товар успешно обновлен'
            ]);
            break;

        case 'deleteProduct':
            // Удалить товар
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Неверный метод запроса');
            }

            if (empty($_POST['product_id'])) {
                throw new Exception('Не указан ID товара');
            }

            $productId = intval($_POST['product_id']);

            // Начинаем транзакцию
            $pdo->beginTransaction();

            // Сначала удаляем связанные данные (изображения и цвета)
            $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = :product_id");
            $stmt->execute(['product_id' => $productId]);

            $stmt = $pdo->prepare("DELETE FROM product_colors WHERE product_id = :product_id");
            $stmt->execute(['product_id' => $productId]);

            // Теперь удаляем сам товар
            $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = :product_id");
            $result = $stmt->execute(['product_id' => $productId]);

            if (!$result) {
                throw new Exception('Ошибка при удалении товара');
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Товар успешно удален'
            ]);
            break;

        case 'toggleVisibility':
            // Изменить видимость товара (скрыть/показать)
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Неверный метод запроса');
            }

            if (empty($_POST['product_id']) || !isset($_POST['quantity'])) {
                throw new Exception('Не указаны необходимые параметры');
            }

            $productId = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity']);

            // Начинаем транзакцию
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = :quantity, updated_at = NOW() WHERE product_id = :product_id");
            $result = $stmt->execute([
                'quantity' => $quantity,
                'product_id' => $productId
            ]);

            if (!$result) {
                throw new Exception('Ошибка при изменении видимости товара');
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => $quantity > 0 ? 'Товар успешно отображен' : 'Товар успешно скрыт'
            ]);
            break;
    }
}