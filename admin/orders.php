<?php
session_start();
include 'includes/db.php';
include 'includes/auth.php';
global $pdo;
$auth = new Auth($pdo);

// orders.php
session_start();
if (!$auth->checkAuth()) {
    header('Location: /login');
    exit;
}

// Получение параметров фильтрации и сортировки
$status = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';
$page = max(1, $_GET['page'] ?? 1);
$per_page = 20;

// Построение SQL запроса
$where = [];
$params = [];

if ($status !== 'all') {
    $where[] = 'status = ?';
    $params[] = $status;
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Получение общего количества заказов
$count_sql = "SELECT COUNT(*) FROM orders $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_orders = $stmt->fetchColumn();
$total_pages = ceil($total_orders / $per_page);

// Получение списка заказов
$offset = ($page - 1) * $per_page;
$sql = "SELECT * FROM orders 
        $where_clause 
        ORDER BY $sort $order 
        LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ТД-мебель</title>
    <link rel="apple-touch-icon" href="../assets/images/logo192.png" >
    <link rel="icon" href="../assets/images/logo192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center gap-3 mb-6">
        <a href="/admin" class="text-base font-medium text-gray-500 rounded-lg bg-gray-50 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white">
            <i class="fa-solid fa-arrow-left"></i>
            Админ-панель
        </a>
        <h1 class="text-2xl font-bold">Управление заказами</h1>
    </div>
    <!-- Фильтры -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form class="flex flex-col sm:flex-row gap-4" method="GET">
            <select name="status" class="border rounded px-3 py-2 w-full sm:w-auto">
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Все статусы</option>
                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Ожидает</option>
                <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>В обработке</option>
                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Завершен</option>
                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Отменен</option>
            </select>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full sm:w-auto">
                Применить фильтры
            </button>
        </form>
    </div>

    <!-- Таблица заказов для больших экранов -->
    <div class="hidden md:block bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="?sort=id&order=<?= $sort === 'id' && $order === 'ASC' ? 'DESC' : 'ASC' ?>&status=<?= $status ?>">
                        ID <?= $sort === 'id' ? ($order === 'ASC' ? '↑' : '↓') : '' ?>
                    </a>
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Клиент
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="?sort=created_at&order=<?= $sort === 'created_at' && $order === 'ASC' ? 'DESC' : 'ASC' ?>&status=<?= $status ?>">
                        Дата <?= $sort === 'created_at' ? ($order === 'ASC' ? '↑' : '↓') : '' ?>
                    </a>
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Статус
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Действия
                </th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= htmlspecialchars($order['id']) ?>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($order['name']) ?>
                        </div>
                        <div class="text-sm text-gray-500">
                            <?= htmlspecialchars($order['phone']) ?>
                        </div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <select class="status-select border rounded px-2 py-1 text-sm w-full" data-order-id="<?= $order['id'] ?>">
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Ожидает</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>В обработке</option>
                            <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Завершен</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Отменен</option>
                        </select>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 view-details" data-order-id="<?= $order['id'] ?>">
                            Подробнее
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Карточки заказов для мобильных устройств -->
    <div class="md:hidden space-y-4">
        <?php foreach ($orders as $order): ?>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="text-lg font-medium">Заказ #<?= htmlspecialchars($order['id']) ?></div>
                        <div class="text-sm text-gray-500"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></div>
                    </div>
                    <button class="text-blue-600 hover:text-blue-900 view-details" data-order-id="<?= $order['id'] ?>">
                        Подробнее
                    </button>
                </div>
                <div class="space-y-2">
                    <div>
                        <div class="text-sm font-medium">Клиент:</div>
                        <div class="text-sm"><?= htmlspecialchars($order['name']) ?></div>
                    </div>
                    <div>
                        <div class="text-sm font-medium">Телефон:</div>
                        <div class="text-sm"><?= htmlspecialchars($order['phone']) ?></div>
                    </div>
                    <div>
                        <div class="text-sm font-medium">Статус:</div>
                        <select class="status-select border rounded px-2 py-1 text-sm w-full mt-1" data-order-id="<?= $order['id'] ?>">
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Ожидает</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>В обработке</option>
                            <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Завершен</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Отменен</option>
                        </select>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Пагинация -->
    <?php if ($total_pages > 1): ?>
        <div class="flex flex-wrap justify-center mt-6 gap-2">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a
                        href="?page=<?= $i ?>&status=<?= $status ?>&sort=<?= $sort ?>&order=<?= $order ?>"
                        class="px-3 py-1 border rounded text-sm <?= $page === $i ? 'bg-blue-500 text-white' : 'bg-white' ?>"
                >
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Модальное окно для деталей заказа -->
<div id="orderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="min-h-screen px-4 text-center">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full sm:max-w-2xl">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Детали заказа</h2>
                    <button class="close-modal text-gray-500 hover:text-gray-700">×</button>
                </div>
                <div id="orderDetails"></div>
                <div class="mt-4">
                    <label class="block mb-2">Адрес:</label>
                    <textarea
                            id="adminAddress"
                            class="w-full border rounded p-2"
                            rows="2"
                            placeholder="Введите адрес клиента"
                    ></textarea>
                </div>
                <div class="mt-4">
                    <label class="block mb-2">Комментарий администратора:</label>
                    <textarea
                            id="adminComment"
                            class="w-full border rounded p-2"
                            rows="3"
                    ></textarea>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button
                        id="saveChanges"
                        class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 sm:ml-3"
                >
                    Сохранить
                </button>
                <button
                        class="close-modal mt-3 sm:mt-0 w-full sm:w-auto px-4 py-2 border rounded"
                >
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Стили для уведомлений */
    #notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1060;
    }

    .notification {
        background: white;
        padding: 16px 24px;
        margin-bottom: 10px;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        font-size: 14px;
        min-width: 280px;
        max-width: calc(100vw - 40px);
        animation: slideIn 0.3s ease-out;
    }

    .notification-success {
        border-left: 4px solid #10B981;
        color: #065F46;
    }

    .notification-error {
        border-left: 4px solid #EF4444;
        color: #991B1B;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Адаптивные стили для модального окна */
    @media (max-width: 640px) {
        #orderModal .bg-white {
            margin: 1rem;
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
        }
    }
