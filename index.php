<?php
// Получаем запрошенный URI
$request = $_SERVER['REQUEST_URI'];

// Убираем возможные GET-параметры
$clean_request = strtok($request, '?');

// Простая маршрутизация
switch ($clean_request) {
    case '':
    case '/':
        include 'pages/home.php'; // Главная страница
        break;
    case '/about':
        include 'pages/about.php'; // Страница "О нас"
        break;
    case '/catalog':
        include 'pages/catalog.php'; // Страница "Каталог"
        break;
    case '/contacts':
        include 'pages/contacts.php'; // Страница "Контакты"
        break;
    case '/login':
        include 'pages/login.php';
        break;
    case '/profile':
        include 'pages/profile.php';
        break;
    case '/cart':
        include 'pages/cart.php';
        break;
    case '/product':
        include 'pages/product.php';
        break;
    case '/delivery':
        include 'pages/delivery.php';
        break;
    case '/payment':
        include 'pages/payment.php';
        break;
    case '/admin':
        include 'admin/index.php';
        break;
    case '/editor':
        include 'admin/edit-page.php';
        break;
    case '/orders':
        include 'admin/orders.php';
        break;
    default:
        http_response_code(404);
        include 'pages/not-found.php'; // Страница ошибки
        break;
}
