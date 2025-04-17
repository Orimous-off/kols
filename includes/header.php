<?php
ob_start();
include 'db.php';
global $pdo;

$request = $_SERVER['REQUEST_URI'];
$clean_request = strtok($request, '?');

$stmt = $pdo->query("SELECT title, link FROM navigation WHERE is_visible = 1 ORDER BY position ASC");
$menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM company_info LIMIT 1");
$companyInfo = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $companyInfo['name']?></title>
    <link rel="apple-touch-icon" href="/assets<?php echo $companyInfo['logo']?>" sizes="180x180">
    <link rel="icon" href="/assets<?php echo $companyInfo['logo']?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ledger&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="root">
        <nav class="container position-rel">
            <div class="navbar-menu">
                <ul>
                    <?php foreach ($menuItems as $item): ?>
                        <li>
                            <a
                                href="<?php echo $item['link']; ?>"
                                <?php if($clean_request == $item['link']) echo 'class="active"'?>>
                                <?php echo $item['title']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a href="/" class="logo">
                <?php echo $companyInfo['logoName']?>
            </a>
            <div class="row">
                <a href="/cart">
                    <img src="../assets/images/shopping-bag.svg" alt="">
                </a>
                <a href="/profile">
                    <img src="../assets/images/profile.svg" alt="">
                </a>
                <div class="burger-container">
                    <img src="../assets/images/burger.svg" alt="" class="burger">
                    <div class="burger-dropdown">
                        <ul>
                            <?php foreach ($menuItems as $item): ?>
                                <li>
                                    <a
                                        href="<?php echo $item['link']; ?>"
                                        <?php if($clean_request == $item['link']) echo 'class="active"'?>>
                                        <?php echo $item['title']; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li class="divider md:d-none"></li>
                            <li><a href="/delivery">Доставка</a></li>
                            <li><a href="/payment">Оплата</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>