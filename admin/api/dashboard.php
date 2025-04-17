<?php
global $pdo;
$db = $pdo;

// Получение статистики
$stats = [
    'orders' => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'products' => $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'revenue' => $db->query("SELECT SUM(total_amount) FROM orders")->fetchColumn()
];

// Последние заказы
$latestOrders = $db->query("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll();
?>
