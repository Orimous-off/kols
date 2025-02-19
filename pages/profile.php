<?php
ob_start();
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';
global $pdo;

error_reporting(E_ALL);
ini_set('display_errors', 1);

$auth = new Auth($pdo);

if (!$auth->checkAuth()) {
    header('Location: /login');
    exit;
}
include 'includes/header.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<style>
    .profile-info {
        margin-bottom: 20px;
    }

    .profile-info div {
        margin-bottom: 10px;
    }

    .label {
        font-weight: bold;
        margin-right: 10px;
    }

    .logout-btn {
        background-color: #dc3545;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .logout-btn:hover {
        background-color: #c82333;
    }

</style>
<div class="container" style="margin-top: 20px;">
    <h2 class="text-center">Профиль пользователя</h2>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <div class="avatar-placeholder">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
            </div>
            <div class="profile-title">
                <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                <span class="member-since text-center">Клиент с <?php echo date('d.m.Y', strtotime($user['created_at'])); ?></span>
            </div>
            <form action="" method="post">
                <button type="submit" class="logout-btn">Выйти</button>
            </form>
        </div>

        <div class="profile-card">
            <div class="card-header">
                <h2>Личные данные</h2>
                <button class="edit-btn" onclick="toggleEditMode()">
                    <i class="fas fa-pencil"></i> Редактировать
                </button>
            </div>

            <!-- Форма просмотра -->
            <div id="viewMode" class="profile-details">
                <div class="detail-group">
                    <span class="detail-label">Имя пользователя:</span>
                    <span class="detail-value"><?= htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="detail-group">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?= htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="detail-group">
                    <span class="detail-label">Телефон:</span>
                    <span class="detail-value"><?= $user['phone_number'] ? htmlspecialchars($user['phone_number']) : 'Не указан'; ?></span>
                </div>
            </div>

            <!-- Форма редактирования -->
            <form id="editMode" class="profile-form" method="post" style="display: none;">
                <div class="form-group">
                    <label for="username">Имя пользователя:</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Телефон:</label>
                    <input type="text" id="phone" name="phone" value="<?= filter_var($user['phone_number'], FILTER_SANITIZE_NUMBER_INT); ?>">
                </div>
                <div class="form-group">
                    <label for="new_password">Новый пароль (оставьте пустым, если не хотите менять):</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Подтвердите новый пароль:</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="toggleEditMode()">Отмена</button>
                    <button type="submit" class="save-btn">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
    <?php
    // Add this code after fetching user data and before the HTML

    // Fetch user's orders
    $stmt = $pdo->prepare("
    SELECT 
        o.order_id,
        o.total_amount,
        o.status,
        o.created_at,
        o.phone,
        COUNT(oi.order_item_id) as items_count
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Function to get order items
    function getOrderItems($pdo, $orderId) {
        $stmt = $pdo->prepare("
        SELECT 
            oi.*,
            p.name as product_name,
            pi.image_path
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        LEFT JOIN (
            SELECT product_id, image_path
            FROM product_images
            WHERE is_main_image = 1
        ) pi ON p.product_id = pi.product_id
        WHERE oi.order_id = ?
    ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add this HTML after the profile-info div and before the logout form
    ?>
    <div class="orders-section">
        <h2>Мои заказы</h2>
        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <p>У вас пока нет заказов</p>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header" onclick="toggleOrder(<?php echo $order['order_id']; ?>)">
                            <div class="order-summary">
                                <div class="order-basic-info">
                                    <span class="order-number">Заказ №<?php echo $order['order_id']; ?></span>
                                    <span class="order-date"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="order-status-price">
                                <span class="order-status <?php echo strtolower($order['status']); ?>">
                                    <?php
                                    $statusMap = [
                                        'pending' => 'В обработке',
                                        'processing' => 'Обрабатывается',
                                        'shipped' => 'Отправлен',
                                        'delivered' => 'Доставлен',
                                        'cancelled' => 'Отменён'
                                    ];
                                    echo $statusMap[$order['status']] ?? $order['status'];
                                    ?>
                                </span>
                                    <span class="order-price"><?php echo number_format($order['total_amount'], 2, ',', ' '); ?> ₽</span>
                                </div>
                            </div>
                            <div class="order-toggle">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div class="order-details" id="order-<?php echo $order['order_id']; ?>">
                            <div class="order-contact">
                                <p><strong>Телефон:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                            </div>
                            <div class="order-items">
                                <?php
                                $orderItems = getOrderItems($pdo, $order['order_id']);
                                foreach ($orderItems as $item):
                                    $finalPrice = $item['price'] * (1 - $item['discount_percentage'] / 100);
                                    ?>
                                    <div class="order-item">
                                        <div class="item-image">
                                            <img src="assets<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                        </div>
                                        <div class="item-info">
                                            <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                            <p class="item-color">Цвет: <?php echo htmlspecialchars($item['color_name']); ?></p>
                                            <p class="item-quantity">Количество: <?php echo $item['quantity']; ?></p>
                                        </div>
                                        <div class="item-price">
                                            <span class="final-price"><?php echo number_format($finalPrice, 2, ',', ' '); ?> ₽</span>
                                            <?php if ($item['discount_percentage'] > 0): ?>
                                                <span class="original-price"><?php echo number_format($item['price'], 2, ',', ' '); ?> ₽</span>
                                                <span class="discount">-<?php echo $item['discount_percentage']; ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <style>
        .orders-section {
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .orders-section h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .empty-orders {
            text-align: center;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            color: #666;
        }

        .order-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .order-header {
            padding: 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            transition: background-color 0.3s;
        }

        .order-header:hover {
            background: #f1f3f5;
        }

        .order-summary {
            flex-grow: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-basic-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .order-number {
            font-weight: bold;
            color: #333;
        }

        .order-date {
            color: #666;
            font-size: 0.9em;
        }

        .order-status-price {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px;
        }

        .order-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .order-status.pending { background: #fff3cd; color: #856404; }
        .order-status.processing { background: #cce5ff; color: #004085; }
        .order-status.shipped { background: #d4edda; color: #155724; }
        .order-status.delivered { background: #d1e7dd; color: #0f5132; }
        .order-status.cancelled { background: #f8d7da; color: #721c24; }

        .order-price {
            font-weight: bold;
            color: #333;
        }

        .order-toggle {
            margin-left: 15px;
        }

        .order-toggle i {
            transition: transform 0.3s;
        }

        .order-details {
            display: none;
            padding: 15px;
            border-top: 1px solid #eee;
        }

        .order-contact {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .order-items {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .order-item {
            display: grid;
            grid-template-columns: 80px 1fr auto;
            gap: 15px;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .item-image img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .item-info h4 {
            margin: 0 0 5px 0;
            font-size: 1em;
        }

        .item-color, .item-quantity {
            margin: 0;
            color: #666;
            font-size: 0.9em;
        }

        .item-price {
            text-align: right;
        }

        .final-price {
            display: block;
            font-weight: bold;
            color: #333;
        }

        .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 0.9em;
        }

        .discount {
            color: #dc3545;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .order-summary {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .order-status-price {
                align-items: flex-start;
            }

            .order-item {
                grid-template-columns: 60px 1fr;
            }

            .item-price {
                grid-column: 1 / -1;
                text-align: left;
            }
        }
    </style>
    <script>
        function toggleOrder(orderId) {
            const detailsEl = document.getElementById(`order-${orderId}`);
            const chevronEl = detailsEl.parentElement.querySelector('.fa-chevron-down');

            if (detailsEl.style.display === 'block') {
                detailsEl.style.display = 'none';
                chevronEl.style.transform = 'rotate(0deg)';
            } else {
                detailsEl.style.display = 'block';
                chevronEl.style.transform = 'rotate(180deg)';
            }
        }
    </script>
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .profile-avatar {
            position: relative;
            width: 120px;
            height: 120px;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .avatar-placeholder {
            width: 100%;
            height: 100%;
            background: #4CAF50;
            color: white;
            font-size: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .change-avatar-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #4CAF50;
            color: #4CAF50;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .change-avatar-btn:hover {
            background: #4CAF50;
            color: #fff;
        }

        .profile-title {
            flex-grow: 1;
        }

        .profile-title h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }

        .member-since {
            color: #666;
            display: flex;
            justify-content: center;
            font-size: 0.9em;
        }

        .profile-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .card-header h2 {
            margin: 0;
            color: #333;
            font-size: 20px;
        }

        .edit-btn {
            padding: 8px 15px;
            background: transparent;
            border: 2px solid #4CAF50;
            color: #4CAF50;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .edit-btn:hover {
            background: #4CAF50;
            color: white;
        }

        .profile-details {
            display: grid;
            gap: 15px;
        }

        .detail-group {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 10px;
            align-items: center;
        }

        .detail-label {
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            color: #333;
        }

        .profile-form {
            display: grid;
            gap: 15px;
        }

        .form-group {
            display: grid;
            gap: 5px;
        }

        .form-group label {
            color: #666;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #4CAF50;
            outline: none;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 10px;
        }

        .cancel-btn {
            padding: 8px 15px;
            background: transparent;
            border: 2px solid #dc3545;
            color: #dc3545;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .cancel-btn:hover {
            background: #dc3545;
            color: white;
        }

        .save-btn {
            padding: 8px 15px;
            background: #4CAF50;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .save-btn:hover {
            background: #45a049;
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .detail-group {
                grid-template-columns: 1fr;
            }

            .detail-label {
                color: #666;
                font-size: 0.9em;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions button {
                width: 100%;
            }
        }
    </style>

    <script src="https://unpkg.com/imask"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация маски для телефона
            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                IMask(phoneInput, {
                    mask: '+{7} (000) 000-00-00'
                });
            }

            // Обработка формы редактирования
            const editForm = document.getElementById('editMode');
            editForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Проверка паролей
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                if (newPassword !== confirmPassword) {
                    alert('Пароли не совпадают');
                    return;
                }

                const formData = new FormData(editForm);
                const response = await fetch('includes/autoUpdate/update_profile.php', {
                    method: 'POST',
                    body: formData
                });

                const responseText = await response.text();
                console.log('Ответ сервера:', responseText);

// Извлечение JSON из текста
                const jsonMatch = responseText.match(/{.*}/);
                if (jsonMatch) {
                    try {
                        const result = JSON.parse(jsonMatch[0]);
                        if (result.success) {
                            location.reload();
                        } else {
                            alert(result.message || 'Ошибка при обновлении профиля');
                        }
                    } catch (error) {
                        console.error('Ошибка парсинга:', error);
                        alert('Ошибка обработки ответа');
                    }
                } else {
                    alert('Не удалось обработать ответ сервера');
                }
            });
        });

        function toggleEditMode() {
            const viewMode = document.getElementById('viewMode');
            const editMode = document.getElementById('editMode');
            const editBtn = document.querySelector('.edit-btn');

            if (viewMode.style.display !== 'none') {
                viewMode.style.display = 'none';
                editMode.style.display = 'grid';
                editBtn.style.display = 'none';
            } else {
                viewMode.style.display = 'grid';
                editMode.style.display = 'none';
                editBtn.style.display = 'flex';
            }
        }
    </script>
</div>
<?php
include 'includes/footer.php';
?>