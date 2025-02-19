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

<div class="space-y-6">
    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="rounded-full bg-blue-100 p-3">
                    <i class="fas fa-shopping-basket text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Заказы</h3>
                    <p class="text-2xl font-semibold"><?php echo $stats['orders']; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="rounded-full bg-green-100 p-3">
                    <i class="fas fa-box text-green-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Товары</h3>
                    <p class="text-2xl font-semibold"><?php echo $stats['products']; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="rounded-full bg-purple-100 p-3">
                    <i class="fas fa-users text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Пользователи</h3>
                    <p class="text-2xl font-semibold"><?php echo $stats['users']; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="rounded-full bg-yellow-100 p-3">
                    <i class="fas fa-ruble-sign text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Выручка</h3>
                    <p class="text-2xl font-semibold"><?php echo number_format($stats['revenue'], 2, '.', ' '); ?> ₽</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Последние заказы -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">Последние заказы</h2>
        </div>
        <div class="p-6">
            <table class="w-full">
                <thead>
                <tr class="text-left">
                    <th class="pb-4">ID</th>
                    <th class="pb-4">Пользователь</th>
                    <th class="pb-4">Сумма</th>
                    <th class="pb-4">Статус</th>
                    <th class="pb-4">Дата</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($latestOrders as $order): ?>
                    <tr class="border-t">
                        <td class="py-4"><?php echo $order['order_id']; ?></td>
                        <td class="py-4"><?php echo htmlspecialchars($order['username']); ?></td>
                        <td class="py-4"><?php echo number_format($order['total_amount'], 2, '.', ' '); ?> ₽</td>
                        <td class="py-4">
                        <span class="px-2 py-1 rounded text-sm
                            <?php echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' :
                            ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                        </td>
                        <td class="py-4"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>