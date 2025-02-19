<?php
global $pdo;
session_start();
require_once '../db.php';

class Cart {
    private $pdo;
    private $errors = [];

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getLastError() {
        return end($this->errors);
    }

    public function getOrCreateCart($userId) {
        try {
            $stmt = $this->pdo->prepare("SELECT cart_id FROM carts WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$userId]);
            $cart = $stmt->fetch();

            if ($cart) {
                return $cart['cart_id'];
            }

            $stmt = $this->pdo->prepare("INSERT INTO carts (user_id, status) VALUES (?, 'active')");
            $stmt->execute([$userId]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->errors[] = "Ошибка при создании корзины: " . $e->getMessage();
            return false;
        }
    }

    public function addToCart($userId, $productId, $color, $quantity = 1) {
        try {
            // Проверяем существование продукта и его цвета
            $stmt = $this->pdo->prepare("
                SELECT p.product_id 
                FROM products p
                JOIN product_colors pc ON p.product_id = pc.product_id
                WHERE p.product_id = ? AND pc.color_name = ?
            ");
            $stmt->execute([$productId, $color]);

            if (!$stmt->fetch()) {
                $this->errors[] = "Товар с указанным цветом не найден";
                return false;
            }

            $this->pdo->beginTransaction();

            $cartId = $this->getOrCreateCart($userId);
            if (!$cartId) {
                throw new Exception("Не удалось получить ID корзины");
            }

            // Проверяем существующий товар в корзине
            $stmt = $this->pdo->prepare("
                SELECT cart_item_id, quantity 
                FROM cart_items 
                WHERE cart_id = ? AND product_id = ? AND color_name = ?
            ");
            $stmt->execute([$cartId, $productId, $color]);
            $existingItem = $stmt->fetch();

            if ($existingItem) {
                // Обновляем количество
                $newQuantity = min($existingItem['quantity'] + $quantity, 99); // Ограничиваем максимум 99
                $stmt = $this->pdo->prepare("
                    UPDATE cart_items 
                    SET quantity = ?, 
                        updated_at = CURRENT_TIMESTAMP
                    WHERE cart_item_id = ?
                ");
                $stmt->execute([$newQuantity, $existingItem['cart_item_id']]);
            } else {
                // Добавляем новый товар
                $stmt = $this->pdo->prepare("
                    INSERT INTO cart_items (cart_id, product_id, color_name, quantity) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$cartId, $productId, $color, min($quantity, 99)]);
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->errors[] = "Ошибка при добавлении товара: " . $e->getMessage();
            return false;
        }
    }
    public function updateQuantity($cartItemId, $quantity) {
        try {
            if ($quantity < 1 || $quantity > 99) {
                throw new Exception("Некорректное количество товара");
            }

            $stmt = $this->pdo->prepare("
                UPDATE cart_items 
                SET quantity = ?, updated_at = CURRENT_TIMESTAMP
                WHERE cart_item_id = ?
            ");
            return $stmt->execute([$quantity, $cartItemId]);
        } catch (Exception $e) {
            $this->errors[] = "Ошибка при обновлении количества: " . $e->getMessage();
            return false;
        }
    }

    public function removeItem($cartItemId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
            return $stmt->execute([$cartItemId]);
        } catch (Exception $e) {
            $this->errors[] = "Ошибка при удалении товара: " . $e->getMessage();
            return false;
        }
    }

    public function getCartTotals($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    SUM(p.price * ci.quantity) as subtotal,
                    SUM(p.price * ci.quantity * (p.discount_percentage / 100)) as discount
                FROM carts c
                JOIN cart_items ci ON c.cart_id = ci.cart_id
                JOIN products p ON ci.product_id = p.product_id
                WHERE c.user_id = ? AND c.status = 'active'
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $subtotal = (float)$result['subtotal'];
            $discount = (float)$result['discount'];

            return [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $subtotal - $discount
            ];
        } catch (Exception $e) {
            $this->errors[] = "Ошибка при расчете итогов: " . $e->getMessage();
            return false;
        }
    }

    public function getCartItemsCount($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(ci.quantity), 0) as total
                FROM carts c
                LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id
                WHERE c.user_id = ? AND c.status = 'active'
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return (int)$result['total'];
        } catch (PDOException $e) {
            $this->errors[] = "Ошибка при подсчете товаров: " . $e->getMessage();
            return 0;
        }
    }
}

// Обработка AJAX запросов
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Пользователь не авторизован'
    ]);
    exit;
}

$cart = new Cart($pdo);
$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add_to_cart':
            $productId = $_POST['product_id'] ?? null;
            $color = $_POST['color'] ?? null;
            $quantity = (int)($_POST['quantity'] ?? 1);

            if (!$productId || !$color) {
                throw new Exception("Не указан товар или цвет");
            }

            if ($cart->addToCart($userId, $productId, $color, $quantity)) {
                $response = [
                    'success' => true,
                    'message' => 'Товар добавлен в корзину',
                    'totals' => $cart->getCartTotals($userId),
                    'cart_count' => $cart->getCartItemsCount($userId)
                ];
            } else {
                throw new Exception($cart->getLastError());
            }
            break;

        case 'update_quantity':
            $cartItemId = $_POST['cart_item_id'] ?? null;
            $quantity = (int)($_POST['quantity'] ?? 0);

            if ($cart->updateQuantity($cartItemId, $quantity)) {
                $response = [
                    'success' => true,
                    'message' => 'Количество обновлено',
                    'totals' => $cart->getCartTotals($userId),
                    'cart_count' => $cart->getCartItemsCount($userId)
                ];
            } else {
                throw new Exception($cart->getLastError());
            }
            break;

        case 'remove_item':
            $cartItemId = $_POST['cart_item_id'] ?? null;

            if ($cart->removeItem($cartItemId)) {
                $response = [
                    'success' => true,
                    'message' => 'Товар удален',
                    'totals' => $cart->getCartTotals($userId),
                    'cart_count' => $cart->getCartItemsCount($userId)
                ];
            } else {
                throw new Exception($cart->getLastError());
            }
            break;

        default:
            throw new Exception('Неизвестное действие');
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}