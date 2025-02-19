<?php
session_start();
include '../includes/db.php';
require_once '../includes/auth.php';
global $pdo;
$auth = new Auth($pdo);

// orders.php
session_start();
if (!$auth->isAdmin()) {
    header('Location: /login');
    exit;
}

$section = $_GET['section'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
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
        <div class="menu-item <?php echo $section === 'dashboard' ? 'active' : ''; ?>">
            <a href="?section=dashboard" class="flex items-center px-4 py-3">
                <i class="fas fa-tachometer-alt w-6"></i>
                <span>Панель управления</span>
            </a>
        </div>

        <!-- Контент -->
        <div class="menu-group">
            <div class="menu-item <?php echo in_array($section, ['main-hero', 'about-hero', 'about-content']) ? 'active' : ''; ?>">
                <a href="#" class="flex items-center justify-between px-4 py-3">
                    <div>
                        <i class="fas fa-file-alt w-6"></i>
                        <span>Контент</span>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu">
                    <a href="?section=main-hero" class="block px-8 py-2 hover:bg-gray-700">Главный экран</a>
                    <a href="?section=about-hero" class="block px-8 py-2 hover:bg-gray-700">О нас - Главный экран</a>
                    <a href="?section=about-content" class="block px-8 py-2 hover:bg-gray-700">О нас - Контент</a>
                </div>
            </div>
        </div>

        <!-- Каталог -->
        <div class="menu-group">
            <div class="menu-item <?php echo in_array($section, ['products', 'categories', 'manufacturers']) ? 'active' : ''; ?>">
                <a href="#" class="flex items-center justify-between px-4 py-3">
                    <div>
                        <i class="fas fa-shopping-cart w-6"></i>
                        <span>Каталог</span>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu">
                    <a href="?section=products" class="block px-8 py-2 hover:bg-gray-700">Товары</a>
                    <a href="?section=categories" class="block px-8 py-2 hover:bg-gray-700">Категории</a>
                    <a href="?section=manufacturers" class="block px-8 py-2 hover:bg-gray-700">Производители</a>
                </div>
            </div>
        </div>

        <!-- Заказы -->
        <div class="menu-item <?php echo $section === 'orders' ? 'active' : ''; ?>">
            <a href="?section=orders" class="flex items-center px-4 py-3">
                <i class="fas fa-shopping-basket w-6"></i>
                <span>Заказы</span>
            </a>
        </div>

        <!-- Пользователи -->
        <div class="menu-item <?php echo $section === 'users' ? 'active' : ''; ?>">
            <a href="?section=users" class="flex items-center px-4 py-3">
                <i class="fas fa-users w-6"></i>
                <span>Пользователи</span>
            </a>
        </div>

        <!-- Настройки -->
        <div class="menu-group">
            <div class="menu-item <?php echo in_array($section, ['company', 'navigation', 'social-networks']) ? 'active' : ''; ?>">
                <a href="#" class="flex items-center justify-between px-4 py-3">
                    <div>
                        <i class="fas fa-cog w-6"></i>
                        <span>Настройки</span>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu">
                    <a href="?section=company" class="block px-8 py-2 hover:bg-gray-700">Компания</a>
                    <a href="?section=navigation" class="block px-8 py-2 hover:bg-gray-700">Навигация</a>
                    <a href="?section=social-networks" class="block px-8 py-2 hover:bg-gray-700">Соц. сети</a>
                </div>
            </div>
        </div>
    </nav>
</aside>

<!-- Основной контент -->
<main class="admin-content pt-12">
    <div class="p-6">
        <?php include "sections/{$section}.php"; ?>
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

    // Обработка форм через AJAX
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('success', result.message);
                    if (result.reload) {
                        location.reload();
                    }
                } else {
                    showNotification('error', result.message);
                }
            } catch (error) {
                showNotification('error', 'Произошла ошибка при выполнении запроса');
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
</script>
</body>
</html>