</style>
<script>
    // Обработка изменения статуса
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', async function() {
            const orderId = this.dataset.orderId;
            const status = this.value;

            try {
                const response = await fetch('/update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ orderId, status })
                });

                const data = await response.json();
                if (data.success) {
                    showNotification('Статус успешно обновлен', 'success');
                } else {
                    showNotification('Ошибка при обновлении статуса', 'error');
                }
            } catch (error) {
                showNotification('Произошла ошибка', 'error');
            }
        });
    });

    // Закрытие модального окна
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('orderModal').classList.add('hidden');
        });
    });

    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', async function() {
            const orderId = this.dataset.orderId;

            try {
                const response = await fetch(`/get_order_details.php?id=${orderId}`);
                const text = await response.text();

                const jsonStr = text.substring(0, text.indexOf('}{') + 1) || text;

                let data;
                try {
                    data = JSON.parse(jsonStr);
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Received data:', text);
                    showNotification('Ошибка при разборе ответа сервера', 'error');
                    return;
                }

                if (!data.success) {
                    throw new Error(data.error || 'Неизвестная ошибка');
                }

                const orderData = data;

                document.getElementById('orderDetails').innerHTML = `
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="font-bold">Клиент:</p>
                            <p>${orderData.name || '-'}</p>
                        </div>
                        <div>
                            <p class="font-bold">Телефон:</p>
                            <p>${orderData.phone || '-'}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="font-bold">Комментарий клиента:</p>
                            <p>${orderData.comment || '-'}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="font-bold">Дата создания:</p>
                            <p>${new Date(orderData.created_at).toLocaleString('ru-RU')}</p>
                        </div>
                    </div>
                `;

                document.getElementById('adminAddress').value = orderData.address || '';
                document.getElementById('adminComment').value = orderData.admin_comment || '';
                document.getElementById('saveChanges').dataset.orderId = orderId;
                document.getElementById('orderModal').classList.remove('hidden');

            } catch (error) {
                console.error('Error:', error);
                showNotification(error.message || 'Ошибка при загрузке данных', 'error');
            }
        });
    });

    // Сохранение комментария администратора
    document.getElementById('saveChanges').addEventListener('click', async function() {
        const orderId = this.dataset.orderId;
        const comment = document.getElementById('adminComment').value;
        const address = document.getElementById('adminAddress').value;

        try {
            const response = await fetch('/update_status.php', { // используем тот же endpoint
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    orderId,
                    comment,
                    address
                })
            });

            const data = await response.json();
            if (data.success) {
                showNotification('Данные сохранены', 'success');
                document.getElementById('orderModal').classList.add('hidden');
            } else {
                showNotification('Ошибка при сохранении данных', 'error');
            }
        } catch (error) {
            showNotification('Произошла ошибка', 'error');
        }
    });
</script>
<script>
    function showNotification(message, type = 'success', duration = 3000) {
        // Создаем контейнер для уведомлений, если его еще нет
        let container = document.getElementById('notification-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-container';
            document.body.appendChild(container);
        }

        // Создаем уведомление
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        // Добавляем в контейнер
        container.appendChild(notification);

        // Удаляем через указанное время
        setTimeout(() => {
            notification.remove();
            if (container.children.length === 0) {
                container.remove();
            }
        }, duration);
    }
</script>

<style>
    #notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }

    .notification {
        background: white;
        padding: 16px 24px;
        margin-bottom: 10px;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        font-size: 14px;
        min-width: 280px;
        animation: slideIn 0.3s ease-out;
    }

    .notification-success {
        border-left: 4px solid #10B981;
        color: #065F46;
    }

    .notification-error {
        border-left: 4px solid #EF4444;
        color: #991B1B;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>
</body>
</html>