<?php
session_start();
include 'includes/db.php';
global $pdo;

header('Content-Type: application/json');

try {
if (!isset($_GET['id'])) {
throw new Exception('Order ID is required');
}

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$_GET['id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
throw new Exception('Order not found');
}

echo json_encode([
'success' => true,
'id' => $order['id'],
'name' => $order['name'],
'phone' => $order['phone'],
'comment' => $order['comment'],
'address' => $order['address'],
'admin_comment' => $order['admin_comment'],
'status' => $order['status'],
'created_at' => $order['created_at'],
'updated_at' => $order['updated_at']
]);
} catch (Exception $e) {
http_response_code(404);
echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// update_comment.php
header('Content-Type: application/json');

try {
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['orderId']) || !isset($data['comment'])) {
throw new Exception('Missing required parameters');
}

$stmt = $pdo->prepare('UPDATE orders SET admin_comment = ? WHERE id = ?');
$stmt->execute([$data['comment'], $data['orderId']]);

echo json_encode(['success' => true]);
} catch (Exception $e) {
http_response_code(400);
echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// Вспомогательные функции для обработки заказов
function getStatusLabel($status) {
$labels = [
'pending' => 'Ожидает',
'processing' => 'В обработке',
'completed' => 'Завершен',
'cancelled' => 'Отменен'
];
return $labels[$status] ?? $status;
}

function formatPhone($phone) {
// Очищаем телефон от всего кроме цифр
$phone = preg_replace('/[^0-9]/', '', $phone);

// Форматируем номер в формат +7 (XXX) XXX-XX-XX
if (strlen($phone) === 11) {
return sprintf(
'+7 (%s) %s-%s-%s',
substr($phone, 1, 3),
substr($phone, 4, 3),
substr($phone, 7, 2),
substr($phone, 9, 2)
);
}

return $phone;
}

// Функция для проверки прав доступа администратора
function checkAdminAccess() {
session_start();
if (!isset($_SESSION['admin_id'])) {
http_response_code(403);
echo json_encode(['success' => false, 'error' => 'Unauthorized']);
exit;
}
}

// Добавляем проверку прав доступа ко всем API эндпоинтам
checkAdminAccess();
