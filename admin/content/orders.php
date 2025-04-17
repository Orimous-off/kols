<?php
define('ROOT_PATH', dirname(__DIR__));

include ROOT_PATH . '/../includes/db.php';
include ROOT_PATH . '/../includes/auth.php';
global $pdo;
$auth = new Auth($pdo);

session_start();
if (!$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Заказы</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-sidebar {
            width: 280px;
            height: 100vh;
            position: fixed;
            background-color: #23282d;
        }
        .admin-content {
            margin-left: 280px;
            min-height: 100vh;
            background-color: #f1f1f1;
        }
        .menu-item:hover {
            background-color: #32373c;
        }
        .submenu {
            background-color: #32373c;
            display: none;
        }
        .menu-item.active .submenu {
            display: block;
        }
        .menu-item.active {
            background-color: #0073aa;
        }
        .icon-input-group {
            position: relative;
        }
        .icon-input-group i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
        }
        .order-details {
            display: none;
        }
        .order-details.active {
            display: block;
        }
    </style>
</head>
<body>
<!-- Верхняя панель -->
<header class="fixed top-0 left-0 right-0 bg-white h-12 shadow-sm z-50 flex items-center px-4">
    <div class="ml-280">
        <span class="text-gray-600">Добро пожаловать, <?php echo $_SESSION['username']; ?></span>
    </div>
    <div class="ml-auto flex items-center space-x-4">
        <a href="/" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-home"></i> Сайт
        </a>
        <a href="/logout.php" class="text-red-600 hover:text-red-800">
            <i class="fas fa-sign-out-alt"></i> Выход
        </a>
    </div>
</header>

<!-- Боковое меню -->
<aside class="admin-sidebar pt-12">
    <div class="px-4 py-6">
        <h1 class="text-white text-xl font-semibold">Админ-панель</h1>
    </div>
    <nav class="text-gray-300">
        <div class="menu-item">
            <a href="/admin" class="flex items-center px-4 py-3">
                <i class="fas fa-tachometer-alt w-6"></i>
                <span>Панель управления</span>
            </a>
        </div>
        <div class="menu-group">
            <div class="menu-item">
                <a href="#" class="flex items-center justify-between px-4 py-3">
                    <div>
                        <i class="fas fa-file-alt w-6"></i>
                        <span>Контент</span>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu">
                    <a href="/admin-main-hero" class="block px-8 py-2 hover:bg-gray-700">Главный экран</a>
                    <a href="/admin-about-hero" class="block px-8 py-2 hover:bg-gray-700">О нас - Главный экран</a>
                    <a href="/admin-about-content" class="block px-8 py-2 hover:bg-gray-700">О нас - Контент</a>
                </div>
            </div>
        </div>
        <div class="menu-group">
            <div class="menu-item">
                <a href="#" class="flex items-center justify-between px-4 py-3">
                    <div>
                        <i class="fas fa-shopping-cart w-6"></i>
                        <span>Каталог</span>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu">
                    <a href="/admin-products" class="block px-8 py-2 hover:bg-gray-700">Товары</a>
                    <a href="/admin-categories" class="block px-8 py-2 hover:bg-gray-700">Категории</a>
                    <a href="/admin-manufacturers" class="block px-8 py-2 hover:bg-gray-700">Производители</a>
                </div>
            </div>
        </div>
        <div class="menu-item active">
            <a href="/admin-orders" class="flex items-center px-4 py-3">
                <i class="fas fa-shopping-basket w-6"></i>
                <span>Заказы</span>
            </a>
        </div>
        <div class="menu-group">
            <div class="menu-item">
                <a href="#" class="flex items-center justify-between px-4 py-3">
                    <div>
                        <i class="fas fa-cog w-6"></i>
                        <span>Настройки</span>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu">
                    <a href="/admin-company" class="block px-8 py-2 hover:bg-gray-700">Компания</a>
                    <a href="/admin-navigation" class="block px-8 py-2 hover:bg-gray-700">Навигация</a>
                    <a href="/admin-social-networks" class="block px-8 py-2 hover:bg-gray-700">Соц. сети</a>
                </div>
            </div>
        </div>
    </nav>
</aside>

