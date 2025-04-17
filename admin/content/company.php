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

$stmt = $pdo->query("SELECT * FROM company_info LIMIT 1");
$companyInfo = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="apple-touch-icon" href="/assets<?php echo $companyInfo['logo']?>" sizes="180x180">
    <link rel="icon" href="/assets<?php echo $companyInfo['logo']?>">
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
        <div class="menu-item">
            <a href="/admin" class="flex items-center px-4 py-3">
                <i class="fas fa-tachometer-alt w-6"></i>
                <span>Панель управления</span>
            </a>
        </div>
        <!-- Контент -->
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
        <!-- Каталог -->
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
        <!-- Заказы -->
        <div class="menu-item">
            <a href="/admin-orders" class="flex items-center px-4 py-3">
                <i class="fas fa-shopping-basket w-6"></i>
                <span>Заказы</span>
            </a>
        </div>
        <!-- Настройки -->
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
            <?php
            $stmt = $pdo->query("SELECT * FROM company_info LIMIT 1");
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            <h2 class="text-2xl font-semibold mb-6">Редактирование информации о компании</h2>

            <?php if (isset($error)): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="/admin/api/company.php" method="post" id="main-hero-form">
                <!--<form action="" class="ori" method="POST" enctype="multipart/form-data" data-ajax>-->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Название</label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="<?php echo htmlspecialchars($company['name'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Название сайта в шапке</label>
                    <input type="text"
                           id="logoName"
                           name="logoName"
                           value="<?php echo htmlspecialchars($company['logoName'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Почта</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
                    <input type="tel"
                           id="phone"
                           name="phone"
                           value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Почта</label>
                    <input type="text"
                           id="address"
                           name="address"
                           value="<?php echo htmlspecialchars($company['address'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-6">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Изображение</label>
                    <?php if (!empty($company['logo'])): ?>
                        <div class="mb-2">
                            <img src="/assets<?php echo htmlspecialchars($company['logo']); ?>"
                                 alt="Текущее изображение"
                                 class="max-w-md h-auto rounded">
                        </div>
                    <?php endif; ?>
                    <input type="file"
                           id="image"
                           name="image_url"
                           accept=".png,.svg"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Рекомендуемый размер: 500x500px. Допустимые форматы: SVG, PNG</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Сохранить изменения
                    </button>
                </div>
            </form>
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