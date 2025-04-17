<?php
// Получаем запрошенный URI
$request = $_SERVER['REQUEST_URI'];

// Убираем возможные GET-параметры
$clean_request = strtok($request, '?');

// Простая маршрутизация
switch ($clean_request) {
    case '':
    case '/':
        include 'pages/home.php';
        break;
    case '/about':
        include 'pages/about.php';
        break;
    case '/catalog':
        include 'pages/catalog.php';
        break;
    case '/contacts':
        include 'pages/contacts.php';
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
        include 'admin/';
        include 'admin/index.php';
        break;
    case '/admin-main-hero':
        include 'admin/content/main-hero.php';
        break;
    case '/admin-about-hero':
        include 'admin/content/about-hero.php';
        break;
    case '/admin-about-content':
        include 'admin/content/about-content.php';
        break;
    case '/admin-products':
        include 'admin/content/products.php';
        break;
    case '/admin-categories':
        include 'admin/content/categories.php';
        break;
    case '/admin-manufacturers':
        include 'admin/content/manufacturers.php';
        break;
    case '/admin-orders':
        include 'admin/content/orders.php';
        break;
    case '/admin-company':
        include 'admin/content/company.php';
        break;
    case '/admin-navigation':
        include 'admin/content/navigation.php';
        break;
    case '/admin-social-networks':
        include 'admin/content/social-networks.php';
        break;
//    default:
//        http_response_code(404);
//        include 'pages/not-found.php'; // Страница ошибки
//        break;
}
