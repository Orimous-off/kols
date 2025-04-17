<?php
define('ROOT_PATH', dirname(__DIR__));

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

include ROOT_PATH . '/../includes/db.php';
include ROOT_PATH . '/../includes/auth.php';
global $pdo;
$auth = new Auth($pdo);

session_start();
if (!$auth->isAdmin()) {
    header('Location: /login');
    exit;
}

// Fetch all navigation items, ordered by position
$stmt = $pdo->query("SELECT * FROM navigation ORDER BY position");
$navigation_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Навигация</title>
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
        .sortable {
            list-style-type: none;
            padding: 0;
        }
        .sortable li {
            cursor: move;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 8px;
            padding: 10px;
            transition: background-color 0.2s;
        }
        .sortable li:hover {
            background-color: #f7fafc;
        }
        .sortable .drag-handle {
            margin-right: 10px;
            color: #a0aec0;
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
        <div class="menu-item">
            <a href="/admin-orders" class="flex items-center px-4 py-3">
                <i class="fas fa-shopping-basket w-6"></i>
                <span>Заказы</span>
            </a>
        </div>
        <div class="menu-group">
            <div class="menu-item active">
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
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-2xl font-semibold mb-6">Редактирование навигации</h2>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <!-- Список навигации -->
            <div>
                <h3 class="text-lg font-medium mb-4">Пункты навигации (перетаскивайте для изменения порядка)</h3>
                <?php if (empty($navigation_items)): ?>
                    <p class="text-gray-600">Нет пунктов навигации.</p>
                <?php else: ?>
                    <ul class="sortable">
                        <?php foreach ($navigation_items as $item): ?>
                            <li data-id="<?php echo $item['id']; ?>">
                                <form action="/admin/api/navigation.php" method="post" class="edit-navigation-form">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <div class="flex items-center space-x-4 mb-4">
                                        <i class="fas fa-grip-vertical drag-handle"></i>
                                        <div class="flex-1">
                                            <div class="mb-4">
                                                <label for="title_<?php echo $item['id']; ?>" class="block text-sm font-medium text-gray-700 mb-2">Название</label>
                                                <input type="text" id="title_<?php echo $item['id']; ?>" name="title" required
                                                       value="<?php echo htmlspecialchars($item['title']); ?>"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div class="mb-4">
                                                <label for="link_<?php echo $item['id']; ?>" class="block text-sm font-medium text-gray-700 mb-2">Ссылка</label>
                                                <input type="text" id="link_<?php echo $item['id']; ?>" name="link" required
                                                       value="<?php echo htmlspecialchars($item['link']); ?>"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div class="mb-4">
                                                <label for="is_visible_<?php echo $item['id']; ?>" class="block text-sm font-medium text-gray-700 mb-2">Видимость</label>
                                                <input type="checkbox" id="is_visible_<?php echo $item['id']; ?>" name="is_visible"
                                                    <?php echo $item['is_visible'] ? 'checked' : ''; ?>
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit"
                                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            Сохранить изменения
                                        </button>
                                    </div>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
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
        } text-white`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Инициализация сортировки
    $(function() {
        $('.sortable').sortable({
            handle: '.drag-handle',
            update: function(event, ui) {
                // Собираем порядок ID
                let order = [];
                $('.sortable li').each(function() {
                    order.push($(this).data('id'));
                });

                // Отправляем AJAX-запрос для обновления порядка
                $.ajax({
                    url: '/admin/api/navigation.php',
                    method: 'POST',
                    data: {
                        action: 'reorder',
                        order: order
                    },
                    success: function(response) {
                        try {
                            let data = JSON.parse(response);
                            if (data.success) {
                                showNotification('success', 'Порядок навигации обновлен');
                            } else {
                                showNotification('error', data.error || 'Ошибка при обновлении порядка');
                            }
                        } catch (e) {
                            showNotification('error', 'Ошибка обработки ответа сервера');
                        }
                    },
                    error: function() {
                        showNotification('error', 'Ошибка при отправке запроса');
                    }
                });
            }
        }).disableSelection();
    });
</script>
</body>
</html>