<!-- Основной контент -->
<main class="admin-content pt-12">
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Управление заказами</h1>
            <p class="text-gray-600">Просмотр и редактирование информации о заказах</p>
        </div>

        <!-- Список заказов -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Список заказов</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Пользователь</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Телефон</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                        </tr>
                    </thead>
                    <tbody id="orders-list" class="bg-white divide-y divide-gray-200">
                        <!-- Здесь будет список заказов -->
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">Загрузка данных...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Детальная информация о заказе -->
        <div id="order-details" class="order-details bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Информация о заказе #<span id="order-id"></span></h2>
                <button id="close-details" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    <i class="fas fa-times"></i> Закрыть
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Информация о клиенте -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-lg mb-3">Информация о клиенте</h3>
                    <div class="space-y-2">
                        <p><strong>Имя:</strong> <span id="customer-name"></span></p>
                        <p><strong>Email:</strong> <span id="customer-email"></span></p>
                        <p><strong>Телефон:</strong> <span id="customer-phone"></span></p>
                    </div>
                </div>
                
                <!-- Информация о заказе -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-lg mb-3">Информация о заказе</h3>
                    <div class="space-y-2">
                        <p><strong>Дата создания:</strong> <span id="order-date"></span></p>
                        <p><strong>Статус:</strong> <span id="order-status-display"></span></p>
                        <p><strong>Общая сумма:</strong> <span id="order-amount"></span> ₽</p>
                    </div>
                </div>
            </div>
            
            <!-- Товары в заказе -->
            <div class="mb-6">
                <h3 class="font-semibold text-lg mb-3">Товары в заказе</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цвет</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Количество</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Скидка</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Итого</th>
                            </tr>
                        </thead>
                        <tbody id="order-items" class="bg-white divide-y divide-gray-200">
                            <!-- Здесь будут товары в заказе -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Редактирование заказа -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-lg mb-3">Редактирование заказа</h3>
                <form method="post" action="/admin/api/edit-order.php" id="edit-order-form">
                    <input type="hidden" id="edit-order-id">
                    <div class="mb-4">
                        <label for="edit-status" class="block text-sm font-medium text-gray-700 mb-1">Статус заказа</label>
                        <select id="edit-status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2">
                            <option value="pending">Ожидание (pending)</option>
                            <option value="processing">В обработке (processing)</option>
                            <option value="shipped">Отправлен (shipped)</option>
                            <option value="delivered">Доставлен (delivered)</option>
                            <option value="cancelled">Отменен (cancelled)</option>
                            <option value="completed">Завершен (completed)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="edit-comment" class="block text-sm font-medium text-gray-700 mb-1">Комментарий к заказу</label>
                        <textarea id="edit-comment" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-save mr-2"></i> Сохранить изменения
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    // Управление подменю
    document.querySelectorAll('.menu-item > a').forEach(item => {
        item.addEventListener('click', (e) => {
            const menuItem = e.currentTarget.parentElement;
            if (menuItem.querySelector('.submenu')) {
                e.preventDefault();
                menuItem.classList.toggle('active');
            }
        });
    });

    // Уведомления
    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded shadow-lg ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white z-50`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Форматирование даты
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('ru-RU', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Получение статуса заказа с переводом
    function getStatusText(status) {
        const statusMap = {
            'pending': 'Ожидание',
            'processing': 'В обработке',
            'shipped': 'Отправлен',
            'delivered': 'Доставлен',
            'cancelled': 'Отменен',
            'completed': 'Завершен'
        };
        return statusMap[status] || status;
    }

    // Получение класса для статуса
    function getStatusClass(status) {
        const statusClassMap = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'processing': 'bg-blue-100 text-blue-800',
            'shipped': 'bg-purple-100 text-purple-800',
            'delivered': 'bg-green-100 text-green-800',
            'cancelled': 'bg-red-100 text-red-800',
            'completed': 'bg-gray-100 text-gray-800'
        };
        return statusClassMap[status] || 'bg-gray-100 text-gray-800';
    }

    // Загрузка списка заказов
    function loadOrders() {
        fetch('/admin/api/orders.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const ordersTable = document.getElementById('orders-list');
                    if (data.orders.length === 0) {
                        ordersTable.innerHTML = `
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">Заказы не найдены</td>
                            </tr>
                        `;
                        return;
                    }
                    
                    ordersTable.innerHTML = '';
                    data.orders.forEach(order => {
                        const statusClass = getStatusClass(order.status);
                        const statusText = getStatusText(order.status);
                        
                        ordersTable.innerHTML += `
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">${order.order_id}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${order.username}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${order.phone}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${parseFloat(order.total_amount).toLocaleString('ru-RU')} ₽</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                        ${statusText}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">${formatDate(order.created_at)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button data-order-id="${order.order_id}" class="view-order text-blue-600 hover:text-blue-900 mr-2">
                                        <i class="fas fa-eye"></i> Просмотр
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    // Добавляем обработчики для кнопок просмотра
                    document.querySelectorAll('.view-order').forEach(button => {
                        button.addEventListener('click', () => {
                            const orderId = button.dataset.orderId;
                            loadOrderDetails(orderId);
                        });
                    });
                } else {
                    showNotification('error', data.message || 'Ошибка загрузки заказов');
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                showNotification('error', 'Ошибка загрузки заказов');
            });
    }

    // Загрузка деталей заказа
    function loadOrderDetails(orderId) {
        fetch(`/admin/api/orders.php?action=get_order&order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const order = data.order;
                    const items = data.items;
                    
                    // Заполняем информацию о заказе
                    document.getElementById('order-id').textContent = order.order_id;
                    document.getElementById('customer-name').textContent = order.username;
                    document.getElementById('customer-email').textContent = order.email;
                    document.getElementById('customer-phone').textContent = order.phone;
                    document.getElementById('order-date').textContent = formatDate(order.created_at);
                    document.getElementById('order-status-display').textContent = getStatusText(order.status);
                    document.getElementById('order-amount').textContent = parseFloat(order.total_amount).toLocaleString('ru-RU');
                    
                    // Заполняем форму редактирования
                    document.getElementById('edit-order-id').value = order.order_id;
                    document.getElementById('edit-status').value = order.status;
                    document.getElementById('edit-comment').value = order.comment || '';
                    
                    // Заполняем товары
                    const itemsTable = document.getElementById('order-items');
                    itemsTable.innerHTML = '';
                    items.forEach(item => {
                        const totalPrice = parseFloat(item.price) * item.quantity;
                        const discountAmount = totalPrice * (parseFloat(item.discount_percentage) / 100);
                        const finalPrice = totalPrice - discountAmount;
                        
                        itemsTable.innerHTML += `
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">${item.name}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${item.color_name}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${item.quantity}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${parseFloat(item.price).toLocaleString('ru-RU')} ₽</td>
                                <td class="px-6 py-4 whitespace-nowrap">${parseFloat(item.discount_percentage) > 0 ? item.discount_percentage + '%' : '-'}</td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium">${finalPrice.toLocaleString('ru-RU')} ₽</td>
                            </tr>
                        `;
                    });
                    
                    // Показываем детали заказа
                    document.getElementById('order-details').classList.add('active');
                } else {
                    showNotification('error', data.message || 'Ошибка загрузки информации о заказе');
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                showNotification('error', 'Ошибка загрузки информации о заказе');
            });
    }

    // Закрытие деталей заказа
    document.getElementById('close-details').addEventListener('click', () => {
        document.getElementById('order-details').classList.remove('active');
    });

    // Отправка формы обновления заказа
    document.getElementById('edit-order-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const orderId = document.getElementById('edit-order-id').value;
        const status = document.getElementById('edit-status').value;
        const comment = document.getElementById('edit-comment').value;
        console.log({ action: 'update_order', order_id: orderId, status, comment });
        
        // Проверка данных перед отправкой
        if (!orderId) {
            showNotification('error', 'Ошибка: ID заказа не указан');
            return;
        }
        
        if (!status) {
            showNotification('error', 'Ошибка: выберите статус заказа');
            return;
        }
        
        // Блокируем кнопку на время отправки запроса
        const submitButton = document.querySelector('#edit-order-form button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Обновление...';
        
        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('status', status);
        formData.append('comment', comment);
        
        fetch('/admin/api/edit-order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Ошибка HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Разблокируем кнопку
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            
            if (data.success) {
                showNotification('success', data.message || 'Заказ успешно обновлен');
                // Обновляем список заказов и детали текущего заказа
                loadOrders();
                loadOrderDetails(orderId);
            } else {
                showNotification('error', data.message || 'Ошибка при обновлении заказа');
                console.error('Ошибка обновления заказа:', data);
            }
        })
        .catch(error => {
            // Разблокируем кнопку в случае ошибки
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            
            console.error('Ошибка запроса:', error);
            showNotification('error', 'Не удалось отправить запрос: проверьте соединение с сервером');
        });
    });

    // Загружаем заказы при загрузке страницы
    document.addEventListener('DOMContentLoaded', loadOrders);
</script>
</body>
</html>