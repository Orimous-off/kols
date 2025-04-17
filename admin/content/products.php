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

$stmt = $pdo->query("
        SELECT 
            p.*, 
            (SELECT COUNT(*) FROM product_images WHERE product_id = p.product_id) as images_count,
            (SELECT COUNT(*) FROM product_colors WHERE product_id = p.product_id) as colors_count
        FROM 
            products p
        ORDER BY p.updated_at DESC
    ");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем список категорий (предполагается, что есть таблица categories)
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем список производителей (предполагается, что есть таблица manufacturers)
$stmt = $pdo->query("SELECT * FROM manufacturers ORDER BY name");
$manufacturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getCategoryName($categoryId, $categories) {
    foreach ($categories as $category) {
        if ($category['category_id'] == $categoryId) {
            return $category['name'];
        }
    }
    return 'Нет категории';
}

// Функция для получения названия производителя по ID
function getManufacturerName($manufacturerId, $manufacturers) {
    foreach ($manufacturers as $manufacturer) {
        if ($manufacturer['manufacturer_id'] == $manufacturerId) {
            return $manufacturer['name'];
        }
    }
    return 'Нет производителя';
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
            <div class="menu-item active">
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
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold">Управление товарами</h2>
                <button id="add-product-btn" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Добавить товар
                </button>
            </div>

            <?php if (isset($error)): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Скидка</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Остаток</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Новинка</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Избранное</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цвета</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Изображения</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="11" class="px-6 py-4 text-center text-gray-500">Товары не найдены</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($product['product_id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo isset($categories) ? htmlspecialchars(getCategoryName($product['category_id'], $categories)) : 'Нет данных'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo number_format($product['price'], 2, ',', ' '); ?> ₽</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $product['discount_percentage'] > 0 ? htmlspecialchars($product['discount_percentage']) . '%' : '-'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($product['stock_quantity']); ?> шт.
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $product['is_new'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $product['is_new'] ? 'Да' : 'Нет'; ?>
                                </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $product['is_featured'] ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $product['is_featured'] ? 'Да' : 'Нет'; ?>
                                </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($product['colors_count']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($product['images_count']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button class="edit-product-btn px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                data-product-id="<?php echo htmlspecialchars($product['product_id']); ?>">
                                            Ред.
                                        </button>
                                        <button class="toggle-visibility-btn px-3 py-1 rounded focus:outline-none focus:ring-2 <?php echo $product['visibly'] ? 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500' : 'bg-green-500 text-white hover:bg-green-600 focus:ring-green-500'; ?>"
                                                data-product-id="<?php echo htmlspecialchars($product['product_id']); ?>"
                                                data-action="<?php echo $product['visibly'] ? 'hide' : 'show'; ?>">
                                            <?php echo $product['visibly'] ? 'Скрыть' : 'Показать'; ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="add-product-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">Добавление товара</h3>
                    <button id="close-add-modal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form method="post" id="add-product-form" class="space-y-4" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Название товара</label>
                            <input type="text" id="name" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Цена (₽)</label>
                            <input type="number" id="price" name="price" required min="0" step="0.01" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Категория</label>
                            <select id="category_id" name="category_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Выберите категорию</option>
                                <?php if(isset($categories)): ?>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div>
                            <label for="manufacturer_id" class="block text-sm font-medium text-gray-700">Производитель</label>
                            <select id="manufacturer_id" name="manufacturer_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Выберите производителя</option>
                                <?php if(isset($manufacturers)): ?>
                                    <?php foreach($manufacturers as $manufacturer): ?>
                                        <option value="<?php echo htmlspecialchars($manufacturer['manufacturer_id']); ?>">
                                            <?php echo htmlspecialchars($manufacturer['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div>
                            <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Общее количество на складе</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" required min="0" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="discount_percentage" class="block text-sm font-medium text-gray-700">Скидка (%)</label>
                            <input type="number" id="discount_percentage" name="discount_percentage" min="0" max="100" step="0.01" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="material" class="block text-sm font-medium text-gray-700">Материал</label>
                            <input type="text" id="material" name="material" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="dimensions" class="block text-sm font-medium text-gray-700">Размеры</label>
                            <input type="text" id="dimensions" name="dimensions" placeholder="например: 200x90x100 см" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700">Вес (кг)</label>
                            <input type="number" id="weight" name="weight" min="0" step="0.01" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="flex items-center h-full mt-6">
                            <input type="checkbox" id="is_new" name="is_new" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_new" class="ml-2 block text-sm text-gray-900">Новинка</label>
                        </div>

                        <div class="flex items-center h-full mt-6">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_featured" class="ml-2 block text-sm text-gray-900">Избранный товар</label>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Описание</label>
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <!-- Раздел для добавления цветов -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Цвета и количество</label>
                        <div id="color-container" class="space-y-2 mt-1">
                            <!-- Здесь будут динамически добавляться поля для цветов -->
                        </div>
                        <button type="button" id="add-color-btn" class="mt-2 px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Добавить цвет
                        </button>
                    </div>

                    <!-- Новый раздел для добавления изображений -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Изображения</label>
                        <div id="image-container" class="space-y-2 mt-1">
                            <!-- Здесь будут динамически добавляться поля для изображений -->
                        </div>
                        <button type="button" id="add-image-btn" class="mt-2 px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Добавить изображение
                        </button>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            // Функция для показа уведомлений
            function showNotification(type, message) {
                const notification = document.createElement('div');
                notification.className = `fixed top-5 right-5 p-4 rounded-md text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
                notification.textContent = message;
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 3000);
            }

            // Динамическое добавление полей для цветов
            document.getElementById('add-color-btn').addEventListener('click', function () {
                const colorContainer = document.getElementById('color-container');
                const colorEntry = document.createElement('div');
                colorEntry.className = 'color-entry flex items-center space-x-2';
                colorEntry.innerHTML = `
        <input type="text" name="colors[]" placeholder="Название цвета" class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <input type="number" name="color_quantities[]" placeholder="Количество" min="0" class="w-32 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <button type="button" class="remove-color-btn text-red-500 hover:text-red-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
                colorContainer.appendChild(colorEntry);

                colorEntry.querySelector('.remove-color-btn').addEventListener('click', function () {
                    colorEntry.remove();
                });
            });

            // Динамическое добавление полей для изображений
            document.getElementById('add-image-btn').addEventListener('click', function () {
                const imageContainer = document.getElementById('image-container');
                const imageEntry = document.createElement('div');
                imageEntry.className = 'image-entry flex items-center space-x-2';
                imageEntry.innerHTML = `
        <input type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp" class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <button type="button" class="remove-image-btn text-red-500 hover:text-red-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
                imageContainer.appendChild(imageEntry);

                imageEntry.querySelector('.remove-image-btn').addEventListener('click', function () {
                    imageEntry.remove();
                });
            });

            // Открытие модального окна
            document.getElementById('add-product-btn').addEventListener('click', function() {
                document.getElementById('add-product-form').reset();
                document.getElementById('product_id').value = '';
                document.getElementById('modal-title').textContent = 'Добавление товара';
                document.getElementById('add-product-modal').classList.remove('hidden');
            });

            // Закрытие модального окна
            document.getElementById('close-add-modal').addEventListener('click', function() {
                document.getElementById('add-product-modal').classList.add('hidden');
            });

            // AJAX для отправки формы
            document.getElementById('add-product-form').addEventListener('submit', function (e) {
                e.preventDefault();

                // Валидация: проверка, чтобы сумма количества цветов совпадала с stock_quantity
                const stockQuantity = parseInt(document.getElementById('stock_quantity').value) || 0;
                const colorQuantities = Array.from(document.getElementsByName('color_quantities[]'))
                    .map(input => parseInt(input.value) || 0);
                const totalColorQuantity = colorQuantities.reduce((sum, qty) => sum + qty, 0);

                if (colorQuantities.length > 0 && totalColorQuantity !== stockQuantity) {
                    showNotification('error', `Общее количество цветов (${totalColorQuantity}) не совпадает с общим количеством на складе (${stockQuantity})`);
                    return;
                }

                // Собираем данные формы
                const formData = new FormData(this);

                // Отправка данных через Fetch API
                fetch('/admin/api/products/add_product.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('success', data.message);
                            if (data.reload) {
                                setTimeout(() => location.reload(), 1000);
                            }
                            document.getElementById('add-product-modal').classList.add('hidden');
                        } else {
                            showNotification('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('error', 'Произошла ошибка при отправке данных');
                    });
            });
        </script>
        <!-- Модальное окно для редактирования товара -->
        <div id="edit-product-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="edit-modal-title">Редактирование товара</h3>
                    <button id="close-edit-modal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form method="post" id="edit-product-form" class="space-y-4" enctype="multipart/form-data">
                    <input type="hidden" id="edit-product-id" name="product_id">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit-name" class="block text-sm font-medium text-gray-700">Название товара</label>
                            <input type="text" id="edit-name" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="edit-price" class="block text-sm font-medium text-gray-700">Цена (₽)</label>
                            <input type="number" id="edit-price" name="price" required min="0" step="0.01" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="edit-category_id" class="block text-sm font-medium text-gray-700">Категория</label>
                            <select id="edit-category_id" name="category_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Выберите категорию</option>
                                <?php if(isset($categories)): ?>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div>
                            <label for="edit-manufacturer_id" class="block text-sm font-medium text-gray-700">Производитель</label>
                            <select id="edit-manufacturer_id" name="manufacturer_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Выберите производителя</option>
                                <?php if(isset($manufacturers)): ?>
                                    <?php foreach($manufacturers as $manufacturer): ?>
                                        <option value="<?php echo htmlspecialchars($manufacturer['manufacturer_id']); ?>">
                                            <?php echo htmlspecialchars($manufacturer['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div>
                            <label for="edit-stock_quantity" class="block text-sm font-medium text-gray-700">Общее количество на складе</label>
                            <input type="number" id="edit-stock_quantity" name="stock_quantity" required min="0" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="edit-discount_percentage" class="block text-sm font-medium text-gray-700">Скидка (%)</label>
                            <input type="number" id="edit-discount_percentage" name="discount_percentage" min="0" max="100" step="0.01" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="edit-material" class="block text-sm font-medium text-gray-700">Материал</label>
                            <input type="text" id="edit-material" name="material" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="edit-dimensions" class="block text-sm font-medium text-gray-700">Размеры</label>
                            <input type="text" id="edit-dimensions" name="dimensions" placeholder="например: 200x90x100 см" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="edit-weight" class="block text-sm font-medium text-gray-700">Вес (кг)</label>
                            <input type="number" id="edit-weight" name="weight" min="0" step="0.01" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="flex items-center h-full mt-6">
                            <input type="checkbox" id="edit-is_new" name="is_new" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="edit-is_new" class="ml-2 block text-sm text-gray-900">Новинка</label>
                        </div>

                        <div class="flex items-center h-full mt-6">
                            <input type="checkbox" id="edit-is_featured" name="is_featured" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="edit-is_featured" class="ml-2 block text-sm text-gray-900">Избранный товар</label>
                        </div>
                    </div>

                    <div>
                        <label for="edit-description" class="block text-sm font-medium text-gray-700">Описание</label>
                        <textarea id="edit-description" name="description" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <!-- Раздел для редактирования цветов -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Цвета и количество</label>
                        <div id="edit-color-container" class="space-y-2 mt-1">
                            <!-- Существующие цвета будут добавлены через JavaScript -->
                        </div>
                        <button type="button" id="edit-add-color-btn" class="mt-2 px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Добавить цвет
                        </button>
                    </div>

                    <!-- Раздел для редактирования изображений -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Изображения</label>
                        <div id="edit-image-container" class="space-y-2 mt-1">
                            <!-- Существующие изображения будут добавлены через JavaScript -->
                        </div>
                        <button type="button" id="edit-add-image-btn" class="mt-2 px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Добавить изображение
                        </button>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Функция для показа уведомлений
            function showNotification(type, message) {
                const notification = document.createElement('div');
                notification.className = `fixed top-5 right-5 p-4 rounded-md text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
                notification.textContent = message;
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 3000);
            }

            // Динамическое добавление полей для цветов (для редактирования)
            document.getElementById('edit-add-color-btn').addEventListener('click', function () {
                const colorContainer = document.getElementById('edit-color-container');
                const colorEntry = document.createElement('div');
                colorEntry.className = 'color-entry flex items-center space-x-2';
                colorEntry.innerHTML = `
        <input type="text" name="new_colors[]" placeholder="Название цвета" class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <input type="number" name="new_color_quantities[]" placeholder="Количество" min="0" class="w-32 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <button type="button" class="remove-color-btn text-red-500 hover:text-red-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
                colorContainer.appendChild(colorEntry);

                colorEntry.querySelector('.remove-color-btn').addEventListener('click', function () {
                    colorEntry.remove();
                });
            });

            // Динамическое добавление полей для новых изображений (для редактирования)
            document.getElementById('edit-add-image-btn').addEventListener('click', function () {
                const imageContainer = document.getElementById('edit-image-container');
                const imageEntry = document.createElement('div');
                imageEntry.className = 'image-entry flex items-center space-x-2';
                imageEntry.innerHTML = `
        <input type="file" name="new_images[]" accept=".jpg,.jpeg,.png,.webp" class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <button type="button" class="remove-image-btn text-red-500 hover:text-red-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
                imageContainer.appendChild(imageEntry);

                imageEntry.querySelector('.remove-image-btn').addEventListener('click', function () {
                    imageEntry.remove();
                });
            });

            // Открытие модального окна для редактирования
            document.querySelectorAll('.edit-product-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const productId = this.getAttribute('data-product-id');

                    // Запрос данных о товаре
                    fetch(`/admin/api/products/get_product.php?product_id=${productId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const product = data.product;

                                // Заполняем форму данными о товаре
                                document.getElementById('edit-product-id').value = product.product_id;
                                document.getElementById('edit-name').value = product.name;
                                document.getElementById('edit-price').value = product.price;
                                document.getElementById('edit-category_id').value = product.category_id || '';
                                document.getElementById('edit-manufacturer_id').value = product.manufacturer_id || '';
                                document.getElementById('edit-stock_quantity').value = product.stock_quantity;
                                document.getElementById('edit-discount_percentage').value = product.discount_percentage || '';
                                document.getElementById('edit-material').value = product.material || '';
                                document.getElementById('edit-dimensions').value = product.dimensions || '';
                                document.getElementById('edit-weight').value = product.weight || '';
                                document.getElementById('edit-is_new').checked = product.is_new == 1;
                                document.getElementById('edit-is_featured').checked = product.is_featured == 1;
                                document.getElementById('edit-description').value = product.description || '';

                                // Заполняем существующие цвета
                                const colorContainer = document.getElementById('edit-color-container');
                                colorContainer.innerHTML = '';
                                if (product.colors && product.colors.length > 0) {
                                    product.colors.forEach(color => {
                                        const colorEntry = document.createElement('div');
                                        colorEntry.className = 'color-entry flex items-center space-x-2';
                                        colorEntry.innerHTML = `
                                <input type="text" name="existing_colors[${color.color_id}]" value="${color.color_name}" class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <input type="number" name="existing_color_quantities[${color.color_id}]" value="${color.stock_quantity}" min="0" class="w-32 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <button type="button" class="remove-color-btn text-red-500 hover:text-red-700" data-color-id="${color.color_id}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            `;
                                        colorContainer.appendChild(colorEntry);

                                        colorEntry.querySelector('.remove-color-btn').addEventListener('click', function () {
                                            colorEntry.remove();
                                        });
                                    });
                                }

                                // Заполняем существующие изображения
                                const imageContainer = document.getElementById('edit-image-container');
                                imageContainer.innerHTML = '';
                                if (product.images && product.images.length > 0) {
                                    product.images.forEach(image => {
                                        const imageEntry = document.createElement('div');
                                        imageEntry.className = 'image-entry flex items-center space-x-2';
                                        imageEntry.innerHTML = `
                                <img src="/assets${image.image_path}" alt="Product Image" class="w-20 h-20 object-cover">
                                <input type="checkbox" name="is_main_image[${image.image_id}]" ${image.is_main_image ? 'checked' : ''} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="ml-2 text-sm text-gray-900">Основное</label>
                                <button type="button" class="remove-image-btn text-red-500 hover:text-red-700" data-image-id="${image.image_id}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            `;
                                        imageContainer.appendChild(imageEntry);

                                        imageEntry.querySelector('.remove-image-btn').addEventListener('click', function () {
                                            imageEntry.remove();
                                        });
                                    });
                                }

                                // Открываем модальное окно
                                document.getElementById('edit-product-modal').classList.remove('hidden');
                            } else {
                                showNotification('error', data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('error', 'Произошла ошибка при загрузке данных товара');
                        });
                });
            });

            // Закрытие модального окна для редактирования
            document.getElementById('close-edit-modal').addEventListener('click', function() {
                document.getElementById('edit-product-modal').classList.add('hidden');
            });

            // AJAX для отправки формы редактирования
            document.getElementById('edit-product-form').addEventListener('submit', function (e) {
                e.preventDefault();

                // Валидация: проверка, чтобы сумма количества цветов совпадала с stock_quantity
                const stockQuantity = parseInt(document.getElementById('edit-stock_quantity').value) || 0;
                const existingColorQuantities = Array.from(document.querySelectorAll('input[name^="existing_color_quantities"]'))
                    .map(input => parseInt(input.value) || 0);
                const newColorQuantities = Array.from(document.querySelectorAll('input[name="new_color_quantities[]"]'))
                    .map(input => parseInt(input.value) || 0);
                const totalColorQuantity = [...existingColorQuantities, ...newColorQuantities].reduce((sum, qty) => sum + qty, 0);

                if ((existingColorQuantities.length > 0 || newColorQuantities.length > 0) && totalColorQuantity !== stockQuantity) {
                    showNotification('error', `Общее количество цветов (${totalColorQuantity}) не совпадает с общим количеством на складе (${stockQuantity})`);
                    return;
                }

                const formData = new FormData(this);

                fetch('/admin/api/products/update_product.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('success', data.message);
                            if (data.reload) {
                                setTimeout(() => location.reload(), 1000);
                            }
                            document.getElementById('edit-product-modal').classList.add('hidden');
                        } else {
                            showNotification('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('error', 'Произошла ошибка при обновлении данных');
                    });
            });
        </script>
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
        notification.className = `fixed top-20 right-4 p-4 rounded shadow-lg ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white`;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    document.querySelectorAll('.toggle-visibility-btn').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.getAttribute('data-product-id');
            const action = this.getAttribute('data-action');

            fetch('/admin/api/products/toggle_visibility.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&action=${action}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message);

                        // Обновляем текст и стиль кнопки
                        this.setAttribute('data-action', data.new_action);
                        if (data.new_action === 'hide') {
                            this.textContent = 'Скрыть';
                            this.classList.remove('bg-green-500', 'hover:bg-green-600', 'focus:ring-green-500');
                            this.classList.add('bg-yellow-500', 'hover:bg-yellow-600', 'focus:ring-yellow-500');
                        } else {
                            this.textContent = 'Показать';
                            this.classList.remove('bg-yellow-500', 'hover:bg-yellow-600', 'focus:ring-yellow-500');
                            this.classList.add('bg-green-500', 'hover:bg-green-600', 'focus:ring-green-500');
                        }
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('error', 'Произошла ошибка при изменении видимости');
                });
        });
    });
</script>
</body>
</html>